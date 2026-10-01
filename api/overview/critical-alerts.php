<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');


try {

    /*
     * Find active manager accounts who have filed
     * a Sick Leave request and still have a
     * scheduled assignment during that leave.
     *
     * Pending and approved sick leave requests
     * are treated as critical alerts because the
     * manager has already filed the request.
     */

    $stmt = $pdo->query("
        SELECT
            s.id AS schedule_id,

            lr.id AS leave_id,

            s.employee_id,

            s.schedule_date,
            s.shift_name,
            s.start_time,
            s.end_time,
            s.work_type,
            s.remarks,

            lr.start_date AS leave_start_date,
            lr.end_date AS leave_end_date,
            lr.status AS leave_status,

            CONCAT_WS(
                ' ',
                e.first_name,
                e.middle_name,
                e.last_name
            ) AS employee_name,

            e.position_name

        FROM schedules s

        INNER JOIN employees e
            ON e.employee_id = s.employee_id

        INNER JOIN accounts a
            ON a.employee_id = e.employee_id

        INNER JOIN leave_requests lr
            ON lr.employee_id = e.employee_id

        WHERE a.role = 'manager'

          AND a.status = 'active'

          AND e.employment_status <> 'terminated'

          AND lr.leave_type = 'Sick Leave'

          AND lr.status IN (
                'pending',
                'approved'
          )

          AND s.status = 'scheduled'

          AND s.schedule_date >= CURDATE()

          AND s.schedule_date
              BETWEEN lr.start_date
              AND lr.end_date

        ORDER BY
            s.schedule_date ASC,
            s.start_time ASC,
            e.employee_id ASC
    ");


    $rows =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);


} catch (Throwable $e) {

    error_log(
        'Critical alerts error: ' .
        $e->getMessage()
    );


    http_response_code(500);


    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load critical alerts.'
    ]);
}