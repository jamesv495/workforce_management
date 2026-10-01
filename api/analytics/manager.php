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
     * Find the logged-in manager's employee row.
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

    $manager = $managerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Manager employee record not found.'
        ]);
        exit;
    }

    /*
     * Count the manager's active team.
     */
    $teamStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM employees
        WHERE manager_id = :manager_id
          AND employment_status <> 'terminated'
    ");

    $teamStmt->execute([
        ':manager_id' => (int)$manager['id']
    ]);

    $teamCount = (int)$teamStmt->fetchColumn();

    /*
     * Build the last 7 calendar days, including today.
     */
    $today = new DateTime('today');

    $dailyStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS team_count,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM schedules s
                        WHERE s.employee_id = e.employee_id
                          AND s.schedule_date = :schedule_date
                          AND s.status <> 'cancelled'
                          AND s.shift_name IS NOT NULL
                          AND UPPER(s.shift_name) NOT IN ('OFF', 'LEAVE', 'ABSENT')
                    )
                    AND NOT EXISTS (
                        SELECT 1
                        FROM leave_requests lr
                        WHERE lr.employee_id = e.employee_id
                          AND lr.status = 'approved'
                          AND :leave_date BETWEEN lr.start_date AND lr.end_date
                    )
                    THEN 1
                    ELSE 0
                END
            ) AS expected_count,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM attendance_records a
                        WHERE a.employee_id = e.employee_id
                          AND a.attendance_date = :attendance_date
                          AND a.status IN ('present', 'late')
                    )
                    THEN 1
                    ELSE 0
                END
            ) AS attending_count,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM attendance_records a2
                        WHERE a2.employee_id = e.employee_id
                          AND a2.attendance_date = :late_date
                          AND a2.status = 'late'
                    )
                    THEN 1
                    ELSE 0
                END
            ) AS late_count,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM leave_requests lr2
                        WHERE lr2.employee_id = e.employee_id
                          AND lr2.status = 'approved'
                          AND :approved_leave_date BETWEEN lr2.start_date AND lr2.end_date
                    )
                    THEN 1
                    ELSE 0
                END
            ) AS leave_count

        FROM employees e

        WHERE e.manager_id = :manager_id
          AND e.employment_status <> 'terminated'
    ");

    $days = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = clone $today;

        if ($i > 0) {
            $date->modify("-{$i} days");
        }

        $dateValue = $date->format('Y-m-d');

        $dailyStmt->execute([
            ':schedule_date' => $dateValue,
            ':leave_date' => $dateValue,
            ':attendance_date' => $dateValue,
            ':late_date' => $dateValue,
            ':approved_leave_date' => $dateValue,
            ':manager_id' => (int)$manager['id']
        ]);

        $row = $dailyStmt->fetch(PDO::FETCH_ASSOC);

        $expected = (int)($row['expected_count'] ?? 0);
        $attending = (int)($row['attending_count'] ?? 0);

        $rate = $expected > 0
            ? (int)round(($attending / $expected) * 100)
            : 0;

        $days[] = [
            'date' => $dateValue,
            'label' => $date->format('D'),
            'expected' => $expected,
            'attending' => $attending,
            'late' => (int)($row['late_count'] ?? 0),
            'leave' => (int)($row['leave_count'] ?? 0),
            'rate' => $rate
        ];
    }

    echo json_encode([
        'success' => true,
        'team_count' => $teamCount,
        'days' => $days
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager analytics API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load workforce analytics.'
    ]);
}