@props(['name' => 'cari', 'placeholder' => 'Cari...', 'server' => false])

<div class="flex h-10 min-w-[14rem] flex-1 items-center gap-2 rounded-lg border border-surface-alt bg-card px-3">
    <x-icon name="search" :size="16" class="shrink-0 text-muted" />
    <input
        type="search"
        name="{{ $name }}"
        value="{{ request()->query($name) }}"
        placeholder="{{ $placeholder }}"
        data-admin-search
        @if ($server) data-server-search @endif
        autocomplete="off"
        class="w-full border-none bg-transparent text-sm text-ink outline-none placeholder:text-muted [&::-webkit-search-cancel-button]:hidden"
    >
    {{-- Cuma ikon X polos, BUKAN dilabeli "Reset" -- "Reset" dipakai khusus
         buat x-admin.filters (reset dropdown/tanggal), biar nggak keliatan
         dobel kalau dua-duanya nongol di halaman yang sama. --}}
    <button
        type="button"
        data-admin-search-clear
        class="{{ request()->query($name) ? '' : 'hidden' }} flex shrink-0 items-center text-muted hover:text-alpha"
        aria-label="Hapus pencarian"
    >
        <x-icon name="close" :size="16" class="shrink-0" />
    </button>
</div>

@pushOnce('scripts')
<script>
    (function () {
        function filterAdminRows(input) {
            const wrapper = input.closest('div');
            const clearBtn = wrapper ? wrapper.querySelector('[data-admin-search-clear]') : null;
            if (clearBtn) {
                clearBtn.classList.toggle('hidden', !input.value);
            }

            const q = input.value.trim().toLowerCase();

            // 1. Filter baris tabel (responsif / standar admin)
            const table = document.querySelector('table.responsive-table') || document.querySelector('table');
            if (table) {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    const rows = tbody.querySelectorAll('tr:not([data-empty-row])');
                    let matchCount = 0;

                    rows.forEach((row) => {
                        const text = row.textContent.toLowerCase();
                        const match = !q || text.includes(q);
                        row.hidden = !match;
                        if (match) matchCount++;
                    });

                    let emptyRow = tbody.querySelector('[data-empty-row]');
                    if (matchCount === 0 && q && rows.length > 0) {
                        if (!emptyRow) {
                            emptyRow = document.createElement('tr');
                            emptyRow.setAttribute('data-empty-row', 'true');
                            emptyRow.innerHTML = '<td colspan="100%" class="px-4 py-8 text-center text-sm text-muted-2">Tidak ada data yang cocok dengan pencarian.</td>';
                            tbody.appendChild(emptyRow);
                        }
                        emptyRow.hidden = false;
                    } else if (emptyRow) {
                        emptyRow.hidden = true;
                    }
                }
            }

            // 2. Filter kartu (jika ada tampilan berbasis kartu)
            const cards = document.querySelectorAll('[data-cari], [data-dispen-card]');
            if (cards.length > 0) {
                let cardVisible = 0;
                cards.forEach((card) => {
                    const text = (card.dataset.cari || card.textContent).toLowerCase();
                    const match = !q || text.includes(q);
                    card.hidden = !match;
                    if (match) cardVisible++;
                });

                const cardEmpty = document.getElementById('admin-kosong') || document.getElementById('dispen-kosong');
                if (cardEmpty) {
                    cardEmpty.hidden = cardVisible > 0;
                }
            }
        }

        // --- Server-side search: AJAX fetch dengan debounce ---
        // Dipakai di halaman yang datanya dipaginasi (siswa, audit-log, dll.)
        // supaya pencarian menjangkau SEMUA data, bukan cuma halaman aktif.
        // Pakai fetch() + debounce 300ms supaya terasa instan tanpa full page
        // reload -- hanya bagian [data-server-search-target] yang diganti di DOM.
        // Kalau target tidak ditemukan, fallback ke form submit biasa.
        const serverSearchTimers = new WeakMap();

        async function fetchServerSearch(input) {
            const form = input.closest('form');
            if (!form) {
                return;
            }

            const target = document.querySelector('[data-server-search-target]');
            if (!target) {
                // Fallback: form submit penuh
                const pageInput = form.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.remove();
                }
                form.submit();
                return;
            }

            // Bangun URL dari isi form, tanpa parameter page
            const params = new URLSearchParams();
            for (const [key, val] of new FormData(form).entries()) {
                if (key !== 'page' && val !== '') {
                    params.set(key, val);
                }
            }
            const url = new URL(form.action);
            url.search = params.toString();

            target.style.opacity = '0.5';
            target.style.pointerEvents = 'none';

            try {
                const res = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.querySelector('[data-server-search-target]');
                if (fresh) {
                    target.innerHTML = fresh.innerHTML;
                    history.replaceState({}, '', url.pathname + (url.search || ''));
                }
            } catch (_) {
                // Jaringan error -- fallback ke form submit
                form.submit();
            } finally {
                target.style.opacity = '';
                target.style.pointerEvents = '';
            }
        }

        document.addEventListener('input', function (e) {
            const input = e.target.closest('[data-admin-search]');
            if (!input) {
                return;
            }

            if (input.dataset.serverSearch !== undefined) {
                const wrapper = input.closest('div');
                const clearBtn = wrapper ? wrapper.querySelector('[data-admin-search-clear]') : null;
                if (clearBtn) {
                    clearBtn.classList.toggle('hidden', !input.value);
                }

                if (serverSearchTimers.has(input)) {
                    clearTimeout(serverSearchTimers.get(input));
                }
                serverSearchTimers.set(input, setTimeout(() => fetchServerSearch(input), 300));
            } else {
                filterAdminRows(input);
            }
        });

        document.addEventListener('click', function (e) {
            const clearBtn = e.target.closest('[data-admin-search-clear]');
            if (!clearBtn) {
                return;
            }

            const wrapper = clearBtn.closest('div');
            const input = wrapper ? wrapper.querySelector('[data-admin-search]') : null;
            if (!input) {
                return;
            }

            input.value = '';

            if (input.dataset.serverSearch !== undefined) {
                fetchServerSearch(input);
                input.focus();
            } else {
                filterAdminRows(input);
                input.focus();

                try {
                    const url = new URL(window.location.href);
                    const name = input.name || 'cari';
                    if (url.searchParams.has(name)) {
                        url.searchParams.delete(name);
                        window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
                    }
                } catch (_) {}
            }
        });

        function init() {
            const inputs = document.querySelectorAll('[data-admin-search]');
            inputs.forEach((input) => {
                if (input.value && input.dataset.serverSearch === undefined) {
                    filterAdminRows(input);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
@endPushOnce
