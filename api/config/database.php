<?php

declare(strict_types=1);
/*
|--------------------------------------------------------------------------
| DATABASE SETTINGS
|--------------------------------------------------------------------------
| For XAMPP/Laragon local development:
| Host     = localhost
| Username = root
| Password = empty
| Database = workforce_management
|
| Change these values when you deploy to your real hosting server.
|--------------------------------------------------------------------------
*/

$db_host = getenv('WFM_DB_HOST') ?: 'localhost';
$db_name = getenv('WFM_DB_NAME') ?: 'workforce_management';
$db_user = getenv('WFM_DB_USER') ?: 'root';
$db_pass = getenv('WFM_DB_PASS') ?: '';

$db_charset = 'utf8mb4';


/*
|--------------------------------------------------------------------------
| PDO CONNECTION
|--------------------------------------------------------------------------
*/

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


try {

    $pdo = new PDO(
        $dsn,
        $db_user,
        $db_pass,
        $options
    );

} catch (PDOException $e) {

    http_response_code(500);

    die('Database connection failed.');

}