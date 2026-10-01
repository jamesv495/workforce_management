<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

/*
|--------------------------------------------------------------------------
| GET = List RFID assignments
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {

        $stmt = $pdo->query("
            SELECT
                r.id,
                r.employee_id,
                r.rfid_uid,
                r.status,
                r.created_at,

                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,

                COALESCE(a.role, 'employee') AS account_role

            FROM rfid_cards r

            INNER JOIN employees e
                ON e.employee_id = r.employee_id

            LEFT JOIN accounts a
                ON a.employee_id = e.employee_id

            ORDER BY
                r.created_at DESC,
                r.id DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];

        foreach ($rows as $row) {

            $data[] = [
                'id' =>
                    (int)$row['id'],

                'employee_id' =>
                    (string)$row['employee_id'],

                'employee_name' =>
                    trim(
                        (string)$row['employee_name']
                    ),

                'account_role' =>
                    strtolower(
                        (string)$row['account_role']
                    ),

                'rfid_uid' =>
                    (string)$row['rfid_uid'],

                'status' =>
                    (string)$row['status'],

                'created_at' =>
                    (string)$row['created_at']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;

    } catch (Throwable $e) {

        error_log(
            'RFID assignment list failed: ' .
            $e->getMessage()
        );

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
                'Unable to load RFID assignments.'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| POST = Assign RFID to Employee
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' =>
                'Invalid JSON request.'
        ]);

        exit;
    }

    $employeeId =
        trim(
            (string)(
                $input['employee_id'] ?? ''
            )
        );

    $rfidUid =
        trim(
            (string)(
                $input['rfid_uid'] ?? ''
            )
        );

    if (
        $employeeId === '' ||
        $rfidUid === ''
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Employee ID and RFID UID are required.'
        ]);

        exit;
    }

    if (strlen($rfidUid) > 100) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'RFID UID is too long.'
        ]);

        exit;
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | Confirm employee exists and is an employee account.
        |--------------------------------------------------------------------------
        */

        $employeeStmt = $pdo->prepare("
            SELECT
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.employment_status,
                a.role

            FROM employees e

            LEFT JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE e.employee_id = :employee_id
              AND (
                    a.role = 'employee'
                    OR a.role IS NULL
                  )

            LIMIT 1
        ");

        $employeeStmt->execute([
            ':employee_id' => $employeeId
        ]);

        $employee =
            $employeeStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$employee) {

            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Employee was not found.'
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Kiosk only accepts active employees.
        |--------------------------------------------------------------------------
        */

        if (
            $employee['employment_status'] !==
            'active'
        ) {

            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Only active employees can be assigned to a kiosk RFID.'
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Make sure employee does not already have an active RFID.
        |--------------------------------------------------------------------------
        */

        $employeeCardStmt = $pdo->prepare("
            SELECT
                id,
                rfid_uid
            FROM rfid_cards
            WHERE employee_id = :employee_id
              AND status = 'active'
            LIMIT 1
        ");

        $employeeCardStmt->execute([
            ':employee_id' => $employeeId
        ]);

        $existingEmployeeCard =
            $employeeCardStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if ($existingEmployeeCard) {

            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' =>
                    'This employee already has an active RFID assigned.',
                'existing_rfid_uid' =>
                    (string)(
                        $existingEmployeeCard['rfid_uid']
                    )
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Make sure the RFID is not already assigned.
        |--------------------------------------------------------------------------
        */

        $rfidStmt = $pdo->prepare("
            SELECT
                id,
                employee_id
            FROM rfid_cards
            WHERE rfid_uid = :rfid_uid
              AND status = 'active'
            LIMIT 1
        ");

        $rfidStmt->execute([
            ':rfid_uid' => $rfidUid
        ]);

        $existingRfid =
            $rfidStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if ($existingRfid) {

            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' =>
                    'This RFID UID is already assigned to another employee.'
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Create assignment
        |--------------------------------------------------------------------------
        */

        $insertStmt = $pdo->prepare("
            INSERT INTO rfid_cards (
                employee_id,
                rfid_uid,
                status,
                created_at
            )
            VALUES (
                :employee_id,
                :rfid_uid,
                'active',
                NOW()
            )
        ");

        $insertStmt->execute([
            ':employee_id' => $employeeId,
            ':rfid_uid' => $rfidUid
        ]);

        $assignmentId =
            (int)$pdo->lastInsertId();

        $employeeName =
            trim(
                implode(
                    ' ',
                    array_filter([
                        $employee['first_name'] ?? '',
                        $employee['middle_name'] ?? '',
                        $employee['last_name'] ?? ''
                    ])
                )
            );

        echo json_encode([
            'success' => true,
            'message' =>
                'Employee ID successfully assigned to RFID.',
            'data' => [
                'id' => $assignmentId,
                'employee_id' => $employeeId,
                'employee_name' => $employeeName,
                'rfid_uid' => $rfidUid,
                'status' => 'active'
            ]
        ]);

        exit;

    } catch (Throwable $e) {

        error_log(
            'RFID assignment failed: ' .
            $e->getMessage()
        );

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
                'Unable to assign RFID to employee.'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Unsupported method
|--------------------------------------------------------------------------
*/

http_response_code(405);

echo json_encode([
    'success' => false,
    'message' =>
        'Only GET and POST requests are allowed.'
]);