<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireLogin();

if (!in_array(($user['role'] ?? ''), ['employee', 'manager'], true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

$employeeId = trim((string)($user['employee_id'] ?? ''));

if ($employeeId === '') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Employee session not found.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$leaveType = trim((string)($input['leave_type'] ?? ''));
$startDate = trim((string)($input['start_date'] ?? ''));
$endDate = trim((string)($input['end_date'] ?? ''));
$reason = trim((string)($input['reason'] ?? ''));

$allowedLeaveTypes = [
    'Service Incentive Leave (SIL)',
    'Vacation Leave',
    'Sick Leave',
    'Maternity Leave',
    'Paternity Leave'
];

if (!in_array($leaveType, $allowedLeaveTypes, true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid leave type.'
    ]);

    exit;
}

if ($leaveType === '' || $startDate === '' || $endDate === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Leave type, start date, and end date are required.'
    ]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Dates must use YYYY-MM-DD format.'
    ]);
    exit;
}

if ($endDate < $startDate) {

    /*
     * Do not allow leave requests that have already ended.
     */
    $todayValue = date('Y-m-d');

    if ($endDate < $todayValue) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Leave dates must include today or a future date.'
        ]);

        exit;
    }

    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'End date cannot be before start date.'
    ]);
    exit;
}

if ($leaveType === 'Service Incentive Leave (SIL)') {

    $employeeStmt = $pdo->prepare("
        SELECT hire_date
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee =
        $employeeStmt->fetch(
            PDO::FETCH_ASSOC
        );

    $hireDateValue =
        trim(
            (string)(
                $employee['hire_date'] ?? ''
            )
        );

    if ($hireDateValue === '') {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'SIL cannot be requested because your hire date is not available.'
        ]);

        exit;
    }

    $hireDate =
        new DateTime(
            $hireDateValue
        );

    $eligibleDate =
        clone $hireDate;

    $eligibleDate->modify(
        '+1 year'
    );

    if (
        $eligibleDate->format('Y-m-d') >
        $startDate
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Service Incentive Leave (SIL) requires at least 1 year of service.'
        ]);

        exit;
    }
}

try {
    /*
 * Prevent overlapping pending/approved leave requests.
 */
$overlapStmt = $pdo->prepare("
    SELECT id
    FROM leave_requests
    WHERE employee_id = :employee_id
      AND status IN ('pending', 'approved')
      AND start_date <= :end_date
      AND end_date >= :start_date
    LIMIT 1
");

$overlapStmt->execute([
    ':employee_id' => $employeeId,
    ':start_date' => $startDate,
    ':end_date' => $endDate
]);

if ($overlapStmt->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(409);

    echo json_encode([
        'success' => false,
        'message' =>
            'You already have a pending or approved leave request covering part of these dates.'
    ]);

    exit;
}
    $stmt = $pdo->prepare("
        INSERT INTO leave_requests
        (
            employee_id,
            leave_type,
            start_date,
            end_date,
            reason,
            status
        )
        VALUES
        (
            :employee_id,
            :leave_type,
            :start_date,
            :end_date,
            :reason,
            'pending'
        )
    ");

    $stmt->execute([
        ':employee_id' => $employeeId,
        ':leave_type' => $leaveType,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':reason' => $reason !== '' ? $reason : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Leave request submitted successfully.',
        'id' => (int)$pdo->lastInsertId()
    ]);
} catch (Throwable $e) {
    error_log('Leave create error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to submit leave request.'
    ]);
}