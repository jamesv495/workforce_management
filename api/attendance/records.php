<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
requireRole('admin');

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

$startDate = trim((string)($_GET['start_date'] ?? ''));
$endDate   = trim((string)($_GET['end_date'] ?? ''));
$department = trim((string)($_GET['department'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$employeeId = trim((string)($_GET['employee_id'] ?? ''));

if ($startDate === '') {
    $startDate = date('Y-m-01');
}

if ($endDate === '') {
    $endDate = date('Y-m-d');
}

try {

    $sql = "
        SELECT
            a.id,
            a.employee_id,
            a.attendance_date,
            a.time_in,
            a.time_out,
            a.status,
            a.source,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.department_name,
            s.shift_name,
            s.start_time,
            s.end_time
        FROM attendance_records a
        INNER JOIN employees e
            ON e.employee_id = a.employee_id
        LEFT JOIN schedules s
            ON s.employee_id = a.employee_id
            AND s.schedule_date = a.attendance_date
            AND s.status <> 'cancelled'
        WHERE a.attendance_date BETWEEN :start_date AND :end_date
    ";

    $params = [
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ];

    if ($department !== '') {
        $sql .= " AND UPPER(e.department_name) = UPPER(:department) ";
        $params[':department'] = $department;
    }

    if ($status !== '') {
        $sql .= " AND UPPER(a.status) = UPPER(:status) ";
        $params[':status'] = $status;
    }

    if ($employeeId !== '') {
        $sql .= " AND a.employee_id = :employee_id ";
        $params[':employee_id'] = $employeeId;
    }

    $sql .= "
        ORDER BY
            a.attendance_date DESC,
            a.employee_id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll();

    $records = [];

    foreach ($rows as $row) {

        $name = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $schedule = '--';

        if (!empty($row['start_time']) && !empty($row['end_time'])) {
            $schedule =
                date('g:i A', strtotime($row['start_time'])) .
                ' - ' .
                date('g:i A', strtotime($row['end_time']));
        } elseif (!empty($row['shift_name'])) {
            $schedule = $row['shift_name'];
        }

        $records[] = [
            'id' => (int)$row['id'],
            'date' => $row['attendance_date'],
            'employeeId' => $row['employee_id'],
            'employee' => strtoupper($name),
            'department' => strtoupper((string)$row['department_name']),
            'schedule' => $schedule,
            'clockIn' => !empty($row['time_in'])
                ? date('g:i A', strtotime($row['time_in']))
                : '--',
            'clockOut' => !empty($row['time_out'])
                ? date('g:i A', strtotime($row['time_out']))
                : '--',
            'status' => strtoupper((string)$row['status']),
            'source' => $row['source']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $records,
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'department' => $department,
            'status' => $status,
            'employee_id' => $employeeId
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Attendance records API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load attendance records.'
    ]);
}