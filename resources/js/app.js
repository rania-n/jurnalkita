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

/* <x-ui.upload-kamera> -- wajib jepret foto langsung dari kamera perangkat
 * (BUKAN pilih dari galeri/file), jalan di HP maupun desktop/laptop. Atribut
 * HTML "capture" cuma ngaruh di browser mobile, jadi di sini dibikin manual
 * pakai getUserMedia() + <video> live + jepret ke <canvas>, hasilnya
 * disuntikkan balik ke <input type=file> asli lewat DataTransfer -- validasi
 * & submit form-nya nggak berubah sama sekali, cuma CARA ngisi file-nya.
 */
function initKameraWajib() {
    document.querySelectorAll('[data-kamera-wrap]').forEach((wrap) => {
        const dialog = wrap.querySelector('[data-modal-open]')
            ? document.getElementById(wrap.querySelector('[data-modal-open]').dataset.modalOpen)
            : null;
        const video = wrap.querySelector('[data-kamera-video]');
        const canvas = wrap.querySelector('[data-kamera-canvas]');
        const pesanError = wrap.querySelector('[data-kamera-error]');
        const tombolJepret = wrap.querySelector('[data-kamera-jepret]');
        const tombolBuka = wrap.querySelectorAll('[data-kamera-buka]');
        const tombolUlang = wrap.querySelector('[data-kamera-ulang]');
        const tombolGanti = wrap.querySelector('[data-kamera-ganti]');
        const tombolPerbesar = wrap.querySelector('[data-kamera-perbesar]');
        const kotakVideo = wrap.querySelector('[data-kamera-box]');
        const input = wrap.querySelector('[data-kamera-input]');
        const img = wrap.querySelector('[data-kamera-img]');
        const placeholder = wrap.querySelector('[data-kamera-placeholder]');
        if (!dialog || !video || !canvas || !input) return;

        let stream = null;
        let diperbesar = false;
        // Default belakang ('environment') -- paling relevan buat foto suasana
        // kelas. Bisa ditukar manual lewat tombol data-kamera-ganti kalau
        // kamera yang kebuka bukan yang diinginkan.
        let facingMode = 'environment';

        async function bukaKamera() {
            pesanError.hidden = true;
            video.hidden = false;
            if (tombolGanti) tombolGanti.hidden = true;
            if (tombolPerbesar) tombolPerbesar.hidden = true;

            // Stream lama (kalau ada, mis. lagi ganti kamera) dimatiin dulu
            // sebelum minta yang baru -- sebagian browser/HP nolak buka kamera
            // kedua selama yang pertama masih aktif.
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }

            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode }, audio: false });
                video.srcObject = stream;
                await video.play();
                if (tombolGanti) tombolGanti.hidden = false;
                if (tombolPerbesar) tombolPerbesar.hidden = false;
            } catch (err) {
                video.hidden = true;
                pesanError.hidden = false;
            }
        }

        function tutupKamera() {
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
            video.srcObject = null;
            // Reset ukuran balik ke default tiap ditutup, biar buka lagi
            // nanti nggak kejebak kegedean dari sesi sebelumnya.
            if (diperbesar) {
                diperbesar = false;
                kotakVideo?.classList.remove('max-h-[85vh]');
                kotakVideo?.classList.add('max-h-[50vh]');
                video.classList.remove('max-h-[85vh]');
                video.classList.add('max-h-[50vh]');
            }
        }

        tombolBuka.forEach((btn) => btn.addEventListener('click', bukaKamera));
        dialog.addEventListener('close', tutupKamera);

        tombolGanti?.addEventListener('click', () => {
            facingMode = facingMode === 'environment' ? 'user' : 'environment';
            bukaKamera();
        });

        tombolPerbesar?.addEventListener('click', () => {
            diperbesar = !diperbesar;
            kotakVideo?.classList.toggle('max-h-[50vh]', !diperbesar);
            kotakVideo?.classList.toggle('max-h-[85vh]', diperbesar);
            video.classList.toggle('max-h-[50vh]', !diperbesar);
            video.classList.toggle('max-h-[85vh]', diperbesar);
        });

        tombolJepret.addEventListener('click', () => {
            if (!stream) return;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob((blob) => {
                if (!blob) return;
                const file = new File([blob], 'foto-suasana-kelas.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));

                img.src = URL.createObjectURL(blob);
                img.hidden = false;
                placeholder.hidden = true;
                tombolUlang.hidden = false;

                dialog.close();
            }, 'image/jpeg', 0.85);
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

/* Tulis HTML fragment (hasil fetch AJAX) ke dalam target, lalu jalanin ulang
 * <script> yang ikut kebawa -- browser SENGAJA nggak ngejalanin <script> yang
 * disisipin lewat innerHTML, jadi elemen script-nya diganti manual biar
 * kejalanin (dipakai buat popup "Lihat"/"Ubah Jurnal" yang isinya punya
 * interaksi sendiri, mis. toggle blok hadir/tidak-hadir). */
function setFragmentHtml(target, html) {
    target.innerHTML = html;
    target.querySelectorAll('script').forEach((lama) => {
        const baru = document.createElement('script');
        baru.textContent = lama.textContent;
        lama.replaceWith(baru);
    });
}

/* Modal <dialog>.
 *   <button data-modal-open="id-modal">Tambah</button>
 *   <button data-modal-open="id-modal"
 *           data-modal-title="Ubah Kelas"
 *           data-modal-fill='{"nama":"X RPL 1","tingkat":"X"}'>Ubah</button>
 * Field diisi berdasarkan atribut name di dalam <dialog>.
 *
 * Ganti isi popup yang UDAH kebuka (tanpa nutup/buka ulang dialognya -- biar
 * kelihatan masih popup yang sama, bukan popup baru numpuk di atasnya):
 *   <button type="button" data-modal-ajax-swap="/url/fragment/lain">Ubah</button>
 * Fragment barunya nimpa isi [data-modal-ajax-target] punya dialog yang sama.
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

                                // Field ini hidden input punya x-ui.cari-pilihan (dropdown
                                // yang bisa diketik) -- kotak teks yang KELIHATAN di
                                // sebelahnya nggak ikut ke-set otomatis cuma dari field.value
                                // di atas, jadi disamain manual di sini biar mode Ubah
                                // nampilin nama pilihannya (bukan kosong/placeholder).
                                if (field.hasAttribute('data-cari-pilihan-value')) {
                                    const wrap = field.closest('[data-cari-pilihan]');
                                    const daftar = JSON.parse(wrap?.dataset.list || '[]');
                                    const cocok = daftar.find((s) => String(s.id) === String(v));
                                    const visibleInput = wrap?.querySelector('[data-cari-pilihan-input]');
                                    if (visibleInput) visibleInput.value = cocok ? cocok.nama : '';
                                    const tombolClear = wrap?.querySelector('[data-cari-pilihan-clear]');
                                    if (tombolClear) tombolClear.hidden = !cocok;
                                }
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
                    .then((html) => setFragmentHtml(ajaxTarget, html))
                    .catch(() => {
                        ajaxTarget.innerHTML = '<p class="py-10 text-center text-sm text-alpha">Gagal memuat detail. Coba lagi.</p>';
                    });
            }
            return;
        }

        if (e.target.closest('[data-modal-close]')) {
            e.target.closest('dialog')?.close();
            return;
        }

        // Ganti isi popup yang lagi kebuka TANPA nutup/buka ulang -- dipakai
        // tombol "Ubah Jurnal" di dalam popup "Lihat" biar keliatan masih
        // popup yang sama, cuma isinya berubah jadi form.
        const swap = e.target.closest('[data-modal-ajax-swap]');
        if (swap) {
            const target = swap.closest('dialog')?.querySelector('[data-modal-ajax-target]');
            if (!target) return;
            target.innerHTML = '<p class="py-10 text-center text-sm text-muted-2">Memuat…</p>';
            fetch(swap.dataset.modalAjaxSwap, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => (r.ok ? r.text() : Promise.reject()))
                .then((html) => setFragmentHtml(target, html))
                .catch(() => {
                    target.innerHTML = '<p class="py-10 text-center text-sm text-alpha">Gagal memuat form ubah. Coba lagi.</p>';
                });
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

/*
 * Titik merah di lonceng notifikasi (topbar, ada di SEMUA halaman) di-cek
 * ulang berkala -- endpoint-nya cuma balikin angka (bukan render ulang HTML),
 * jadi ringan buat di-poll tiap 20 detik. Biar guru/sekre nggak ketinggalan
 * notifikasi baru (mis. jurnal disubmit guru lain, dispensasi diputuskan)
 * tanpa harus reload manual -- isi popupnya sendiri baru di-fetch pas
 * lonceng-nya beneran diklik (lihat initModals() + notifikasi/_daftar-fragment).
 * Berhenti kalau tab lagi disembunyikan (hemat request pas HP dikunci/pindah app).
 */
function initNotifikasiPoll() {
    const tombol = document.querySelector('[data-notif-jumlah-url]');
    const titik = document.querySelector('[data-notif-titik]');
    if (!tombol || !titik) return;

    const cek = () => {
        if (document.hidden) return;
        fetch(tombol.dataset.notifJumlahUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((r) => (r.ok ? r.json() : Promise.reject()))
            .then((data) => { titik.hidden = !(data.jumlah > 0); })
            .catch(() => {});
    };

    setInterval(cek, 20000);
}

/*
 * Banner "ada data baru" generik -- lihat components/ui/auto-refresh.blade.php
 * buat alasan kenapa nggak auto-reload sendiri. Satu halaman bisa pasang lebih
 * dari satu (jarang, tapi nggak masalah -- masing-masing independen).
 */
function initAutoRefresh() {
    document.querySelectorAll('[data-auto-refresh]').forEach((wrap) => {
        const url = wrap.dataset.autoRefreshUrl;
        const interval = parseInt(wrap.dataset.autoRefreshInterval || '20000', 10);
        const banner = wrap.querySelector('[data-auto-refresh-banner]');
        const tombol = wrap.querySelector('[data-auto-refresh-reload]');
        if (!url || !banner) return;

        let versiAwal = null;
        let timer = null;

        function cek() {
            if (document.hidden) return;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => (r.ok ? r.json() : Promise.reject()))
                .then((data) => {
                    if (versiAwal === null) { versiAwal = data.versi; return; }
                    if (data.versi !== versiAwal) {
                        banner.hidden = false;
                        clearInterval(timer); // udah ketauan beda, nggak perlu cek terus
                    }
                })
                .catch(() => {});
        }

        cek(); // baseline pertama, nggak langsung nampilin banner
        timer = setInterval(cek, interval);
        tombol?.addEventListener('click', () => location.reload());
    });
}

/*
 * Kotak "cari siswa" (ketik nama/NIS langsung, nggak perlu pilih kelas
 * dulu) -- lihat components/ui/cari-siswa.blade.php. Daftar siswa udah
 * di-embed di data-list (JSON), difilter di sini pas ngetik.
 */
function initCariSiswa() {
    document.querySelectorAll('[data-cari-siswa]').forEach((wrap) => {
        const data = JSON.parse(wrap.dataset.list || '[]');
        const input = wrap.querySelector('[data-cari-siswa-input]');
        const hidden = wrap.querySelector('[data-cari-siswa-value]');
        const hasil = wrap.querySelector('[data-cari-siswa-hasil]');
        const tombolClear = wrap.querySelector('[data-cari-siswa-clear]');
        if (!input || !hidden || !hasil) return;

        function render(list) {
            if (list.length === 0) {
                hasil.innerHTML = '<p class="px-3.5 py-2.5 text-sm text-muted-2">Tidak ada siswa yang cocok.</p>';
                return;
            }
            hasil.innerHTML = list.slice(0, 30).map((s) => `
                <button type="button" data-id="${s.id}" class="flex w-full flex-col gap-0.5 px-3.5 py-2.5 text-left hover:bg-surface-alt">
                    <span class="text-sm font-semibold text-ink">${s.nama}</span>
                    <span class="text-xs text-muted-2">${s.nis}${s.kelas ? ' · ' + s.kelas : ''}</span>
                </button>
            `).join('');
        }

        function pilih(s) {
            input.value = `${s.nama} · ${s.nis}`;
            hidden.value = s.id;
            hasil.hidden = true;
            if (tombolClear) tombolClear.hidden = false;
        }

        function kosongkan() {
            input.value = '';
            hidden.value = '';
            hasil.hidden = true;
            if (tombolClear) tombolClear.hidden = true;
            input.focus();
        }

        input.addEventListener('input', () => {
            hidden.value = ''; // udah ngetik lagi -> pilihan lama batal, harus pilih ulang
            if (tombolClear) tombolClear.hidden = true;

            const q = input.value.trim().toLowerCase();
            if (!q) { hasil.hidden = true; return; }

            const cocok = data.filter((s) => s.nama.toLowerCase().includes(q) || s.nis.includes(q));
            render(cocok);
            hasil.hidden = false;
        });

        input.addEventListener('focus', () => {
            if (input.value.trim() && !hidden.value) hasil.hidden = false;
        });

        hasil.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-id]');
            if (!btn) return;
            const s = data.find((x) => String(x.id) === btn.dataset.id);
            if (s) pilih(s);
        });

        tombolClear?.addEventListener('click', kosongkan);

        document.addEventListener('click', (e) => {
            if (!wrap.contains(e.target)) hasil.hidden = true;
        });
    });
}

/*
 * Versi generik dari initCariSiswa() -- buat dropdown APA AJA yang daftarnya
 * kepanjangan buat discroll (mapel, guru, kelas, dll), bukan cuma siswa.
 * Sengaja dipisah dari initCariSiswa() (bukan digabung/direfactor bareng)
 * biar nggak beresiko ngerusak alur cari-siswa yang udah jalan di beberapa
 * halaman -- lihat components/ui/cari-pilihan.blade.php.
 */
function initCariPilihan() {
    document.querySelectorAll('[data-cari-pilihan]').forEach((wrap) => {
        const data = JSON.parse(wrap.dataset.list || '[]');
        const input = wrap.querySelector('[data-cari-pilihan-input]');
        const hidden = wrap.querySelector('[data-cari-pilihan-value]');
        const hasil = wrap.querySelector('[data-cari-pilihan-hasil]');
        const daftar = wrap.querySelector('[data-cari-pilihan-daftar]');
        const tombolClear = wrap.querySelector('[data-cari-pilihan-clear]');
        if (!input || !hidden || !hasil || !daftar) return;

        function render(list) {
            if (list.length === 0) {
                daftar.innerHTML = '<p class="px-3.5 py-2.5 text-sm text-muted-2">Tidak ada yang cocok.</p>';
                return;
            }
            daftar.innerHTML = list.slice(0, 30).map((s) => `
                <button type="button" data-id="${s.id}" class="flex w-full px-3.5 py-2.5 text-left text-sm font-semibold text-ink hover:bg-surface-alt">${s.nama}</button>
            `).join('');
        }

        const autoSubmit = wrap.hasAttribute('data-auto-submit');

        function pilih(s) {
            input.value = s.nama;
            hidden.value = s.id;
            hasil.hidden = true;
            if (tombolClear) tombolClear.hidden = false;
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            if (autoSubmit) wrap.closest('form')?.requestSubmit();
        }

        function kosongkan() {
            input.value = '';
            hidden.value = '';
            hasil.hidden = true;
            if (tombolClear) tombolClear.hidden = true;
            if (autoSubmit) { wrap.closest('form')?.requestSubmit(); return; }
            input.focus();
        }

        input.addEventListener('input', () => {
            hidden.value = ''; // udah ngetik lagi -> pilihan lama batal, harus pilih ulang
            if (tombolClear) tombolClear.hidden = true;

            const q = input.value.trim().toLowerCase();
            const cocok = q ? data.filter((s) => s.nama.toLowerCase().includes(q)) : data;
            render(cocok);
            hasil.hidden = false;
        });

        input.addEventListener('focus', () => {
            if (!hidden.value) { render(data); hasil.hidden = false; }
        });

        daftar.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-id]');
            if (!btn) return;
            const s = data.find((x) => String(x.id) === btn.dataset.id);
            if (s) pilih(s);
        });

        tombolClear?.addEventListener('click', kosongkan);

        document.addEventListener('click', (e) => {
            if (!wrap.contains(e.target)) hasil.hidden = true;
        });
    });
}

/*
 * Multi-pilih yang bisa dicari (x-ui.cari-checkbox) -- ngetik nyaring baris
 * checkbox yang KELIHATAN (bukan dropdown terpisah kayak cari-pilihan),
 * soalnya di sini bisa milih lebih dari satu.
 */
function initCariCheckbox() {
    document.querySelectorAll('[data-cari-checkbox]').forEach((wrap) => {
        const input = wrap.querySelector('[data-cari-checkbox-input]');
        const rows = wrap.querySelectorAll('[data-cari-checkbox-row]');
        const kosong = wrap.querySelector('[data-cari-checkbox-kosong]');
        if (!input) return;

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            let ada = false;
            rows.forEach((row) => {
                const cocok = !q || row.dataset.nama.includes(q);
                row.hidden = !cocok;
                if (cocok) ada = true;
            });
            if (kosong) kosong.hidden = ada;
        });
    });
}

function init() {
    initPasswordToggles();
    initUploadPreview();
    initKameraWajib();
    initCariSiswa();
    initCariPilihan();
    initCariCheckbox();
    initConfirm();
    initModals();
    initNavGroups();
    initResponsiveTables();
    initJamSekarang();
    initNotifikasiPoll();
    initAutoRefresh();
}

document.addEventListener('DOMContentLoaded', init);
