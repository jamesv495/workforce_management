<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

try {

    $today = new DateTime('today');

    $periodStart = clone $today;
    $periodStart->modify('-6 days');

    $startValue = $periodStart->format('Y-m-d');
    $todayValue = $today->format('Y-m-d');

    /*
     * Active employees.
     */
    $employeeStmt = $pdo->query("
        SELECT COUNT(*)
        FROM employees
        WHERE employment_status = 'active'
    ");

    $activeEmployees =
        (int)$employeeStmt->fetchColumn();

    /*
     * Attendance records during the last 7 days.
     */
    $attendanceStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_records,

            SUM(
                CASE
                    WHEN status IN ('present', 'late')
                    THEN 1
                    ELSE 0
                END
            ) AS attended_records,

            SUM(
                CASE
                    WHEN status = 'late'
                    THEN 1
                    ELSE 0
                END
            ) AS late_records,

            SUM(
                CASE
                    WHEN status = 'absent'
                    THEN 1
                    ELSE 0
                END
            ) AS absent_records

        FROM attendance_records
        WHERE attendance_date BETWEEN :start_date AND :end_date
    ");

    $attendanceStmt->execute([
        ':start_date' => $startValue,
        ':end_date' => $todayValue
    ]);

    $attendance =
        $attendanceStmt->fetch(
            PDO::FETCH_ASSOC
        ) ?: [];

    $totalAttendance =
        (int)($attendance['total_records'] ?? 0);

    $attendedRecords =
        (int)($attendance['attended_records'] ?? 0);

    $lateRecords =
        (int)($attendance['late_records'] ?? 0);

    $absentRecords =
        (int)($attendance['absent_records'] ?? 0);

    /*
     * Approved leave days during the last 7 days.
     */
    $leaveStmt = $pdo->prepare("
        SELECT
            employee_id,
            start_date,
            end_date
        FROM leave_requests
        WHERE status = 'approved'
          AND start_date <= :end_date
          AND end_date >= :start_date
    ");

    $leaveStmt->execute([
        ':start_date' => $startValue,
        ':end_date' => $todayValue
    ]);

    $approvedLeaveDays = 0;

    while ($leave = $leaveStmt->fetch(PDO::FETCH_ASSOC)) {

        $leaveStart =
            new DateTime($leave['start_date']);

        $leaveEnd =
            new DateTime($leave['end_date']);

        if ($leaveStart < $periodStart) {
            $leaveStart =
                clone $periodStart;
        }

        if ($leaveEnd > $today) {
            $leaveEnd =
                clone $today;
        }

        for (
            $date = clone $leaveStart;
            $date <= $leaveEnd;
            $date->modify('+1 day')
        ) {

            $weekday =
                (int)$date->format('N');

            if ($weekday <= 5) {
                $approvedLeaveDays++;
            }
        }
    }

    /*
     * Scheduled workdays in the last 7 days.
     */
    $scheduleStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS scheduled_rows
        FROM schedules s
        INNER JOIN employees e
            ON e.employee_id = s.employee_id
        WHERE s.schedule_date BETWEEN :start_date AND :end_date
          AND s.status <> 'cancelled'
          AND e.employment_status <> 'terminated'
          AND UPPER(COALESCE(s.shift_name, '')) NOT IN (
              'OFF',
              'LEAVE',
              'ABSENT'
          )
    ");

    $scheduleStmt->execute([
        ':start_date' => $startValue,
        ':end_date' => $todayValue
    ]);

    $scheduledRows =
        (int)$scheduleStmt->fetchColumn();

    /*
     * Worked hours from attendance.
     */
    $hoursStmt = $pdo->prepare("
        SELECT
            time_in,
            time_out
        FROM attendance_records
        WHERE attendance_date BETWEEN :start_date AND :end_date
          AND time_in IS NOT NULL
          AND time_out IS NOT NULL
    ");

    $hoursStmt->execute([
        ':start_date' => $startValue,
        ':end_date' => $todayValue
    ]);

    $regularHours = 0.0;
    $overtimeHours = 0.0;

    while ($row = $hoursStmt->fetch(PDO::FETCH_ASSOC)) {

        $timeIn =
            strtotime($row['time_in']);

        $timeOut =
            strtotime($row['time_out']);

        if (
            !$timeIn ||
            !$timeOut ||
            $timeOut <= $timeIn
        ) {
            continue;
        }

        $workedMinutes = max(
            0,
            (
                $timeOut -
                $timeIn
            ) / 60 - 60
        );

        $regularMinutes =
            min(480, $workedMinutes);

        $overtimeMinutes =
            max(
                0,
                $workedMinutes - 480
            );

        $regularHours +=
            $regularMinutes / 60;

        $overtimeHours +=
            $overtimeMinutes / 60;
    }

    /*
     * Attendance rate.
     */
    $attendanceRate =
        $totalAttendance > 0
            ? round(
                (
                    $attendedRecords /
                    $totalAttendance
                ) * 100,
                1
            )
            : 0.0;

    /*
     * Shift fulfillment.
     */
    $shiftFulfillment =
        $scheduledRows > 0
            ? round(
                min(
                    100,
                    (
                        $attendedRecords /
                        $scheduledRows
                    ) * 100
                ),
                1
            )
            : 0.0;

    /*
     * Department summary.
     */
    $departmentStmt = $pdo->prepare("
        SELECT
            COALESCE(
                NULLIF(e.department_name, ''),
                'Unassigned'
            ) AS department_name,
            COUNT(*) AS employee_count
        FROM employees e
        WHERE e.employment_status = 'active'
        GROUP BY
            COALESCE(
                NULLIF(e.department_name, ''),
                'Unassigned'
            )
        ORDER BY employee_count DESC
    ");

    $departmentStmt->execute();

    $departments = [];

    while (
        $row =
            $departmentStmt->fetch(
                PDO::FETCH_ASSOC
            )
    ) {
        $departments[] = [
            'department' =>
                (string)$row['department_name'],

            'employees' =>
                (int)$row['employee_count']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'active_employees' =>
                $activeEmployees,

            'attendance_rate' =>
                $attendanceRate,

            'late_records' =>
                $lateRecords,

            'absent_records' =>
                $absentRecords,

            'approved_leave_days' =>
                $approvedLeaveDays,

            'regular_hours' =>
                round(
                    $regularHours,
                    2
                ),

            'overtime_hours' =>
                round(
                    $overtimeHours,
                    2
                ),

            'shift_fulfillment' =>
                $shiftFulfillment,

            'departments' =>
                $departments,

            'period_start' =>
                $startValue,

            'period_end' =>
                $todayValue
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin analytics API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load Admin Workforce Analytics.'
    ]);
}