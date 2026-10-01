<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

$employeeId = trim((string)($managerUser['employee_id'] ?? ''));

if ($employeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager employee session not found.'
    ]);

    exit;
}

try {
    $managerStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $managerStmt->execute([
        ':employee_id' => $employeeId
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

    $stmt = $pdo->prepare("
        SELECT
            lr.id,
            lr.employee_id,
            lr.leave_type,
            lr.start_date,
            lr.end_date,
            lr.reason,
            lr.status,
            lr.created_at,

            CONCAT_WS(
                ' ',
                e.first_name,
                e.middle_name,
                e.last_name
            ) AS employee_name

        FROM leave_requests lr

        INNER JOIN employees e
            ON e.employee_id = lr.employee_id

        WHERE e.manager_id = :manager_id

        ORDER BY lr.created_at DESC, lr.id DESC
    ");

    $stmt->execute([
        ':manager_id' => (int)$manager['id']
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            'type' => $row['leave_type'],
            'dates' =>
                date('M j, Y', strtotime($row['start_date'])) .
                ' - ' .
                date('M j, Y', strtotime($row['end_date'])),
            'reason' => $row['reason'] ?? '',
            'status' =>
                $statusMap[$row['status']]
                ?? ucfirst($row['status']),
            'startDate' => $row['start_date'],
            'endDate' => $row['end_date']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager team leave list error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load team leave requests.'
    ]);
}