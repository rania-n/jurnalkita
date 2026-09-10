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

/* Segmented control / tabs tombol (pilih satu).
 * <div data-segmented>
 *   <button data-segment value="hadir" aria-pressed="true">Hadir</button>
 *   <input type="hidden" name="status" data-segment-value>
 * </div>
 */
function initSegmented() {
    document.querySelectorAll('[data-segmented]').forEach((group) => {
        const buttons = group.querySelectorAll('[data-segment]');
        const hidden = group.querySelector('[data-segment-value]');
        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                buttons.forEach((b) => b.setAttribute('aria-pressed', 'false'));
                btn.setAttribute('aria-pressed', 'true');
                if (hidden) hidden.value = btn.value;
                group.dispatchEvent(new CustomEvent('segment:change', { detail: { value: btn.value } }));
            });
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

function init() {
    initPasswordToggles();
    initSegmented();
    initUploadPreview();
    initConfirm();
    initModals();
}

document.addEventListener('DOMContentLoaded', init);
