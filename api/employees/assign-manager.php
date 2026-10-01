<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

function respond(
    bool $success,
    string $message = '',
    int $status = 200,
    array $extra = []
): never {
    http_response_code($status);

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        )
    );

    exit;
}

try {

    /*
     * GET = list employees and managers for the modal.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $employeeStmt = $pdo->query("
            SELECT
                e.employee_id,
                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS employee_name,
                e.manager_id

            FROM employees e

            LEFT JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE e.employment_status <> 'terminated'
              AND (
                  a.role = 'employee'
                  OR a.role IS NULL
              )

            ORDER BY employee_name ASC
        ");

        $employees =
            $employeeStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        $managerStmt = $pdo->query("
            SELECT
                e.id AS manager_id,
                e.employee_id,
                CONCAT_WS(
                    ' ',
                    e.first_name,
                    e.middle_name,
                    e.last_name
                ) AS manager_name,
                e.department_name,
                e.avatar_url

            FROM employees e

            INNER JOIN accounts a
                ON a.employee_id = e.employee_id

            WHERE a.role = 'manager'
              AND e.employment_status <> 'terminated'

            ORDER BY manager_name ASC
        ");

        $managers =
            $managerStmt->fetchAll(
                PDO::FETCH_ASSOC
            );

        respond(
            true,
            '',
            200,
            [
                'employees' => $employees,
                'managers' => $managers
            ]
        );
    }

    /*
     * POST = assign or unassign employee.
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($input)) {
            respond(
                false,
                'Invalid JSON request.',
                400
            );
        }

        $employeeId =
            trim(
                (string)(
                    $input['employee_id'] ?? ''
                )
            );

        $managerId =
            (int)(
                $input['manager_id'] ?? 0
            );

        if ($employeeId === '') {
            respond(
                false,
                'Employee is required.',
                422
            );
        }

        /*
         * Confirm employee exists.
         */
        $employeeCheck =
            $pdo->prepare("
                SELECT id
                FROM employees
                WHERE employee_id = :employee_id
                  AND employment_status <> 'terminated'
                LIMIT 1
            ");

        $employeeCheck->execute([
            ':employee_id' =>
                $employeeId
        ]);

        $employee =
            $employeeCheck->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$employee) {
            respond(
                false,
                'Employee not found.',
                404
            );
        }

        /*
         * manager_id = 0 means unassign.
         */
        if ($managerId > 0) {

            $managerCheck =
                $pdo->prepare("
                    SELECT
                        e.id
                    FROM employees e
                    INNER JOIN accounts a
                        ON a.employee_id = e.employee_id
                    WHERE e.id = :manager_id
                      AND a.role = 'manager'
                      AND e.employment_status <> 'terminated'
                    LIMIT 1
                ");

            $managerCheck->execute([
                ':manager_id' =>
                    $managerId
            ]);

            if (!$managerCheck->fetch()) {
                respond(
                    false,
                    'Selected manager was not found.',
                    404
                );
            }
        }

        $update =
            $pdo->prepare("
                UPDATE employees
                SET
                    manager_id = :manager_id,
                    updated_at = NOW()
                WHERE employee_id = :employee_id
            ");

        $update->execute([
            ':manager_id' =>
                $managerId > 0
                    ? $managerId
                    : null,

            ':employee_id' =>
                $employeeId
        ]);

        respond(
            true,
            $managerId > 0
                ? 'Employee assigned to manager successfully.'
                : 'Employee manager assignment removed.'
        );
    }

    respond(
        false,
        'Only GET and POST requests are allowed.',
        405
    );

} catch (Throwable $e) {

    error_log(
        'Employee manager assignment failed: ' .
        $e->getMessage()
    );

    respond(
        false,
        'Unable to update employee manager assignment.',
        500
    );
}