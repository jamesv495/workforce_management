<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Workforce Management - Gmail SMTP Configuration
|--------------------------------------------------------------------------
|
| Preferred deployment:
|   WFM_MAIL_USERNAME
|   WFM_MAIL_PASSWORD
|   WFM_MAIL_FROM
|   WFM_MAIL_FROM_NAME
|
| Local XAMPP testing:
|   You may create api/config/mail.local.php (not committed to Git)
|   and return an array with the same keys.
|
| Do NOT use the normal Gmail account password.
|--------------------------------------------------------------------------
*/

$defaults = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => '',
    'password' => '',
    'from_email' => '',
    'from_name' => 'Holiday Travelers Workforce Management'
];

$localConfig = [];

$localFile = __DIR__ . '/mail.local.php';

if (is_file($localFile)) {
    $loaded = require $localFile;

    if (is_array($loaded)) {
        $localConfig = $loaded;
    }
}

return [
    'host' =>
        getenv('WFM_MAIL_HOST') !== false && getenv('WFM_MAIL_HOST') !== ''
            ? getenv('WFM_MAIL_HOST')
            : ($localConfig['host'] ?? $defaults['host']),

    'port' =>
        getenv('WFM_MAIL_PORT') !== false && getenv('WFM_MAIL_PORT') !== ''
            ? (int) getenv('WFM_MAIL_PORT')
            : (int) ($localConfig['port'] ?? $defaults['port']),

    'username' =>
        getenv('WFM_MAIL_USERNAME') !== false && getenv('WFM_MAIL_USERNAME') !== ''
            ? getenv('WFM_MAIL_USERNAME')
            : ($localConfig['username'] ?? $defaults['username']),

    'password' =>
        getenv('WFM_MAIL_PASSWORD') !== false && getenv('WFM_MAIL_PASSWORD') !== ''
            ? getenv('WFM_MAIL_PASSWORD')
            : ($localConfig['password'] ?? $defaults['password']),

    'from_email' =>
        getenv('WFM_MAIL_FROM') !== false && getenv('WFM_MAIL_FROM') !== ''
            ? getenv('WFM_MAIL_FROM')
            : ($localConfig['from_email']
                ?? ($localConfig['username'] ?? $defaults['from_email'])),

    'from_name' =>
        getenv('WFM_MAIL_FROM_NAME') !== false && getenv('WFM_MAIL_FROM_NAME') !== ''
            ? getenv('WFM_MAIL_FROM_NAME')
            : ($localConfig['from_name'] ?? $defaults['from_name'])
];
