document.addEventListener("DOMContentLoaded", () => {

    /* ================= SLIDESHOW ================= */
    const images = document.querySelectorAll('.image-slide');
    const texts = document.querySelectorAll('.text-slide');
    let index = 0;
    const total = images.length;

    function updateSlides() {
        images.forEach((img, i) => {
            img.className = 'image-slide';
            if (i === index) img.classList.add('active');
            else if (i === (index + 1) % total) img.classList.add('next');
            else if (i === (index - 1 + total) % total) img.classList.add('prev');
        });

        texts.forEach((txt, i) => {
            txt.className = 'text-slide';
            if (i === index) txt.classList.add('active');
            else txt.classList.add('out');
        });
    }

    setInterval(() => {
        index = (index + 1) % total;
        updateSlides();
    }, 3000);

    /* ================= STICKY NAV ================= */
    const nav = document.getElementById('heroNav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    });

    /* ================= LANGUAGE SYSTEM (NEW) ================= */

    const TRANSLATIONS = {
        cs: {
            hero_title: "ALABARTE",
            kontakt: "Kontakt",
            vina: "Vína",
            galerie: "Galerie",
            eshop: "E-shop",
            text1_h2: "První text",
            text1_p: "Synchronizovaný s prvním obrázkem",
            text2_h2: "Druhý text",
            text2_p: "Synchronizovaný s druhým obrázkem",
            text3_h2: "Třetí text",
            text3_p: "Synchronizovaný s třetím obrázkem"
        },
        en: {
            hero_title: "ALABARTE",
            kontakt: "Contact",
            vina: "Wines",
            galerie: "Gallery",
            eshop: "Shop",
            text1_h2: "First text",
            text1_p: "Synchronized with first image",
            text2_h2: "Second text",
            text2_p: "Synchronized with second image",
            text3_h2: "Third text",
            text3_p: "Synchronized with third image"
        }
    };

    function setLanguage(lang) {
        const dict = TRANSLATIONS[lang];

        if (!dict) return;

        document.querySelectorAll('[data-key]').forEach(el => {
            const key = el.getAttribute('data-key');
            if (dict[key]) {
                el.textContent = dict[key];
            }
        });

        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === lang);
        });

        document.documentElement.lang = lang;
        localStorage.setItem('lang', lang);
    }

    // init
    const savedLang = localStorage.getItem('lang') || 'cs';
    setLanguage(savedLang);

    // buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            setLanguage(btn.dataset.lang);
        });
    });

});
