<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

$employeeId = trim(
    (string)($_POST['employee_id'] ?? '')
);

$rfidUid = trim(
    (string)($_POST['rfid_uid'] ?? '')
);

if ($employeeId === '' || $rfidUid === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Employee ID and Admin RFID are required.'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Verify that the scanned RFID belongs to an active Admin account.
    | This does NOT create attendance and does NOT create a kiosk log.
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            a.id AS account_id,
            a.employee_id,
            a.role,
            a.status,
            r.id AS rfid_card_id,
            r.rfid_uid
        FROM rfid_cards r
        INNER JOIN employees e
            ON e.employee_id = r.employee_id
        INNER JOIN accounts a
            ON a.employee_id = e.employee_id
        WHERE r.rfid_uid = :rfid_uid
          AND r.status = 'active'
          AND a.role = 'admin'
          AND a.status = 'active'
        LIMIT 1
    ");

    $stmt->execute([
        ':rfid_uid' => $rfidUid
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'The scanned RFID is not registered to an active Admin account.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Save a short-lived server-side authorization.
    |--------------------------------------------------------------------------
    */

    $_SESSION['employee_edit_rfid_authorization'] = [
        'admin_account_id' => (int)$admin['account_id'],
        'employee_id' => $employeeId,
        'verified_at' => time(),
        'expires_at' => time() + 300
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Admin RFID verified successfully.'
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin RFID employee edit verification failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to verify Admin RFID.'
    ]);
}