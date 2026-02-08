<?php
declare(strict_types=1);

require_once __DIR__ . '/content_store.php';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Renders .page-content sections for index.php/admin preview.
 */
function render_index_sections_from_json(string $assetPrefix = ''): string
{
    $store = load_index_sections();
    $sections = $store['sections'] ?? [];
    if (!is_array($sections) || count($sections) === 0) {
        return '';
    }

    $assetPrefix = rtrim($assetPrefix, '/');
    $prefix = $assetPrefix !== '' ? ($assetPrefix . '/') : '';

    $out = "";
    foreach ($sections as $s) {
        if (!is_array($s)) continue;
        $id = isset($s['id']) && is_string($s['id']) ? $s['id'] : '';
        if ($id === '') continue;

        $class = isset($s['class']) && is_string($s['class']) && $s['class'] !== '' ? $s['class'] : 'info-section';

        // i18n fields
        $titleCs = '';
        $titleEn = '';
        if (isset($s['title']) && is_array($s['title'])) {
            $titleCs = (string)($s['title']['cs'] ?? '');
            $titleEn = (string)($s['title']['en'] ?? '');
        } else {
            $titleCs = (string)($s['title'] ?? '');
        }

        $p1Cs = '';
        $p1En = '';
        if (isset($s['p1']) && is_array($s['p1'])) {
            $p1Cs = (string)($s['p1']['cs'] ?? '');
            $p1En = (string)($s['p1']['en'] ?? '');
        } else {
            $p1Cs = (string)($s['p1'] ?? '');
        }

        $p2Cs = '';
        $p2En = '';
        if (isset($s['p2']) && is_array($s['p2'])) {
            $p2Cs = (string)($s['p2']['cs'] ?? '');
            $p2En = (string)($s['p2']['en'] ?? '');
        } else {
            $p2Cs = (string)($s['p2'] ?? '');
        }

        $imgSrc = isset($s['img_src']) ? (string)$s['img_src'] : '';
        $imgAlt = isset($s['img_alt']) ? (string)$s['img_alt'] : '';

        $imgSrc = ltrim($imgSrc, '/');
        $imgSrc = $prefix . $imgSrc;

        $keyTitle = 'section:' . $id . ':title';
        $keyP1 = 'section:' . $id . ':p1';
        $keyP2 = 'section:' . $id . ':p2';

        $out .= "\n        <section class=\"" . h($class) . "\" id=\"" . h($id) . "\" data-admin-section=\"" . h($id) . "\"";
        $out .= " data-admin-title-cs=\"" . h($titleCs) . "\" data-admin-p1-cs=\"" . h($p1Cs) . "\" data-admin-p2-cs=\"" . h($p2Cs) . "\"";
        $out .= " data-admin-title-en=\"" . h($titleEn) . "\" data-admin-p1-en=\"" . h($p1En) . "\" data-admin-p2-en=\"" . h($p2En) . "\"";
        $out .= ">\n";

        $out .= "            <div class=\"info-inner\">\n";
        $out .= "                <div class=\"info-media\">\n";
        $out .= "                    <img src=\"" . h($imgSrc) . "\" alt=\"" . h($imgAlt) . "\">\n";
        $out .= "                </div>\n\n";
        $out .= "                <div class=\"info-text\">\n";
        $out .= "                    <div class=\"brush-card\">\n";
        $out .= "                        <h2 class=\"info-title\" data-key=\"" . h($keyTitle) . "\">" . h($titleCs) . "</h2>\n";
        $out .= "                        <p class=\"info-par\" data-key=\"" . h($keyP1) . "\">" . h($p1Cs) . "</p>\n";
        $out .= "                        <p class=\"info-par\" data-key=\"" . h($keyP2) . "\">" . h($p2Cs) . "</p>\n";
        $out .= "                    </div>\n";
        $out .= "                </div>\n";
        $out .= "            </div>\n";
        $out .= "        </section>\n";
    }

    return $out;
}