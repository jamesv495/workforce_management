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

function timeToMinutes(?string $value): ?int
{
    if (!$value) {
        return null;
    }

    $parts = explode(':', $value);

    if (count($parts) < 2) {
        return null;
    }

    return ((int)$parts[0] * 60) + (int)$parts[1];
}

function formatTime(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    return date('H:i', strtotime($value));
}

try {

    $today = new DateTime('today');

    $weekStart = clone $today;

    $dayNumber = (int)$weekStart->format('N');

    if ($dayNumber !== 1) {
        $weekStart->modify(
            '-' . ($dayNumber - 1) . ' days'
        );
    }

    $weekEnd = clone $weekStart;
    $weekEnd->modify('+6 days');

    $startDate = $weekStart->format('Y-m-d');
    $endDate = $weekEnd->format('Y-m-d');

    $employeeStmt = $pdo->prepare("
        SELECT
            employee_id,
            first_name,
            middle_name,
            last_name,
            position_name
        FROM employees
        WHERE employee_id = :employee_id
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

    $stmt = $pdo->prepare("
        SELECT
            s.schedule_date,
            s.shift_name,
            s.start_time,
            s.end_time,

            a.time_in,
            a.time_out,
            a.status AS attendance_status,
            a.notes AS attendance_notes

        FROM schedules s

        LEFT JOIN attendance_records a
            ON a.employee_id = s.employee_id
            AND a.attendance_date = s.schedule_date
            AND a.id = (
                SELECT MAX(a2.id)
                FROM attendance_records a2
                WHERE a2.employee_id = a.employee_id
                  AND a2.attendance_date = a.attendance_date
            )

        WHERE s.employee_id = :employee_id
          AND s.schedule_date BETWEEN :start_date AND :end_date
          AND s.status <> 'cancelled'

        ORDER BY
            s.schedule_date ASC,
            s.id DESC
    ");

    $stmt->execute([
        ':employee_id' => $employeeId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byDate = [];

    foreach ($rows as $row) {
        $date = (string)$row['schedule_date'];

        if (!isset($byDate[$date])) {
            $byDate[$date] = $row;
        }
    }

    $reviewStmt = $pdo->prepare("
        SELECT
            status,
            remarks,
            reviewed_at
        FROM timesheet_reviews
        WHERE employee_id = :employee_id
          AND period_start = :period_start
          AND period_end = :period_end
        ORDER BY id DESC
        LIMIT 1
    ");

    $reviewStmt->execute([
        ':employee_id' => $employeeId,
        ':period_start' => $startDate,
        ':period_end' => $endDate
    ]);

    $review = $reviewStmt->fetch(PDO::FETCH_ASSOC);

    $reviewStatus =
        (string)($review['status'] ?? 'pending');

    $reviewLabels = [
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected'
    ];

    $reviewLabel =
        $reviewLabels[$reviewStatus]
        ?? 'Pending Approval';

    $weekdayNames = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday'
    ];

    $days = [];

    for ($offset = 0; $offset <= 4; $offset++) {

        $date = clone $weekStart;

        if ($offset > 0) {
            $date->modify(
                '+' . $offset . ' days'
            );
        }

        $dateISO = $date->format('Y-m-d');

        $row = $byDate[$dateISO] ?? null;

        $timeIn = formatTime(
            $row['time_in'] ?? null
        );

        $timeOut = formatTime(
            $row['time_out'] ?? null
        );

        $workedHours = 0;

        $inMinutes = timeToMinutes($timeIn);
        $outMinutes = timeToMinutes($timeOut);

        if (
            $inMinutes !== null &&
            $outMinutes !== null &&
            $outMinutes > $inMinutes
        ) {
            $workedHours = max(
                0,
                ($outMinutes - $inMinutes - 60) / 60
            );
        }

        $regularHours = min(
            8,
            $workedHours
        );

        $overtimeHours = max(
            0,
            $workedHours - 8
        );

        $status = 'Day Off';

        if ($row) {

            if (!$timeIn && !$timeOut) {
                $status = 'Absent';

            } elseif ($timeIn && !$timeOut) {
                $status = 'In Progress';

            } elseif (
                strtolower(
                    (string)($row['attendance_status'] ?? '')
                ) === 'late'
            ) {
                $status = 'Late';

            } else {
                $status = 'Completed';
            }
        }

        $days[] = [
            'date' => $dateISO,
            'day' => $weekdayNames[$offset + 1],

            'shiftName' =>
                $row['shift_name'] ?? null,

            'timeIn' =>
                $timeIn,

            'timeOut' =>
                $timeOut,

            'regularHours' =>
                round($regularHours, 2),

            'overtimeHours' =>
                round($overtimeHours, 2),

            'totalHours' =>
                round($workedHours, 2),

            'status' =>
                $status
        ];
    }

    $regularTotal = round(
        array_sum(
            array_column(
                $days,
                'regularHours'
            )
        ),
        2
    );

    $overtimeTotal = round(
        array_sum(
            array_column(
                $days,
                'overtimeHours'
            )
        ),
        2
    );

    $totalHours = round(
        $regularTotal + $overtimeTotal,
        2
    );

    echo json_encode([
        'success' => true,

        'employee' => [
            'employeeId' =>
                (string)$employee['employee_id'],

            'name' =>
                trim(
                    ($employee['first_name'] ?? '') .
                    ' ' .
                    ($employee['middle_name'] ?? '') .
                    ' ' .
                    ($employee['last_name'] ?? '')
                ),

            'position' =>
                (string)(
                    $employee['position_name'] ?? ''
                )
        ],

        'period' => [
            'start' => $startDate,
            'end' => $endDate,

            'label' =>
                $weekStart->format('F j') .
                ' – ' .
                $weekEnd->format('j, Y')
        ],

        'review' => [
            'status' => $reviewStatus,
            'label' => $reviewLabel,
            'remarks' =>
                (string)(
                    $review['remarks'] ?? ''
                ),
            'reviewedAt' =>
                $review['reviewed_at'] ?? null
        ],

        'days' => $days,

        'summary' => [
            'regularHours' =>
                $regularTotal,

            'overtimeHours' =>
                $overtimeTotal,

            'totalHours' =>
                $totalHours
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Employee timesheet API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to load employee timesheet.'
    ]);
}