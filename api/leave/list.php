<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("
        SELECT
            lr.id,
            lr.employee_id,
            lr.leave_type,
            lr.start_date,
            lr.end_date,
            lr.reason,
            lr.status,
            lr.approved_by,
            lr.approved_at,
            lr.created_at,
            e.avatar_url,
            CONCAT_WS(
                ' ',
                e.first_name,
                e.middle_name,
                e.last_name
            ) AS employee_name
        FROM leave_requests lr
        INNER JOIN employees e
            ON e.employee_id = lr.employee_id
        ORDER BY lr.created_at DESC
    ");

    $rows = $stmt->fetchAll();

    $data = [];

    foreach ($rows as $row) {
        $statusMap = [
            'pending'   => 'Pending',
            'approved'  => 'Approved',
            'rejected'  => 'Declined',
            'cancelled' => 'Cancelled'
        ];

        $data[] = [
            'id' => (int)$row['id'],
            'employeeId' => $row['employee_id'],
            'name' => $row['employee_name'],
            'avatar_url' => $row['avatar_url'],
            'type' => $row['leave_type'],
            'dates' => date('M j, Y', strtotime($row['start_date']))
                . ' - ' .
                date('M j, Y', strtotime($row['end_date'])),
            'reason' => $row['reason'] ?? '',
            'status' => $statusMap[$row['status']] ?? ucfirst($row['status']),
            'startDate' => $row['start_date'],
            'endDate' => $row['end_date']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Throwable $e) {
    error_log('Leave list error: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load leave requests.'
    ]);
}