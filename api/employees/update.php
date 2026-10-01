<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

$employeeId = trim((string)($_POST['employee_id'] ?? ''));

if ($employeeId === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Check the short-lived Admin RFID authorization
|--------------------------------------------------------------------------
*/

$authorization =
    $_SESSION['employee_edit_rfid_authorization'] ?? null;

if (
    !is_array($authorization) ||
    (string)($authorization['employee_id'] ?? '') !== $employeeId ||
    (int)($authorization['expires_at'] ?? 0) < time()
) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Admin RFID verification is required before editing this employee.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Read submitted values
|--------------------------------------------------------------------------
*/

$firstName = trim((string)($_POST['first_name'] ?? ''));
$middleName = trim((string)($_POST['middle_name'] ?? ''));
$lastName = trim((string)($_POST['last_name'] ?? ''));

$dateOfBirth = trim((string)($_POST['date_of_birth'] ?? ''));
$gender = trim((string)($_POST['gender'] ?? ''));

$address = trim((string)($_POST['address'] ?? ''));

$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

$department = trim((string)($_POST['department_name'] ?? ''));
$position = trim((string)($_POST['position_name'] ?? ''));

$hireDate = trim((string)($_POST['hire_date'] ?? ''));
$employmentType = trim((string)($_POST['employment_type'] ?? ''));
$employmentStatus = trim((string)($_POST['employment_status'] ?? ''));

$emergencyName = trim((string)($_POST['emergency_name'] ?? ''));
$emergencyRelationship = trim((string)($_POST['emergency_relationship'] ?? ''));
$emergencyPhone = trim((string)($_POST['emergency_phone'] ?? ''));

$newPassword = (string)($_POST['new_password'] ?? '');
$retypeNewPassword = (string)($_POST['retype_new_password'] ?? '');

$hasPasswordChange =
    $newPassword !== '' ||
    $retypeNewPassword !== '';

if ($hasPasswordChange) {

    if ($newPassword === '' || $retypeNewPassword === '') {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Please enter and re-type the new password.'
        ]);

        exit;
    }

    if ($newPassword !== $retypeNewPassword) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'New passwords do not match.'
        ]);

        exit;
    }

    if (
        strlen($newPassword) < 6 ||
        !preg_match('/[A-Za-z]/', $newPassword) ||
        !preg_match('/[0-9]/', $newPassword) ||
        !preg_match('/[!@%]/', $newPassword)
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters and include a letter, a number, and a special character (!@%).'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Required fields
|--------------------------------------------------------------------------
*/

if ($firstName === '' || $lastName === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'First name and last name are required.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Validate email when supplied
|--------------------------------------------------------------------------
*/

if (
    $email !== '' &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Validate employment status
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'active',
    'inactive',
    'on_leave',
    'terminated'
];

if (
    $employmentStatus !== '' &&
    !in_array($employmentStatus, $allowedStatuses, true)
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid employment status.'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Make sure the employee exists
    |--------------------------------------------------------------------------
    */

    $checkStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $checkStmt->execute([
        ':employee_id' => $employeeId
    ]);

    if (!$checkStmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Employee record not found.'
        ]);

        exit;
    }

    $accountUpdatedAt = null;

    if ($hasPasswordChange) {

        $accountStmt = $pdo->prepare("
            SELECT id
            FROM accounts
            WHERE employee_id = :employee_id
            LIMIT 1
        ");

        $accountStmt->execute([
            ':employee_id' => $employeeId
        ]);

        $account = $accountStmt->fetch();

        if (!$account) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Employee account not found.'
            ]);

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update employee information
    |--------------------------------------------------------------------------
    */

    $updateStmt = $pdo->prepare("
        UPDATE employees
        SET
            first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            date_of_birth = :date_of_birth,
            gender = :gender,
            address = :address,
            phone = :phone,
            email = :email,
            department_name = :department_name,
            position_name = :position_name,
            hire_date = :hire_date,
            employment_type = :employment_type,
            employment_status = :employment_status,
            emergency_name = :emergency_name,
            emergency_relationship = :emergency_relationship,
            emergency_phone = :emergency_phone
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $updateStmt->execute([
        ':first_name' => $firstName,
        ':middle_name' => ($middleName !== '' ? $middleName : null),
        ':last_name' => $lastName,
        ':date_of_birth' => ($dateOfBirth !== '' ? $dateOfBirth : null),
        ':gender' => ($gender !== '' ? $gender : null),
        ':address' => ($address !== '' ? $address : null),
        ':phone' => ($phone !== '' ? $phone : null),
        ':email' => ($email !== '' ? $email : null),
        ':department_name' => ($department !== '' ? $department : null),
        ':position_name' => ($position !== '' ? $position : null),
        ':hire_date' => ($hireDate !== '' ? $hireDate : null),
        ':employment_type' => ($employmentType !== '' ? $employmentType : null),
        ':employment_status' => ($employmentStatus !== '' ? $employmentStatus : 'active'),
        ':emergency_name' => ($emergencyName !== '' ? $emergencyName : null),
        ':emergency_relationship' => ($emergencyRelationship !== '' ? $emergencyRelationship : null),
        ':emergency_phone' => ($emergencyPhone !== '' ? $emergencyPhone : null),
        ':employee_id' => $employeeId
    ]);

    if ($hasPasswordChange) {

        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $passwordUpdateStmt = $pdo->prepare("
            UPDATE accounts
            SET password_hash = :password_hash
            WHERE employee_id = :employee_id
            LIMIT 1
        ");

        $passwordUpdateStmt->execute([
            ':password_hash' => $passwordHash,
            ':employee_id' => $employeeId
        ]);

        $passwordDateStmt = $pdo->prepare("
            SELECT updated_at
            FROM accounts
            WHERE employee_id = :employee_id
            LIMIT 1
        ");

        $passwordDateStmt->execute([
            ':employee_id' => $employeeId
        ]);

        $passwordUpdatedAt = $passwordDateStmt->fetchColumn();

        $accountUpdatedAt = $passwordUpdatedAt
            ? date('m/d/y', strtotime((string)$passwordUpdatedAt))
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Consume the RFID authorization
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['employee_edit_rfid_authorization']);

    echo json_encode([
        'success' => true,
        'message' => 'Employee information updated successfully.',
        'password_updated_at' => $accountUpdatedAt
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin employee update failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update employee information.'
    ]);
}