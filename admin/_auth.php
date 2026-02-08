<?php
declare(strict_types=1);

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        $next = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header('Location: login.php?next=' . rawurlencode($next));
        exit;
    }
}