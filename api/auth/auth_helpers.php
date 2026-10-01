<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Workforce Management - Authentication Helpers
|--------------------------------------------------------------------------
*/

const WFM_TRUSTED_DEVICE_COOKIE = 'wfm_trusted_device';
const WFM_TRUSTED_DEVICE_SECONDS = 86400; // 24 hours
const WFM_OTP_SECONDS = 600;               // 10 minutes
const WFM_OTP_MAX_ATTEMPTS = 5;

function startWfmSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

function maskEmail(string $email): string
{
    $parts = explode('@', $email, 2);

    if (count($parts) !== 2) {
        return $email;
    }

    [$local, $domain] = $parts;

    if (strlen($local) <= 2) {
        $maskedLocal = str_repeat('*', strlen($local));
    } else {
        $maskedLocal =
            substr($local, 0, 1) .
            str_repeat('*', max(1, strlen($local) - 2)) .
            substr($local, -1);
    }

    return $maskedLocal . '@' . $domain;
}

function createOtpCode(): string
{
    return (string) random_int(100000, 999999);
}

function createTrustedDeviceToken(): string
{
    return rtrim(
        strtr(
            base64_encode(random_bytes(32)),
            '+/',
            '-_'
        ),
        '='
    );
}

function setTrustedDeviceCookie(string $token): void
{
    setcookie(
        WFM_TRUSTED_DEVICE_COOKIE,
        $token,
        [
            'expires' => time() + WFM_TRUSTED_DEVICE_SECONDS,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

function getTrustedDeviceToken(): ?string
{
    $token = $_COOKIE[WFM_TRUSTED_DEVICE_COOKIE] ?? null;

    if (!is_string($token) || $token === '') {
        return null;
    }

    return $token;
}

function clearPendingTwoFactor(): void
{
    unset($_SESSION['pending_2fa']);
}

function setAuthenticatedSession(array $account): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $account['id'],
        'employee_id' => $account['employee_id'] ?? null,
        'email' => $account['email'],
        'name' => $account['name'],
        'role' => $account['role']
    ];

    $_SESSION['authenticated_at'] = date('Y-m-d H:i:s');
    $_SESSION['two_factor_verified'] = true;

    clearPendingTwoFactor();
}
