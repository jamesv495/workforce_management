<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

$employeeId = trim(
    (string)($_GET['employee_id'] ?? '')
);

if ($employeeId === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required.'
    ]);

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Employee information
    |--------------------------------------------------------------------------
    */

    $employeeStmt = $pdo->prepare("
        SELECT
            e.employee_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.avatar_url,
            e.date_of_birth,
            e.gender,
            e.address,
            e.phone,
            e.email,
            e.department_name,
            e.position_name,
            e.hire_date,
            e.employment_type,
            e.employment_status,
            e.emergency_name,
            e.emergency_relationship,
            e.emergency_phone,
            a.updated_at AS password_updated_at
        FROM employees e
        LEFT JOIN accounts a
            ON a.employee_id = e.employee_id
        WHERE e.employee_id = :employee_id
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee = $employeeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Employee record not found.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance summary - current month
    |--------------------------------------------------------------------------
    */

    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');

    $attendanceStmt = $pdo->prepare("
        SELECT
            SUM(
                CASE
                    WHEN status IN ('present', 'late')
                    THEN 1
                    ELSE 0
                END
            ) AS days_present,

            SUM(
                CASE
                    WHEN status = 'late'
                    THEN 1
                    ELSE 0
                END
            ) AS late_arrivals

        FROM attendance_records

        WHERE employee_id = :employee_id
          AND attendance_date BETWEEN :month_start AND :month_end
    ");

    $attendanceStmt->execute([
        ':employee_id' => $employeeId,
        ':month_start' => $monthStart,
        ':month_end' => $monthEnd
    ]);

    $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    /*
    |--------------------------------------------------------------------------
    | Upcoming schedule
    |--------------------------------------------------------------------------
    */

    $scheduleStmt = $pdo->prepare("
        SELECT
            schedule_date,
            shift_name,
            start_time,
            end_time,
            status
        FROM schedules
        WHERE employee_id = :employee_id
          AND schedule_date >= CURDATE()
          AND status <> 'cancelled'
        ORDER BY schedule_date ASC, id ASC
        LIMIT 10
    ");

    $scheduleStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $schedule = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Approved leave used
    |--------------------------------------------------------------------------
    */

    $leaveStmt = $pdo->prepare("
        SELECT
            leave_type,
            start_date,
            end_date
        FROM leave_requests
        WHERE employee_id = :employee_id
          AND status = 'approved'
        ORDER BY start_date DESC
    ");

    $leaveStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $leaveRows = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);

    $vacationUsed = 0;
    $sickUsed = 0;
    $emergencyUsed = 0;

    foreach ($leaveRows as $leave) {

        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);

        $days = 0;

        for (
            $date = clone $start;
            $date <= $end;
            $date->modify('+1 day')
        ) {
            if ((int)$date->format('N') <= 5) {
                $days++;
            }
        }

        if ($leave['leave_type'] === 'Vacation Leave') {
            $vacationUsed += $days;
        }

        if ($leave['leave_type'] === 'Sick Leave') {
            $sickUsed += $days;
        }
        
        if ($leave['leave_type'] === 'Emergency Leave') {
    $emergencyUsed += $days;
}
    }

    /*
|--------------------------------------------------------------------------
| Leave balance
|--------------------------------------------------------------------------
*/

$balanceStmt = $pdo->prepare("
    SELECT
        vacation_days,
        sick_days,
        emergency_days
    FROM employee_leave_balances
    WHERE employee_id = :employee_id
      AND balance_year = YEAR(CURDATE())
    LIMIT 1
");

$balanceStmt->execute([
    ':employee_id' => $employeeId
]);

$leaveBalance =
    $balanceStmt->fetch(PDO::FETCH_ASSOC);

$vacationEntitled =
    (float)($leaveBalance['vacation_days'] ?? 0);

$sickEntitled =
    (float)($leaveBalance['sick_days'] ?? 0);

$emergencyEntitled =
    (float)($leaveBalance['emergency_days'] ?? 0);

$vacationRemaining =
    max(
        0,
        $vacationEntitled - $vacationUsed
    );

$sickRemaining =
    max(
        0,
        $sickEntitled - $sickUsed
    );

$emergencyRemaining =
    max(
        0,
        $emergencyEntitled - $emergencyUsed
    );

    /*
    |--------------------------------------------------------------------------
    | Timesheet history
    | Uses actual attendance records because the database stores
    | time in / time out in attendance_records.
    |--------------------------------------------------------------------------
    */

    $timesheetStmt = $pdo->prepare("
        SELECT
            attendance_date,
            time_in,
            time_out,
            status
        FROM attendance_records
        WHERE employee_id = :employee_id
        ORDER BY attendance_date DESC, id DESC
        LIMIT 10
    ");

    $timesheetStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $timesheetRows = $timesheetStmt->fetchAll(PDO::FETCH_ASSOC);

    $timesheetHistory = [];

    foreach ($timesheetRows as $row) {

        $workedHours = 0;

        if (
            !empty($row['time_in']) &&
            !empty($row['time_out'])
        ) {
            $timeIn = strtotime($row['time_in']);
            $timeOut = strtotime($row['time_out']);

            if (
                $timeIn !== false &&
                $timeOut !== false &&
                $timeOut > $timeIn
            ) {
                $workedHours = round(
                    ($timeOut - $timeIn) / 3600,
                    2
                );
            }
        }

        $timesheetHistory[] = [
            'date' => $row['attendance_date'],
            'time_in' => $row['time_in'],
            'time_out' => $row['time_out'],
            'worked_hours' => $workedHours,
            'status' => $row['status']
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'success' => true,

        'data' => [
            'employee' => [
                'employee_id' => $employee['employee_id'],
                'first_name' => $employee['first_name'],
                'middle_name' => $employee['middle_name'],
                'last_name' => $employee['last_name'],
                'avatar_url' => $employee['avatar_url'],
                'full_name' => trim(
                    implode(
                        ' ',
                        array_filter([
                            $employee['first_name'],
                            $employee['middle_name'],
                            $employee['last_name']
                        ])
                    )
                ),
                'date_of_birth' => $employee['date_of_birth'],
                'gender' => $employee['gender'],
                'address' => $employee['address'],
                'phone' => $employee['phone'],
                'email' => $employee['email'],
                'department' => $employee['department_name'],
                'position' => $employee['position_name'],
                'hire_date' => $employee['hire_date'],
                'employment_type' => $employee['employment_type'],
                'employment_status' => $employee['employment_status'],
                'emergency_name' => $employee['emergency_name'],
                'emergency_relationship' => $employee['emergency_relationship'],
                'emergency_phone' => $employee['emergency_phone'],
                'password_updated_at' => !empty($employee['password_updated_at'])
                    ? date('m/d/y', strtotime($employee['password_updated_at']))
                    : null
            ],

            'attendance_summary' => [
                'days_present' => (int)(
                    $attendance['days_present'] ?? 0
                ),
                'late_arrivals' => (int)(
                    $attendance['late_arrivals'] ?? 0
                ),
                'period_start' => $monthStart,
                'period_end' => $monthEnd
            ],

            'schedule' => $schedule,

            'leave_balance' => [
    'vacation_entitled' => $vacationEntitled,
    'vacation_used' => $vacationUsed,
    'vacation_remaining' => $vacationRemaining,

    'sick_entitled' => $sickEntitled,
    'sick_used' => $sickUsed,
    'sick_remaining' => $sickRemaining,

    'emergency_entitled' => $emergencyEntitled,
    'emergency_used' => $emergencyUsed,
    'emergency_remaining' => $emergencyRemaining
],

            'timesheet_history' => $timesheetHistory
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin employee profile API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load employee profile.'
    ]);
}