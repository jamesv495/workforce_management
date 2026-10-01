<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/session_guard.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireLogin();

$userRole = strtolower(
    (string)($user['role'] ?? '')
);

if (!in_array($userRole, ['manager', 'employee'], true)) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);

    exit;
}

$employeeId = trim(
    (string)($user['employee_id'] ?? '')
);

if ($employeeId === '') {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Employee account could not be identified.'
    ]);

    exit;
}

$uploadDirectory =
    __DIR__ .
    '/../../uploads/profile_photos';

$publicDirectory =
    'uploads/profile_photos';

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0755, true)) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to create profile photo directory.'
        ]);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Get current profile photo
|--------------------------------------------------------------------------
*/

try {

    $currentStmt = $pdo->prepare("
        SELECT avatar_url
        FROM employees
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $currentStmt->execute([
        ':employee_id' => $employeeId
    ]);

    $current = $currentStmt->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$current) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Employee record not found.'
        ]);

        exit;
    }

    $currentAvatar =
        $current['avatar_url'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | REMOVE PHOTO
    |--------------------------------------------------------------------------
    */

    $action =
        $_POST['action'] ??
        '';

    if ($action === 'remove') {

        if (
            $currentAvatar &&
            strpos(
                (string)$currentAvatar,
                $publicDirectory . '/'
            ) === 0
        ) {

            $oldFile =
                __DIR__ .
                '/../../' .
                $currentAvatar;

            if (
                is_file($oldFile)
            ) {
                @unlink($oldFile);
            }
        }

        $update = $pdo->prepare("
            UPDATE employees
            SET
                avatar_url = NULL,
                updated_at = NOW()
            WHERE employee_id = :employee_id
            LIMIT 1
        ");

        $update->execute([
            ':employee_id' => $employeeId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Profile photo removed.',
            'avatar_url' => null
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD PHOTO
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_FILES['photo']) ||
        !is_array($_FILES['photo'])
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Please select a photo.'
        ]);

        exit;
    }

    $file = $_FILES['photo'];

    if (
        !isset($file['error']) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'The photo upload failed.'
        ]);

        exit;
    }

    if (
        !isset($file['tmp_name']) ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid uploaded file.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Max file size = 5 MB
    |--------------------------------------------------------------------------
    */

    $maxSize = 5 * 1024 * 1024;

    if (
        !isset($file['size']) ||
        (int)$file['size'] > $maxSize
    ) {
        http_response_code(413);

        echo json_encode([
            'success' => false,
            'message' => 'Profile photo must be 5 MB or smaller.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate actual image
    |--------------------------------------------------------------------------
    */

    $imageInfo =
        @getimagesize($file['tmp_name']);

    if (!$imageInfo) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Please upload a valid image.'
        ]);

        exit;
    }

    $finfo =
        finfo_open(FILEINFO_MIME_TYPE);

    $mime =
        $finfo
            ? finfo_file(
                $finfo,
                $file['tmp_name']
            )
            : '';

    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mime])) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Only JPG, PNG, and WEBP photos are allowed.'
        ]);

        exit;
    }

    $extension =
        $allowedTypes[$mime];

    /*
    |--------------------------------------------------------------------------
    | Create unique filename
    |--------------------------------------------------------------------------
    */

    $randomPart =
        bin2hex(random_bytes(12));

    $filename =
        'manager_' .
        preg_replace(
            '/[^A-Za-z0-9_-]/',
            '',
            $employeeId
        ) .
        '_' .
        $randomPart .
        '.' .
        $extension;

    $destination =
        $uploadDirectory .
        '/' .
        $filename;

    $publicPath =
        $publicDirectory .
        '/' .
        $filename;

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Unable to save the profile photo.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Remove previous photo
    |--------------------------------------------------------------------------
    */

    if (
        $currentAvatar &&
        strpos(
            (string)$currentAvatar,
            $publicDirectory . '/'
        ) === 0
    ) {

        $oldFile =
            __DIR__ .
            '/../../' .
            $currentAvatar;

        if (
            is_file($oldFile)
        ) {
            @unlink($oldFile);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save new path to database
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare("
        UPDATE employees
        SET
            avatar_url = :avatar_url,
            updated_at = NOW()
        WHERE employee_id = :employee_id
        LIMIT 1
    ");

    $update->execute([
        ':avatar_url' => $publicPath,
        ':employee_id' => $employeeId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated successfully.',
        'avatar_url' => $publicPath
    ]);

} catch (Throwable $e) {

    error_log(
        'Manager profile photo error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update profile photo.'
    ]);
}