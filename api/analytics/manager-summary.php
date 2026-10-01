<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$managerUser = requireRole('manager');

$managerEmployeeId = trim(
    (string)($managerUser['employee_id'] ?? '')
);

if ($managerEmployeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Manager session not found.'
    ]);

    exit;
}

try {

    /*
     * Find the logged-in manager.
     */
    $managerStmt = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $managerStmt->execute([
        ':employee_id' => $managerEmployeeId
    ]);

    $manager = $managerStmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$manager) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Manager employee record not found.'
        ]);

        exit;
    }

    $managerId = (int)$manager['id'];

    /*
     * Get the manager's active team.
     */
    $teamStmt = $pdo->prepare("
        SELECT employee_id
        FROM employees
        WHERE manager_id = :manager_id
          AND employment_status <> 'terminated'
        ORDER BY employee_id
    ");

    $teamStmt->execute([
        ':manager_id' => $managerId
    ]);

    $teamRows = $teamStmt->fetchAll(
        PDO::FETCH_ASSOC
    );

    $teamIds = array_map(
        static fn(array $row): string =>
            (string)$row['employee_id'],
        $teamRows
    );

    $teamCount = count($teamIds);

    $today = new DateTime('today');

    $periodStart = clone $today;
    $periodStart->modify('-6 days');

    $startValue = $periodStart->format('Y-m-d');
    $todayValue = $today->format('Y-m-d');

    $expectedDays = 0;
    $attendingDays = 0;
    $lateArrivals = 0;
    $approvedLeaveDays = 0;
    $regularHours = 0.0;
    $overtimeHours = 0.0;

    /*
     * No team members means all analytics are zero.
     */
    if ($teamCount > 0) {

        $placeholders = implode(
            ',',
            array_fill(
                0,
                $teamCount,
                '?'
            )
        );

        /*
         * Schedules.
         */
        $scheduleSql = "
            SELECT
                employee_id,
                schedule_date,
                shift_name,
                start_time,
                end_time
            FROM schedules
            WHERE employee_id IN ($placeholders)
              AND schedule_date BETWEEN ? AND ?
              AND status <> 'cancelled'
            ORDER BY id ASC
        ";

        $scheduleStmt =
            $pdo->prepare($scheduleSql);

        $scheduleParams =
            array_merge(
                $teamIds,
                [
                    $startValue,
                    $todayValue
                ]
            );

        $scheduleStmt->execute(
            $scheduleParams
        );

        $scheduleRows =
            $scheduleStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $schedules = [];

        foreach ($scheduleRows as $row) {

            $key =
                $row['employee_id'] .
                '|' .
                $row['schedule_date'];

            $schedules[$key] = $row;
        }

        /*
         * Attendance.
         */
        $attendanceSql = "
            SELECT
                id,
                employee_id,
                attendance_date,
                time_in,
                time_out,
                status
            FROM attendance_records
            WHERE employee_id IN ($placeholders)
              AND attendance_date BETWEEN ? AND ?
            ORDER BY id ASC
        ";

        $attendanceStmt =
            $pdo->prepare(
                $attendanceSql
            );

        $attendanceStmt->execute(
            $scheduleParams
        );

        $attendanceRows =
            $attendanceStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $attendance = [];

        foreach ($attendanceRows as $row) {

            $key =
                $row['employee_id'] .
                '|' .
                $row['attendance_date'];

            /*
             * Keep the newest record when duplicates exist.
             */
            $attendance[$key] = $row;
        }

        /*
         * Approved leave.
         */
        $leaveSql = "
            SELECT
                employee_id,
                start_date,
                end_date
            FROM leave_requests
            WHERE employee_id IN ($placeholders)
              AND status = 'approved'
              AND start_date <= ?
              AND end_date >= ?
        ";

        $leaveStmt =
            $pdo->prepare(
                $leaveSql
            );

        $leaveParams =
            array_merge(
                $teamIds,
                [
                    $todayValue,
                    $startValue
                ]
            );

        $leaveStmt->execute(
            $leaveParams
        );

        $leaveRows =
            $leaveStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        /*
         * Build approved-leave lookup by employee/date.
         */
        $leaveLookup = [];

        foreach ($leaveRows as $leave) {

            $leaveStart =
                new DateTime(
                    $leave['start_date']
                );

            $leaveEnd =
                new DateTime(
                    $leave['end_date']
                );

            for (
                $date = clone $leaveStart;
                $date <= $leaveEnd;
                $date->modify('+1 day')
            ) {

                if (
                    $date < $periodStart ||
                    $date > $today
                ) {
                    continue;
                }

                /*
                 * Analytics count weekdays only.
                 */
                $weekday =
                    (int)$date->format('N');

                if ($weekday >= 6) {
                    continue;
                }

                $key =
                    $leave['employee_id'] .
                    '|' .
                    $date->format('Y-m-d');

                $leaveLookup[$key] = true;
            }
        }

        /*
         * Calculate the analytics over the last 7 days.
         */
        foreach ($teamIds as $employeeId) {

            for (
                $date = clone $periodStart;
                $date <= $today;
                $date->modify('+1 day')
            ) {

                $weekday =
                    (int)$date->format('N');

                if ($weekday >= 6) {
                    continue;
                }

                $dateValue =
                    $date->format('Y-m-d');

                $key =
                    $employeeId .
                    '|' .
                    $dateValue;

                $schedule =
                    $schedules[$key] ?? null;

                $attendance =
                    $attendance[$key] ?? null;

                /*
                 * Approved leave day.
                 */
                if (
                    isset(
                        $leaveLookup[$key]
                    )
                ) {
                    $approvedLeaveDays++;
                }

                /*
                 * Expected work day.
                 */
                if ($schedule) {

                    $shiftName =
                        strtoupper(
                            trim(
                                (string)(
                                    $schedule['shift_name']
                                    ?? ''
                                )
                            )
                        );

                    $isOff =
                        $shiftName === '' ||
                        in_array(
                            $shiftName,
                            [
                                'OFF',
                                'LEAVE',
                                'ABSENT'
                            ],
                            true
                        );

                    if (
                        !$isOff &&
                        !isset(
                            $leaveLookup[$key]
                        )
                    ) {

                        $expectedDays++;

                        if (
                            $attendance &&
                            in_array(
                                strtolower(
                                    (string)(
                                        $attendance['status']
                                        ?? ''
                                    )
                                ),
                                [
                                    'present',
                                    'late'
                                ],
                                true
                            )
                        ) {
                            $attendingDays++;
                        }
                    }
                }

                /*
                 * Late arrivals.
                 */
                if (
                    $attendance &&
                    strtolower(
                        (string)(
                            $attendance['status']
                            ?? ''
                        )
                    ) === 'late'
                ) {
                    $lateArrivals++;
                }

                /*
                 * Worked hours.
                 */
                if (
                    $attendance &&
                    !empty(
                        $attendance['time_in']
                    ) &&
                    !empty(
                        $attendance['time_out']
                    )
                ) {

                    $timeIn =
                        strtotime(
                            $attendance['time_in']
                        );

                    $timeOut =
                        strtotime(
                            $attendance['time_out']
                        );

                    if (
                        $timeIn &&
                        $timeOut &&
                        $timeOut > $timeIn
                    ) {

                        $workedMinutes =
                            max(
                                0,
                                (
                                    $timeOut -
                                    $timeIn
                                ) / 60 - 60
                            );

                        $regularMinutes =
                            min(
                                480,
                                $workedMinutes
                            );

                        $overtimeMinutes =
                            max(
                                0,
                                $workedMinutes -
                                480
                            );

                        $regularHours +=
                            $regularMinutes / 60;

                        $overtimeHours +=
                            $overtimeMinutes / 60;
                    }
                }
            }
        }
    }

    $attendanceRate =
        $expectedDays > 0
            ? round(
                (
                    $attendingDays /
                    $expectedDays
                ) * 100,
                1
            )
            : 0.0;

    echo json_encode([
        'success' => true,
        'data' => [
            'team_members' =>
                $teamCount,

            'attendance_rate' =>
                $attendanceRate,

            'late_arrivals' =>
                $lateArrivals,

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

            'period_start' =>
                $startValue,

            'period_end' =>
                $todayValue
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager analytics summary failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load workforce analytics summary.'
    ]);
}