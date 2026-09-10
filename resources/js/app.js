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

function init() {
    initPasswordToggles();
    initSegmented();
    initUploadPreview();
    initConfirm();
}

document.addEventListener('DOMContentLoaded', init);
