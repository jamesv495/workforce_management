<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$employeeUser = requireRole('employee');

$employeeId = trim(
    (string)($employeeUser['employee_id'] ?? '')
);

if ($employeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Employee session not found.'
    ]);

    exit;
}

try {

    /*
     * Current week: Monday through Sunday.
     */
    $today = new DateTime('today');

    $monday = clone $today;

    $dayNumber = (int)$monday->format('N');

    if ($dayNumber !== 1) {
        $monday->modify(
            '-' . ($dayNumber - 1) . ' days'
        );
    }

    $sunday = clone $monday;
    $sunday->modify('+6 days');

    /*
     * Get employee information.
     */
    $employeeStmt = $pdo->prepare("
        SELECT
            employee_id,
            first_name,
            middle_name,
            last_name,
            position_name
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Employee record not found.'
        ]);

        exit;
    }

    /*
     * Get the employee's schedules for the week.
     */
    $stmt = $pdo->prepare("
        SELECT
            id,
            schedule_date,
            shift_name,
            start_time,
            end_time,
            status
        FROM schedules
        WHERE employee_id = :employee_id
          AND schedule_date BETWEEN :start_date AND :end_date
          AND status <> 'cancelled'
        ORDER BY schedule_date ASC, id DESC
    ");

    $stmt->execute([
        ':employee_id' => $employeeId,
        ':start_date' => $monday->format('Y-m-d'),
        ':end_date' => $sunday->format('Y-m-d')
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Keep the latest schedule row for each date.
     */
    $byDate = [];

    foreach ($rows as $row) {

        $date = (string)$row['schedule_date'];

        if (!isset($byDate[$date])) {
            $byDate[$date] = $row;
        }
    }

    $days = [];

    $dateLabels = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];

    for (
        $date = clone $monday;
        $date <= $sunday;
        $date->modify('+1 day')
    ) {

        $isoDate = $date->format('Y-m-d');

        $dayNumber = (int)$date->format('N');

        $schedule = $byDate[$isoDate] ?? null;

        $shiftName = 'Day Off';

        if ($schedule && !empty($schedule['shift_name'])) {
            $shiftName = (string)$schedule['shift_name'];
        }

        $startTime = null;
        $endTime = null;

        if (
            $schedule &&
            !empty($schedule['start_time']) &&
            !empty($schedule['end_time'])
        ) {
            $startTime = substr(
                (string)$schedule['start_time'],
                0,
                5
            );

            $endTime = substr(
                (string)$schedule['end_time'],
                0,
                5
            );
        }

        $days[] = [
            'date' => $isoDate,
            'day' => $dateLabels[$dayNumber],
            'shortDay' => substr(
                $dateLabels[$dayNumber],
                0,
                3
            ),
            'dayNumber' => $dayNumber,
            'dateNumber' => (int)$date->format('j'),
            'shiftName' => $shiftName,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'position' =>
                (string)($employee['position_name'] ?? ''),
            'isOff' =>
                !$schedule ||
                strtoupper($shiftName) === 'OFF' ||
                strtoupper($shiftName) === 'DAY OFF'
        ];
    }

    echo json_encode([
        'success' => true,

        'employee' => [
            'employeeId' =>
                (string)$employee['employee_id'],

            'name' =>
                trim(
                    ($employee['first_name'] ?? '') . ' ' .
                    ($employee['middle_name'] ?? '') . ' ' .
                    ($employee['last_name'] ?? '')
                ),

            'position' =>
                (string)($employee['position_name'] ?? '')
        ],

        'weekStart' =>
            $monday->format('Y-m-d'),

        'weekEnd' =>
            $sunday->format('Y-m-d'),

        'weekLabel' =>
            $monday->format('F j') .
            '–' .
            $sunday->format('j, Y'),

        'days' => $days
    ]);

} catch (Throwable $e) {

    error_log(
        'Employee schedule API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load employee schedule.'
    ]);
}