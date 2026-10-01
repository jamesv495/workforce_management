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

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Please enter your email and password.'
    ], 400);
}

$sql = "
    SELECT
        a.id,
        a.employee_id,
        a.email,
        a.password_hash,
        a.role,
        a.status,
        e.first_name,
        e.middle_name,
        e.last_name
    FROM accounts a
    LEFT JOIN employees e
        ON e.employee_id = a.employee_id
    WHERE LOWER(a.email) = LOWER(:email)
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':email' => $email
]);

$account = $stmt->fetch();

if (!$account) {
    jsonResponse([
        'success' => false,
        'message' => 'Invalid email or password.'
    ], 401);
}

if ($account['status'] !== 'active') {
    jsonResponse([
        'success' => false,
        'message' => 'This account is not active.'
    ], 403);
}

if (!password_verify($password, $account['password_hash'])) {
    jsonResponse([
        'success' => false,
        'message' => 'Invalid email or password.'
    ], 401);
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

$account['name'] = $name;

/*
|--------------------------------------------------------------------------
| Check for a trusted device
|--------------------------------------------------------------------------
*/

$trustedToken = getTrustedDeviceToken();

if ($trustedToken !== null) {
    $tokenHash = hash('sha256', $trustedToken);

    $trustedStmt = $pdo->prepare("
        SELECT id
        FROM trusted_devices
        WHERE account_id = :account_id
          AND device_token_hash = :device_token_hash
          AND expires_at > NOW()
        ORDER BY expires_at DESC
        LIMIT 1
    ");

    $trustedStmt->execute([
        ':account_id' => (int) $account['id'],
        ':device_token_hash' => $tokenHash
    ]);

    $trustedDevice = $trustedStmt->fetch();

    if ($trustedDevice) {
        $touchStmt = $pdo->prepare("
            UPDATE trusted_devices
            SET last_used_at = NOW()
            WHERE id = :id
        ");

        $touchStmt->execute([
            ':id' => (int) $trustedDevice['id']
        ]);

        setAuthenticatedSession($account);

        $pages = [
            'admin' => 'admin.php',
            'manager' => 'manager.php',
            'employee' => 'employee.php'
        ];

        jsonResponse([
            'success' => true,
            'requires_2fa' => false,
            'message' => 'Login successful.',
            'account_id' => (int) $account['id'],
            'employee_id' => $account['employee_id'],
            'name' => $account['name'],
            'email' => $account['email'],
            'role' => $account['role'],
            'redirect' => $pages[$account['role']] ?? 'login.php'
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Password is correct, but this device is not trusted.
| Generate a new six-digit OTP.
|--------------------------------------------------------------------------
*/

$pdo->beginTransaction();

try {
    /*
    | Remove any previous active OTP challenge for this account.
    */
    $invalidateStmt = $pdo->prepare("
        UPDATE otp_challenges
        SET consumed_at = NOW()
        WHERE account_id = :account_id
          AND consumed_at IS NULL
    ");

    $invalidateStmt->execute([
        ':account_id' => (int) $account['id']
    ]);

    $otpCode = createOtpCode();
    $otpHash = password_hash($otpCode, PASSWORD_DEFAULT);
    $otpExpiresAt = date(
        'Y-m-d H:i:s',
        time() + WFM_OTP_SECONDS
    );

    $otpStmt = $pdo->prepare("
        INSERT INTO otp_challenges (
            account_id,
            code_hash,
            expires_at,
            attempts
        )
        VALUES (
            :account_id,
            :code_hash,
            :expires_at,
            0
        )
    ");

    $otpStmt->execute([
        ':account_id' => (int) $account['id'],
        ':code_hash' => $otpHash,
        ':expires_at' => $otpExpiresAt
    ]);

    $_SESSION['pending_2fa'] = [
        'account_id' => (int) $account['id'],
        'employee_id' => $account['employee_id'],
        'email' => $account['email'],
        'name' => $account['name'],
        'role' => $account['role'],
        'otp_sent_at' => date('Y-m-d H:i:s')
    ];

    /*
    | The database insert and session state are ready before sending.
    | If sending fails, consume the OTP so it cannot be used.
    */
    require_once __DIR__ . '/../config/mailer.php';
    sendWfmOtpEmail($account['email'], $otpCode);

    $pdo->commit();

    jsonResponse([
        'success' => false,
        'requires_2fa' => true,
        'message' => 'A six-digit verification code was sent to your email.',
        'masked_email' => maskEmail($account['email'])
    ], 200);

} catch (Throwable $e) {

    $pdo->rollBack();

    /*
    | Clear the pending login state if mail delivery failed.
    */
    clearPendingTwoFactor();

    /*
    | Do not expose SMTP/database details to the browser.
    */
    error_log('WFM login OTP error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'requires_2fa' => false,
        'message' => 'We could not send the verification code. Please try again.'
    ], 500);
}
