<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

$managerEmployeeId = trim(
    (string)($managerUser['employee_id'] ?? '')
);

$managerAccountId = (int)($managerUser['id'] ?? 0);

if ($managerEmployeeId === '' || $managerAccountId <= 0) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager session is invalid.'
    ]);

    exit;
}

function parseRequestedShift(string $shift): array
{
    $shift = trim($shift);

    if (
        $shift === '' ||
        strtoupper($shift) === 'OFF'
    ) {
        return [
            'shift_name' => $shift ?: 'OFF',
            'start_time' => null,
            'end_time' => null
        ];
    }

    if (
        preg_match(
            '/^(\d{1,2})(?::(\d{2}))?\s*-\s*(\d{1,2})(?::(\d{2}))?$/',
            $shift,
            $matches
        )
    ) {
        $startHour = (int)$matches[1];
        $startMin = $matches[2] !== ''
            ? (int)$matches[2]
            : 0;

        $endHour = (int)$matches[3];
        $endMin = $matches[4] !== ''
            ? (int)$matches[4]
            : 0;

        if ($startHour >= 1 && $startHour <= 7) {
            $startHour += 12;
        }

        if ($endHour >= 1 && $endHour <= 7) {
            $endHour += 12;
        }

        if (
            $startHour > 23 ||
            $endHour > 23 ||
            $startMin > 59 ||
            $endMin > 59
        ) {
            return [
                'shift_name' => $shift,
                'start_time' => null,
                'end_time' => null
            ];
        }

        return [
            'shift_name' => $shift,
            'start_time' => sprintf(
                '%02d:%02d:00',
                $startHour,
                $startMin
            ),
            'end_time' => sprintf(
                '%02d:%02d:00',
                $endHour,
                $endMin
            )
        ];
    }

    return [
        'shift_name' => $shift,
        'start_time' => null,
        'end_time' => null
    ];
}

function shiftStatusLabel(string $status): string
{
    return match ($status) {
        'approved' => 'Approved',
        'rejected' => 'Declined',
        default => 'Pending'
    };
}

try {

    /*
     * Find this manager's employee record.
     */
    $managerStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $managerStmt->execute([
        ':employee_id' => $managerEmployeeId
    ]);

    $manager = $managerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Manager employee record not found.'
        ]);

        exit;
    }

    /*
     * ------------------------------------------------------------
     * GET — Load shift requests
     * ------------------------------------------------------------
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $stmt = $pdo->prepare("
            SELECT
                sr.id,
                sr.employee_id,
                sr.requested_date,
                sr.requested_shift,
                sr.reason,
                sr.status,
                sr.reviewed_by,
                sr.reviewed_at,
                sr.created_at,

                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,

                e.position_name,

                s.shift_name AS current_shift

            FROM shift_requests sr

            INNER JOIN employees e
                ON e.employee_id = sr.employee_id

            LEFT JOIN schedules s
                ON s.employee_id = sr.employee_id
                AND s.schedule_date = sr.requested_date
                AND s.id = (
                    SELECT MAX(s2.id)
                    FROM schedules s2
                    WHERE s2.employee_id = sr.employee_id
                      AND s2.schedule_date = sr.requested_date
                )

            WHERE e.manager_id = :manager_id

            ORDER BY
                CASE
                    WHEN sr.status = 'pending' THEN 0
                    ELSE 1
                END,
                sr.created_at DESC,
                sr.id DESC
        ");

        $stmt->execute([
            ':manager_id' => (int)$manager['id']
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];

        foreach ($rows as $row) {

            $requestedDate =
                (string)$row['requested_date'];

            $dateObject = new DateTime(
                $requestedDate
            );

            $monday = clone $dateObject;

            $dayNumber = (int)$monday->format('N');

            if ($dayNumber !== 1) {
                $monday->modify(
                    '-' . ($dayNumber - 1) . ' days'
                );
            }

            $friday = clone $monday;
            $friday->modify('+4 days');

            $dayMap = [
                1 => 'mon',
                2 => 'tue',
                3 => 'wed',
                4 => 'thu',
                5 => 'fri'
            ];

            $dayLabel = $dayMap[$dayNumber] ?? '';

            $data[] = [
                'id' =>
                    (int)$row['id'],

                'employeeId' =>
                    (string)$row['employee_id'],

                'name' =>
                    strtoupper(
                        trim(
                            (string)$row['employee_name']
                        )
                    ),

                'position' =>
                    (string)(
                        $row['position_name'] ?? ''
                    ),

                'date' =>
                    $requestedDate,

                'week' =>
                    $monday->format('M j') .
                    '-' .
                    $friday->format('j, Y'),

                'day' =>
                    $dayLabel,

                'currentShift' =>
                    (string)(
                        $row['current_shift'] ?? ''
                    ),

                'requestedShift' =>
                    (string)$row['requested_shift'],

                'reason' =>
                    (string)(
                        $row['reason'] ?? ''
                    ),

                'status' =>
                    shiftStatusLabel(
                        (string)$row['status']
                    ),

                'createdAt' =>
                    (string)$row['created_at']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;
    }

    /*
     * ------------------------------------------------------------
     * POST — Approve or decline
     * ------------------------------------------------------------
     */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Only GET and POST requests are allowed.'
        ]);

        exit;
    }

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid request body.'
        ]);

        exit;
    }

    $requestId = (int)($input['id'] ?? 0);

    $action = strtolower(
        trim((string)($input['action'] ?? ''))
    );

    if ($requestId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid shift request ID.'
        ]);

        exit;
    }

    if (!in_array($action, ['approve', 'decline'], true)) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid shift request action.'
        ]);

        exit;
    }

    /*
     * Make sure this request belongs to this manager's team.
     */
    $requestStmt = $pdo->prepare("
        SELECT
            sr.id,
            sr.employee_id,
            sr.requested_date,
            sr.requested_shift,
            sr.reason,
            sr.status

        FROM shift_requests sr

        INNER JOIN employees e
            ON e.employee_id = sr.employee_id

        WHERE sr.id = :id
          AND e.manager_id = :manager_id

        LIMIT 1
    ");

    $requestStmt->execute([
        ':id' => $requestId,
        ':manager_id' => (int)$manager['id']
    ]);

    $request = $requestStmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Shift request was not found for your team.'
        ]);

        exit;
    }

    if ((string)$request['status'] !== 'pending') {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'This shift request has already been processed.'
        ]);

        exit;
    }

    $newStatus =
        $action === 'approve'
            ? 'approved'
            : 'rejected';

    $pdo->beginTransaction();

    try {

        if ($action === 'approve') {

            /*
             * Convert the requested shift to DB time values.
             */
            $parsed =
                parseRequestedShift(
                    (string)$request['requested_shift']
                );

            /*
             * Remove the existing schedule for this employee/date.
             */
            $deleteStmt = $pdo->prepare("
                DELETE FROM schedules
                WHERE employee_id = :employee_id
                  AND schedule_date = :schedule_date
            ");

            $deleteStmt->execute([
                ':employee_id' =>
                    $request['employee_id'],

                ':schedule_date' =>
                    $request['requested_date']
            ]);

            /*
             * Create the approved schedule.
             */
            $insertStmt = $pdo->prepare("
                INSERT INTO schedules (
                    employee_id,
                    schedule_date,
                    shift_name,
                    start_time,
                    end_time,
                    status
                )
                VALUES (
                    :employee_id,
                    :schedule_date,
                    :shift_name,
                    :start_time,
                    :end_time,
                    'scheduled'
                )
            ");

            $insertStmt->execute([
                ':employee_id' =>
                    $request['employee_id'],

                ':schedule_date' =>
                    $request['requested_date'],

                ':shift_name' =>
                    $parsed['shift_name'],

                ':start_time' =>
                    $parsed['start_time'],

                ':end_time' =>
                    $parsed['end_time']
            ]);
        }

        /*
         * Update the shift request itself.
         */
        $updateStmt = $pdo->prepare("
            UPDATE shift_requests
            SET
                status = :status,
                reviewed_by = :reviewed_by,
                reviewed_at = NOW()
            WHERE id = :id
              AND status = 'pending'
        ");

        $updateStmt->execute([
            ':status' => $newStatus,
            ':reviewed_by' => $managerAccountId,
            ':id' => $requestId
        ]);

        if ($updateStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'The shift request could not be updated.'
            );
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' =>
                $action === 'approve'
                    ? 'Shift request approved.'
                    : 'Shift request declined.'
        ]);

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    exit;
}

catch (Throwable $e) {

    error_log(
        'Manager shift request API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to process shift request.'
    ]);
}