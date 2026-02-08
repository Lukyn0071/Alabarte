<?php
declare(strict_types=1);

// Admin bootstrap (shared include)

// Basic hardening headers for admin area
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session cookie settings (secure will be enabled automatically on HTTPS)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} else {
    // Fallback for older PHP
    session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);
}

session_start();

require_once __DIR__ . '/_config_loader.php';
require_once __DIR__ . '/_auth.php';
