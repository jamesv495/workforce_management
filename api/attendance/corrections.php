<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function respond(
    bool $success,
    string $message = '',
    int $status = 200,
    array $extra = []
): never {
    http_response_code($status);

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        )
    );

    exit;
}

try {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        requireRole('admin');

        $stmt = $pdo->query("
            SELECT
                ac.id,
                ac.employee_id,
                ac.attendance_record_id,
                ac.correction_date,
                ac.requested_clock_in,
                ac.requested_clock_out,
                ac.reason,
                ac.attachment,
                ac.status,
                ac.reviewed_by,
                ac.reviewed_at,
                ac.created_at,

                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,

                ar.time_in AS current_clock_in,
                ar.time_out AS current_clock_out

            FROM attendance_corrections ac

            INNER JOIN employees e
                ON e.employee_id = ac.employee_id

            LEFT JOIN attendance_records ar
                ON ar.id = ac.attendance_record_id

            ORDER BY
                CASE
                    WHEN ac.status = 'pending' THEN 0
                    ELSE 1
                END,
                ac.created_at DESC,
                ac.id DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];

        foreach ($rows as $row) {

            $statusMap = [
                'pending' => 'PENDING',
                'approved' => 'APPROVED',
                'rejected' => 'REJECTED'
            ];

            $data[] = [
                'id' => (int)$row['id'],
                'employeeId' => $row['employee_id'],
                'employee' => $row['employee_name'],
                'date' => $row['correction_date'],
                'clockin' => $row['current_clock_in'],
                'clockout' => $row['current_clock_out'],
                'requestedClockin' => $row['requested_clock_in'],
                'requestedClockout' => $row['requested_clock_out'],
                'reason' => $row['reason'] ?? '',
                'attachment' => $row['attachment'] ?? '',
                'status' => $statusMap[$row['status']] ?? 'PENDING'
            ];
        }

        respond(
            true,
            '',
            200,
            [
                'data' => $data
            ]
        );
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
            trim((string)($input['action'] ?? ''));

        /*
         * Employee/Manager can submit a correction request.
         */
        if ($action === 'create') {

    $user = requireLogin();

    $role = (string)($user['role'] ?? '');

    if (!in_array($role, ['employee', 'manager'], true)) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Only employees and managers can submit attendance corrections.'
        ]);

        exit;
    }

    $employeeId =
        trim(
            (string)($user['employee_id'] ?? '')
        );

            if ($employeeId === '') {
                respond(
                    false,
                    'Employee session not found.',
                    401
                );
            }

            $correctionDate =
                trim(
                    (string)($input['correction_date'] ?? '')
                );

            $requestedClockIn =
                trim(
                    (string)($input['requested_clock_in'] ?? '')
                );

            $requestedClockOut =
                trim(
                    (string)($input['requested_clock_out'] ?? '')
                );

            $reason =
                trim(
                    (string)($input['reason'] ?? '')
                );

            if (
                $correctionDate === '' ||
                $reason === ''
            ) {
                respond(
                    false,
                    'Correction date and reason are required.',
                    422
                );
            }

            $dateCheck =
                DateTime::createFromFormat(
                    'Y-m-d',
                    $correctionDate
                );

            if (
                !$dateCheck ||
                $dateCheck->format('Y-m-d') !== $correctionDate
            ) {
                respond(
                    false,
                    'Invalid correction date.',
                    422
                );
            }

            $clockInValue = null;
            $clockOutValue = null;

            if ($requestedClockIn !== '') {
                $clockInDate =
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $requestedClockIn
                    );

                if (!$clockInDate) {
                    respond(
                        false,
                        'Invalid requested clock-in time.',
                        422
                    );
                }

                $clockInValue =
                    $clockInDate->format('Y-m-d H:i:s');
            }

            if ($requestedClockOut !== '') {
                $clockOutDate =
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $requestedClockOut
                    );

                if (!$clockOutDate) {
                    respond(
                        false,
                        'Invalid requested clock-out time.',
                        422
                    );
                }

                $clockOutValue =
                    $clockOutDate->format('Y-m-d H:i:s');
            }

            if (
                $clockInValue === null &&
                $clockOutValue === null
            ) {
                respond(
                    false,
                    'At least one corrected time is required.',
                    422
                );
            }

            /*
             * Get the attendance record for the date.
             */
            $attendanceStmt = $pdo->prepare("
                SELECT id
                FROM attendance_records
                WHERE employee_id = :employee_id
                  AND attendance_date = :attendance_date
                ORDER BY id DESC
                LIMIT 1
            ");

            $attendanceStmt->execute([
                ':employee_id' => $employeeId,
                ':attendance_date' => $correctionDate
            ]);

            $attendanceRecord =
                $attendanceStmt->fetch(
                    PDO::FETCH_ASSOC
                );

            /*
             * Do not allow multiple pending corrections
             * for the same employee/date.
             */
            $pendingStmt = $pdo->prepare("
                SELECT id
                FROM attendance_corrections
                WHERE employee_id = :employee_id
                  AND correction_date = :correction_date
                  AND status = 'pending'
                LIMIT 1
            ");

            $pendingStmt->execute([
                ':employee_id' => $employeeId,
                ':correction_date' => $correctionDate
            ]);

            if ($pendingStmt->fetch()) {
                respond(
                    false,
                    'A pending attendance correction already exists for this date.',
                    409
                );
            }

            $insert = $pdo->prepare("
                INSERT INTO attendance_corrections (
                    employee_id,
                    attendance_record_id,
                    correction_date,
                    requested_clock_in,
                    requested_clock_out,
                    reason,
                    status
                )
                VALUES (
                    :employee_id,
                    :attendance_record_id,
                    :correction_date,
                    :requested_clock_in,
                    :requested_clock_out,
                    :reason,
                    'pending'
                )
            ");

            $insert->execute([
                ':employee_id' =>
                    $employeeId,

                ':attendance_record_id' =>
                    $attendanceRecord
                        ? (int)$attendanceRecord['id']
                        : null,

                ':correction_date' =>
                    $correctionDate,

                ':requested_clock_in' =>
                    $clockInValue,

                ':requested_clock_out' =>
                    $clockOutValue,

                ':reason' =>
                    $reason
            ]);

            respond(
                true,
                'Attendance correction request submitted successfully.',
                201,
                [
                    'id' =>
                        (int)$pdo->lastInsertId()
                ]
            );
        }

        /*
         * Admin approves or rejects.
         */
        if (
            $action === 'approve' ||
            $action === 'reject'
        ) {

            $adminUser =
                requireRole('admin');

            $accountId =
                (int)($adminUser['id'] ?? 0);

            $correctionId =
                (int)($input['correction_id'] ?? 0);

            if ($correctionId <= 0) {
                respond(
                    false,
                    'Invalid correction request.',
                    422
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT *
                FROM attendance_corrections
                WHERE id = :id
                FOR UPDATE
            ");

            $stmt->execute([
                ':id' => $correctionId
            ]);

            $correction =
                $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$correction) {
                $pdo->rollBack();

                respond(
                    false,
                    'Attendance correction request not found.',
                    404
                );
            }

            if ($correction['status'] !== 'pending') {
                $pdo->rollBack();

                respond(
                    false,
                    'This attendance correction has already been processed.',
                    409
                );
            }

            if ($action === 'approve') {

                $attendanceRecordId =
                    $correction['attendance_record_id']
                    ? (int)$correction['attendance_record_id']
                    : 0;

                if ($attendanceRecordId <= 0) {

                    $recordStmt = $pdo->prepare("
                        SELECT id
                        FROM attendance_records
                        WHERE employee_id = :employee_id
                          AND attendance_date = :attendance_date
                        ORDER BY id DESC
                        LIMIT 1
                        FOR UPDATE
                    ");

                    $recordStmt->execute([
                        ':employee_id' =>
                            $correction['employee_id'],

                        ':attendance_date' =>
                            $correction['correction_date']
                    ]);

                    $record =
                        $recordStmt->fetch(
                            PDO::FETCH_ASSOC
                        );

                    if (!$record) {
                        $pdo->rollBack();

                        respond(
                            false,
                            'No attendance record exists for the requested correction date.',
                            422
                        );
                    }

                    $attendanceRecordId =
                        (int)$record['id'];
                }

                $updateParts = [];
                $params = [
                    ':id' => $attendanceRecordId
                ];

                if (
                    $correction['requested_clock_in'] !== null
                ) {
                    $updateParts[] =
                        'time_in = :requested_clock_in';

                    $params[':requested_clock_in'] =
                        $correction['requested_clock_in'];
                }

                if (
                    $correction['requested_clock_out'] !== null
                ) {
                    $updateParts[] =
                        'time_out = :requested_clock_out';

                    $params[':requested_clock_out'] =
                        $correction['requested_clock_out'];
                }

                if (!$updateParts) {
                    $pdo->rollBack();

                    respond(
                        false,
                        'No corrected attendance time was supplied.',
                        422
                    );
                }

                $updateParts[] =
                    "source = 'manual'";

                $updateParts[] =
                    "updated_at = NOW()";

                $attendanceUpdate = $pdo->prepare("
                    UPDATE attendance_records
                    SET
                        " . implode(
                            ",\n                        ",
                            $updateParts
                        ) . "
                    WHERE id = :id
                ");

                $attendanceUpdate->execute(
                    $params
                );
            }

            $status =
                $action === 'approve'
                    ? 'approved'
                    : 'rejected';

            $reviewStmt = $pdo->prepare("
                UPDATE attendance_corrections
                SET
                    status = :status,
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ");

            $reviewStmt->execute([
                ':status' =>
                    $status,

                ':reviewed_by' =>
                    $accountId,

                ':id' =>
                    $correctionId
            ]);

            $pdo->commit();

            respond(
                true,
                $action === 'approve'
                    ? 'Attendance correction approved.'
                    : 'Attendance correction rejected.'
            );
        }

        respond(
            false,
            'Invalid attendance correction action.',
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
        'Attendance correction API failed: ' .
        $e->getMessage()
    );

    respond(
        false,
        'Unable to process attendance correction.',
        500
    );
}