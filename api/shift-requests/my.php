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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $stmt = $pdo->prepare("
            SELECT
                id,
                requested_date,
                requested_shift,
                reason,
                status,
                reviewed_at,
                created_at
            FROM shift_requests
            WHERE employee_id = :employee_id
            ORDER BY created_at DESC, id DESC
        ");

        $stmt->execute([
            ':employee_id' => $employeeId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $rows
        ]);

        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Only GET and POST requests are allowed.'
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
            'message' => 'Invalid request body.'
        ]);

        exit;
    }

    $requestedDate = trim(
        (string)($input['requested_date'] ?? '')
    );

    $requestedShift = trim(
        (string)($input['requested_shift'] ?? '')
    );

    $reason = trim(
        (string)($input['reason'] ?? '')
    );

    if (
        $requestedDate === '' ||
        $requestedShift === ''
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Requested date and requested shift are required.'
        ]);

        exit;
    }

    $dateObject = DateTime::createFromFormat(
        'Y-m-d',
        $requestedDate
    );

    if (
        $dateObject === false ||
        $dateObject->format('Y-m-d') !== $requestedDate
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid requested date.'
        ]);

        exit;
    }

    $dayNumber =
        (int)$dateObject->format('N');

    if ($dayNumber >= 6) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Shift requests are only allowed for Monday to Friday.'
        ]);

        exit;
    }

    /*
     * Prevent duplicate pending requests
     * for the same employee and date.
     */
    $duplicateStmt = $pdo->prepare("
        SELECT id
        FROM shift_requests
        WHERE employee_id = :employee_id
          AND requested_date = :requested_date
          AND status = 'pending'
        LIMIT 1
    ");

    $duplicateStmt->execute([
        ':employee_id' => $employeeId,
        ':requested_date' => $requestedDate
    ]);

    if ($duplicateStmt->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'A pending shift request already exists for this date.'
        ]);

        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO shift_requests (
            employee_id,
            requested_date,
            requested_shift,
            reason,
            status
        )
        VALUES (
            :employee_id,
            :requested_date,
            :requested_shift,
            :reason,
            'pending'
        )
    ");

    $stmt->execute([
        ':employee_id' => $employeeId,
        ':requested_date' => $requestedDate,
        ':requested_shift' => $requestedShift,
        ':reason' =>
            $reason !== ''
                ? $reason
                : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Shift request submitted successfully.',
        'id' => (int)$pdo->lastInsertId()
    ]);

} catch (Throwable $e) {

    error_log(
        'Employee shift request API failed: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to process shift request.'
    ]);
}