<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ]);

    exit;
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);

    exit;
}

$employeeId = trim(
    (string)($input['employee_id'] ?? '')
);

$leaveType = trim(
    (string)($input['leave_type'] ?? '')
);

$startDate = trim(
    (string)($input['start_date'] ?? '')
);

$endDate = trim(
    (string)($input['end_date'] ?? '')
);

$reason = trim(
    (string)($input['reason'] ?? '')
);

$allowedTypes = [
    'Service Incentive Leave (SIL)',
    'Vacation Leave',
    'Sick Leave',
    'Maternity Leave',
    'Paternity Leave'
];

if (
    $employeeId === '' ||
    $leaveType === '' ||
    $startDate === '' ||
    $endDate === '' ||
    $reason === ''
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Applicant, leave type, dates, and reason are required.'
    ]);

    exit;
}

if (!in_array($leaveType, $allowedTypes, true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid leave type.'
    ]);

    exit;
}

$start = DateTime::createFromFormat(
    'Y-m-d',
    $startDate
);

$end = DateTime::createFromFormat(
    'Y-m-d',
    $endDate
);

if (
    !$start ||
    $start->format('Y-m-d') !== $startDate ||
    !$end ||
    $end->format('Y-m-d') !== $endDate
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Dates must use YYYY-MM-DD format.'
    ]);

    exit;
}

if ($endDate < $startDate) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'End date cannot be before start date.'
    ]);

    exit;
}

if ($endDate < date('Y-m-d')) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Leave dates cannot be completely in the past.'
    ]);

    exit;
}

try {

    /*
     * Only Employee and Manager records can be applicants.
     */
    $employeeStmt = $pdo->prepare("
        SELECT
            e.employee_id,
            e.hire_date,
            e.employment_status,
            a.role
        FROM employees e
        INNER JOIN accounts a
            ON a.employee_id = e.employee_id
        WHERE e.employee_id = :employee_id
          AND a.role IN ('employee', 'manager')
        ORDER BY a.id ASC
        LIMIT 1
    ");

    $employeeStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $employee = $employeeStmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$employee) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Selected applicant was not found.'
        ]);

        exit;
    }

    if (
        strtolower(
            (string)$employee['employment_status']
        ) === 'terminated'
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Terminated employees cannot be given a leave request.'
        ]);

        exit;
    }

    /*
     * SIL requires at least one completed year of service
     * by the first day of the leave.
     */
    if ($leaveType === 'Service Incentive Leave (SIL)') {

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
                'message' => 'SIL cannot be granted because the applicant has no hire date.'
            ]);

            exit;
        }

        $hireDate = new DateTime(
            $hireDateValue
        );

        $eligibleDate = clone $hireDate;
        $eligibleDate->modify('+1 year');

        if ($eligibleDate->format('Y-m-d') > $startDate) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Service Incentive Leave (SIL) requires at least 1 year of service.'
            ]);

            exit;
        }
    }

    /*
     * Prevent overlapping pending or approved leave.
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

    if ($overlapStmt->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'This applicant already has a pending or approved leave covering part of these dates.'
        ]);

        exit;
    }

    $insert = $pdo->prepare("
        INSERT INTO leave_requests (
            employee_id,
            leave_type,
            start_date,
            end_date,
            reason,
            status
        )
        VALUES (
            :employee_id,
            :leave_type,
            :start_date,
            :end_date,
            :reason,
            'pending'
        )
    ");

    $insert->execute([
        ':employee_id' => $employeeId,
        ':leave_type' => $leaveType,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':reason' => $reason
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Leave request filed successfully.',
        'id' => (int)$pdo->lastInsertId()
    ]);

} catch (Throwable $e) {

    error_log(
        'Admin leave creation error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to file leave request.'
    ]);
}