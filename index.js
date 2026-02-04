// index.js
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
            if (dict[key]) el.textContent = dict[key];
        });

        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === lang);
        });

        document.documentElement.lang = lang;
        localStorage.setItem('lang', lang);
    }

    const savedLang = localStorage.getItem('lang') || 'cs';
    setLanguage(savedLang);

    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => setLanguage(btn.dataset.lang));
    });

    /* ================= NEWS MODAL (ONLY ONE IMPLEMENTATION) =================
       DŮLEŽITÉ: smaž/zakomentuj všechny ostatní bloky pro modal v souboru,
       hlavně duplikovaný IIFE a blok s document.querySelector('.modal')...
    */
    (() => {
        const modal = document.getElementById("newsModal");
        if (!modal) return;

        const imageEl = document.getElementById("newsModalImage");
        const titleEl = document.getElementById("newsModalTitle");
        const metaEl = document.getElementById("newsModalMeta");
        const perexEl = document.getElementById("newsModalPerex");
        const bodyEl = document.getElementById("newsModalBody");

        const openModal = (card) => {
            const title = card.dataset.title || "";
            const date = card.dataset.date || "";
            const image = card.dataset.image || "";
            const perex = card.dataset.perex || "";
            const body = card.dataset.body || "";

            if (titleEl) titleEl.textContent = title;
            if (metaEl) metaEl.textContent = date;
            if (perexEl) perexEl.textContent = perex;

            if (imageEl) {
                imageEl.src = image;
                imageEl.alt = title || "Aktualita";
                imageEl.style.display = image ? "" : "none";
            }

            if (bodyEl) {
                bodyEl.innerHTML = "";
                body.split("\n\n").forEach((chunk) => {
                    const t = chunk.trim();
                    if (!t) return;
                    const p = document.createElement("p");
                    p.textContent = t;
                    bodyEl.appendChild(p);
                });
            }

            modal.classList.remove("is-closing");
            modal.classList.add("is-open");
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("modal-open");
        };

        const closeModal = () => {
            if (!modal.classList.contains("is-open")) return;

            modal.classList.add("is-closing");
            window.setTimeout(() => {
                modal.classList.remove("is-open", "is-closing");
                modal.setAttribute("aria-hidden", "true");
                document.body.classList.remove("modal-open");
            }, 250);
        };

        document.querySelectorAll(".news-item[data-news]").forEach((card) => {
            card.addEventListener("click", () => openModal(card));
            card.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    openModal(card);
                }
            });
        });

        modal.addEventListener("click", (e) => {
            const target = e.target;
            if (!(target instanceof Element)) return;
            if (target.matches("[data-close='true']")) closeModal();
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") closeModal();
        });
    })();

});
