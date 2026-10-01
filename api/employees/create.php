<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

function respond(
    bool $success,
    string $message,
    int $status = 200,
    array $extra = []
): never {
    http_response_code($status);

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(
        false,
        'Only POST requests are allowed.',
        405
    );
}

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

$employeeId = trim(
    (string)($input['employee_no'] ?? '')
);

$firstName = trim(
    (string)($input['first_name'] ?? '')
);

$middleName = trim(
    (string)($input['middle_name'] ?? '')
);

$lastName = trim(
    (string)($input['last_name'] ?? '')
);

$phone = trim(
    (string)($input['phone'] ?? '')
);

$email = strtolower(
    trim((string)($input['email'] ?? ''))
);

$password = (string)(
    $input['password'] ?? ''
);

$department = trim(
    (string)($input['department_name'] ?? '')
);

$position = trim(
    (string)($input['position_name'] ?? '')
);

$hireDate = $input['hire_date'] ?? null;

$address = trim(
    (string)($input['address'] ?? '')
);

$employmentType = trim(
    (string)($input['employment_type'] ?? 'REGULAR')
);

$employmentStatus = strtoupper(
    trim(
        (string)($input['employment_status'] ?? 'ACTIVE')
    )
);

if (
    $employeeId === '' ||
    $firstName === '' ||
    $lastName === '' ||
    $email === '' ||
    $password === '' ||
    $department === '' ||
    $position === ''
) {
    respond(
        false,
        'Please complete all required employee fields.',
        422
    );
}

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {
    respond(
        false,
        'Please enter a valid Gmail address.',
        422
    );
}

if (
    !preg_match(
        '/@gmail\.com$/i',
        $email
    )
) {
    respond(
        false,
        'Employee Gmail must use an @gmail.com address.',
        422
    );
}

if (strlen($password) < 8) {
    respond(
        false,
        'Password must be at least 8 characters.',
        422
    );
}

$statusMap = [
    'ACTIVE' => 'active',
    'LEAVE' => 'on_leave',
    'INACTIVE' => 'inactive'
];

$dbStatus =
    $statusMap[$employmentStatus] ?? null;

if ($dbStatus === null) {
    respond(
        false,
        'Invalid employment status.',
        422
    );
}

if ($hireDate === '') {
    $hireDate = null;
}

if ($hireDate !== null) {

    $date = DateTime::createFromFormat(
        'Y-m-d',
        (string)$hireDate
    );

    if (
        !$date ||
        $date->format('Y-m-d') !== $hireDate
    ) {
        respond(
            false,
            'Invalid hire date.',
            422
        );
    }
}

try {

    $pdo->beginTransaction();

    /*
     * Check Employee ID.
     */
    $checkEmployee = $pdo->prepare("
        SELECT id
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $checkEmployee->execute([
        ':employee_id' => $employeeId
    ]);

    if ($checkEmployee->fetch()) {

        $pdo->rollBack();

        respond(
            false,
            'Employee ID already exists.',
            409
        );
    }

    /*
     * Check Gmail against employees.
     */
    $checkEmail = $pdo->prepare("
        SELECT employee_id
        FROM employees
        WHERE email = :email
        LIMIT 1
    ");

    $checkEmail->execute([
        ':email' => $email
    ]);

    if ($checkEmail->fetch()) {

        $pdo->rollBack();

        respond(
            false,
            'This Gmail address is already assigned to an employee.',
            409
        );
    }

    /*
     * Check Gmail against accounts.
     */
    $checkAccountEmail = $pdo->prepare("
        SELECT id
        FROM accounts
        WHERE email = :email
        LIMIT 1
    ");

    $checkAccountEmail->execute([
        ':email' => $email
    ]);

    if ($checkAccountEmail->fetch()) {

        $pdo->rollBack();

        respond(
            false,
            'This Gmail address already has an account.',
            409
        );
    }

    /*
     * Create employee.
     */
    $employeeInsert = $pdo->prepare("
        INSERT INTO employees (
            employee_id,
            first_name,
            middle_name,
            last_name,
            email,
            phone,
            department_name,
            position_name,
            hire_date,
            address,
            employment_type,
            employment_status
        )
        VALUES (
            :employee_id,
            :first_name,
            :middle_name,
            :last_name,
            :email,
            :phone,
            :department_name,
            :position_name,
            :hire_date,
            :address,
            :employment_type,
            :employment_status
        )
    ");

    $employeeInsert->execute([
        ':employee_id' => $employeeId,
        ':first_name' => $firstName,
        ':middle_name' =>
            $middleName !== ''
                ? $middleName
                : null,
        ':last_name' => $lastName,
        ':email' => $email,
        ':phone' =>
            $phone !== ''
                ? $phone
                : null,
        ':department_name' => $department,
        ':position_name' => $position,
        ':hire_date' => $hireDate,
        ':address' =>
            $address !== ''
                ? $address
                : null,
        ':employment_type' =>
            $employmentType !== ''
                ? $employmentType
                : 'REGULAR',
        ':employment_status' => $dbStatus
    ]);

    /*
     * Create login account immediately.
     */
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $accountStatus =
        $dbStatus === 'active'
            ? 'active'
            : 'inactive';

    $accountInsert = $pdo->prepare("
        INSERT INTO accounts (
            employee_id,
            email,
            password_hash,
            role,
            status
        )
        VALUES (
            :employee_id,
            :email,
            :password_hash,
            'employee',
            :status
        )
    ");

    $accountInsert->execute([
        ':employee_id' => $employeeId,
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':status' => $accountStatus
    ]);

    $pdo->commit();

    respond(
        true,
        'Employee and employee account created successfully.',
        201,
        [
            'employee' => [
                'employee_no' => $employeeId,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'department_name' => $department,
                'position_name' => $position,
                'hire_date' => $hireDate,
                'address' => $address,
                'employment_type' => $employmentType,
                'employment_status' => $employmentStatus
            ],
            'account' => [
                'email' => $email,
                'role' => 'employee',
                'status' => $accountStatus
            ]
        ]
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Employee creation failed: ' .
        $e->getMessage()
    );

    respond(
        false,
        'Unable to create employee and employee account.',
        500
    );
}