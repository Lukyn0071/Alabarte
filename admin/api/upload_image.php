<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

// CSRF
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$expected = $_SESSION['csrf_token'];
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || $csrf === '' || !hash_equals($expected, $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF token je neplatný.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Chybí soubor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$f = $_FILES['image'];
if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Upload selhal.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$tmp = $f['tmp_name'] ?? '';
$origName = $f['name'] ?? 'image';
if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Neplatný upload.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$maxBytes = 5 * 1024 * 1024;
$size = (int)($f['size'] ?? 0);
if ($size <= 0 || $size > $maxBytes) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Soubor je příliš velký (max 5 MB).'], JSON_UNESCAPED_UNICODE);
    exit;
}

$info = @getimagesize($tmp);
if ($info === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Soubor není obrázek.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$mime = $info['mime'] ?? '';
$ext = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    default => null,
};

if ($ext === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nepodporovaný formát. Povolené: JPG, PNG, WEBP.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$destDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($destDir) && !@mkdir($destDir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Nelze vytvořit složku pro upload.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$base = pathinfo($origName, PATHINFO_FILENAME);
$base = preg_replace('~[^a-z0-9\-_]+~i', '-', $base);
$base = trim((string)$base, '-');
if ($base === '') $base = 'image';

$filename = $base . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
$destPath = $destDir . DIRECTORY_SEPARATOR . $filename;

if (!@move_uploaded_file($tmp, $destPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Nelze uložit soubor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// return web path
$webPath = 'Images/uploads/' . $filename;

echo json_encode(['ok' => true, 'path' => $webPath], JSON_UNESCAPED_UNICODE);
