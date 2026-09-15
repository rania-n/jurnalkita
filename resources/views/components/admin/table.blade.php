@props(['head' => [], 'dense' => false])

{{--
    <x-admin.table :head="['Nama', 'Kelas', '']">
        @forelse ($rows as $r) <tr>...</tr> @empty ... @endforelse
    </x-admin.table>
--}}
{{--
    responsive-table: di HP (< 640px) baris tabel otomatis jadi kartu bertumpuk
    (label per sel diambil dari header lewat initResponsiveTables() di app.js),
    bukan geser ke samping kayak sebelumnya (min-w-[36rem] dulu selalu lebih
    lebar dari layar HP). Lihat .responsive-table di resources/css/app.css.

    dense: buat tabel yang isinya banyak kolom pendek/angka (rekap kehadiran
    dkk) -- satu-kolom-per-baris jadi kepanjangan & boros scroll kalau
    kolomnya 7-9. Dengan dense, di HP field-nya disusun grid 3 kolom, bukan
    ditumpuk satu-satu. Cuma dipakai kalau kontennya memang pendek semua
    (angka/kata singkat) -- kalau ada kolom teks panjang, JANGAN dipakai.
--}}
<div class="overflow-x-auto rounded-xl border border-surface-alt bg-card">
    <table @class(['responsive-table w-full text-left text-sm', 'responsive-table--dense' => $dense])>
        @if (! empty($head))
            <thead>
                <tr class="border-b border-surface-alt">
                    @foreach ($head as $col)
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-muted-2">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-surface-alt">
            {{ $slot }}
        </tbody>
    </table>
</div>
