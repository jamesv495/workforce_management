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

$rfidUid = trim((string)($_POST['rfid_uid'] ?? ''));

if ($rfidUid === '') {
    jsonResponse([
        'success' => false,
        'message' => 'RFID UID is required.'
    ], 400);
}

/*
|--------------------------------------------------------------------------
| Find the account attached to this RFID.
| Step 7 uses the existing employee_id relationship:
| rfid_cards -> employees -> accounts.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.employee_id,
        a.email,
        a.role,
        a.status,
        e.first_name,
        e.middle_name,
        e.last_name,
        r.id AS rfid_card_id
    FROM rfid_cards r
    INNER JOIN employees e
        ON e.employee_id = r.employee_id
    INNER JOIN accounts a
        ON a.employee_id = e.employee_id
    WHERE r.rfid_uid = :rfid_uid
      AND r.status = 'active'
    LIMIT 1
");

$stmt->execute([
    ':rfid_uid' => $rfidUid
]);

$account = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| RFID login is ONLY for the Admin account.
| Manager/Employee RFID does not log in through this shortcut.
|--------------------------------------------------------------------------
*/

if (!$account || $account['role'] !== 'admin') {
    jsonResponse([
        'success' => false,
        'message' => 'RFID is not registered for Admin login.'
    ], 403);
}

if ($account['status'] !== 'active') {
    jsonResponse([
        'success' => false,
        'message' => 'This account is not active.'
    ], 403);
}

/*
|--------------------------------------------------------------------------
| Display name
|--------------------------------------------------------------------------
*/

$nameParts = array_filter([
    $account['first_name'] ?? '',
    $account['middle_name'] ?? '',
    $account['last_name'] ?? ''
]);

$name = trim(implode(' ', $nameParts));

if ($name === '') {
    $name = 'Admin Account';
}

$account['name'] = $name;

/*
|--------------------------------------------------------------------------
| Check the same 24-hour trusted device used by normal login.
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
        ':account_id' => (int)$account['id'],
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
            ':id' => (int)$trustedDevice['id']
        ]);

        setAuthenticatedSession($account);

        jsonResponse([
            'success' => true,
            'requires_2fa' => false,
            'message' => 'Admin RFID login successful.',
            'account_id' => (int)$account['id'],
            'employee_id' => $account['employee_id'],
            'name' => $account['name'],
            'email' => $account['email'],
            'role' => 'admin',
            'redirect' => 'admin.php'
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Device is not trusted.
| Create the same 6-digit Gmail OTP used by normal login.
|--------------------------------------------------------------------------
*/

$pdo->beginTransaction();

try {
    $invalidateStmt = $pdo->prepare("
        UPDATE otp_challenges
        SET consumed_at = NOW()
        WHERE account_id = :account_id
          AND consumed_at IS NULL
    ");

    $invalidateStmt->execute([
        ':account_id' => (int)$account['id']
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
        ':account_id' => (int)$account['id'],
        ':code_hash' => $otpHash,
        ':expires_at' => $otpExpiresAt
    ]);

    $_SESSION['pending_2fa'] = [
        'account_id' => (int)$account['id'],
        'employee_id' => $account['employee_id'],
        'email' => $account['email'],
        'name' => $account['name'],
        'role' => 'admin',
        'otp_sent_at' => date('Y-m-d H:i:s'),
        'login_method' => 'rfid'
    ];

    require_once __DIR__ . '/../config/mailer.php';

    sendWfmOtpEmail($account['email'], $otpCode);

    $pdo->commit();

    jsonResponse([
        'success' => false,
        'requires_2fa' => true,
        'message' => 'A six-digit verification code was sent to your email.',
        'masked_email' => maskEmail($account['email'])
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    clearPendingTwoFactor();

    error_log('WFM Admin RFID login OTP error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'requires_2fa' => false,
        'message' => 'We could not send the verification code. Please try again.'
    ], 500);
}
