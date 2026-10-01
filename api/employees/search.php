<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('employee');

$query = trim(
    (string)($_GET['q'] ?? '')
);

if ($query === '') {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);

    exit;
}

if (mb_strlen($query) < 1) {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);

    exit;
}

try {

    $search = '%' . $query . '%';

    $stmt = $pdo->prepare("
        SELECT
            employee_id,
            first_name,
            middle_name,
            last_name,
            department_name,
            position_name,
            employment_status
        FROM employees
        WHERE
            employee_id LIKE :search_id
            OR first_name LIKE :search_first
            OR middle_name LIKE :search_middle
            OR last_name LIKE :search_last
            OR department_name LIKE :search_department
            OR position_name LIKE :search_position
        ORDER BY
            first_name ASC,
            last_name ASC
        LIMIT 10
    ");

    $stmt->execute([
        ':search_id' => $search,
        ':search_first' => $search,
        ':search_middle' => $search,
        ':search_last' => $search,
        ':search_department' => $search,
        ':search_position' => $search
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];

    foreach ($rows as $row) {

        $name = trim(
            implode(
                ' ',
                array_filter([
                    $row['first_name'] ?? '',
                    $row['middle_name'] ?? '',
                    $row['last_name'] ?? ''
                ])
            )
        );

        $results[] = [
            'employee_id' => $row['employee_id'],
            'name' => $name,
            'department' => $row['department_name'] ?? '',
            'position' => $row['position_name'] ?? '',
            'status' => $row['employment_status'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);

} catch (Throwable $e) {

    error_log(
        'Employee search API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to search employees.'
    ]);
}