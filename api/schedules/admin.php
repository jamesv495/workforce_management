<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function adminScheduleError(
    string $message,
    int $status = 400
): never {

    http_response_code($status);

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);

    exit;
}


function isValidAdminScheduleDate(
    string $date
): bool {

    $parsed =
        DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

    return $parsed !== false &&
        $parsed->format('Y-m-d') === $date;
}


function getAdminShift(
    string $shift
): array {

    return match ($shift) {

        'Regular' => [
            'shift_name' => '8-5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00'
        ],

        'Morning' => [
            'shift_name' => '8-12',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00'
        ],

        'Afternoon' => [
            'shift_name' => '1-5',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00'
        ],

        default => []
    };
}


function getAllowedWorkTypes(): array {

    return [
        'Field Duty',
        'Office Work',
        'Driving',
        'Monitoring',
        'Meeting'
    ];
}


/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action =
        trim(
            (string)(
                $_GET['action'] ?? ''
            )
        );


    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE DROPDOWN
    |--------------------------------------------------------------------------
    */

    if ($action === 'employees') {

        $date =
            trim(
                (string)(
                    $_GET['date']
                    ?? date('Y-m-d')
                )
            );

        if (
            !isValidAdminScheduleDate(
                $date
            )
        ) {

            adminScheduleError(
                'Invalid schedule date.',
                422
            );
        }


        $stmt = $pdo->prepare("
            SELECT
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.position_name,
                e.employment_status,

                a.role AS account_role,
                a.status AS account_status

            FROM employees e

            INNER JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE a.role IN ('employee', 'manager')

              AND e.employment_status <> 'terminated'

              AND e.employment_status <> 'on_leave'

              AND NOT EXISTS (
                    SELECT 1
                    FROM leave_requests lr
                    WHERE lr.employee_id = e.employee_id
                      AND lr.status = 'approved'
                      AND lr.start_date <= :leave_date
                      AND lr.end_date >= :leave_date
              )

            ORDER BY
                CASE
                    WHEN a.role = 'manager'
                    THEN 0
                    ELSE 1
                END,

                e.employee_id ASC
        ");

        $stmt->execute([
            ':leave_date' => $date
        ]);

        $rows =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $data = [];


        foreach ($rows as $row) {

            $name =
                trim(
                    ($row['first_name'] ?? '') .
                    ' ' .
                    ($row['middle_name'] ?? '') .
                    ' ' .
                    ($row['last_name'] ?? '')
                );


            $position =
                trim(
                    (string)(
                        $row['position_name']
                        ?? ''
                    )
                );


            if ($position === '') {

                $position =
                    strtolower(
                        (string)(
                            $row['account_role']
                            ?? ''
                        )
                    ) === 'manager'
                        ? 'Manager'
                        : 'Employee';
            }


            $accountStatus =
                strtolower(
                    (string)(
                        $row['account_status']
                        ?? ''
                    )
                );

            $employmentStatus =
                strtolower(
                    (string)(
                        $row['employment_status']
                        ?? ''
                    )
                );


            $isActive =
                $accountStatus === 'active'
                &&
                $employmentStatus === 'active';


            $status =
                $isActive
                    ? 'Active'
                    : 'Inactive';


            $data[] = [
                'id' =>
                    (string)$row['employee_id'],

                'name' =>
                    $name,

                'position' =>
                    $position,

                'accountRole' =>
                    (string)(
                        $row['account_role']
                        ?? ''
                    ),

                'status' =>
                    $status
            ];
        }


        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET SCHEDULES FOR MONTH/WEEK
    |--------------------------------------------------------------------------
    */

    $startDate =
        trim(
            (string)(
                $_GET['start_date']
                ?? ''
            )
        );

    $endDate =
        trim(
            (string)(
                $_GET['end_date']
                ?? ''
            )
        );


    if ($startDate === '') {
        $startDate =
            date('Y-m-d');
    }

    if ($endDate === '') {
        $endDate =
            $startDate;
    }


    if (
        !isValidAdminScheduleDate(
            $startDate
        )
        ||
        !isValidAdminScheduleDate(
            $endDate
        )
    ) {

        adminScheduleError(
            'Invalid schedule date range.',
            422
        );
    }


    if ($endDate < $startDate) {

        adminScheduleError(
            'End date cannot be before start date.',
            422
        );
    }


    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.employee_id,
            s.schedule_date,
            s.shift_name,
            s.start_time,
            s.end_time,
            s.work_type,
            s.remarks,
            s.status,

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

        WHERE s.schedule_date
              BETWEEN :start_date
              AND :end_date

          AND s.status <> 'cancelled'

          AND s.id = (
                SELECT MAX(s2.id)
                FROM schedules s2
                WHERE s2.employee_id = s.employee_id
                  AND s2.schedule_date = s.schedule_date
                  AND s2.status <> 'cancelled'
          )

        ORDER BY
            s.schedule_date ASC,
            e.employee_id ASC,
            s.id ASC
    ");


    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);


    echo json_encode([
        'success' => true,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'data' =>
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            )
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    adminScheduleError(
        'Only GET and POST requests are allowed.',
        405
    );
}


$input =
    json_decode(
        file_get_contents(
            'php://input'
        ),
        true
    );


if (!is_array($input)) {

    adminScheduleError(
        'Invalid request body.',
        400
    );
}


$action =
    trim(
        (string)(
            $input['action'] ?? ''
        )
    );


/*
|--------------------------------------------------------------------------
| CREATE SCHEDULE
|--------------------------------------------------------------------------
*/

if ($action === 'create') {

    $employeeId =
        trim(
            (string)(
                $input['employee_id']
                ?? ''
            )
        );

    $scheduleDate =
        trim(
            (string)(
                $input['schedule_date']
                ?? ''
            )
        );

    $shift =
        trim(
            (string)(
                $input['shift']
                ?? ''
            )
        );

    $workType =
        trim(
            (string)(
                $input['work_type']
                ?? ''
            )
        );

    $remarks =
        trim(
            (string)(
                $input['remarks']
                ?? ''
            )
        );


    if (
        $employeeId === '' ||
        $scheduleDate === '' ||
        $shift === '' ||
        $workType === ''
    ) {

        adminScheduleError(
            'Employee, date, shift, and work type are required.',
            422
        );
    }


    if (
        !isValidAdminScheduleDate(
            $scheduleDate
        )
    ) {

        adminScheduleError(
            'Invalid schedule date.',
            422
        );
    }


    $shiftData =
        getAdminShift($shift);


    if (!$shiftData) {

        adminScheduleError(
            'Invalid shift.',
            422
        );
    }


    if (
        !in_array(
            $workType,
            getAllowedWorkTypes(),
            true
        )
    ) {

        adminScheduleError(
            'Invalid work type.',
            422
        );
    }


    /*
     * Confirm employee exists.
     */

    $employeeStmt =
        $pdo->prepare("
            SELECT
                e.employee_id,
                e.employment_status,
                a.status AS account_status

            FROM employees e

            INNER JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE e.employee_id = :employee_id

              AND a.role IN (
                    'employee',
                    'manager'
              )

              AND e.employment_status <> 'terminated'

            LIMIT 1
        ");


    $employeeStmt->execute([
        ':employee_id' =>
            $employeeId
    ]);


    $employee =
        $employeeStmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$employee) {

        adminScheduleError(
            'Selected employee account was not found.',
            404
        );
    }


    /*
     * Do not schedule someone currently on leave.
     */

    if (
        strtolower(
            (string)(
                $employee['employment_status']
                ?? ''
            )
        ) === 'on_leave'
    ) {

        adminScheduleError(
            'This employee is currently on leave.',
            409
        );
    }


    /*
     * Do not schedule someone who has approved leave
     * covering this date.
     */

    $leaveStmt =
        $pdo->prepare("
            SELECT id
            FROM leave_requests

            WHERE employee_id = :employee_id

              AND status = 'approved'

              AND start_date <= :schedule_date

              AND end_date >= :schedule_date

            LIMIT 1
        ");


    $leaveStmt->execute([
        ':employee_id' =>
            $employeeId,

        ':schedule_date' =>
            $scheduleDate
    ]);


    if ($leaveStmt->fetch()) {

        adminScheduleError(
            'This employee has approved leave covering this date.',
            409
        );
    }


    /*
     * Prevent duplicate active schedule
     * for the same employee/date.
     */

    $duplicateStmt =
        $pdo->prepare("
            SELECT id
            FROM schedules

            WHERE employee_id = :employee_id

              AND schedule_date = :schedule_date

              AND status <> 'cancelled'

            LIMIT 1
        ");


    $duplicateStmt->execute([
        ':employee_id' =>
            $employeeId,

        ':schedule_date' =>
            $scheduleDate
    ]);


    if ($duplicateStmt->fetch()) {

        adminScheduleError(
            'This employee already has a schedule on this date.',
            409
        );
    }


    /*
     * Insert schedule.
     */

    $insert =
        $pdo->prepare("
            INSERT INTO schedules (
                employee_id,
                schedule_date,
                shift_name,
                start_time,
                end_time,
                work_type,
                remarks,
                status
            )

            VALUES (
                :employee_id,
                :schedule_date,
                :shift_name,
                :start_time,
                :end_time,
                :work_type,
                :remarks,
                'scheduled'
            )
        ");


    $insert->execute([
        ':employee_id' =>
            $employeeId,

        ':schedule_date' =>
            $scheduleDate,

        ':shift_name' =>
            $shiftData['shift_name'],

        ':start_time' =>
            $shiftData['start_time'],

        ':end_time' =>
            $shiftData['end_time'],

        ':work_type' =>
            $workType,

        ':remarks' =>
            $remarks !== ''
                ? $remarks
                : null
    ]);


    echo json_encode([
        'success' => true,
        'message' =>
            'Schedule saved successfully.',
        'schedule_id' =>
            (int)$pdo->lastInsertId()
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| RE-ASSIGN SCHEDULE
|--------------------------------------------------------------------------
*/

if ($action === 'reassign') {

    $scheduleId =
        (int)(
            $input['schedule_id']
            ?? 0
        );

    $replacementId =
        trim(
            (string)(
                $input[
                    'replacement_employee_id'
                ]
                ?? ''
            )
        );


    if (
        $scheduleId <= 0 ||
        $replacementId === ''
    ) {

        adminScheduleError(
            'Schedule and replacement employee are required.',
            422
        );
    }


    /*
     * Get current schedule.
     */

    $scheduleStmt =
        $pdo->prepare("
            SELECT
                id,
                employee_id,
                schedule_date,
                status

            FROM schedules

            WHERE id = :schedule_id

            LIMIT 1
        ");


    $scheduleStmt->execute([
        ':schedule_id' =>
            $scheduleId
    ]);


    $schedule =
        $scheduleStmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$schedule) {

        adminScheduleError(
            'Schedule not found.',
            404
        );
    }


    if (
        (string)$schedule['employee_id']
        ===
        $replacementId
    ) {

        adminScheduleError(
            'Replacement employee must be different from the current employee.',
            422
        );
    }


    /*
     * Make sure replacement exists.
     */

    $replacementStmt =
        $pdo->prepare("
            SELECT
                e.employee_id,
                e.employment_status,
                a.role,
                a.status AS account_status

            FROM employees e

            INNER JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE e.employee_id = :employee_id

              AND a.role IN (
                    'employee',
                    'manager'
              )

              AND e.employment_status <> 'terminated'

            LIMIT 1
        ");


    $replacementStmt->execute([
        ':employee_id' =>
            $replacementId
    ]);


    $replacement =
        $replacementStmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$replacement) {

        adminScheduleError(
            'Replacement employee was not found.',
            404
        );
    }


    if (
        strtolower(
            (string)(
                $replacement['employment_status']
                ?? ''
            )
        ) === 'on_leave'
    ) {

        adminScheduleError(
            'The replacement employee is currently on leave.',
            409
        );
    }


    /*
     * Check approved leave.
     */

    $replacementLeaveStmt =
        $pdo->prepare("
            SELECT id
            FROM leave_requests

            WHERE employee_id = :employee_id

              AND status = 'approved'

              AND start_date <= :schedule_date

              AND end_date >= :schedule_date

            LIMIT 1
        ");


    $replacementLeaveStmt->execute([
        ':employee_id' =>
            $replacementId,

        ':schedule_date' =>
            $schedule['schedule_date']
    ]);


    if ($replacementLeaveStmt->fetch()) {

        adminScheduleError(
            'The replacement employee has approved leave on this date.',
            409
        );
    }


    /*
     * Reassign.
     */

    $update =
        $pdo->prepare("
            UPDATE schedules

            SET
                employee_id = :employee_id,
                updated_at = CURRENT_TIMESTAMP

            WHERE id = :schedule_id
        ");


    $update->execute([
        ':employee_id' =>
            $replacementId,

        ':schedule_id' =>
            $scheduleId
    ]);


    echo json_encode([
        'success' => true,
        'message' =>
            'Schedule re-assigned successfully.'
    ]);

    exit;
}


adminScheduleError(
    'Invalid schedule action.',
    422
);