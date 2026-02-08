<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_admin();
require_once dirname(__DIR__, 2) . '/lib/content_store.php';

header('Content-Type: application/json; charset=utf-8');

$store = load_index_sections();
$sections = isset($store['sections']) && is_array($store['sections']) ? $store['sections'] : [];

$changed = 0;
foreach ($sections as &$s) {
    if (!is_array($s)) continue;

    foreach (['title', 'p1', 'p2'] as $k) {
        if (isset($s[$k]) && is_string($s[$k])) {
            $s[$k] = ['cs' => $s[$k], 'en' => ''];
            $changed++;
        }
    }
}
unset($s);

$store['sections'] = $sections;
save_index_sections($store);

echo json_encode(['ok' => true, 'changedFields' => $changed], JSON_UNESCAPED_UNICODE);
