<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

try {
    $today = new DateTime('today');
    $todayValue = $today->format('Y-m-d');

    $weekStart = clone $today;
    $weekStart->modify('monday this week');
    $weekStartValue = $weekStart->format('Y-m-d');

    /*
     * Total active staff.
     */
    $employeeStmt = $pdo->query("
        SELECT COUNT(*)
        FROM employees
        WHERE employment_status = 'active'
    ");

    $totalEmployees = (int)$employeeStmt->fetchColumn();

    /*
     * Staff currently clocked in today.
     */
    $clockedInStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance_records a
        INNER JOIN employees e
            ON e.employee_id = a.employee_id
        WHERE a.attendance_date = :today
          AND a.time_in IS NOT NULL
          AND a.time_out IS NULL
          AND e.employment_status = 'active'
          AND a.status <> 'on_leave'
    ");

    $clockedInStmt->execute([
        ':today' => $todayValue
    ]);

    $clockedIn = (int)$clockedInStmt->fetchColumn();

    /*
     * Late arrivals this week.
     */
    $lateStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM attendance_records a
        INNER JOIN employees e
            ON e.employee_id = a.employee_id
        WHERE a.attendance_date BETWEEN :week_start AND :today
          AND a.status = 'late'
          AND e.employment_status = 'active'
    ");

    $lateStmt->execute([
        ':week_start' => $weekStartValue,
        ':today' => $todayValue
    ]);

    $lateThisWeek = (int)$lateStmt->fetchColumn();

    /*
     * Staff on approved leave today.
     */
    $leaveStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT lr.employee_id)
        FROM leave_requests lr
        INNER JOIN employees e
            ON e.employee_id = lr.employee_id
        WHERE lr.status = 'approved'
          AND :today BETWEEN lr.start_date AND lr.end_date
          AND e.employment_status <> 'terminated'
    ");

    $leaveStmt->execute([
        ':today' => $todayValue
    ]);

    $onLeaveToday = (int)$leaveStmt->fetchColumn();

    /*
     * Pending leave requests.
     */
    $pendingLeaveStmt = $pdo->query("
        SELECT COUNT(*)
        FROM leave_requests
        WHERE status = 'pending'
    ");

    $pendingLeaves = (int)$pendingLeaveStmt->fetchColumn();

    /*
     * Pending timesheet reviews.
     */
    $pendingTimesheetStmt = $pdo->query("
        SELECT COUNT(*)
        FROM timesheet_reviews
        WHERE status = 'pending'
    ");

    $pendingTimesheets = (int)$pendingTimesheetStmt->fetchColumn();

    /*
     * The current database schema has no tour/tour-operations table,
     * so Active Tours remains 0.
     */
    $activeTours = 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'total_employees' => $totalEmployees,
            'clocked_in' => $clockedIn,
            'active_tours' => $activeTours,
            'late_this_week' => $lateThisWeek,
            'on_leave_today' => $onLeaveToday,
            'pending_leaves' => $pendingLeaves,
            'pending_timesheets' => $pendingTimesheets
        ]
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin dashboard API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load admin dashboard statistics.'
    ]);
}