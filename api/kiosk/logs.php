<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Only GET requests are allowed.'
        ]);

        exit;
    }

    $stmt = $pdo->query("
        SELECT
            k.id,
            k.employee_id,
            k.rfid_card_id,
            k.rfid_uid,
            k.kiosk_name,
            k.scan_status,
            k.scanned_at,

            CONCAT_WS(
                ' ',
                e.first_name,
                e.middle_name,
                e.last_name
            ) AS employee_name

        FROM kiosk_log_entries k

        INNER JOIN employees e
            ON e.employee_id = k.employee_id

        ORDER BY
            k.scanned_at DESC,
            k.id DESC
    ");

    $rows = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );

    $data = [];

    foreach ($rows as $row) {

        $data[] = [
            'id' =>
                (int)$row['id'],

            'employeeId' =>
                (string)$row['employee_id'],

            'employee' =>
                trim(
                    (string)$row['employee_name']
                ),

            'rfidUid' =>
                (string)$row['rfid_uid'],

            'kioskName' =>
                (string)$row['kiosk_name'],

            'scanStatus' =>
                strtoupper(
                    (string)$row['scan_status']
                ),

            'scannedAt' =>
                (string)$row['scanned_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Throwable $e) {

    error_log(
        'Kiosk log API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load kiosk log entries.'
    ]);
}