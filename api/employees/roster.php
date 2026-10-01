<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

try {

    $stmt = $pdo->query("
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.avatar_url,
            e.position_name,
            e.department_name,
            e.employment_status,
            a.role AS account_role

        FROM employees e

        LEFT JOIN accounts a
            ON a.employee_id = e.employee_id

        WHERE e.employment_status <> 'terminated'

        ORDER BY
            CASE
                WHEN a.role = 'manager' THEN 0
                WHEN a.role = 'employee' THEN 1
                ELSE 2
            END,
            e.employee_id ASC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {

        $name = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $accountRole = strtolower(
            (string)($row['account_role'] ?? '')
        );

        $position = trim(
            (string)($row['position_name'] ?? '')
        );

        $department = trim(
            (string)($row['department_name'] ?? '')
        );

        if ($accountRole === 'manager') {
            $roleLabel = 'Manager';
        } else {
            $roleLabel = $position !== ''
                ? $position
                : 'Employee';
        }

        $data[] = [
            'id' => (string)$row['employee_id'],
            'name' => $name,
            'role' => $roleLabel,
            'accountRole' => $accountRole,
            'department' => $department,
            'status' => (string)$row['employment_status'],
            'avatar_url' => $row['avatar_url']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin roster API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load Master Roster.'
    ]);
}