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

try {

    /*
     * Find the logged-in manager's employees.id.
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
     * Get employees assigned to this manager.
     */
    $stmt = $pdo->prepare("
        SELECT
            employee_id,
            first_name,
            middle_name,
            last_name,
            position_name,
            department_name,
            employment_status
        FROM employees
        WHERE manager_id = :manager_id
          AND employment_status <> 'terminated'
        ORDER BY employee_id ASC
    ");

    $stmt->execute([
        ':manager_id' => (int)$manager['id']
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $employees = [];

    foreach ($rows as $row) {

        $name = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $employees[] = [
            'id' => (string)$row['employee_id'],
            'name' => $name,
            'position' => (string)(
                $row['position_name'] ?? ''
            ),
            'department' => (string)(
                $row['department_name'] ?? ''
            ),
            'status' => (string)(
                $row['employment_status'] ?? ''
            )
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager team API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load manager team.'
    ]);
}