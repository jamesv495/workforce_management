<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireLogin();

$employeeId = trim(
    (string)($user['employee_id'] ?? '')
);

$accountId = (int)($user['id'] ?? 0);

if ($employeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Employee account could not be identified.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GET - Load the logged-in user's profile
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {

        $stmt = $pdo->prepare("
            SELECT
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.avatar_url,
                e.email,
                e.position_name,
                e.department_name,
                a.email AS account_email,
                a.role
            FROM employees e
            LEFT JOIN accounts a
                ON a.employee_id = e.employee_id
            WHERE e.employee_id = :employee_id
            LIMIT 1
        ");

        $stmt->execute([
            ':employee_id' => $employeeId
        ]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Profile record not found.'
            ]);

            exit;
        }

        $fullName = trim(
            ($record['first_name'] ?? '') . ' ' .
            ($record['middle_name'] ?? '') . ' ' .
            ($record['last_name'] ?? '')
        );

        echo json_encode([
            'success' => true,
            'data' => [
                'employee_id' => $record['employee_id'],
                'first_name' => $record['first_name'],
                'middle_name' => $record['middle_name'],
                'last_name' => $record['last_name'],
                'avatar_url' => $record['avatar_url'],
                'full_name' => $fullName,
                'email' => $record['account_email'] ?: $record['email'],
                'position' => $record['position_name'],
                'department' => $record['department_name'],
                'role' => $record['role'] ?? $user['role']
            ]
        ]);

        exit;

    } catch (Throwable $e) {

        error_log(
            'Profile GET failed: ' .
            $e->getMessage()
        );

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to load profile.'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| POST - Update logged-in user's profile
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Read JSON request
|--------------------------------------------------------------------------
*/

$raw = file_get_contents('php://input');

$payload = json_decode(
    $raw ?: '',
    true
);

if (!is_array($payload)) {
    $payload = $_POST;
}

$fullName = trim(
    (string)($payload['name'] ?? '')
);

$email = trim(
    (string)($payload['email'] ?? '')
);

$position = trim(
    (string)($payload['position'] ?? '')
);

$department = trim(
    (string)($payload['department'] ?? '')
);

/*
|--------------------------------------------------------------------------
| Validate name
|--------------------------------------------------------------------------
*/

if ($fullName === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Full name is required.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Split full name into database fields
|
| Example:
| John Michael Doe
| -> first_name  = John
| -> middle_name = Michael
| -> last_name   = Doe
|--------------------------------------------------------------------------
*/

$nameParts = preg_split(
    '/\s+/',
    $fullName,
    -1,
    PREG_SPLIT_NO_EMPTY
);

if (!$nameParts || count($nameParts) < 2) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter at least a first name and last name.'
    ]);

    exit;
}

$firstName = array_shift($nameParts);
$lastName = array_pop($nameParts);

$middleName = !empty($nameParts)
    ? implode(' ', $nameParts)
    : null;

/*
|--------------------------------------------------------------------------
| Validate email
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

try {

    /*
    |--------------------------------------------------------------------------
    | Make sure the account exists
    |--------------------------------------------------------------------------
    */

    $accountStmt = $pdo->prepare("
        SELECT
            id,
            employee_id,
            email,
            role
        FROM accounts
        WHERE id = :account_id
          AND employee_id = :employee_id
        LIMIT 1
    ");

    $accountStmt->execute([
        ':account_id' => $accountId,
        ':employee_id' => $employeeId
    ]);

    $account = $accountStmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Account record not found.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Check email duplication
    |--------------------------------------------------------------------------
    */

    if ($email !== '') {

        $emailCheck = $pdo->prepare("
            SELECT id
            FROM accounts
            WHERE LOWER(email) = LOWER(:email)
              AND id <> :account_id
            LIMIT 1
        ");

        $emailCheck->execute([
            ':email' => $email,
            ':account_id' => $accountId
        ]);

        if ($emailCheck->fetch()) {

            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' => 'That email address is already being used.'
            ]);

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update both employees and accounts
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    $employeeUpdate = $pdo->prepare("
        UPDATE employees
        SET
            first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            email = :employee_email,
            position_name = :position_name,
            department_name = :department_name
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $employeeUpdate->execute([
        ':first_name' => $firstName,
        ':middle_name' => $middleName,
        ':last_name' => $lastName,
        ':employee_email' => ($email !== '' ? $email : null),
        ':position_name' => ($position !== '' ? $position : null),
        ':department_name' => ($department !== '' ? $department : null),
        ':employee_id' => $employeeId
    ]);

    if ($email !== '') {

        $accountUpdate = $pdo->prepare("
            UPDATE accounts
            SET email = :email
            WHERE id = :account_id
              AND employee_id = :employee_id
            LIMIT 1
        ");

        $accountUpdate->execute([
            ':email' => $email,
            ':account_id' => $accountId,
            ':employee_id' => $employeeId
        ]);
    }

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Update the authenticated PHP session
    |--------------------------------------------------------------------------
    */

    $newEmail =
        $email !== ''
            ? $email
            : (string)$account['email'];

    $_SESSION['user']['name'] = $fullName;
    $_SESSION['user']['email'] = $newEmail;

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.',
        'user' => [
            'id' => $accountId,
            'employee_id' => $employeeId,
            'name' => $fullName,
            'email' => $newEmail,
            'role' => $account['role']
        ]
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Profile update failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update profile.'
    ]);
}