<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireLogin();

if (!in_array(($user['role'] ?? ''), ['employee', 'manager'], true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

$employeeId = trim((string)($user['employee_id'] ?? ''));

if ($employeeId === '') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Employee session not found.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            attendance_date,
            time_in,
            time_out,
            status,
            source
        FROM attendance_records
        WHERE employee_id = :employee_id
        ORDER BY attendance_date DESC, id DESC
    ");

    $stmt->execute([
        ':employee_id' => $employeeId
    ]);

    echo json_encode([
        'success' => true,
        'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Throwable $e) {
    error_log('My attendance error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load attendance records.'
    ]);
}