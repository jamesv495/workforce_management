<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.email,
            e.phone,
            e.department_name,
            e.position_name,
            e.hire_date,
            e.address,
            e.employment_type,
            e.employment_status,
            a.email AS account_email,
            a.role AS account_role,
            a.status AS account_status
        FROM employees e
        LEFT JOIN accounts a
            ON a.employee_id = e.employee_id
        WHERE a.role = 'employee'
           OR a.role IS NULL
        ORDER BY e.employee_id ASC
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $employees = [];

    foreach ($rows as $row) {
        $fullName = trim(
            ($row['first_name'] ?? '') . ' ' .
            ($row['middle_name'] ?? '') . ' ' .
            ($row['last_name'] ?? '')
        );

        $statusMap = [
            'active'     => 'ACTIVE',
            'on_leave'   => 'LEAVE',
            'inactive'   => 'INACTIVE',
            'terminated' => 'INACTIVE'
        ];

        $employees[] = [
            'id' => $row['employee_id'],
            'name' => $fullName,
            'email' => $row['email'],
            'phone' => $row['phone'],
            'position' => $row['position_name'],
            'department' => strtoupper((string)$row['department_name']),
            'hireDate' => $row['hire_date'],
            'address' => $row['address'],
            'employmentType' => $row['employment_type'],
            'status' => $statusMap[$row['employment_status']] ?? 'INACTIVE',
            'accountEmail' => $row['account_email'],
            'accountRole' => $row['account_role'],
            'accountStatus' => $row['account_status']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load employees.'
    ]);
}