<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth_helpers.php';

startWfmSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ], 405);
}

$rfidUid = trim((string)($_POST['rfid_uid'] ?? ''));
$kioskName = trim((string)($_POST['kiosk_name'] ?? 'Main Kiosk'));

if ($rfidUid === '' || strlen($rfidUid) > 100) {
    jsonResponse([
        'success' => false,
        'scan_status' => 'DENIED',
        'message' => 'Invalid RFID UID.'
    ], 400);
}

if ($kioskName === '') {
    $kioskName = 'Main Kiosk';
}

$kioskName = mb_substr($kioskName, 0, 100);

try {
    /*
    |--------------------------------------------------------------------------
    | Find the active RFID card and its active employee.
    |--------------------------------------------------------------------------
    */
    $lookup = $pdo->prepare("
        SELECT
            r.id AS rfid_card_id,
            r.rfid_uid,
            r.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.employment_status
        FROM rfid_cards r
        INNER JOIN employees e
            ON e.employee_id = r.employee_id
        WHERE r.rfid_uid = :rfid_uid
          AND r.status = 'active'
          AND e.employment_status = 'active'
        LIMIT 1
    ");

    $lookup->execute([':rfid_uid' => $rfidUid]);
    $card = $lookup->fetch();

    if (!$card) {
        jsonResponse([
            'success' => false,
            'scan_status' => 'DENIED',
            'message' => 'RFID card is not registered or is inactive.'
        ], 404);
    }

    $employeeName = trim(implode(' ', array_filter([
        $card['first_name'] ?? '',
        $card['middle_name'] ?? '',
        $card['last_name'] ?? ''
    ], static fn($value) => $value !== null && $value !== '')));

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Prevent two rapid scans from creating contradictory attendance states.
    |--------------------------------------------------------------------------
    */
    $attendanceStmt = $pdo->prepare("
        SELECT
            id,
            time_in,
            time_out,
            status,
            source
        FROM attendance_records
        WHERE employee_id = :employee_id
          AND attendance_date = CURDATE()
        ORDER BY id DESC
        LIMIT 1
        FOR UPDATE
    ");

    $attendanceStmt->execute([
        ':employee_id' => $card['employee_id']
    ]);

    $attendance = $attendanceStmt->fetch();

    $attendanceAction = 'time_in';
    $attendanceId = null;
    $attendanceStatus = 'present';
    $scanMessage = 'Time in recorded successfully.';

    if (!$attendance) {
        $insertAttendance = $pdo->prepare("
            INSERT INTO attendance_records (
                employee_id,
                attendance_date,
                time_in,
                status,
                source,
                notes
            )
            VALUES (
                :employee_id,
                CURDATE(),
                NOW(),
                'present',
                'kiosk',
                :notes
            )
        ");

        $insertAttendance->execute([
            ':employee_id' => $card['employee_id'],
            ':notes' => 'Recorded by RFID kiosk.'
        ]);

        $attendanceId = (int)$pdo->lastInsertId();
        $attendanceAction = 'time_in';
        $attendanceStatus = 'present';
        $scanMessage = 'Time in recorded successfully.';
    } elseif ($attendance['time_out'] === null) {
        $updateAttendance = $pdo->prepare("
            UPDATE attendance_records
            SET
                time_out = NOW(),
                source = 'kiosk',
                updated_at = NOW()
            WHERE id = :id
        ");

        $updateAttendance->execute([
            ':id' => (int)$attendance['id']
        ]);

        $attendanceId = (int)$attendance['id'];
        $attendanceAction = 'time_out';
        $attendanceStatus = (string)$attendance['status'];
        $scanMessage = 'Time out recorded successfully.';
    } else {
        /*
        | If today's attendance is already complete, do not create a second
        | attendance row. Still log the approved physical RFID scan below.
        */
        $attendanceId = (int)$attendance['id'];
        $attendanceAction = 'already_completed';
        $attendanceStatus = (string)$attendance['status'];
        $scanMessage = 'Attendance for today is already complete.';
    }

    /*
    |--------------------------------------------------------------------------
    | Every successful/authorized kiosk scan is preserved in kiosk_log_entries.
    |--------------------------------------------------------------------------
    */
    $logStmt = $pdo->prepare("
        INSERT INTO kiosk_log_entries (
            employee_id,
            rfid_card_id,
            rfid_uid,
            kiosk_name,
            scan_status,
            scanned_at
        )
        VALUES (
            :employee_id,
            :rfid_card_id,
            :rfid_uid,
            :kiosk_name,
            'APPROVED',
            NOW()
        )
    ");

    $logStmt->execute([
        ':employee_id' => $card['employee_id'],
        ':rfid_card_id' => (int)$card['rfid_card_id'],
        ':rfid_uid' => $card['rfid_uid'],
        ':kiosk_name' => $kioskName
    ]);

    $kioskLogId = (int)$pdo->lastInsertId();

    $pdo->commit();

    jsonResponse([
        'success' => true,
        'scan_status' => 'APPROVED',
        'message' => $scanMessage,
        'attendance_action' => $attendanceAction,
        'attendance_id' => $attendanceId,
        'attendance_status' => $attendanceStatus,
        'kiosk_log_id' => $kioskLogId,
        'rfid_uid' => $card['rfid_uid'],
        'employee_id' => $card['employee_id'],
        'employee_name' => $employeeName,
        'kiosk_name' => $kioskName
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('WFM kiosk attendance error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'scan_status' => 'DENIED',
        'message' => 'Unable to record the kiosk scan. Please try again.'
    ], 500);
}
