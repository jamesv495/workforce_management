<?php

declare(strict_types=1);

require_once __DIR__ . '/auth_helpers.php';

startWfmSession();

function requireLogin(): array
{
    if (
        !isset($_SESSION['user']) ||
        !is_array($_SESSION['user'])
    ) {
        header('Location: login.php');
        exit;
    }

    return $_SESSION['user'];
}

function requireRole(string $requiredRole): array
{
    $user = requireLogin();

    if (($user['role'] ?? '') !== $requiredRole) {
        http_response_code(403);
        exit('Access denied.');
    }

    return $user;
}
