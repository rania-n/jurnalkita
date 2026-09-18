/*
 * JS bersama jurnalkita.
 * Interaksi kecil yang berulang di banyak halaman. Dipicu lewat atribut data-*
 * sehingga markup Blade tetap bersih tanpa <script> inline.
 */

/* Toggle lihat/sembunyikan password + tukar ikon mata.
 * Pakai:  <button data-toggle-password="#password"> ...<x-icon name="visibility"/> </button> */
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.togglePassword);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            const icon = btn.querySelector('.material-symbols-rounded');
            if (icon) icon.textContent = show ? 'visibility_off' : 'visibility';
        });
    });
}

/* Pratinjau gambar setelah pilih file di <x-ui.upload>. */
function initUploadPreview() {
    document.querySelectorAll('[data-upload-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            const box = input.closest('label');
            if (!file || !box) return;
            const name = box.querySelector('span');
            if (name) name.textContent = file.name;
            box.classList.add('border-navy');
        });
    });
}

/* Konfirmasi sebelum aksi merusak (hapus). Pakai pada <form> atau <a>:
 *   <button data-confirm="Yakin hapus data ini?">Hapus</button>          (di dalam form)
 *   <a href="..." data-confirm="Yakin hapus?">Hapus</a>
 */
function initConfirm() {
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-confirm]');
        if (!el) return;
        if (!window.confirm(el.dataset.confirm)) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
}

/* Modal <dialog>.
 *   <button data-modal-open="id-modal">Tambah</button>
 *   <button data-modal-open="id-modal"
 *           data-modal-title="Ubah Kelas"
 *           data-modal-fill='{"nama":"X RPL 1","tingkat":"X"}'>Ubah</button>
 * Field diisi berdasarkan atribut name di dalam <dialog>.
 */
function initModals() {
    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-modal-open]');
        if (opener) {
            const dlg = document.getElementById(opener.dataset.modalOpen);
            if (!dlg) return;

            const titleEl = dlg.querySelector('[data-modal-title]');
            if (titleEl && opener.dataset.modalTitle) titleEl.textContent = opener.dataset.modalTitle;

            const form = dlg.querySelector('form');
            if (form) {
                form.reset();
                if (opener.dataset.modalFill) {
                    try {
                        const data = JSON.parse(opener.dataset.modalFill);
                        Object.entries(data).forEach(([k, v]) => {
                            const field = form.elements[k] || form.elements[k + '[]'];
                            if (!field) return;

                            if (typeof field.forEach === 'function' && !(field instanceof HTMLSelectElement)) {
                                // grup checkbox / radio
                                const arr = (Array.isArray(v) ? v : [v]).map(String);
                                field.forEach((el) => { el.checked = arr.includes(el.value); });
                            } else if (field.multiple) {
                                const arr = (Array.isArray(v) ? v : [v]).map(String);
                                Array.from(field.options).forEach((o) => { o.selected = arr.includes(o.value); });
                            } else {
                                field.value = v ?? '';
                            }
                        });
                    } catch (_) { /* abaikan */ }
                }
                // Kirim id record (kalau ada) untuk mode ubah.
                let idInput = form.querySelector('input[name="id"]');
                if (opener.dataset.modalId) {
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    idInput.value = opener.dataset.modalId;
                } else if (idInput) {
                    idInput.value = '';
                }
            }

            dlg.showModal();
            dlg.dispatchEvent(new CustomEvent('modal:open'));

            // Modal isi via AJAX (dipakai buat "lihat detail" tanpa pindah
            // halaman): <button data-modal-open="id" data-ajax-url="...">,
            // dialognya butuh satu <div data-modal-ajax-target> buat nampung
            // HTML fragment yang di-fetch.
            const ajaxTarget = dlg.querySelector('[data-modal-ajax-target]');
            if (ajaxTarget && opener.dataset.ajaxUrl) {
                ajaxTarget.innerHTML = '<p class="py-10 text-center text-sm text-muted-2">Memuat…</p>';
                fetch(opener.dataset.ajaxUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((r) => (r.ok ? r.text() : Promise.reject()))
                    .then((html) => { ajaxTarget.innerHTML = html; })
                    .catch(() => {
                        ajaxTarget.innerHTML = '<p class="py-10 text-center text-sm text-alpha">Gagal memuat detail. Coba lagi.</p>';
                    });
            }
            return;
        }

        if (e.target.closest('[data-modal-close]')) {
            e.target.closest('dialog')?.close();
        }
    });

    // Klik di area backdrop (di luar isi) -> tutup.
    document.querySelectorAll('dialog').forEach((dlg) => {
        dlg.addEventListener('click', (e) => {
            if (e.target === dlg) dlg.close();
        });
    });
}

/* Grup sidebar admin (<details data-nav-group="...">) diingat lewat
 * localStorage, biar grup yang sudah dibuka user TETAP kebuka pas pindah
 * halaman -- bukan cuma ngikut halaman aktif doang. */
function initNavGroups() {
    const groups = document.querySelectorAll('[data-nav-group]');
    if (!groups.length) return;

    let opened = [];
    try { opened = JSON.parse(localStorage.getItem('adminNavOpen') || '[]'); } catch (_) { /* abaikan */ }

    groups.forEach((el) => {
        if (opened.includes(el.dataset.navGroup)) el.open = true;

        el.addEventListener('toggle', () => {
            let list = [];
            try { list = JSON.parse(localStorage.getItem('adminNavOpen') || '[]'); } catch (_) { /* abaikan */ }
            list = list.filter((g) => g !== el.dataset.navGroup);
            if (el.open) list.push(el.dataset.navGroup);
            try { localStorage.setItem('adminNavOpen', JSON.stringify(list)); } catch (_) { /* abaikan */ }
        });
    });
}

/* Tabel admin (.responsive-table, lihat x-admin.table) jadi kartu bertumpuk
 * di HP lewat CSS (app.css) -- tapi CSS-nya butuh tahu nama kolom tiap sel
 * (data-label), jadi di sini label itu diambil otomatis dari <thead><th>
 * dan dipasang ke <td> yang sejajar. Nggak perlu ubah markup di tiap
 * halaman admin satu-satu. */
function initResponsiveTables() {
    document.querySelectorAll('table.responsive-table').forEach((table) => {
        const heads = [...table.querySelectorAll('thead th')].map((th) => th.textContent.trim());
        if (!heads.length) return;
        table.querySelectorAll('tbody tr').forEach((tr) => {
            [...tr.children].forEach((td, i) => {
                if (heads[i]) td.setAttribute('data-label', heads[i]);
            });
        });
    });
}

/* Jam berjalan di widget x-ui.jam-sekarang (dasbor) -- JP-nya dihitung server,
 * tapi jamnya sendiri di-tick tiap detik di client biar kelihatan "hidup". */
function initJamSekarang() {
    const els = document.querySelectorAll('[data-jam-sekarang]');
    if (!els.length) return;

    const tulis = () => {
        const teks = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        els.forEach((el) => { el.textContent = teks; });
    };

    tulis();
    setInterval(tulis, 1000);
}

function init() {
    initPasswordToggles();
    initUploadPreview();
    initConfirm();
    initModals();
    initNavGroups();
    initResponsiveTables();
    initJamSekarang();
}

document.addEventListener('DOMContentLoaded', init);
