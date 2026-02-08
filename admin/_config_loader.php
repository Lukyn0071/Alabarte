<?php
declare(strict_types=1);

// Loads admin credentials.
// Preferred location: /config/admin.secret.php
// Fallback location: /admin/.env.php

$config = null;

$root = dirname(__DIR__);
$preferred = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'admin.secret.php';
$fallback = __DIR__ . DIRECTORY_SEPARATOR . '.env.php';

if (is_file($preferred)) {
    /** @var array $config */
    $config = require $preferred;
} elseif (is_file($fallback)) {
    /** @var array $config */
    $config = require $fallback;
} else {
    http_response_code(500);
    echo 'Admin config not found. Create config/admin.secret.php or admin/.env.php.';
    exit;
}

if (!is_array($config)) {
    http_response_code(500);
    echo 'Admin config invalid.';
    exit;
}

// New simplest setup: ADMIN_USER + ADMIN_PASS (plaintext)
// Backward compatibility: if ADMIN_PASS is missing but ADMIN_PASS_HASH exists, we still define ADMIN_PASS_HASH.

if (!isset($config['ADMIN_USER']) || !is_string($config['ADMIN_USER']) || $config['ADMIN_USER'] === '') {
    http_response_code(500);
    echo 'Admin config missing: ADMIN_USER';
    exit;
}

define('ADMIN_USER', $config['ADMIN_USER']);

if (isset($config['ADMIN_PASS']) && is_string($config['ADMIN_PASS']) && $config['ADMIN_PASS'] !== '') {
    define('ADMIN_PASS', $config['ADMIN_PASS']);
} else {
    // legacy
    if (!isset($config['ADMIN_PASS_HASH']) || !is_string($config['ADMIN_PASS_HASH']) || $config['ADMIN_PASS_HASH'] === '') {
        http_response_code(500);
        echo 'Admin config missing: ADMIN_PASS (or legacy ADMIN_PASS_HASH)';
        exit;
    }
    define('ADMIN_PASS_HASH', $config['ADMIN_PASS_HASH']);
}