<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$autoloadFile = __DIR__ . '/../../vendor/autoload.php';

if (!is_file($autoloadFile)) {
    throw new RuntimeException(
        'PHPMailer is not installed. Run "composer install" in the Workforce Management project folder.'
    );
}

require_once $autoloadFile;

function sendWfmOtpEmail(string $recipient, string $otpCode): void
{
    $config = require __DIR__ . '/mail.php';

    if (
        $config['username'] === '' ||
        $config['password'] === '' ||
        $config['from_email'] === ''
    ) {
        throw new RuntimeException(
            'Gmail SMTP is not configured. Set WFM_MAIL_USERNAME, WFM_MAIL_PASSWORD, and WFM_MAIL_FROM.'
        );
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = trim((string) $config['username']);
        // Gmail App Passwords are often copied with spaces; SMTP expects the 16-character token without spaces.
        $mail->Password = preg_replace('/\s+/', '', (string) $config['password']);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $config['from_email'],
            $config['from_name']
        );

        $mail->addAddress($recipient);

        $mail->isHTML(true);

        $safeCode = htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8');

        $mail->Subject = 'Your Workforce Management Verification Code';

        $mail->Body = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background:#F8FAFC; padding:30px; color:#163B6D;">
    <div style="max-width:520px; margin:0 auto; background:#FFFFFF; border:1px solid #E5E7EB; border-radius:16px; padding:32px;">
        <h2 style="margin:0 0 10px;">Workforce Management</h2>
        <p style="color:#6B7280;">Use this six-digit code to complete your sign-in.</p>

        <div style="margin:28px 0; text-align:center;">
            <span style="display:inline-block; padding:16px 24px; border-radius:12px; background:#F8FAFC; border:1px solid #E5E7EB; font-size:32px; font-weight:700; letter-spacing:10px; color:#163B6D;">
                {$safeCode}
            </span>
        </div>

        <p style="color:#6B7280;">
            This code expires in 10 minutes.
        </p>

        <p style="font-size:13px; color:#9CA3AF;">
            If you did not try to sign in, you can ignore this email.
        </p>
    </div>
</body>
</html>
HTML;

        $mail->AltBody =
            "Your Workforce Management verification code is {$otpCode}. "
            . "It expires in 10 minutes.";

        $mail->send();

    } catch (Exception $e) {
        error_log('WFM PHPMailer error: ' . $e->getMessage());
        throw new RuntimeException(
            'Unable to send the verification email.'
        );
    }
}
