<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_helpers.php';

startWfmSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$pending = $_SESSION['pending_2fa'] ?? null;
$code = trim($_POST['code'] ?? '');

if (
    !is_array($pending) ||
    !isset($pending['account_id'])
) {
    jsonResponse([
        'success' => false,
        'message' => 'Your verification session has expired. Please sign in again.'
    ], 401);
}

if (!preg_match('/^\d{6}$/', $code)) {
    jsonResponse([
        'success' => false,
        'message' => 'Enter the 6-digit verification code.'
    ], 400);
}

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            code_hash,
            expires_at,
            attempts,
            consumed_at
        FROM otp_challenges
        WHERE account_id = :account_id
          AND consumed_at IS NULL
        ORDER BY id DESC
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        ':account_id' => (int) $pending['account_id']
    ]);

    $challenge = $stmt->fetch();

    if (!$challenge) {
        $pdo->rollBack();

        jsonResponse([
            'success' => false,
            'message' => 'No active verification code was found. Please request a new code.'
        ], 400);
    }

    if (strtotime($challenge['expires_at']) <= time()) {
        $consumeStmt = $pdo->prepare("
            UPDATE otp_challenges
            SET consumed_at = NOW()
            WHERE id = :id
        ");

        $consumeStmt->execute([
            ':id' => (int) $challenge['id']
        ]);

        $pdo->commit();

        jsonResponse([
            'success' => false,
            'message' => 'This verification code has expired. Please request a new code.'
        ], 400);
    }

    if ((int) $challenge['attempts'] >= WFM_OTP_MAX_ATTEMPTS) {
        $pdo->rollBack();

        jsonResponse([
            'success' => false,
            'message' => 'Too many incorrect attempts. Please request a new code.'
        ], 429);
    }

    if (!password_verify($code, $challenge['code_hash'])) {
        $attemptStmt = $pdo->prepare("
            UPDATE otp_challenges
            SET attempts = attempts + 1
            WHERE id = :id
        ");

        $attemptStmt->execute([
            ':id' => (int) $challenge['id']
        ]);

        $pdo->commit();

        $remaining =
            WFM_OTP_MAX_ATTEMPTS -
            ((int) $challenge['attempts'] + 1);

        if ($remaining < 0) {
            $remaining = 0;
        }

        jsonResponse([
            'success' => false,
            'message' => $remaining > 0
                ? "Incorrect verification code. {$remaining} attempt(s) remaining."
                : 'Too many incorrect attempts. Please request a new code.'
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch the account again before finalizing authentication.
    |--------------------------------------------------------------------------
    */

    $accountStmt = $pdo->prepare("
        SELECT
            a.id,
            a.employee_id,
            a.email,
            a.role,
            a.status,
            e.first_name,
            e.middle_name,
            e.last_name
        FROM accounts a
        LEFT JOIN employees e
            ON e.employee_id = a.employee_id
        WHERE a.id = :account_id
        LIMIT 1
        FOR UPDATE
    ");

    $accountStmt->execute([
        ':account_id' => (int) $pending['account_id']
    ]);

    $account = $accountStmt->fetch();

    if (!$account || $account['status'] !== 'active') {
        $pdo->rollBack();

        jsonResponse([
            'success' => false,
            'message' => 'This account is no longer active.'
        ], 403);
    }

    $nameParts = array_filter([
        $account['first_name'] ?? '',
        $account['middle_name'] ?? '',
        $account['last_name'] ?? ''
    ]);

    $name = trim(implode(' ', $nameParts));

    if ($name === '') {
        $name = match ($account['role']) {
            'admin' => 'Admin Account',
            'manager' => 'Manager Account',
            'employee' => 'Employee Account',
            default => $account['email']
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Consume current OTP
    |--------------------------------------------------------------------------
    */

    $consumeStmt = $pdo->prepare("
        UPDATE otp_challenges
        SET consumed_at = NOW()
        WHERE id = :id
    ");

    $consumeStmt->execute([
        ':id' => (int) $challenge['id']
    ]);

    /*
    |--------------------------------------------------------------------------
    | Create 24-hour trusted device
    |--------------------------------------------------------------------------
    */

    $deviceToken = createTrustedDeviceToken();
    $deviceTokenHash = hash('sha256', $deviceToken);

    $deviceStmt = $pdo->prepare("
        INSERT INTO trusted_devices (
            account_id,
            device_token_hash,
            verified_at,
            expires_at,
            last_used_at
        )
        VALUES (
            :account_id,
            :device_token_hash,
            NOW(),
            DATE_ADD(NOW(), INTERVAL 24 HOUR),
            NOW()
        )
    ");

    $deviceStmt->execute([
        ':account_id' => (int) $account['id'],
        ':device_token_hash' => $deviceTokenHash
    ]);

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Set the trusted-device browser cookie and PHP session.
    |--------------------------------------------------------------------------
    */

    setTrustedDeviceCookie($deviceToken);

    $account['name'] = $name;

    setAuthenticatedSession($account);

    $pages = [
        'admin' => 'admin.php',
        'manager' => 'manager.php',
        'employee' => 'employee.php'
    ];

    jsonResponse([
        'success' => true,
        'message' => 'Verification successful.',
        'account_id' => (int) $account['id'],
        'employee_id' => $account['employee_id'],
        'name' => $name,
        'email' => $account['email'],
        'role' => $account['role'],
        'redirect' => $pages[$account['role']] ?? 'login.php'
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('WFM OTP verification error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'message' => 'Unable to verify the code. Please try again.'
    ], 500);
}
