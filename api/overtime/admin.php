<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Only GET requests are allowed.'
        ]);

        exit;
    }

    $stmt = $pdo->query("
        SELECT
            o.id,
            o.employee_id,
            o.attendance_record_id,
            o.overtime_date,
            o.overtime_hours,
            o.reason,
            o.status,
            o.reviewed_by,
            o.reviewed_at,
            o.created_at,

            CONCAT_WS(
                ' ',
                e.first_name,
                e.middle_name,
                e.last_name
            ) AS employee_name

        FROM overtime_requests o

        INNER JOIN employees e
            ON e.employee_id = o.employee_id

        ORDER BY
            o.overtime_date DESC,
            o.id DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pending = 0;
    $approved = 0;
    $totalHours = 0.00;

    $data = [];

    foreach ($rows as $row) {

        $status = strtolower(
            (string)$row['status']
        );

        if ($status === 'pending') {
            $pending++;
        }

        if ($status === 'approved') {
            $approved++;
        }

        $hours = (float)$row['overtime_hours'];

        $totalHours += $hours;

        $data[] = [
            'id' => (int)$row['id'],
            'employeeId' => $row['employee_id'],
            'employee' => $row['employee_name'],
            'date' => $row['overtime_date'],
            'hours' => $hours,
            'reason' => $row['reason'] ?? '',
            'status' => strtoupper($status)
        ];
    }

    echo json_encode([
        'success' => true,
        'summary' => [
            'pending' => $pending,
            'approved' => $approved,
            'total_hours' => round($totalHours, 2)
        ],
        'data' => $data
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin overtime API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load overtime records.'
    ]);
}