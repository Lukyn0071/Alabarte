document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("wineModal");
    const modalImage = document.getElementById("modalImage");
    const modalTitle = document.getElementById("modalTitle");
    const modalMeta  = document.getElementById("modalMeta");
    const modalStory = document.getElementById("modalStory");
    const modalSpecs = document.getElementById("modalSpecs");
    const modalPairing = document.getElementById("modalPairing");

    // Data pro detail (zatím klidně opakované obrázky)
    const WINES = {
        ryzlink: {
            title: "Ryzlink rýnský",
            meta: "2023 • Morava",
            image: "Images/Vína/víno1.png",
            story:
                "Víno vzniklo z hroznů sbíraných v chladnějším ranním okně, aby se zachovala čistota aromatiky. " +
                "Ryzlink je postavený na mineralitě a přesnosti – působí klidně, ale s dlouhou energií v závěru.",
            specs: [
                "Odrůda: Ryzlink rýnský",
                "Styl: suché, minerální",
                "Zrání: nerez pro zachování svěžesti",
                "Alkohol: 12,5 % (příklad)",
                "Servis: 8–10 °C"
            ],
            pairing: [
                "Profil: citrus, bílé květy, jemné koření",
                "Párování: ryby, lehké omáčky, kozí sýr",
                "Příležitost: aperitiv, delikátní večeře"
            ]
        },
        pinot: {
            title: "Pinot Noir",
            meta: "2022 • elegantní styl",
            image: "Images/Vína/Víno2.png",
            story:
                "Pinot, který stojí na jemnosti a vrstvení. Tříslovina je uhlazená, ovoce čisté a vše drží pohromadě " +
                "nenápadná kořenitost. Víno působí jako tichá jistota – čím déle ho máš ve sklenici, tím víc nabídne.",
            specs: [
                "Odrůda: Pinot Noir",
                "Styl: jemné červené",
                "Zrání: částečně sud (příklad)",
                "Alkohol: 13 % (příklad)",
                "Servis: 14–16 °C"
            ],
            pairing: [
                "Profil: červené ovoce, lesní tón, samet",
                "Párování: drůbež, telecí, houby",
                "Doporučení: krátce dekantovat"
            ]
        },
        sekt: {
            title: "Sekt Brut",
            meta: "metoda tradiční • 24 měsíců",
            image: "Images/Vína/Víno%203.png",
            story:
                "Sekt s jemným perlením a přesnou suchostí. Druhotné kvašení a delší zrání na kalech dává hloubku, " +
                "ale pořád zůstává osvěžující. Ideální pro slavnostní momenty i jako sofistikovaný aperitiv.",
            specs: [
                "Styl: Brut",
                "Metoda: tradiční",
                "Zrání na kalech: 24 měsíců",
                "Servis: 6–8 °C"
            ],
            pairing: [
                "Profil: svěží, čisté, jemné perlení",
                "Párování: canapés, ústřice, tvrdé sýry",
                "Příležitost: oslavy, aperitiv"
            ]
        },
        sauvignon: {
            title: "Sauvignon",
            meta: "2023 • aromatické bílé",
            image: "Images/Vína/víno1.png",
            story:
                "Aromatika bylin a angreštu je výrazná, ale nepřekřičená. Chuť je šťavnatá a suchá, s energií, " +
                "která dělá z vína perfektní volbu pro lehkou gastronomii.",
            specs: [
                "Odrůda: Sauvignon",
                "Styl: suché, aromatické",
                "Zrání: nerez (příklad)",
                "Servis: 8–10 °C"
            ],
            pairing: [
                "Profil: bylinky, angrešt, svěžest",
                "Párování: saláty, sushi, kozí sýr",
                "Tip: skvělé i samotné"
            ]
        },
        frankovka: {
            title: "Frankovka",
            meta: "2021 • červené s charakterem",
            image: "Images/Vína/Víno2.png",
            story:
                "Frankovka s pevnějším páteřem a kořenitostí. Je to víno, které působí seriózně, ale není těžké. " +
                "Dlouhý dozvuk a struktura z něj dělají ideálního partnera k jídlu.",
            specs: [
                "Odrůda: Frankovka",
                "Styl: strukturované červené",
                "Tříslovina: střední až vyšší",
                "Servis: 16–18 °C"
            ],
            pairing: [
                "Profil: tmavé ovoce, koření, struktura",
                "Párování: gril, zvěřina, tvrdé sýry",
                "Doporučení: dekantace 15–30 min"
            ]
        },
        rulandske: {
            title: "Rulandské šedé",
            meta: "2022 • plnější bílé",
            image: "Images/Vína/Víno%203.png",
            story:
                "Plnější, hebké bílé s krémovější texturou. V aromatice najdeš hrušku a peckoviny, v chuti příjemnou " +
                "kulatost, která ale zůstává elegantní a čistá.",
            specs: [
                "Odrůda: Rulandské šedé",
                "Styl: plnější bílé",
                "Zrání: na jemných kalech (příklad)",
                "Servis: 9–11 °C"
            ],
            pairing: [
                "Profil: hruška, peckovina, jemná krémovost",
                "Párování: drůbež, těstoviny, sýr",
                "Příležitost: večeře, degustace"
            ]
        }
    };

    function openModal(wineKey) {
        const data = WINES[wineKey];
        if (!data) return;

        modal.classList.remove("is-closing");

        modalTitle.textContent = data.title;
        modalMeta.textContent = data.meta;

        modalImage.src = data.image;
        modalImage.alt = data.title;

        modalStory.textContent = data.story;

        modalSpecs.innerHTML = data.specs.map(item => `<li>${item}</li>`).join("");
        modalPairing.innerHTML = data.pairing.map(item => `<li>${item}</li>`).join("");

        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("modal-open");
    }

    function closeModal() {
        if (modal.classList.contains("is-closing")) return;

        modal.classList.add("is-closing");

        setTimeout(() => {
            modal.classList.remove("is-open", "is-closing");
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("modal-open");
            modalImage.src = "";
        }, 250);
    }

    // Klik/Enter/Space na kartu
    document.querySelectorAll(".wine-card[data-wine]").forEach(card => {
        card.addEventListener("click", () => openModal(card.dataset.wine));
        card.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                openModal(card.dataset.wine);
            }
        });
    });

    // Zavírání (křížek + backdrop)
    modal.addEventListener("click", (e) => {
        const close = e.target.closest("[data-close='true']");
        if (close) closeModal();
    });

    // ESC zavře
    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.classList.contains("is-open")) {
            closeModal();
        }
    });
});

/* ===== JEMNÝ NÁJEZD OBSAHU (po načtení všeho, včetně obrázků) ===== */
window.addEventListener("load", () => {
    document.querySelector(".vina-header")?.classList.add("is-visible");
    document.querySelector(".vina-content")?.classList.add("is-visible");
    document.querySelector(".wines")?.classList.add("is-visible");
});
