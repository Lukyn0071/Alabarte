<?php
require_once __DIR__ . '/lib/render_sections.php';
require_once __DIR__ . '/lib/content_store.php';

$dynamicSectionsHtml = render_index_sections_from_json();
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

    // EN fallback to CS if not filled
    $dynTranslations['en']["section:$id:title"] = $titleEn !== '' ? $titleEn : $titleCs;
    $dynTranslations['en']["section:$id:p1"] = $p1En !== '' ? $p1En : $p1Cs;
    $dynTranslations['en']["section:$id:p2"] = $p2En !== '' ? $p2En : $p2Cs;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALABARTE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <script>
        window.DYNAMIC_TRANSLATIONS = <?= json_encode($dynTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <script src="index.js" defer></script>
</head>
<body>

<section class="hero">
    <!-- ✅ jen tato část má pozadí -->
    <div class="hero-bg">

        <div class="hero-overlay">

            <!-- ===== TOP BAR ===== -->
            <div class="hero-top">

                <!-- LANG SWITCHER -->
                <div class="lang-switcher">
                    <button type="button" class="lang-btn" data-lang="cs">CS</button>
                    <button type="button" class="lang-btn" data-lang="en">EN</button>
                </div>

                <!-- NAV -->
                <nav class="hero-nav" id="heroNav">
                    <a href="#kontakt" data-key="kontakt">Kontakt</a>
                    <a href="vina.html" data-key="vina">Vína</a>
                    <a href="aktuality.html" data-key="aktuality">Galerie</a>
                    <a href="https://www.alabarte.cz/vino/" data-key="eshop">E-shop</a>
                </nav>

            </div>

            <!-- ===== HERO HEADER ===== -->
            <header class="hero-header">
                <div class="hero-logo">
                    <a href="index.html">
                        <img src="Images/Alabarte-logo.webp" alt="Alabarte">
                    </a>
                </div>
            </header>

            <!-- ===== SLIDES ===== -->
            <div class="hero-slides">
                <!-- IMAGES (klik -> https://www.alabarte.cz/vino/) -->
                <a href="https://www.alabarte.cz/vino/" class="slideshow image-stage hero-wine-link" aria-label="Zobrazit vína">
                    <div class="image-slide active">
                        <img src="Images/Vina/vino1.png" alt="">
                    </div>

                    <div class="image-slide next">
                        <img src="Images/Vina/Vino2.png" alt="">
                    </div>

                    <div class="image-slide prev">
                        <img src="Images/Vina/Vino3.png" alt="">
                    </div>
                </a>

                <!-- TEXTS -->
                <div class="slideshow text-stage">

                    <!-- STATICKÉ POZADÍ (NEANIMUJE SE) -->
                    <div class="text-paper" aria-hidden="true"></div>

                    <div class="text-slide active" data-cta-primary-href="vina.html" data-cta-ghost-href="https://www.alabarte.cz/vernaccia-di-san-gimignano-docg/">
                        <div class="text-bg">
                            <h2 data-key="text1_h2">Vernaccia di San Gimignano</h2>
                            <p data-key="text1_p">
                                Svěží bílé víno s minerálním charakterem, jemnými citrusovými tóny
                                a typickou elegancí toskánské krajiny.
                            </p>
                        </div>
                    </div>

                    <div class="text-slide" data-cta-primary-href="vina.html" data-cta-ghost-href="https://www.alabarte.cz/birbone/">
                        <div class="text-bg">
                            <h2 data-key="text2_h2">Druhý text</h2>
                            <p data-key="text2_p">Synchronizovaný s druhým obrázkem</p>
                        </div>
                    </div>

                    <div class="text-slide" data-cta-primary-href="vina.html" data-cta-ghost-href="https://www.alabarte.cz/vino/">
                        <div class="text-bg">
                            <h2 data-key="text3_h2">Třetí text</h2>
                            <p data-key="text3_p">Synchronizovaný s třetím obrázkem</p>
                        </div>
                    </div>

                    <!-- CTA outside slides (always clickable), still on text-paper background -->
                    <div class="hero-cta hero-cta--textstage" aria-label="Akce">
                        <a class="cta-btn cta-btn--primary" href="vina.html">Více o Vínech...</a>
                        <a class="cta-btn cta-btn--ghost" href="https://www.alabarte.cz/vino/" target="_blank" rel="noopener">E-shop</a>
                    </div>

                </div>
            </div>
            <!-- ✅ konec pozadí je přesně tady -->
            <div class="hero-divider"></div>

        </div>
    </div>
</section>

<!-- ✅ odtud už BEZ hero pozadí -->
<div class="page-content">

    <!-- ✅ NOVĚ: jeden velký box pro obě sekce -->
    <div class="info-wrapper">

        <?php if ($dynamicSectionsHtml !== ''): ?>
            <?= $dynamicSectionsHtml ?>
        <?php else: ?>
            <!-- Původní statický obsah jako fallback (dokud JSON nebude naplněn) -->

            <!-- O ALABARTE -->
            <section class="info-section" id="o-nas" data-admin-section="o-nas">
                <div class="info-inner js-reveal">
                    <div class="info-media">
                        <img src="Images/o-nas.png" alt="Alabarte – dovoz toskánských vín">
                    </div>

                    <div class="info-text">
                        <div class="brush-card">
                            <span class="info-eyebrow" data-key="about_eyebrow">O nás</span>
                            <h2 class="info-title" data-key="about_title">Alabarte – Toskánsko v každé lahvi</h2>
                            <p class="info-par" data-key="about_p1">
                                Jsme česká firma, která dováží pečlivě vybraná vína z Toskánska do České republiky.
                            </p>
                            <p class="info-par" data-key="about_p2">
                                Spolupracujeme s vinařstvím Fattoria La Torre a přinášíme vína s typickým projevem regionu.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FATTORIA LA TORRE -->
            <section class="info-section info-section--reverse" id="fattoria" data-admin-section="fattoria">
                <div class="info-inner">
                    <div class="info-media">
                        <img src="Images/fattoria.jpg" alt="Fattoria La Torre – vinařství v Toskánsku">
                    </div>

                    <div class="info-text">
                        <span class="info-eyebrow" data-key="winery_eyebrow">Vinařství</span>
                        <h2 class="info-title" data-key="winery_title">Fattoria La Torre</h2>
                        <p class="info-par" data-key="winery_p1">
                            Rodinné vinařství v srdci Toskánska, kde se potkává tradice s moderním přístupem...
                        </p>

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