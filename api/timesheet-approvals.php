<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session_guard.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

function respond(
    bool $ok,
    string $message = '',
    int $status = 200,
    array $extra = []
): never {
    http_response_code($status);

    echo json_encode(
        array_merge(
            [
                'ok' => $ok,
                'message' => $message
            ],
            $extra
        )
    );

    exit;
}

function formatPeriodLabel(
    string $start,
    string $end
): string {

    $startDate = new DateTime($start);
    $endDate = new DateTime($end);

    if (
        $startDate->format('Y-m') ===
        $endDate->format('Y-m')
    ) {
        return sprintf(
            '%s %d - %s %d, %d',
            $startDate->format('M'),
            (int)$startDate->format('j'),
            $endDate->format('M'),
            (int)$endDate->format('j'),
            (int)$endDate->format('Y')
        );
    }

    return sprintf(
        '%s %d - %s %d, %d',
        $startDate->format('M'),
        (int)$startDate->format('j'),
        $endDate->format('M'),
        (int)$endDate->format('j'),
        (int)$endDate->format('Y')
    );
}

function formatTimeValue(
    ?string $value
): string {

    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);

    if (!$timestamp) {
        return '—';
    }

    return date('g:i A', $timestamp);
}

function calculateDailyHours(
    ?string $timeIn,
    ?string $timeOut
): array {

    if (!$timeIn || !$timeOut) {
        return [
            'regular' => 0.0,
            'overtime' => 0.0
        ];
    }

    $start = strtotime($timeIn);
    $end = strtotime($timeOut);

    if (!$start || !$end || $end <= $start) {
        return [
            'regular' => 0.0,
            'overtime' => 0.0
        ];
    }

    $minutes = (int)floor(
        ($end - $start) / 60
    );

    /*
     * One-hour unpaid break.
     */
    $workedMinutes = max(
        0,
        $minutes - 60
    );

    $regularMinutes = min(
        480,
        $workedMinutes
    );

    $overtimeMinutes = max(
        0,
        $workedMinutes - 480
    );

    return [
        'regular' =>
            round($regularMinutes / 60, 2),

        'overtime' =>
            round($overtimeMinutes / 60, 2)
    ];
}

function uiStatus(
    string $databaseStatus,
    ?string $latestHistoryStatus
): string {

    $databaseStatus =
        strtolower(trim($databaseStatus));

    if ($databaseStatus === 'approved') {
        return 'Approved';
    }

    if ($databaseStatus === 'rejected') {
        return 'Rejected';
    }

    if (
        strtolower(
            trim((string)$latestHistoryStatus)
        ) === 'correction requested'
    ) {
        return 'Correction Requested';
    }

    return 'Pending';
}

try {

    /*
     * GET — load Timesheet Approvals.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        requireRole('admin');

        /*
         * Load the latest review for each employee/pay period.
         */
        $reviewStmt = $pdo->query("
            SELECT
                tr.id,
                tr.employee_id,
                tr.period_start,
                tr.period_end,
                tr.status,
                tr.reviewed_by,
                tr.reviewed_at,
                tr.remarks,
                tr.created_at,

                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,

                e.department_name

            FROM timesheet_reviews tr

            INNER JOIN employees e
                ON e.employee_id = tr.employee_id

            WHERE tr.id = (
                SELECT MAX(tr2.id)
                FROM timesheet_reviews tr2
                WHERE tr2.employee_id = tr.employee_id
                  AND tr2.period_start = tr.period_start
                  AND tr2.period_end = tr.period_end
            )

            ORDER BY
                tr.period_start DESC,
                tr.id DESC
        ");

        $reviewRows =
            $reviewStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        /*
         * Load latest history state for each review.
         */
        $historyStateStmt = $pdo->query("
            SELECT
                h.timesheet_review_id,
                h.new_status
            FROM timesheet_review_history h
            INNER JOIN (
                SELECT
                    timesheet_review_id,
                    MAX(id) AS latest_id
                FROM timesheet_review_history
                GROUP BY timesheet_review_id
            ) latest
                ON latest.latest_id = h.id
        ");

        $historyStateRows =
            $historyStateStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $historyState = [];

        foreach ($historyStateRows as $row) {
            $historyState[
                (int)$row['timesheet_review_id']
            ] =
                (string)$row['new_status'];
        }

        /*
         * Prepare daily-entry query.
         */
        $dailyStmt = $pdo->prepare("
            SELECT
                s.schedule_date,
                s.shift_name,
                s.start_time,
                s.end_time,

                a.time_in,
                a.time_out,
                a.status AS attendance_status

            FROM schedules s

            LEFT JOIN attendance_records a
                ON a.employee_id = s.employee_id
                AND a.attendance_date = s.schedule_date
                AND a.id = (
                    SELECT MAX(a2.id)
                    FROM attendance_records a2
                    WHERE a2.employee_id = s.employee_id
                      AND a2.attendance_date = s.schedule_date
                )

            WHERE s.employee_id = :employee_id
              AND s.schedule_date BETWEEN :period_start AND :period_end
              AND s.status <> 'cancelled'

            ORDER BY s.schedule_date ASC
        ");

        $leaveStmt = $pdo->prepare("
            SELECT id
            FROM leave_requests
            WHERE employee_id = :employee_id
              AND status = 'approved'
              AND :date_value BETWEEN start_date AND end_date
            LIMIT 1
        ");

        $timesheets = [];

        foreach ($reviewRows as $review) {

            $reviewId =
                (int)$review['id'];

            $employeeId =
                (string)$review['employee_id'];

            $periodStart =
                (string)$review['period_start'];

            $periodEnd =
                (string)$review['period_end'];

            $dailyStmt->execute([
                ':employee_id' =>
                    $employeeId,

                ':period_start' =>
                    $periodStart,

                ':period_end' =>
                    $periodEnd
            ]);

            $scheduleRows =
                $dailyStmt->fetchAll(
                    PDO::FETCH_ASSOC
                );

            /*
             * Index schedule/attendance rows by date.
             */
            $dailyByDate = [];

            foreach ($scheduleRows as $row) {
                $dailyByDate[
                    $row['schedule_date']
                ] = $row;
            }

            $startDate =
                new DateTime($periodStart);

            $endDate =
                new DateTime($periodEnd);

            $dailyEntries = [];

            $regularHours = 0.0;
            $overtimeHours = 0.0;

            for (
                $date = clone $startDate;
                $date <= $endDate;
                $date->modify('+1 day')
            ) {

                /*
                 * Timesheet contains weekdays only.
                 */
                $weekday =
                    (int)$date->format('N');

                if ($weekday >= 6) {
                    continue;
                }

                $dateValue =
                    $date->format('Y-m-d');

                $row =
                    $dailyByDate[$dateValue]
                    ?? null;

                $timeIn =
                    $row['time_in']
                    ?? null;

                $timeOut =
                    $row['time_out']
                    ?? null;

                $hours =
                    calculateDailyHours(
                        $timeIn,
                        $timeOut
                    );

                $regular =
                    $hours['regular'];

                $overtime =
                    $hours['overtime'];

                $regularHours +=
                    $regular;

                $overtimeHours +=
                    $overtime;

                $attendanceStatus =
                    '';

                if ($row) {

                    $rawStatus =
                        strtolower(
                            (string)(
                                $row['attendance_status']
                                ?? ''
                            )
                        );

                    if (
                        $rawStatus ===
                        'present'
                    ) {
                        $attendanceStatus =
                            'Present';

                    } elseif (
                        $rawStatus ===
                        'late'
                    ) {
                        $attendanceStatus =
                            'Late';

                    } elseif (
                        $rawStatus ===
                        'absent'
                    ) {
                        $attendanceStatus =
                            'Absent';

                    } elseif (
                        $rawStatus ===
                        'half_day'
                    ) {
                        $attendanceStatus =
                            'Present';

                    } elseif (
                        $rawStatus ===
                        'on_leave'
                    ) {
                        $attendanceStatus =
                            'Leave';
                    }
                }

                /*
                 * Approved leave overrides a missing
                 * attendance record.
                 */
                $leaveStmt->execute([
                    ':employee_id' =>
                        $employeeId,

                    ':date_value' =>
                        $dateValue
                ]);

                if ($leaveStmt->fetch()) {
                    $attendanceStatus =
                        'Leave';
                }

                if (
                    $attendanceStatus === ''
                ) {

                    $shiftName =
                        strtoupper(
                            trim(
                                (string)(
                                    $row['shift_name']
                                    ?? ''
                                )
                            )
                        );

                    if (
                        $row &&
                        $shiftName !== '' &&
                        $shiftName !== 'OFF'
                    ) {
                        $attendanceStatus =
                            'Missing Record';
                    } else {
                        $attendanceStatus =
                            'Absent';
                    }
                }

                $dailyEntries[] = [
                    'date' =>
                        $dateValue,

                    'timeIn' =>
                        formatTimeValue(
                            $timeIn
                        ),

                    'timeOut' =>
                        formatTimeValue(
                            $timeOut
                        ),

                    'break' =>
                        $timeIn && $timeOut
                            ? '1 hr'
                            : '—',

                    'regularHours' =>
                        $regular,

                    'overtime' =>
                        $overtime,

                    'attendanceStatus' =>
                        $attendanceStatus
                ];
            }

            $status =
                uiStatus(
                    (string)$review['status'],
                    $historyState[$reviewId]
                        ?? null
                );

            $rejectionReason = '';

            $correctionReason = '';

            if (
                $status === 'Rejected'
            ) {
                $rejectionReason =
                    (string)(
                        $review['remarks']
                        ?? ''
                    );
            }

            if (
                $status ===
                'Correction Requested'
            ) {
                $correctionReason =
                    (string)(
                        $review['remarks']
                        ?? ''
                    );
            }

            $timesheets[] = [
                'id' =>
                    (string)$reviewId,

                'avatar' => '',

                'employee' =>
                    trim(
                        (string)(
                            $review['employee_name']
                        )
                    ),

                'employeeId' =>
                    $employeeId,

                'department' =>
                    (string)(
                        $review['department_name']
                        ?? ''
                    ),

                'period' =>
                    formatPeriodLabel(
                        $periodStart,
                        $periodEnd
                    ),

                'regularHours' =>
                    round(
                        $regularHours,
                        2
                    ),

                'overtimeHours' =>
                    round(
                        $overtimeHours,
                        2
                    ),

                'status' =>
                    $status,

                'submittedDate' =>
                    $review['created_at']
                        ? date(
                            'F j, Y',
                            strtotime(
                                $review['created_at']
                            )
                        )
                        : '—',

                'rejectionReason' =>
                    $rejectionReason,

                'correctionReason' =>
                    $correctionReason,

                'dailyEntries' =>
                    $dailyEntries
            ];
        }

        /*
         * Approval history.
         */
        $historyStmt = $pdo->query("
            SELECT
                h.previous_status,
                h.new_status,
                h.action,
                h.reason,
                h.reviewed_at,

                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,

                CONCAT_WS(
                    ' ',
                    reviewer.first_name,
                    reviewer.middle_name,
                    reviewer.last_name
                ) AS reviewer_name

            FROM timesheet_review_history h

            INNER JOIN employees e
                ON e.employee_id = h.employee_id

            LEFT JOIN accounts ra
                ON ra.id = h.reviewed_by

            LEFT JOIN employees reviewer
                ON reviewer.employee_id = ra.employee_id

            ORDER BY
                h.reviewed_at DESC,
                h.id DESC
        ");

        $historyRows =
            $historyStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $history = [];

        foreach ($historyRows as $row) {

            $history[] = [
                'employee' =>
                    (string)(
                        $row['employee_name']
                        ?? ''
                    ),

                'action' =>
                    (string)(
                        $row['action']
                        ?? ''
                    ),

                'previousStatus' =>
                    (string)(
                        $row['previous_status']
                        ?? ''
                    ),

                'newStatus' =>
                    (string)(
                        $row['new_status']
                        ?? ''
                    ),

                'admin' =>
                    (string)(
                        $row['reviewer_name']
                        ?? 'Admin'
                    ),

                'timestamp' =>
                    $row['reviewed_at']
                        ? date(
                            'F j, Y - g:i A',
                            strtotime(
                                $row['reviewed_at']
                            )
                        )
                        : '—',

                'reason' =>
                    (string)(
                        $row['reason']
                        ?? ''
                    )
            ];
        }

        respond(
            true,
            '',
            200,
            [
                'timesheets' =>
                    $timesheets,

                'history' =>
                    $history
            ]
        );
    }

    /*
     * POST — approve/reject/correction.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $adminUser =
            requireRole('admin');

        $accountId =
            (int)(
                $adminUser['id'] ?? 0
            );

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($input)) {
            respond(
                false,
                'Invalid JSON request.',
                400
            );
        }

        $action =
            trim(
                (string)(
                    $input['action'] ?? ''
                )
            );

        /*
         * Bulk approval.
         */
        if ($action === 'bulk_approve') {

            $ids =
                $input['ids'] ?? [];

            if (!is_array($ids)) {
                respond(
                    false,
                    'Invalid timesheet list.',
                    422
                );
            }

            $changed = 0;

            $pdo->beginTransaction();

            foreach ($ids as $rawId) {

                $id = (int)$rawId;

                if ($id <= 0) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM timesheet_reviews
                    WHERE id = :id
                    FOR UPDATE
                ");

                $stmt->execute([
                    ':id' => $id
                ]);

                $review =
                    $stmt->fetch(
                        PDO::FETCH_ASSOC
                    );

                if (
                    !$review ||
                    $review['status'] !==
                        'pending'
                ) {
                    continue;
                }

                $update = $pdo->prepare("
                    UPDATE timesheet_reviews
                    SET
                        status = 'approved',
                        reviewed_by = :reviewed_by,
                        reviewed_at = NOW(),
                        remarks = 'Bulk approval by admin.',
                        updated_at = NOW()
                    WHERE id = :id
                ");

                $update->execute([
                    ':reviewed_by' =>
                        $accountId,

                    ':id' =>
                        $id
                ]);

                $historyInsert =
                    $pdo->prepare("
                        INSERT INTO timesheet_review_history (
                            timesheet_review_id,
                            employee_id,
                            period_start,
                            period_end,
                            previous_status,
                            new_status,
                            action,
                            reviewed_by,
                            reason
                        )
                        VALUES (
                            :review_id,
                            :employee_id,
                            :period_start,
                            :period_end,
                            'Pending',
                            'Approved',
                            'Timesheet Approved',
                            :reviewed_by,
                            'Bulk approval by admin.'
                        )
                    ");

                $historyInsert->execute([
                    ':review_id' =>
                        $id,

                    ':employee_id' =>
                        $review['employee_id'],

                    ':period_start' =>
                        $review['period_start'],

                    ':period_end' =>
                        $review['period_end'],

                    ':reviewed_by' =>
                        $accountId
                ]);

                $changed++;
            }

            $pdo->commit();

            respond(
                true,
                'Timesheets approved successfully.',
                200,
                [
                    'changed' =>
                        $changed
                ]
            );
        }

        /*
         * Single update.
         */
        if ($action === 'update') {

            $id =
                (int)(
                    $input['id'] ?? 0
                );

            $requestedStatus =
                trim(
                    (string)(
                        $input['status'] ?? ''
                    )
                );

            $reason =
                trim(
                    (string)(
                        $input['reason'] ?? ''
                    )
                );

            if ($id <= 0) {
                respond(
                    false,
                    'Invalid timesheet ID.',
                    422
                );
            }

            $allowed =
                [
                    'Approved',
                    'Rejected',
                    'Correction Requested'
                ];

            if (
                !in_array(
                    $requestedStatus,
                    $allowed,
                    true
                )
            ) {
                respond(
                    false,
                    'Invalid timesheet status.',
                    422
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT *
                FROM timesheet_reviews
                WHERE id = :id
                FOR UPDATE
            ");

            $stmt->execute([
                ':id' => $id
            ]);

            $review =
                $stmt->fetch(
                    PDO::FETCH_ASSOC
                );

            if (!$review) {

                $pdo->rollBack();

                respond(
                    false,
                    'Timesheet review not found.',
                    404
                );
            }

            if (
                in_array(
                    $review['status'],
                    ['approved', 'rejected'],
                    true
                )
            ) {

                $pdo->rollBack();

                respond(
                    false,
                    'This timesheet has already been processed.',
                    409
                );
            }

            /*
             * Determine current UI status.
             */
            $latestHistoryStmt =
                $pdo->prepare("
                    SELECT new_status
                    FROM timesheet_review_history
                    WHERE timesheet_review_id = :id
                    ORDER BY id DESC
                    LIMIT 1
                ");

            $latestHistoryStmt->execute([
                ':id' => $id
            ]);

            $latestHistory =
                $latestHistoryStmt->fetch(
                    PDO::FETCH_ASSOC
                );

            $previousStatus =
                uiStatus(
                    (string)$review['status'],
                    $latestHistory['new_status']
                        ?? null
                );

            if (
                $requestedStatus ===
                'Approved'
            ) {

                $databaseStatus =
                    'approved';

                $actionLabel =
                    'Timesheet Approved';

            } elseif (
                $requestedStatus ===
                'Rejected'
            ) {

                $databaseStatus =
                    'rejected';

                $actionLabel =
                    'Timesheet Rejected';

            } else {

                /*
                 * Correction Requested stays pending
                 * in the existing timesheet_reviews schema.
                 */
                $databaseStatus =
                    'pending';

                $actionLabel =
                    'Correction Requested';
            }

            $update =
                $pdo->prepare("
                    UPDATE timesheet_reviews
                    SET
                        status = :status,
                        reviewed_by = :reviewed_by,
                        reviewed_at = NOW(),
                        remarks = :remarks,
                        updated_at = NOW()
                    WHERE id = :id
                ");

            $update->execute([
                ':status' =>
                    $databaseStatus,

                ':reviewed_by' =>
                    $accountId,

                ':remarks' =>
                    $reason !== ''
                        ? $reason
                        : null,

                ':id' =>
                    $id
            ]);

            $historyInsert =
                $pdo->prepare("
                    INSERT INTO timesheet_review_history (
                        timesheet_review_id,
                        employee_id,
                        period_start,
                        period_end,
                        previous_status,
                        new_status,
                        action,
                        reviewed_by,
                        reason
                    )
                    VALUES (
                        :review_id,
                        :employee_id,
                        :period_start,
                        :period_end,
                        :previous_status,
                        :new_status,
                        :action,
                        :reviewed_by,
                        :reason
                    )
                ");

            $historyInsert->execute([
                ':review_id' =>
                    $id,

                ':employee_id' =>
                    $review['employee_id'],

                ':period_start' =>
                    $review['period_start'],

                ':period_end' =>
                    $review['period_end'],

                ':previous_status' =>
                    $previousStatus,

                ':new_status' =>
                    $requestedStatus,

                ':action' =>
                    $actionLabel,

                ':reviewed_by' =>
                    $accountId,

                ':reason' =>
                    $reason !== ''
                        ? $reason
                        : null
            ]);

            $pdo->commit();

            respond(
                true,
                'Timesheet approval saved successfully.'
            );
        }

        respond(
            false,
            'Invalid timesheet action.',
            400
        );
    }

    respond(
        false,
        'Only GET and POST requests are allowed.',
        405
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Timesheet approvals API failed: ' .
        $e->getMessage()
    );

    respond(
        false,
        'Unable to process timesheet approval.',
        500
    );
}