<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_admin();

require_once dirname(__DIR__) . '/lib/render_sections.php';
require_once dirname(__DIR__) . '/lib/content_store.php';

$assetPrefix = '..';

$dynamicSectionsHtml = render_index_sections_from_json($assetPrefix);

$sectionsStore = load_index_sections();
$sectionsData = isset($sectionsStore['sections']) && is_array($sectionsStore['sections']) ? $sectionsStore['sections'] : [];

$dynTranslations = ['cs' => [], 'en' => []];
foreach ($sectionsData as $s) {
    if (!is_array($s)) continue;
    $id = isset($s['id']) && is_string($s['id']) ? $s['id'] : '';
    if ($id === '') continue;

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

    $dynTranslations['cs']["section:$id:title"] = $titleCs;
    $dynTranslations['cs']["section:$id:p1"] = $p1Cs;
    $dynTranslations['cs']["section:$id:p2"] = $p2Cs;

    $dynTranslations['en']["section:$id:title"] = $titleEn !== '' ? $titleEn : $titleCs;
    $dynTranslations['en']["section:$id:p1"] = $p1En !== '' ? $p1En : $p1Cs;
    $dynTranslations['en']["section:$id:p2"] = $p2En !== '' ? $p2En : $p2Cs;
}

?><!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ALABARTE (admin content)</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= $assetPrefix ?>/style.css">

    <script>
        window.DYNAMIC_TRANSLATIONS = <?= json_encode($dynTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.ADMIN_CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <script src="<?= $assetPrefix ?>/index.js" defer></script>
    <script src="index-editor.js" defer></script>
</head>
<body>

<div class="admin-bar" style="position: sticky; top: 0; z-index: 9999; display:flex; justify-content: space-between; gap:12px; padding:8px 12px; background: rgba(0,0,0,0.55); backdrop-filter: blur(6px); border-bottom:1px solid rgba(255,255,255,0.18); color: rgba(255,255,255,0.92); font-size: 13px;">
    <div style="display:flex; gap: 12px; align-items:center;">
        <strong>Admin</strong>
    </div>
    <div class="admin-bar__right" style="display:flex; gap: 12px; align-items:center;">
        <a href="<?= $assetPrefix ?>/index.php" target="_blank" rel="noopener" style="color:#fff; text-decoration:none;">Otevřít web</a>
        <a href="logout.php" style="color:#fff; text-decoration:none;">Odhlásit</a>
    </div>
</div>

<section class="hero">
    <div class="hero-bg">
        <div class="hero-overlay">
            <div class="hero-top">
                <div class="lang-switcher">
                    <button type="button" class="lang-btn" data-lang="cs">CS</button>
                    <button type="button" class="lang-btn" data-lang="en">EN</button>
                </div>
                <nav class="hero-nav" id="heroNav">
                    <a href="#kontakt" data-key="kontakt">Kontakt</a>
                    <a href="<?= $assetPrefix ?>/vina.html" data-key="vina">Vína</a>
                    <a href="<?= $assetPrefix ?>/aktuality.html" data-key="aktuality">Galerie</a>
                    <a href="https://www.alabarte.cz/vino/" data-key="eshop">E-shop</a>
                </nav>
            </div>

            <header class="hero-header">
                <div class="hero-logo">
                    <a href="<?= $assetPrefix ?>/index.php">
                        <img src="<?= $assetPrefix ?>/Images/Alabarte-logo.webp" alt="Alabarte">
                    </a>
                </div>
            </header>

            <div class="hero-slides">
                <a href="https://www.alabarte.cz/vino/" class="slideshow image-stage hero-wine-link" aria-label="Zobrazit vína">
                    <div class="image-slide active"><img src="<?= $assetPrefix ?>/Images/Vina/vino1.png" alt=""></div>
                    <div class="image-slide next"><img src="<?= $assetPrefix ?>/Images/Vina/Vino2.png" alt=""></div>
                    <div class="image-slide prev"><img src="<?= $assetPrefix ?>/Images/Vina/Vino3.png" alt=""></div>
                </a>
                <div class="slideshow text-stage">
                    <div class="text-paper" aria-hidden="true"></div>
                    <div class="text-slide active"><div class="text-bg"><h2 data-key="text1_h2">Vernaccia di San Gimignano</h2><p data-key="text1_p">Svěží bílé víno s minerálním charakterem, jemnými citrusovými tóny a typickou elegancí toskánské krajiny.</p></div></div>
                    <div class="text-slide"><div class="text-bg"><h2 data-key="text2_h2">Druhý text</h2><p data-key="text2_p">Synchronizovaný s druhým obrázkem</p></div></div>
                    <div class="text-slide"><div class="text-bg"><h2 data-key="text3_h2">Třetí text</h2><p data-key="text3_p">Synchronizovaný s třetím obrázkem</p></div></div>
                </div>
            </div>

            <div class="hero-cta">
                <a class="cta-btn cta-btn--primary" href="<?= $assetPrefix ?>/vina.html">Naše vína</a>
                <a class="cta-btn cta-btn--ghost" href="https://www.alabarte.cz/vino/" target="_blank" rel="noopener">E-shop</a>
            </div>
            <div class="hero-divider"></div>
        </div>
    </div>
</section>

<div class="page-content">
    <div class="info-wrapper">
        <?php if ($dynamicSectionsHtml !== ''): ?>
            <?= $dynamicSectionsHtml ?>
        <?php else: ?>
            <!-- fallback: keep same initial sections so you can start editing immediately -->
            <section class="info-section" id="o-nas" data-admin-section="o-nas">
                <div class="info-inner js-reveal">
                    <div class="info-media"><img src="<?= $assetPrefix ?>/Images/o-nas.png" alt="Alabarte – dovoz toskánských vín"></div>
                    <div class="info-text"><div class="brush-card">
                        <span class="info-eyebrow" data-key="about_eyebrow">O nás</span>
                        <h2 class="info-title" data-key="about_title">Alabarte – Toskánsko v každé lahvi</h2>
                        <p class="info-par" data-key="about_p1">Jsme česká firma, která dováží pečlivě vybraná vína z Toskánska do České republiky.</p>
                        <p class="info-par" data-key="about_p2">Spolupracujeme s vinařstvím Fattoria La Torre a přinášíme vína s typickým projevem regionu.</p>
                    </div></div>
                </div>
            </section>

            <section class="info-section info-section--reverse" id="fattoria" data-admin-section="fattoria">
                <div class="info-inner">
                    <div class="info-media"><img src="<?= $assetPrefix ?>/Images/fattoria.jpg" alt="Fattoria La Torre – vinařství v Toskánsku"></div>
                    <div class="info-text">
                        <span class="info-eyebrow" data-key="winery_eyebrow">Vinařství</span>
                        <h2 class="info-title" data-key="winery_title">Fattoria La Torre</h2>
                        <p class="info-par" data-key="winery_p1">Rodinné vinařství v srdci Toskánska, kde se potkává tradice s moderním přístupem...</p>
                        <ul class="info-list">
                            <li data-key="winery_li1">Typický projev Toskánska a odrůd jako Sangiovese či Vernaccia</li>
                            <li data-key="winery_li2">Důraz na čistotu, eleganci a vyváženost</li>
                            <li data-key="winery_li3">Vína vhodná k jídlu i k samostatnému vychutnání</li>
                        </ul>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

</body>
</html>