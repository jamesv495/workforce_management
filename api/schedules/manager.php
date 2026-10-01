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

function validDateString(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);

    return $parsed !== false &&
           $parsed->format('Y-m-d') === $date;
}

function parseShift(string $shift): array
{
    $shift = trim($shift);

    if (
        $shift === '' ||
        in_array(strtoupper($shift), ['OFF', 'LEAVE', 'ABSENT'], true)
    ) {
        return [
            'shift_name' => strtoupper($shift ?: 'OFF'),
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
        $startMin  = isset($matches[2]) && $matches[2] !== ''
            ? (int)$matches[2]
            : 0;

        $endHour = (int)$matches[3];
        $endMin  = isset($matches[4]) && $matches[4] !== ''
            ? (int)$matches[4]
            : 0;

        /*
         * Convert 8-5 style values to 24-hour time.
         * Existing UI uses 8-5, 9-6 and 10-7.
         */
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

try {

    /*
     * Find manager employee record.
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
     * GET
     * ------------------------------------------------------------
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $weekStart = trim(
            (string)($_GET['week_start'] ?? '')
        );

        error_log(
    'MANAGER SCHEDULE WEEK RECEIVED: ' . $weekStart
);

        if ($weekStart === '') {
            $weekStart = date(
                'Y-m-d',
                strtotime('monday this week')
            );
        }

        if (!validDateString($weekStart)) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Invalid week start date.'
            ]);

            exit;
        }

        $startDate = new DateTime($weekStart);

$dayOfWeek = (int)$startDate->format('N');

if ($dayOfWeek !== 1) {
    $startDate->modify(
        '-' . ($dayOfWeek - 1) . ' days'
    );
}

error_log(
    'MANAGER SCHEDULE WEEK START USED: ' .
    $startDate->format('Y-m-d')
);

        $endDate = clone $startDate;
        $endDate->modify('+4 days');

        $stmt = $pdo->prepare("
            SELECT
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.position_name,

                s.schedule_date,
                s.shift_name,
                s.start_time,
                s.end_time,
                s.status

            FROM employees e

            LEFT JOIN schedules s
                ON s.employee_id = e.employee_id
                AND s.schedule_date BETWEEN :start_date AND :end_date
                AND s.id = (
                    SELECT MAX(s2.id)
                    FROM schedules s2
                    WHERE s2.employee_id = s.employee_id
                      AND s2.schedule_date = s.schedule_date
                )

            WHERE e.manager_id = :manager_id
              AND e.employment_status <> 'terminated'

            ORDER BY
                e.employee_id ASC,
                s.schedule_date ASC
        ");

        $stmt->execute([
            ':start_date' => $startDate->format('Y-m-d'),
            ':end_date' => $endDate->format('Y-m-d'),
            ':manager_id' => (int)$manager['id']
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $employees = [];

        foreach ($rows as $row) {

            $employeeId = (string)$row['employee_id'];

            if (!isset($employees[$employeeId])) {

                $name = trim(
                    ($row['first_name'] ?? '') . ' ' .
                    ($row['middle_name'] ?? '') . ' ' .
                    ($row['last_name'] ?? '')
                );

                $employees[$employeeId] = [
                    'employeeId' => $employeeId,
                    'name' => strtoupper($name),
                    'position' => (string)(
                        $row['position_name'] ?? ''
                    ),
                    'mon' => 'OFF',
                    'tue' => 'OFF',
                    'wed' => 'OFF',
                    'thu' => 'OFF',
                    'fri' => 'OFF'
                ];
            }

            if (!empty($row['schedule_date'])) {

                $scheduleDate = new DateTime(
                    $row['schedule_date']
                );

                $dayKey = [
                    1 => 'mon',
                    2 => 'tue',
                    3 => 'wed',
                    4 => 'thu',
                    5 => 'fri'
                ][(int)$scheduleDate->format('N')] ?? null;

                if ($dayKey) {
                    $employees[$employeeId][$dayKey] =
                        (string)($row['shift_name'] ?? 'OFF');
                }
            }
        }

        echo json_encode([
            'success' => true,
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $endDate->format('Y-m-d'),
            'week_label' =>
                $startDate->format('M j') .
                '-' .
                $endDate->format('j, Y'),
            'data' => array_values($employees)
        ]);

        exit;
    }

    /*
     * ------------------------------------------------------------
     * POST
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

    $employeeId = trim(
        (string)($input['employee_id'] ?? '')
    );

    $weekStart = trim(
        (string)($input['week_start'] ?? '')
    );

    $day = strtolower(
        trim((string)($input['day'] ?? ''))
    );

    $shift = trim(
        (string)($input['shift'] ?? '')
    );

    if ($employeeId === '' || $weekStart === '') {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Employee and week start are required.'
        ]);

        exit;
    }

    if (!validDateString($weekStart)) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid week start date.'
        ]);

        exit;
    }

    /*
     * Verify employee belongs to this manager.
     */
    $employeeStmt = $pdo->prepare("
        SELECT employee_id
        FROM employees
        WHERE employee_id = :employee_id
          AND manager_id = :manager_id
          AND employment_status <> 'terminated'
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId,
        ':manager_id' => (int)$manager['id']
    ]);

    if (!$employeeStmt->fetch()) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' =>
                'This employee is not assigned to your team.'
        ]);

        exit;
    }

    $startDate = new DateTime($weekStart);

    /*
     * Normalize to Monday.
     */
    $dayOfWeek = (int)$startDate->format('N');

    if ($dayOfWeek !== 1) {
        $startDate->modify(
            '-' . ($dayOfWeek - 1) . ' days'
        );
    }

    $days = [
        'mon' => 0,
        'tue' => 1,
        'wed' => 2,
        'thu' => 3,
        'fri' => 4
    ];

    $pdo->beginTransaction();

    try {

        /*
         * --------------------------------------------------------
         * ASSIGN ONE SHIFT
         * --------------------------------------------------------
         */
        if ($day !== '') {

            if (!isset($days[$day])) {
                throw new RuntimeException(
                    'Invalid weekday.'
                );
            }

            if ($shift === '') {
                throw new RuntimeException(
                    'Shift is required.'
                );
            }

            $scheduleDate = clone $startDate;

            if ($days[$day] > 0) {
                $scheduleDate->modify(
                    '+' . $days[$day] . ' days'
                );
            }

            $parsedShift = parseShift($shift);

            $deleteStmt = $pdo->prepare("
                DELETE FROM schedules
                WHERE employee_id = :employee_id
                  AND schedule_date = :schedule_date
            ");

            $deleteStmt->execute([
                ':employee_id' => $employeeId,
                ':schedule_date' =>
                    $scheduleDate->format('Y-m-d')
            ]);

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
                ':employee_id' => $employeeId,
                ':schedule_date' =>
                    $scheduleDate->format('Y-m-d'),
                ':shift_name' =>
                    $parsedShift['shift_name'],
                ':start_time' =>
                    $parsedShift['start_time'],
                ':end_time' =>
                    $parsedShift['end_time']
            ]);

        /*
         * --------------------------------------------------------
         * CREATE / REPLACE FULL MONDAY-FRIDAY SCHEDULE
         * --------------------------------------------------------
         */
        } else {

            $scheduleValues =
                $input['schedules'] ?? null;

            if (!is_array($scheduleValues)) {
                throw new RuntimeException(
                    'Schedule values are required.'
                );
            }

            foreach (
                ['mon', 'tue', 'wed', 'thu', 'fri']
                as $requiredDay
            ) {
                if (
                    !isset($scheduleValues[$requiredDay]) ||
                    trim((string)$scheduleValues[$requiredDay]) === ''
                ) {
                    throw new RuntimeException(
                        'A shift is required for ' .
                        strtoupper($requiredDay) . '.'
                    );
                }
            }

            $deleteStmt = $pdo->prepare("
                DELETE FROM schedules
                WHERE employee_id = :employee_id
                  AND schedule_date BETWEEN :start_date AND :end_date
            ");

            $endDate = clone $startDate;
            $endDate->modify('+4 days');

            $deleteStmt->execute([
                ':employee_id' => $employeeId,
                ':start_date' =>
                    $startDate->format('Y-m-d'),
                ':end_date' =>
                    $endDate->format('Y-m-d')
            ]);

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

            foreach (
                [
                    'mon' => 0,
                    'tue' => 1,
                    'wed' => 2,
                    'thu' => 3,
                    'fri' => 4
                ] as $dayKey => $offset
            ) {

                $scheduleDate = clone $startDate;

                if ($offset > 0) {
                    $scheduleDate->modify(
                        '+' . $offset . ' days'
                    );
                }

                $parsedShift = parseShift(
                    trim(
                        (string)$scheduleValues[$dayKey]
                    )
                );

                $insertStmt->execute([
                    ':employee_id' => $employeeId,
                    ':schedule_date' =>
                        $scheduleDate->format('Y-m-d'),
                    ':shift_name' =>
                        $parsedShift['shift_name'],
                    ':start_time' =>
                        $parsedShift['start_time'],
                    ':end_time' =>
                        $parsedShift['end_time']
                ]);
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Schedule saved successfully.'
        ]);

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    exit;


http_response_code(405);

echo json_encode([
    'success' => false,
    'message' => 'Unsupported request method.'
]);

} catch (Throwable $e) {

    error_log(
        'Manager schedule API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to process manager schedule request.'
    ]);
}