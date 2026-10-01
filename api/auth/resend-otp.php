<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/../config/mailer.php';

startWfmSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$pending = $_SESSION['pending_2fa'] ?? null;

if (
    !is_array($pending) ||
    !isset($pending['account_id'])
) {
    jsonResponse([
        'success' => false,
        'message' => 'Your verification session has expired. Please sign in again.'
    ], 401);
}

$stmt = $pdo->prepare("
    SELECT id, email, status
    FROM accounts
    WHERE id = :account_id
    LIMIT 1
");

$stmt->execute([
    ':account_id' => (int) $pending['account_id']
]);

$account = $stmt->fetch();

if (!$account || $account['status'] !== 'active') {
    clearPendingTwoFactor();

    jsonResponse([
        'success' => false,
        'message' => 'This account is no longer active.'
    ], 403);
}

$pdo->beginTransaction();

try {

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

    sendWfmOtpEmail($account['email'], $otpCode);

    $pdo->commit();

    $_SESSION['pending_2fa']['otp_sent_at'] = date('Y-m-d H:i:s');

    jsonResponse([
        'success' => true,
        'message' => 'A new verification code was sent.'
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('WFM OTP resend error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'message' => 'We could not send a new verification code.'
    ], 500);
}
