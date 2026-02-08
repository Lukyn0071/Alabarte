(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Use relative URLs so it works under any base path
  const apiUrl = 'api/index_content.php';
  const uploadUrl = 'api/upload_image.php';

  function ensureEditorUI(section) {
    if (section.querySelector('.admin-section-actions')) return;

    section.style.position = section.style.position || 'relative';

    const bar = document.createElement('div');
    bar.className = 'admin-section-actions';
    bar.innerHTML = `
      <button type="button" class="admin-btn admin-btn-edit">Upravit</button>
      <button type="button" class="admin-btn admin-btn-delete">Smazat</button>
    `;

    section.appendChild(bar);

    bar.querySelector('.admin-btn-edit').addEventListener('click', () => openEditor(section));
    bar.querySelector('.admin-btn-delete').addEventListener('click', () => deleteSection(section));
  }

  function ensureAddSectionButton() {
    // Place the button under the sections (not in the top admin bar)
    const wrapper = document.querySelector('.page-content .info-wrapper');
    if (!wrapper || wrapper.querySelector('.admin-add-section')) return;

    const footer = document.createElement('div');
    footer.className = 'admin-add-section-wrap';
    footer.innerHTML = `
      <div class="admin-add-section-box">
        <button type="button" class="admin-add-section admin-btn">Přidat sekci</button>
      </div>
    `;

    wrapper.appendChild(footer);

    footer.querySelector('.admin-add-section').addEventListener('click', openAddSection);
  }

  function createModalIfMissing() {
    if ($('#adminEditModal')) return;

    const modal = document.createElement('div');
    modal.id = 'adminEditModal';
    modal.className = 'admin-modal';
    modal.innerHTML = `
      <div class="admin-modal__backdrop" data-close="1"></div>
      <div class="admin-modal__panel" role="dialog" aria-modal="true" aria-label="Upravit sekci">
        <div class="admin-modal__head">
          <strong>Upravit sekci</strong>
          <button type="button" class="admin-icon-btn" data-close="1">✕</button>
        </div>

        <div class="admin-lang-tabs">
          <button type="button" class="admin-lang-tab is-active" data-lang="cs">Čeština</button>
          <button type="button" class="admin-lang-tab" data-lang="en">English</button>
        </div>

        <form class="admin-form" data-mode="edit">
          <input type="hidden" name="sectionId" value="">
          <input type="hidden" name="active_lang" value="cs">

          <div class="admin-lang-panel" data-lang-panel="cs">
            <label>Nadpis (CS)</label>
            <input name="title_cs" type="text" maxlength="200" />

            <label>Text 1 (CS)</label>
            <textarea name="p1_cs" rows="4"></textarea>

            <label>Text 2 (CS)</label>
            <textarea name="p2_cs" rows="4"></textarea>
          </div>

          <div class="admin-lang-panel" data-lang-panel="en" style="display:none;">
            <label>Title (EN)</label>
            <input name="title_en" type="text" maxlength="200" />

            <label>Text 1 (EN)</label>
            <textarea name="p1_en" rows="4"></textarea>

            <label>Text 2 (EN)</label>
            <textarea name="p2_en" rows="4"></textarea>
          </div>

          <div class="admin-grid2">
            <div>
              <label>Obrázek (vybrat soubor)</label>
              <input name="img_file" type="file" accept="image/png,image/jpeg,image/webp" />
              <div class="admin-small">Povoleno: JPG/PNG/WEBP, max 5 MB</div>
            </div>
            <div>
              <label>Náhled</label>
              <div class="admin-preview"><img alt="" /></div>
            </div>
          </div>

          <label>Alt text</label>
          <input name="img_alt" type="text" maxlength="200" />

          <div class="admin-form__actions">
            <button type="button" class="admin-btn" data-close="1">Zavřít</button>
            <button type="submit" class="admin-btn admin-btn-primary">Uložit</button>
          </div>
          <div class="admin-form__msg" aria-live="polite"></div>
        </form>
      </div>
    `;

    document.body.appendChild(modal);

    $$('[data-close="1"]', modal).forEach(el => el.addEventListener('click', closeModal));

    // tabs
    $$('.admin-lang-tab', modal).forEach(btn => {
      btn.addEventListener('click', () => {
        const lang = btn.getAttribute('data-lang');
        if (!lang) return;
        $('input[name="active_lang"]', modal).value = lang;
        $$('.admin-lang-tab', modal).forEach(b => b.classList.toggle('is-active', b === btn));
        $$('.admin-lang-panel', modal).forEach(p => {
          p.style.display = (p.getAttribute('data-lang-panel') === lang) ? '' : 'none';
        });
      });
    });

    $('.admin-form', modal).addEventListener('submit', async (e) => {
      e.preventDefault();
      await saveForm();
    });

    $('input[name="img_file"]', modal).addEventListener('change', () => {
      const file = $('input[name="img_file"]', modal).files?.[0];
      const img = $('.admin-preview img', modal);
      if (!file) return;
      img.src = URL.createObjectURL(file);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && $('#adminEditModal')?.classList.contains('is-open')) closeModal();
    });
  }

  function createAddModalIfMissing() {
    if ($('#adminAddModal')) return;

    const modal = document.createElement('div');
    modal.id = 'adminAddModal';
    modal.className = 'admin-modal';
    modal.innerHTML = `
      <div class="admin-modal__backdrop" data-close-add="1"></div>
      <div class="admin-modal__panel" role="dialog" aria-modal="true" aria-label="Přidat sekci">
        <div class="admin-modal__head">
          <strong>Přidat sekci</strong>
          <button type="button" class="admin-icon-btn" data-close-add="1">✕</button>
        </div>

        <div class="admin-lang-tabs">
          <button type="button" class="admin-lang-tab is-active" data-lang="cs">Čeština</button>
          <button type="button" class="admin-lang-tab" data-lang="en">English</button>
        </div>

        <form class="admin-form" data-mode="add">
          <input type="hidden" name="active_lang" value="cs">

          <label>ID sekce (unikátní; bez mezer, např. novasekce)</label>
          <input name="new_section_id" type="text" maxlength="64" placeholder="novasekce" />

          <label>Pozice</label>
          <select name="position">
            <option value="end">Na konec</option>
            <option value="start">Na začátek</option>
          </select>

          <div class="admin-lang-panel" data-lang-panel="cs">
            <label>Nadpis (CS)</label>
            <input name="title_cs" type="text" maxlength="200" />

            <label>Text 1 (CS)</label>
            <textarea name="p1_cs" rows="4"></textarea>

            <label>Text 2 (CS)</label>
            <textarea name="p2_cs" rows="4"></textarea>
          </div>

          <div class="admin-lang-panel" data-lang-panel="en" style="display:none;">
            <label>Title (EN)</label>
            <input name="title_en" type="text" maxlength="200" />

            <label>Text 1 (EN)</label>
            <textarea name="p1_en" rows="4"></textarea>

            <label>Text 2 (EN)</label>
            <textarea name="p2_en" rows="4"></textarea>
          </div>

          <label>Obrázek (vybrat soubor)</label>
          <input name="img_file" type="file" accept="image/png,image/jpeg,image/webp" />

          <label>Alt text</label>
          <input name="img_alt" type="text" maxlength="200" />

          <div class="admin-form__actions">
            <button type="button" class="admin-btn" data-close-add="1">Zavřít</button>
            <button type="submit" class="admin-btn admin-btn-primary">Vytvořit</button>
          </div>
          <div class="admin-form__msg" aria-live="polite"></div>
        </form>
      </div>
    `;

    document.body.appendChild(modal);
    $$('[data-close-add="1"]', modal).forEach(el => el.addEventListener('click', closeAddModal));

    // tabs
    $$('.admin-lang-tab', modal).forEach(btn => {
      btn.addEventListener('click', () => {
        const lang = btn.getAttribute('data-lang');
        if (!lang) return;
        $('input[name="active_lang"]', modal).value = lang;
        $$('.admin-lang-tab', modal).forEach(b => b.classList.toggle('is-active', b === btn));
        $$('.admin-lang-panel', modal).forEach(p => {
          p.style.display = (p.getAttribute('data-lang-panel') === lang) ? '' : 'none';
        });
      });
    });

    $('.admin-form', modal).addEventListener('submit', async (e) => {
      e.preventDefault();
      await createSectionFromForm();
    });
  }

  function openEditor(section) {
    createModalIfMissing();
    const modal = $('#adminEditModal');
    const form = $('.admin-form', modal);
    const msg = $('.admin-form__msg', modal);
    msg.textContent = '';

    const sectionId = section.getAttribute('data-admin-section');
    form.sectionId.value = sectionId;

    const titleEl = $('.info-title', section);
    const pEls = $$('.info-par', section);
    const imgEl = $('img', $('.info-media', section) || section);

    // If section already has saved bilingual fields, prefer them.
    // We store them in data attributes on the section for admin preview.
    const csTitle = section.getAttribute('data-admin-title-cs') || (titleEl ? titleEl.textContent.trim() : '');
    const csP1 = section.getAttribute('data-admin-p1-cs') || (pEls[0] ? pEls[0].textContent.trim() : '');
    const csP2 = section.getAttribute('data-admin-p2-cs') || (pEls[1] ? pEls[1].textContent.trim() : '');

    const enTitle = section.getAttribute('data-admin-title-en') || '';
    const enP1 = section.getAttribute('data-admin-p1-en') || '';
    const enP2 = section.getAttribute('data-admin-p2-en') || '';

    form.title_cs.value = csTitle;
    form.p1_cs.value = csP1;
    form.p2_cs.value = csP2;

    form.title_en.value = enTitle;
    form.p1_en.value = enP1;
    form.p2_en.value = enP2;

    form.img_alt.value = imgEl ? (imgEl.getAttribute('alt') || '') : '';

    // reset file input + preview
    const fileInput = $('input[name="img_file"]', modal);
    fileInput.value = '';
    const preview = $('.admin-preview img', modal);
    preview.src = imgEl ? (imgEl.getAttribute('src') || '') : '';

    // reset to cs tab
    $('input[name="active_lang"]', modal).value = 'cs';
    $$('.admin-lang-tab', modal).forEach(b => b.classList.toggle('is-active', b.getAttribute('data-lang') === 'cs'));
    $$('.admin-lang-panel', modal).forEach(p => p.style.display = (p.getAttribute('data-lang-panel') === 'cs') ? '' : 'none');

    modal.classList.add('is-open');
  }

  function closeModal() {
    const modal = $('#adminEditModal');
    if (modal) modal.classList.remove('is-open');
  }

  function openAddSection() {
    createAddModalIfMissing();
    const modal = $('#adminAddModal');
    const msg = $('.admin-form__msg', modal);
    msg.textContent = '';
    modal.classList.add('is-open');
  }

  function closeAddModal() {
    const modal = $('#adminAddModal');
    if (modal) modal.classList.remove('is-open');
  }

  async function uploadSelectedImage(file) {
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch(uploadUrl, {
      method: 'POST',
      headers: { 'X-CSRF-Token': window.ADMIN_CSRF_TOKEN || '' },
      body: fd
    });
    const data = await res.json().catch(() => null);
    if (!res.ok || !data || !data.ok) {
      throw new Error((data && data.error) ? data.error : 'Upload se nezdařil.');
    }
    return data.path;
  }

  async function saveForm() {
    const modal = $('#adminEditModal');
    const form = $('.admin-form', modal);
    const msg = $('.admin-form__msg', modal);

    msg.textContent = 'Ukládám…';

    let imgPath = null;
    const file = $('input[name="img_file"]', modal).files?.[0] || null;
    if (file) {
      try {
        msg.textContent = 'Nahrávám obrázek…';
        imgPath = await uploadSelectedImage(file);
      } catch (e) {
        msg.textContent = e.message || 'Upload se nezdařil.';
        return;
      }
    }

    const fields = {
      // bilingual payload
      title_cs: form.title_cs.value,
      p1_cs: form.p1_cs.value,
      p2_cs: form.p2_cs.value,
      title_en: form.title_en.value,
      p1_en: form.p1_en.value,
      p2_en: form.p2_en.value,

      img_alt: form.img_alt.value,
    };
    if (imgPath) fields.img_src = imgPath;

    const payload = {
      action: 'update',
      page: 'index',
      sectionId: form.sectionId.value,
      fields
    };

    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': window.ADMIN_CSRF_TOKEN || ''
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json().catch(() => null);
    if (!res.ok || !data || !data.ok) {
      msg.textContent = (data && data.error) ? data.error : 'Uložení se nezdařilo.';
      return;
    }

    msg.textContent = 'Uloženo. Obnovuji…';
    window.location.reload();
  }

  async function createSectionFromForm() {
    const modal = $('#adminAddModal');
    const form = $('.admin-form', modal);
    const msg = $('.admin-form__msg', modal);

    msg.textContent = 'Vytvářím…';

    const newSectionId = (form.new_section_id.value || '').trim();
    if (!/^[a-z0-9\-]{1,64}$/i.test(newSectionId)) {
      msg.textContent = 'Neplatné ID sekce (povoleno: písmena/čísla/pomlčka).';
      return;
    }

    let imgPath = null;
    const file = $('input[name="img_file"]', modal).files?.[0] || null;
    if (file) {
      try {
        msg.textContent = 'Nahrávám obrázek…';
        imgPath = await uploadSelectedImage(file);
      } catch (e) {
        msg.textContent = e.message || 'Upload se nezdařil.';
        return;
      }
    }

    const payload = {
      action: 'add',
      page: 'index',
      sectionId: newSectionId,
      position: form.position.value,
      fields: {
        title_cs: form.title_cs.value,
        p1_cs: form.p1_cs.value,
        p2_cs: form.p2_cs.value,
        title_en: form.title_en.value,
        p1_en: form.p1_en.value,
        p2_en: form.p2_en.value,
        img_src: imgPath || 'Images/o-nas.png',
        img_alt: form.img_alt.value,
      }
    };

    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': window.ADMIN_CSRF_TOKEN || ''
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json().catch(() => null);
    if (!res.ok || !data || !data.ok) {
      msg.textContent = (data && data.error) ? data.error : 'Vytvoření se nezdařilo.';
      return;
    }

    msg.textContent = 'Hotovo. Obnovuji…';
    window.location.reload();
  }

  async function deleteSection(section) {
    const sectionId = section.getAttribute('data-admin-section');
    if (!sectionId) return;

    if (!confirm('Opravdu chcete tuto sekci smazat?')) return;

    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': window.ADMIN_CSRF_TOKEN || ''
      },
      body: JSON.stringify({ action: 'delete', page: 'index', sectionId })
    });

    const data = await res.json().catch(() => null);
    if (!res.ok || !data || !data.ok) {
      alert((data && data.error) ? data.error : 'Smazání se nezdařilo.');
      return;
    }

    window.location.reload();
  }

  function injectStyles() {
    if ($('#adminEditorStyles')) return;
    const style = document.createElement('style');
    style.id = 'adminEditorStyles';
    style.textContent = `
      [data-admin-section] { position: relative; }
      .admin-section-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 8px;
        z-index: 50;
      }
      .admin-btn {
        font: inherit;
        font-size: 13px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(17,17,17,0.25);
        background: rgba(255,255,255,0.72);
        color: #111;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(0,0,0,0.08);
      }
      .admin-btn:hover { background: rgba(255,255,255,0.92); }
      .admin-btn-primary { background: rgba(255,255,255,0.92); }

      .admin-add-section-wrap {
        margin-top: 16px;
        display: flex;
        justify-content: center;
      }
      .admin-add-section-box {
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px dashed rgba(255,255,255,0.35);
        background: rgba(0,0,0,0.18);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
      }

      .admin-modal { display:none; }
      .admin-modal.is-open { display:block; }
      .admin-modal__backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,0.38); z-index: 10000;
      }
      .admin-modal__panel {
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: min(760px, calc(100% - 24px));
        background: rgba(255,255,255,0.92);
        color: #111;
        border: 1px solid rgba(0,0,0,0.10);
        border-radius: 18px;
        z-index: 10001;
        box-shadow: 0 20px 60px rgba(0,0,0,0.22);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
      }
      .admin-modal__head {
        display:flex; align-items:center; justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid rgba(0,0,0,0.08);
      }
      .admin-icon-btn {
        font: inherit;
        width: 36px; height: 36px;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.10);
        background: rgba(255,255,255,0.8);
        color: #111;
        cursor: pointer;
      }
      .admin-form { padding: 16px; display:grid; gap: 10px; }
      .admin-form label { font-size: 12px; opacity: 0.85; }
      .admin-form input, .admin-form textarea, .admin-form select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.15);
        background: rgba(255,255,255,0.85);
        color: #111;
        font: inherit;
        box-sizing: border-box;
      }
      .admin-form textarea { resize: vertical; }
      .admin-form__actions { display:flex; justify-content: flex-end; gap: 10px; margin-top: 4px; }
      .admin-form__msg { font-size: 13px; opacity: 0.9; min-height: 18px; }
      .admin-small { font-size: 12px; opacity: 0.75; margin-top: 6px; }
      .admin-grid2 { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start; }
      @media (max-width: 680px) { .admin-grid2 { grid-template-columns: 1fr; } }
      .admin-preview {
        width: 100%;
        min-height: 110px;
        border-radius: 14px;
        border: 1px dashed rgba(0,0,0,0.20);
        background: rgba(255,255,255,0.55);
        display:flex;
        align-items:center;
        justify-content:center;
        overflow: hidden;
      }
      .admin-preview img { display:block; max-width: 100%; max-height: 180px; }

      .admin-bar .admin-btn { box-shadow: none; }

      .admin-lang-tabs {
        display: flex;
        gap: 8px;
        padding: 10px 16px 0;
      }
      .admin-lang-tab {
        font: inherit;
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(0,0,0,0.12);
        background: rgba(255,255,255,0.6);
        cursor: pointer;
      }
      .admin-lang-tab.is-active {
        background: rgba(0,0,0,0.06);
        border-color: rgba(0,0,0,0.18);
      }
      .admin-lang-panel { padding-top: 6px; }
    `;
    document.head.appendChild(style);
  }

  function init() {
    injectStyles();
    createModalIfMissing();
    ensureAddSectionButton();

    const sections = $$('[data-admin-section]');
    sections.forEach(ensureEditorUI);

    // If JSON is empty on server, the public index.php still shows static fallback.
    // To start using JSON immediately, encourage creating at least one update/add.
  }

  document.addEventListener('DOMContentLoaded', init);
})();