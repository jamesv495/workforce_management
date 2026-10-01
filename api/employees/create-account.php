<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
requireRole('admin');

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function responseJson(bool $success, string $message, int $status = 200, array $data = []): never
{
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        ...$data
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(false, 'Only POST requests are allowed.', 405);
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    responseJson(false, 'Invalid request.', 400);
}

$employeeId = trim((string)($input['employee_id'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($employeeId === '') {
    responseJson(false, 'Employee ID is required.', 422);
}

if ($password === '' || strlen($password) < 8) {
    responseJson(
        false,
        'Password must be at least 8 characters.',
        422
    );
}

try {

    /*
     * 1. Find the employee.
     */
    $employeeStmt = $pdo->prepare("
        SELECT
            employee_id,
            email,
            employment_status
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee = $employeeStmt->fetch();

    if (!$employee) {
        responseJson(false, 'Employee does not exist.', 404);
    }

    if (empty($employee['email'])) {
        responseJson(
            false,
            'Employee does not have an email address.',
            422
        );
    }

    if ($employee['employment_status'] !== 'active') {
        responseJson(
            false,
            'Employee is not active.',
            422
        );
    }

    /*
     * 2. Check whether an account already exists.
     */
    $accountCheck = $pdo->prepare("
        SELECT id
        FROM accounts
        WHERE employee_id = :employee_id
           OR email = :email
        LIMIT 1
    ");

    $accountCheck->execute([
        ':employee_id' => $employeeId,
        ':email' => $employee['email']
    ]);

    if ($accountCheck->fetch()) {
        responseJson(
            false,
            'An account already exists for this employee.',
            409
        );
    }

    /*
     * 3. Hash the password.
     */
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /*
     * 4. Create employee account.
     */
    $insert = $pdo->prepare("
        INSERT INTO accounts (
            employee_id,
            email,
            password_hash,
            role,
            status
        )
        VALUES (
            :employee_id,
            :email,
            :password_hash,
            'employee',
            'active'
        )
    ");

    $insert->execute([
        ':employee_id' => $employeeId,
        ':email' => $employee['email'],
        ':password_hash' => $passwordHash
    ]);

    responseJson(
        true,
        'Employee account created successfully.',
        201,
        [
            'account' => [
                'employee_id' => $employeeId,
                'email' => $employee['email'],
                'role' => 'employee',
                'status' => 'active'
            ]
        ]
    );

} catch (Throwable $e) {

    error_log(
        'Employee account creation failed: ' .
        $e->getMessage()
    );

    responseJson(
        false,
        'Unable to create employee account.',
        500
    );
}