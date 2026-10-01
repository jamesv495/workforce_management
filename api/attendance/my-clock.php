<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$employeeUser = requireRole('employee');

$employeeId = trim(
    (string)($employeeUser['employee_id'] ?? '')
);

if ($employeeId === '') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Employee session not found.'
    ]);
    exit;
}

try {

    /*
     * GET = today's attendance + today's schedule.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $stmt = $pdo->prepare("
            SELECT
                a.id,
                a.attendance_date,
                a.time_in,
                a.time_out,
                a.status,
                s.shift_name,
                s.start_time,
                s.end_time
            FROM attendance_records a
            LEFT JOIN schedules s
                ON s.employee_id = a.employee_id
                AND s.schedule_date = a.attendance_date
                AND s.status <> 'cancelled'
            WHERE a.employee_id = :employee_id
              AND a.attendance_date = CURDATE()
            ORDER BY a.id DESC
            LIMIT 1
        ");

        $stmt->execute([
            ':employee_id' => $employeeId
        ]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {

            $scheduleStmt = $pdo->prepare("
                SELECT
                    shift_name,
                    start_time,
                    end_time
                FROM schedules
                WHERE employee_id = :employee_id
                  AND schedule_date = CURDATE()
                  AND status <> 'cancelled'
                ORDER BY id DESC
                LIMIT 1
            ");

            $scheduleStmt->execute([
                ':employee_id' => $employeeId
            ]);

            $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'attendance' => null,
                    'schedule' => $schedule ?: null
                ]
            ]);

            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'attendance' => $record,
                'schedule' => [
                    'shift_name' => $record['shift_name'] ?? null,
                    'start_time' => $record['start_time'] ?? null,
                    'end_time' => $record['end_time'] ?? null
                ]
            ]
        ]);

        exit;
    }

    /*
     * POST = clock in / clock out.
     */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Only GET and POST requests are allowed.'
        ]);

        exit;
    }

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        $input = [];
    }

    $action = trim(
        (string)($input['action'] ?? '')
    );

    if (!in_array($action, ['time_in', 'time_out'], true)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid attendance action.'
        ]);

        exit;
    }

    $pdo->beginTransaction();

    /*
     * Lock today's attendance record while changing it.
     */
    $attendanceStmt = $pdo->prepare("
        SELECT
            id,
            time_in,
            time_out,
            status
        FROM attendance_records
        WHERE employee_id = :employee_id
          AND attendance_date = CURDATE()
        ORDER BY id DESC
        LIMIT 1
        FOR UPDATE
    ");

    $attendanceStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

    /*
     * Get today's schedule.
     */
    $scheduleStmt = $pdo->prepare("
        SELECT
            shift_name,
            start_time,
            end_time
        FROM schedules
        WHERE employee_id = :employee_id
          AND schedule_date = CURDATE()
          AND status <> 'cancelled'
        ORDER BY id DESC
        LIMIT 1
    ");

    $scheduleStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

    if ($action === 'time_in') {

            /*
         * Approved leave blocks clock-in for today.
         */
        $leaveStmt = $pdo->prepare("
            SELECT leave_type
            FROM leave_requests
            WHERE employee_id = :employee_id
              AND status = 'approved'
              AND CURDATE() BETWEEN start_date AND end_date
            ORDER BY id DESC
            LIMIT 1
        ");

        $leaveStmt->execute([
            ':employee_id' => $employeeId
        ]);

        $approvedLeave = $leaveStmt->fetch(
            PDO::FETCH_ASSOC
        );

        if ($approvedLeave) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' =>
                    'You are on approved leave today and cannot clock in.'
            ]);

            exit;
        }

        if ($attendance && !empty($attendance['time_in'])) {

            $pdo->rollBack();

            echo json_encode([
                'success' => false,
                'message' => 'You are already clocked in today.'
            ]);

            exit;
        }

        /*
         * Determine present/late from today's scheduled start time.
         */
        $status = 'present';

        if (
            $schedule &&
            !empty($schedule['start_time'])
        ) {

            $nowTime = new DateTime('now');

            $scheduledStart = new DateTime(
                $today = date('Y-m-d') . ' ' .
                $schedule['start_time']
            );

            if ($nowTime > $scheduledStart) {
                $status = 'late';
            }
        }

        if ($attendance) {

            $update = $pdo->prepare("
                UPDATE attendance_records
                SET
                    time_in = NOW(),
                    time_out = NULL,
                    status = :status,
                    source = 'manual',
                    notes = 'Clocked in from employee portal.',
                    updated_at = NOW()
                WHERE id = :id
            ");

            $update->execute([
                ':status' => $status,
                ':id' => (int)$attendance['id']
            ]);

            $attendanceId = (int)$attendance['id'];

        } else {

            $insert = $pdo->prepare("
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
                    :status,
                    'manual',
                    'Clocked in from employee portal.'
                )
            ");

            $insert->execute([
                ':employee_id' => $employeeId,
                ':status' => $status
            ]);

            $attendanceId = (int)$pdo->lastInsertId();
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => $status === 'late'
                ? 'Clock in recorded as late.'
                : 'Clock in recorded successfully.',
            'action' => 'time_in',
            'attendance_id' => $attendanceId,
            'status' => $status
        ]);

        exit;
    }

    /*
     * Clock out.
     */
    if (!$attendance || empty($attendance['time_in'])) {

        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Please clock in first.'
        ]);

        exit;
    }

    if (!empty($attendance['time_out'])) {

        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'You are already clocked out today.'
        ]);

        exit;
    }

    $update = $pdo->prepare("
        UPDATE attendance_records
        SET
            time_out = NOW(),
            source = 'manual',
            updated_at = NOW()
        WHERE id = :id
    ");

    $update->execute([
        ':id' => (int)$attendance['id']
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Clock out recorded successfully.',
        'action' => 'time_out',
        'attendance_id' => (int)$attendance['id']
    ]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Employee clock API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update attendance.'
    ]);
}
