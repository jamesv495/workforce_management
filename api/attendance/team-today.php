<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
$managerUser = requireRole('manager');

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only GET requests are allowed.'
    ]);
    exit;
}

try {
    /*
    | Find the manager's employee record using the logged-in account.
    */
    $managerStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $managerStmt->execute([
        ':employee_id' => $managerUser['employee_id'] ?? ''
    ]);

    $manager = $managerStmt->fetch();

    if (!$manager) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Manager employee record was not found.'
        ]);
        exit;
    }

    $sql = "
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.position_name,

            a.attendance_date,
            a.time_in,
            a.time_out,
            a.status AS attendance_status,

            s.shift_name,
            s.start_time,
            s.end_time,

            lr.status AS leave_status

        FROM employees e

        LEFT JOIN attendance_records a
            ON a.employee_id = e.employee_id
            AND a.attendance_date = CURDATE()

        LEFT JOIN schedules s
            ON s.employee_id = e.employee_id
            AND s.schedule_date = CURDATE()
            AND s.status <> 'cancelled'

        LEFT JOIN leave_requests lr
            ON lr.employee_id = e.employee_id
            AND CURDATE() BETWEEN lr.start_date AND lr.end_date
            AND lr.status = 'approved'

        WHERE e.manager_id = :manager_id
          AND e.employment_status <> 'terminated'

        ORDER BY e.employee_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':manager_id' => (int)$manager['id']
    ]);

    $rows = $stmt->fetchAll();

    $records = [];

    foreach ($rows as $row) {
        $name = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        if (!empty($row['leave_status'])) {
            $status = 'LEAVE';
        } elseif (!empty($row['attendance_status'])) {
            $status = strtoupper((string)$row['attendance_status']);
        } elseif (!empty($row['time_in'])) {
            $status = 'PRESENT';
        } else {
            $status = 'ABSENT';
        }

        $schedule = '—';

        if (
            !empty($row['start_time']) &&
            !empty($row['end_time'])
        ) {
            $schedule =
                date('g:i A', strtotime($row['start_time'])) .
                ' - ' .
                date('g:i A', strtotime($row['end_time']));
        } elseif (!empty($row['shift_name'])) {
            $schedule = (string)$row['shift_name'];
        }

        $records[] = [
            'id' => $row['employee_id'],
            'name' => strtoupper($name),
            'position' => (string)($row['position_name'] ?? ''),
            'schedule' => $schedule,
            'clockIn' => !empty($row['time_in'])
                ? date('g:i A', strtotime($row['time_in']))
                : '—',
            'clockOut' => !empty($row['time_out'])
                ? date('g:i A', strtotime($row['time_out']))
                : '—',
            'status' => $status
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $records
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager team attendance API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load team attendance.'
    ]);
}