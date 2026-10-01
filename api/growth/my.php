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
    |--------------------------------------------------------------------------
    | Employee
    |--------------------------------------------------------------------------
    */

    $employeeStmt = $pdo->prepare("
        SELECT
            employee_id,
            CONCAT_WS(
                ' ',
                first_name,
                middle_name,
                last_name
            ) AS employee_name,
            position_name,
            department_name,
            hire_date
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee =
        $employeeStmt->fetch(PDO::FETCH_ASSOC);

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
    | Current year
    |--------------------------------------------------------------------------
    */

    $yearStart = date('Y-01-01');
    $yearEnd = date('Y-12-31');

    /*
    |--------------------------------------------------------------------------
    | Attendance, Work Hours and Overtime
    |--------------------------------------------------------------------------
    |
    | Work hours:
    |   Time Out - Time In - 1 hour meal break
    |
    | Regular hours:
    |   Maximum 8 hours per completed attendance record
    |
    | Overtime:
    |   Hours above 8 per completed attendance record
    |
    */

    $attendanceStmt = $pdo->prepare("
        SELECT
            attendance_date,
            status,
            time_in,
            time_out
        FROM attendance_records
        WHERE employee_id = :employee_id
          AND attendance_date BETWEEN :year_start AND :year_end
        ORDER BY attendance_date ASC, id ASC
    ");

    $attendanceStmt->execute([
        ':employee_id' => $employeeId,
        ':year_start' => $yearStart,
        ':year_end' => $yearEnd
    ]);

    $attendanceRows =
        $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
|--------------------------------------------------------------------------
| Attendance Trend - Last 4 Weeks
|--------------------------------------------------------------------------
*/

$attendanceTrend = [];

$today = new DateTime('today');

$currentWeekStart = clone $today;
$currentDayNumber =
    (int)$currentWeekStart->format('N');

if ($currentDayNumber !== 1) {
    $currentWeekStart->modify(
        '-' . ($currentDayNumber - 1) . ' days'
    );
}

for ($week = 3; $week >= 0; $week--) {

    $weekStart = clone $currentWeekStart;

    if ($week > 0) {
        $weekStart->modify(
            '-' . ($week * 7) . ' days'
        );
    }

    $weekEnd = clone $weekStart;
    $weekEnd->modify('+4 days');

    $weekTotal = 0;
    $weekAttended = 0;

    foreach ($attendanceRows as $row) {

        if (empty($row['attendance_date'])) {
            continue;
        }

        $attendanceDate =
            new DateTime(
                $row['attendance_date']
            );

        if (
            $attendanceDate < $weekStart ||
            $attendanceDate > $weekEnd
        ) {
            continue;
        }

        $weekTotal++;

        $status =
            strtolower(
                trim(
                    (string)(
                        $row['status'] ?? ''
                    )
                )
            );

        if (
            $status === 'present' ||
            $status === 'late'
        ) {
            $weekAttended++;
        }
    }

    $weekRate =
        $weekTotal > 0
            ? round(
                (
                    $weekAttended /
                    $weekTotal
                ) * 100,
                1
            )
            : 0;

    $weekNumber = 4 - $week;

    $attendanceTrend[] = [
        'label' => 'W' . $weekNumber,
        'rate' => $weekRate
    ];
}    

    $totalRecords = 0;
    $presentRecords = 0;
    $lateRecords = 0;
    $absentRecords = 0;

    $totalWorkHours = 0.0;
    $totalOvertimeHours = 0.0;

    foreach ($attendanceRows as $row) {

        $status =
            strtolower(
                trim(
                    (string)($row['status'] ?? '')
                )
            );

        $totalRecords++;

        if ($status === 'present') {
            $presentRecords++;
        }

        if ($status === 'late') {
            $lateRecords++;
        }

        if ($status === 'absent') {
            $absentRecords++;
        }

        /*
         * Only calculate worked hours when
         * both Time In and Time Out exist.
         */
        if (
            empty($row['time_in']) ||
            empty($row['time_out'])
        ) {
            continue;
        }

        $timeIn =
            strtotime(
                (string)$row['time_in']
            );

        $timeOut =
            strtotime(
                (string)$row['time_out']
            );

        if (
            $timeIn === false ||
            $timeOut === false ||
            $timeOut <= $timeIn
        ) {
            continue;
        }

        $rawHours =
            ($timeOut - $timeIn) / 3600;

        /*
         * Deduct one-hour meal break.
         */
        $workedHours =
            max(
                0,
                $rawHours - 1
            );

        $regularHours =
            min(
                8,
                $workedHours
            );

        $overtimeHours =
            max(
                0,
                $workedHours - 8
            );

        $totalWorkHours +=
            $regularHours;

        $totalOvertimeHours +=
            $overtimeHours;
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance percentage
    |--------------------------------------------------------------------------
    |
    | Present and Late are counted as attended days.
    */

    $attendedRecords =
        $presentRecords +
        $lateRecords;

    $attendanceRate =
        $totalRecords > 0
            ? round(
                (
                    $attendedRecords /
                    $totalRecords
                ) * 100,
                1
            )
            : 0.0;

    /*
    |--------------------------------------------------------------------------
    | Approved leave used this calendar year
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
          AND start_date <= :year_end
          AND end_date >= :year_start
        ORDER BY start_date ASC
    ");

    $leaveStmt->execute([
        ':employee_id' => $employeeId,
        ':year_start' => $yearStart,
        ':year_end' => $yearEnd
    ]);

    $leaveByType = [];
    $totalLeaveDays = 0;

    while (
        $leave =
            $leaveStmt->fetch(PDO::FETCH_ASSOC)
    ) {

        $start =
            new DateTime(
                $leave['start_date']
            );

        $end =
            new DateTime(
                $leave['end_date']
            );

        $yearStartDate =
            new DateTime($yearStart);

        $yearEndDate =
            new DateTime($yearEnd);

        if ($start < $yearStartDate) {
            $start =
                clone $yearStartDate;
        }

        if ($end > $yearEndDate) {
            $end =
                clone $yearEndDate;
        }

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

        $type =
            (string)$leave['leave_type'];

        $leaveByType[$type] =
            ($leaveByType[$type] ?? 0) +
            $days;

        $totalLeaveDays += $days;
    }

    /*
|--------------------------------------------------------------------------
| Timesheet Completion
|--------------------------------------------------------------------------
|
| Completion rate:
| Approved timesheet reviews / Total timesheet reviews
|
*/

$timesheetStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_reviews,
        SUM(
            CASE
                WHEN status = 'approved' THEN 1
                ELSE 0
            END
        ) AS approved_reviews
    FROM timesheet_reviews
    WHERE employee_id = :employee_id
      AND period_start >= :year_start
      AND period_end <= :year_end
");

$timesheetStmt->execute([
    ':employee_id' => $employeeId,
    ':year_start' => $yearStart,
    ':year_end' => $yearEnd
]);

$timesheetData =
    $timesheetStmt->fetch(PDO::FETCH_ASSOC);

$totalReviews =
    (int)($timesheetData['total_reviews'] ?? 0);

$approvedReviews =
    (int)($timesheetData['approved_reviews'] ?? 0);

$timesheetCompletionRate =
    $totalReviews > 0
        ? round(
            (
                $approvedReviews /
                $totalReviews
            ) * 100,
            1
        )
        : 0.0;

    /*
    |--------------------------------------------------------------------------
    | Years of service
    |--------------------------------------------------------------------------
    */

    $yearsOfService = 0;

    if (!empty($employee['hire_date'])) {

        $hireDate =
            new DateTime(
                $employee['hire_date']
            );

        $today =
            new DateTime('today');

        $yearsOfService =
            $hireDate->diff($today)->y;
    }

    echo json_encode([
        'success' => true,

        'data' => [

            'employee_id' =>
                $employee['employee_id'],

            'employee_name' =>
                $employee['employee_name'],

            'position' =>
                $employee['position_name'] ?? '',

            'department' =>
                $employee['department_name'] ?? '',

            'hire_date' =>
                $employee['hire_date'],

            'years_of_service' =>
                $yearsOfService,

            'attendance_rate' =>
                $attendanceRate,

            'present_records' =>
                $presentRecords,

            'late_records' =>
                $lateRecords,

            'absent_records' =>
                $absentRecords,

            'attendance_records' =>
                $totalRecords,
             
            'attendance_trend' =>
                $attendanceTrend,    

            'work_hours' =>
                round(
                    $totalWorkHours,
                    2
                ),

            'overtime_hours' =>
                round(
                    $totalOvertimeHours,
                    2
                ),

            'leave_used_days' =>
                $totalLeaveDays,

            'timesheet_completion_rate' =>
                $timesheetCompletionRate,         

            'leave_by_type' => array_map(
                static function (
                    $type,
                    $used
                ) {
                    return [
                        'type' => $type,
                        'used' => $used
                    ];
                },
                array_keys($leaveByType),
                array_values($leaveByType)
            ),

            'period_start' =>
                $yearStart,

            'period_end' =>
                $yearEnd
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Employee growth API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load employee growth information.'
    ]);
}