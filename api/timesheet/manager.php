<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

$managerEmployeeId = trim(
    (string)($managerUser['employee_id'] ?? '')
);

if ($managerEmployeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager session not found.'
    ]);

    exit;
}

try {

    /* Find the logged-in manager's employee record. */
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
     * Existing Timesheets UI generates:
     * - last week
     * - current week
     * - today
     *
     * We therefore load from last Monday through today.
     */
    $today = new DateTime('today');

    $lastMonday = clone $today;

    $dayNumber = (int)$lastMonday->format('N');

    if ($dayNumber !== 1) {
        $lastMonday->modify(
            '-' . ($dayNumber - 1) . ' days'
        );
    }

    $lastMonday->modify('-7 days');

    $todayISO = $today->format('Y-m-d');
    $startISO = $lastMonday->format('Y-m-d');

    /*
     * Get schedules for employees assigned to this manager.
     *
     * The latest schedule row for each employee/date is used.
     */
    $stmt = $pdo->prepare("
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.position_name,

            s.id AS schedule_id,
            s.schedule_date,
            s.shift_name,
            s.start_time,
            s.end_time,
            s.status AS schedule_status,

            a.time_in,
            a.time_out,
            a.status AS attendance_status,
            a.notes AS attendance_notes

        FROM employees e

        INNER JOIN schedules s
            ON s.employee_id = e.employee_id
            AND s.schedule_date BETWEEN :start_date AND :end_date
            AND s.id = (
                SELECT MAX(s2.id)
                FROM schedules s2
                WHERE s2.employee_id = s.employee_id
                  AND s2.schedule_date = s.schedule_date
            )

        LEFT JOIN attendance_records a
            ON a.employee_id = e.employee_id
            AND a.attendance_date = s.schedule_date
            AND a.id = (
                SELECT MAX(a2.id)
                FROM attendance_records a2
                WHERE a2.employee_id = a.employee_id
                  AND a2.attendance_date = a.attendance_date
            )

        WHERE e.manager_id = :manager_id
          AND e.employment_status <> 'terminated'
          AND s.status <> 'cancelled'

        ORDER BY
            s.schedule_date DESC,
            e.employee_id ASC
    ");

    $stmt->execute([
        ':start_date' => $startISO,
        ':end_date' => $todayISO,
        ':manager_id' => (int)$manager['id']
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $records = [];

    foreach ($rows as $row) {

        $date = (string)$row['schedule_date'];

        $dateObject = new DateTime($date);

        /*
         * Match the existing JavaScript behavior:
         * skip Saturday/Sunday, except today.
         */
        $weekday = (int)$dateObject->format('N');

        if (
            $weekday >= 6 &&
            $date !== $todayISO
        ) {
            continue;
        }

        $employeeName = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $timeIn = null;
        $timeOut = null;

        if (!empty($row['time_in'])) {
            $timeIn = date(
                'H:i',
                strtotime($row['time_in'])
            );
        }

        if (!empty($row['time_out'])) {
            $timeOut = date(
                'H:i',
                strtotime($row['time_out'])
            );
        }

        $remarks = trim(
            (string)($row['attendance_notes'] ?? '')
        );

        if ($remarks === '') {

            if ($timeIn && $timeOut) {
                $remarks =
                    'Attendance record loaded from MySQL.';
            } elseif ($timeIn && !$timeOut) {

                $remarks =
                    $date === $todayISO
                        ? 'Currently on shift, no Time Out yet.'
                        : 'Time Out was not recorded.';

            } else {

                $remarks =
                    'No attendance record for this date.';
            }
        }

        $records[] = [
            'id' =>
                $row['employee_id'] . '_' . $date,

            'employeeId' =>
                (string)$row['employee_id'],

            'employeeName' =>
                strtoupper($employeeName),

            'position' =>
                (string)($row['position_name'] ?? ''),

            'date' =>
                $date,

            'scheduleStart' =>
                !empty($row['start_time'])
                    ? substr((string)$row['start_time'], 0, 5)
                    : null,

            'scheduleEnd' =>
                !empty($row['end_time'])
                    ? substr((string)$row['end_time'], 0, 5)
                    : null,

            'timeIn' =>
                $timeIn,

            'timeOut' =>
                $timeOut,

            'breakMin' =>
                60,

            'remarks' =>
                $remarks
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $records
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager timesheet API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load manager timesheets.'
    ]);
}