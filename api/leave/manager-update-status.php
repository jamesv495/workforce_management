<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ]);

    exit;
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

$id = (int)($input['id'] ?? 0);
$action = strtolower(
    trim((string)($input['action'] ?? ''))
);

if ($id <= 0) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid leave request ID.'
    ]);

    exit;
}

$statusMap = [
    'approve' => 'approved',
    'decline' => 'rejected'
];

if (!isset($statusMap[$action])) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid action.'
    ]);

    exit;
}

$accountId = (int)($managerUser['id'] ?? 0);

if ($accountId <= 0) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager session not found.'
    ]);

    exit;
}

$employeeId = trim(
    (string)($managerUser['employee_id'] ?? '')
);

try {

    $stmt = $pdo->prepare("
        UPDATE leave_requests lr

        INNER JOIN employees e
            ON e.employee_id = lr.employee_id

        SET
            lr.status = :status,
            lr.approved_by = :approved_by,
            lr.approved_at = NOW()

        WHERE lr.id = :id
          AND e.manager_id = (
              SELECT id
              FROM employees
              WHERE employee_id = :manager_employee_id
              LIMIT 1
          )
          AND lr.status = 'pending'
    ");

    $stmt->execute([
        ':status' => $statusMap[$action],
        ':approved_by' => $accountId,
        ':id' => $id,
        ':manager_employee_id' => $employeeId
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' =>
                'Leave request was not found, is already processed, or does not belong to your team.'
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Leave request updated successfully.'
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager leave status update error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update leave request.'
    ]);
}