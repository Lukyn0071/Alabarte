<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_admin();
require_once dirname(__DIR__, 2) . '/lib/content_store.php';

header('Content-Type: application/json; charset=utf-8');

// --- CSRF ---
if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$expectedCsrf = $_SESSION['csrf_token'];
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || $csrf === '' || !hash_equals($expectedCsrf, $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF token je neplatný. Obnovte stránku a zkuste to znovu.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Neplatná data.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $data['action'] ?? '';
$page = $data['page'] ?? '';
$sectionId = $data['sectionId'] ?? '';
$position = $data['position'] ?? 'end';

if ($page !== 'index') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nepodporovaná stránka.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!is_string($action) || !in_array($action, ['update', 'delete', 'add'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nepodporovaná akce.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!is_string($sectionId) || !preg_match('~^[a-z0-9\-]{1,64}$~i', $sectionId)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Neplatné ID sekce.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!is_string($position) || !in_array($position, ['start', 'end'], true)) {
    $position = 'end';
}

// validate local image path (uploads only)
$validateImgPath = static function (?string $imgSrc): ?string {
    if ($imgSrc === null) return null;
    $src = ltrim($imgSrc);
    $src = preg_replace('~^/+~', '', $src);
    if (
        preg_match('~^(Images/)[A-Za-z0-9_\-/]+\.(png|jpe?g|webp)$~i', $src)
        && !str_contains($src, '..')
        && !str_contains($src, ':')
    ) {
        return $src;
    }
    return null;
};

try {
    $store = load_index_sections();
    $sections = isset($store['sections']) && is_array($store['sections']) ? $store['sections'] : [];

    // Auto-migrate legacy string fields to i18n objects
    foreach ($sections as $i => $s) {
        if (!is_array($s)) continue;
        foreach (['title', 'p1', 'p2'] as $k) {
            if (isset($sections[$i][$k]) && is_string($sections[$i][$k])) {
                $sections[$i][$k] = ['cs' => (string)$sections[$i][$k], 'en' => ''];
            }
        }
    }

    if ($action === 'add') {
        if (find_section_index($sections, $sectionId) !== -1) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Sekce s tímto ID už existuje.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $fields = $data['fields'] ?? null;
        if (!is_array($fields)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Chybí fields.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $imgSrc = isset($fields['img_src']) && is_string($fields['img_src']) ? $validateImgPath(trim($fields['img_src'])) : null;
        if ($imgSrc === null) {
            $imgSrc = 'Images/o-nas.png';
        }

        $new = [
            'id' => $sectionId,
            'class' => 'info-section',
            'title' => [
                'cs' => (string)($fields['title_cs'] ?? ($fields['title'] ?? '')),
                'en' => (string)($fields['title_en'] ?? ''),
            ],
            'p1' => [
                'cs' => (string)($fields['p1_cs'] ?? ($fields['p1'] ?? '')),
                'en' => (string)($fields['p1_en'] ?? ''),
            ],
            'p2' => [
                'cs' => (string)($fields['p2_cs'] ?? ($fields['p2'] ?? '')),
                'en' => (string)($fields['p2_en'] ?? ''),
            ],
            'img_src' => $imgSrc,
            'img_alt' => (string)($fields['img_alt'] ?? ''),
        ];

        if ($position === 'start') {
            array_unshift($sections, $new);
        } else {
            $sections[] = $new;
        }
    }

    if ($action === 'update') {
        $idx = find_section_index($sections, $sectionId);
        if ($idx === -1) {
            $sections[] = ['id' => $sectionId, 'class' => 'info-section'];
            $idx = count($sections) - 1;
        }

        $fields = $data['fields'] ?? null;
        if (!is_array($fields)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Chybí fields.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Ensure nested objects exist
        if (!isset($sections[$idx]['title']) || !is_array($sections[$idx]['title'])) {
            $sections[$idx]['title'] = ['cs' => (string)($sections[$idx]['title'] ?? ''), 'en' => ''];
        }
        if (!isset($sections[$idx]['p1']) || !is_array($sections[$idx]['p1'])) {
            $sections[$idx]['p1'] = ['cs' => (string)($sections[$idx]['p1'] ?? ''), 'en' => ''];
        }
        if (!isset($sections[$idx]['p2']) || !is_array($sections[$idx]['p2'])) {
            $sections[$idx]['p2'] = ['cs' => (string)($sections[$idx]['p2'] ?? ''), 'en' => ''];
        }

        // Update bilingual texts (fallback to legacy keys)
        $sections[$idx]['title']['cs'] = isset($fields['title_cs']) ? (string)$fields['title_cs'] : ($sections[$idx]['title']['cs'] ?? ((string)($fields['title'] ?? '')));
        $sections[$idx]['p1']['cs'] = isset($fields['p1_cs']) ? (string)$fields['p1_cs'] : ($sections[$idx]['p1']['cs'] ?? ((string)($fields['p1'] ?? '')));
        $sections[$idx]['p2']['cs'] = isset($fields['p2_cs']) ? (string)$fields['p2_cs'] : ($sections[$idx]['p2']['cs'] ?? ((string)($fields['p2'] ?? '')));

        if (isset($fields['title_en'])) $sections[$idx]['title']['en'] = (string)$fields['title_en'];
        if (isset($fields['p1_en'])) $sections[$idx]['p1']['en'] = (string)$fields['p1_en'];
        if (isset($fields['p2_en'])) $sections[$idx]['p2']['en'] = (string)$fields['p2_en'];

        $sections[$idx]['img_alt'] = isset($fields['img_alt']) ? (string)$fields['img_alt'] : ($sections[$idx]['img_alt'] ?? '');

        if (isset($fields['img_src']) && is_string($fields['img_src'])) {
            $img = $validateImgPath(trim($fields['img_src']));
            if ($img === null) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Neplatná cesta k obrázku.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $sections[$idx]['img_src'] = $img;
        }
    }

    if ($action === 'delete') {
        $idx = find_section_index($sections, $sectionId);
        if ($idx === -1) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Sekce nebyla nalezena.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        array_splice($sections, $idx, 1);
    }

    $store['sections'] = array_values($sections);
    save_index_sections($store);

    echo json_encode(['ok' => true, 'action' => $action, 'updatedSectionId' => $sectionId], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}