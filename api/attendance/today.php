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

try {
    $sql = "
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.department_name,
            e.employment_status,

            a.id AS attendance_id,
            a.attendance_date,
            a.time_in,
            a.time_out,
            a.status AS attendance_status,
            a.source,

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

        WHERE e.employment_status <> 'terminated'

        ORDER BY e.employee_id ASC
    ";

    $stmt = $pdo->query($sql);
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
        } elseif (($row['employment_status'] ?? '') === 'on_leave') {
            $status = 'LEAVE';
        } elseif (!empty($row['time_in'])) {
            $status = 'PRESENT';
        } else {
            $status = 'ABSENT';
        }

        $schedule = 'N/A';

        if (!empty($row['start_time']) && !empty($row['end_time'])) {
            $start = date('g:i A', strtotime($row['start_time']));
            $end = date('g:i A', strtotime($row['end_time']));
            $schedule = $start . ' - ' . $end;
        }

        $records[] = [
            'id' => $row['employee_id'],
            'name' => strtoupper($name),
            'department' => strtoupper((string)($row['department_name'] ?? '')),
            'schedule' => $schedule,
            'clockIn' => !empty($row['time_in'])
                ? date('g:i A', strtotime($row['time_in']))
                : '--',
            'clockOut' => !empty($row['time_out'])
                ? date('g:i A', strtotime($row['time_out']))
                : '--',
            'status' => $status,
            'source' => $row['source'] ?? null
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $records
    ]);

} catch (Throwable $e) {

    error_log(
        'Attendance today API failed: ' . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load attendance records.'
    ]);
}