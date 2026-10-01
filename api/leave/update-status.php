<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

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
$action = strtolower(trim((string)($input['action'] ?? '')));

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

try {

    $adminUser = requireRole('admin');

$accountId = (int)($adminUser['id'] ?? 0);

if ($accountId <= 0) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Admin session not found.'
    ]);

    exit;
}

    $stmt = $pdo->prepare("
        UPDATE leave_requests
        SET
            status = :status,
            approved_by = :approved_by,
            approved_at = NOW()
        WHERE id = :id
          AND status = 'pending'
    ");

    $stmt->execute([
        ':status' => $statusMap[$action],
        ':approved_by' => $accountId,
        ':id' => $id
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Leave request was not found or was already processed.'
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Leave request updated successfully.'
    ]);

} catch (Throwable $e) {

    error_log(
        'Leave status update error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update leave request.'
    ]);
}