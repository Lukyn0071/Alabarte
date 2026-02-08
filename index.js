document.addEventListener("DOMContentLoaded", () => {

    /* ================= SLIDESHOW ================= */
    const images = document.querySelectorAll('.image-slide');
    const texts = document.querySelectorAll('.text-slide');
    let index = 0;
    const total = images.length;

    function setCtaFromSlide(slide) {
        const cta = document.querySelector('.hero-cta--textstage');
        if (!cta || !slide) return;

        const primaryHref = slide.getAttribute('data-cta-primary-href') || 'vina.html';
        const ghostHref = slide.getAttribute('data-cta-ghost-href') || 'https://www.alabarte.cz/vino/';

        const primaryBtn = cta.querySelector('.cta-btn--primary');
        const ghostBtn = cta.querySelector('.cta-btn--ghost');

        if (primaryBtn) primaryBtn.setAttribute('href', primaryHref);
        if (ghostBtn) {
            ghostBtn.setAttribute('href', ghostHref);
            const isExternal = /^https?:\/\//i.test(ghostHref);
            if (isExternal) {
                ghostBtn.setAttribute('target', '_blank');
                ghostBtn.setAttribute('rel', 'noopener');
            } else {
                ghostBtn.removeAttribute('target');
                ghostBtn.removeAttribute('rel');
            }
        }
    }

    function updateSlides(nextIndex = index) {
        // --- images ---
        images.forEach((img, i) => {
            img.className = 'image-slide';
            if (i === nextIndex) img.classList.add('active');
            else if (i === (nextIndex + 1) % total) img.classList.add('next');
            else if (i === (nextIndex - 1 + total) % total) img.classList.add('prev');
        });

        const prevIndex = index;
        const prev = texts[prevIndex];
        const next = texts[nextIndex];

        if (prev) {
            prev.classList.remove('active');
            prev.classList.add('out');
        }

        if (next) {
            next.classList.remove('out');
            next.classList.remove('active');
            next.offsetHeight;
            requestAnimationFrame(() => {
                next.classList.add('active');
                setCtaFromSlide(next);
            });
        }

        texts.forEach((t, i) => {
            if (i !== prevIndex && i !== nextIndex) {
                t.classList.remove('active');
                t.classList.remove('out');
            }
        });

        index = nextIndex;
    }

    // Set initial state (no outgoing)
    texts.forEach((t) => {
        t.classList.remove('active');
        t.classList.remove('out');
    });
    if (texts[0]) {
        texts[0].classList.add('active');
        setCtaFromSlide(texts[0]);
    }
    index = 0;

    setInterval(() => {
        const nextIndex = (index + 1) % total;
        updateSlides(nextIndex);
    }, 3000);

    /* ================= STICKY NAV ================= */
    const nav = document.getElementById('heroNav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    });

    /* ================= SCROLL CROSSFADE (hero background) ================= */
    const heroBg = document.querySelector(".hero-bg");

    function clamp01(v) {
        return Math.max(0, Math.min(1, v));
    }

    let ticking = false;
    window.addEventListener("scroll", () => {
        if (ticking) return;
        ticking = true;

        requestAnimationFrame(() => {
            const range = 520; // kolik px scrollu = plné prolnutí
            const t = clamp01(window.scrollY / range);

            if (heroBg) heroBg.style.setProperty("--blend", t);

            ticking = false;
        });
    });

    /* ================= LANGUAGE SYSTEM ================= */
    const TRANSLATIONS = {
        cs: {
            hero_title: "ALABARTE",
            kontakt: "Kontakt",
            vina: "Vina",
            aktuality: "Aktuality",
            eshop: "E-shop",

            text1_h2: "Vernaccia di San Gimignano",
            text1_p: "Svěží bílé víno s minerálním charakterem, jemnými citrusovými tóny a typickou elegancí toskánské krajiny.",

            text2_h2: "Rosso Toscana",
            text2_p: "Vyvážené červené víno s tóny zralého ovoce a jemného koření, které spojuje tradici Toskánska s moderním projevem.",

            text3_h2: "Sangiovese z Toskánska",
            text3_p: "Charakteristické červené víno s jemnými tříslovinami, ovocným profilem a dlouhým, harmonickým závěrem.",

            about_eyebrow: "O nás",
            about_title: "Alabarte – Toskánsko v každé lahvi",
            about_p1: "Jsme česká firma, která dováží pečlivě vybraná vína z Toskánska do České republiky. Zaměřujeme se na charakter, čistotu a příběh každé lahve – od vinice až po váš stůl.",
            about_p2: "Spolupracujeme s vinařstvím Fattoria La Torre a přinášíme vína, která vynikají elegancí, typickým projevem regionu a poctivou prací ve vinici.",

            winery_eyebrow: "Vinařství",
            winery_title: "Fattoria La Torre",
            winery_p1: "Rodinné vinařství v srdci Toskánska, kde se potkává tradice s moderním přístupem. Důraz je kladen na práci ve vinici, šetrné zpracování a styl vín, který je věrný místu původu.",
            winery_li1: "Typický projev Toskánska a odrůd jako Sangiovese či Vernaccia",
            winery_li2: "Důraz na čistotu, eleganci a vyváženost",
            winery_li3: "Vina vhodná k jídlu i k samostatnému vychutnání"
        },

        en: {
            hero_title: "ALABARTE",
            kontakt: "Contact",
            vina: "Wines",
            aktuality: "News",
            eshop: "Shop",

            text1_h2: "Vernaccia di San Gimignano",
            text1_p: "Fresh white wine with a mineral character, gentle citrus notes, and the signature elegance of Tuscany.",

            text2_h2: "Rosso Toscana",
            text2_p: "Balanced red wine with ripe fruit and subtle spice, blending Tuscan tradition with a modern expression.",

            text3_h2: "Sangiovese from Tuscany",
            text3_p: "A distinctive red with smooth tannins, a fruity profile, and a long, harmonious finish.",

            about_eyebrow: "About",
            about_title: "Alabarte – Tuscany in every bottle",
            about_p1: "We are a Czech company bringing carefully selected Tuscan wines to the Czech Republic. We focus on character, purity, and the story behind each bottle—from vineyard to table.",
            about_p2: "We work with Fattoria La Torre, offering wines defined by elegance, a true sense of place, and honest vineyard craft.",

            winery_eyebrow: "Winery",
            winery_title: "Fattoria La Torre",
            winery_p1: "A family winery in the heart of Tuscany where tradition meets a modern approach. The focus is on vineyard work, gentle processing, and a style that stays true to its origin.",
            winery_li1: "A true Tuscan expression of varieties like Sangiovese and Vernaccia",
            winery_li2: "Focus on purity, elegance, and balance",
            winery_li3: "Wines made for food and for pure enjoyment"
        }
    };

    function setLanguage(lang) {
        const dict = TRANSLATIONS[lang];
        if (!dict) return;

        const dynamic = (window.DYNAMIC_TRANSLATIONS && window.DYNAMIC_TRANSLATIONS[lang]) ? window.DYNAMIC_TRANSLATIONS[lang] : {};

        document.querySelectorAll('[data-key]').forEach(el => {
            const key = el.getAttribute('data-key');
            if (!key) return;

            if (Object.prototype.hasOwnProperty.call(dynamic, key)) {
                el.textContent = dynamic[key];
                return;
            }

            if (Object.prototype.hasOwnProperty.call(dict, key)) {
                el.textContent = dict[key];
            }
        });

        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === lang);
        });

        document.documentElement.lang = lang;
        localStorage.setItem('lang', lang);
    }

    // init jazyk
    const savedLang = localStorage.getItem('lang') || 'cs';
    setLanguage(savedLang);

    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            setLanguage(btn.dataset.lang);
        });
    });

    /* ===== NÁJEZD SEKCE "O NÁS" ===== */
    window.addEventListener("load", () => {
        document.querySelector("#o-nas .js-reveal")?.classList.add("is-visible");
    });

});