<?php

declare(strict_types=1);

require_once __DIR__ . '/../api/auth/session_guard.php';

function wfmRequirePageRole(?string $role = null): array
{
    return $role === null ? requireLogin() : requireRole($role);
}
