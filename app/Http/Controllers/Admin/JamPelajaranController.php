<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JamPelajaranController extends Controller
{
    /**
     * Simpan ulang seluruh baris untuk satu kategori (editor multi-baris,
     * mode "Manual" di modal Jam Pelajaran). Kategori BOLEH nama baru (admin
     * ketik bebas, mis. "Ramadhan") atau kategori yang udah ada -- satu field
     * "kategori" doang, nggak ada lagi field kategori_baru terpisah (dulu
     * modal Edit & Kategori Baru itu 2 modal beda, sekarang udah digabung
     * jadi 1 modal Jam Pelajaran, jadi nggak perlu bedain lagi mana yang
     * "baru" mana yang "sudah ada" -- selalu di-upsert berdasarkan nama).
     */
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:50'],
            'mulai' => ['required', 'array', 'min:1'],
            'mulai.*' => ['required', 'date_format:H:i'],
            'selesai' => ['required', 'array'],
            'selesai.*' => ['required', 'date_format:H:i'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:100'],
            // Jeda per baris -- checkbox "Ada jeda sebelum baris ini" di view
            // ngirim jeda_menit[i] cuma kalau dicentang (lihat syncJeda() di
            // JS), jadi array-nya bisa "bolong" (nggak semua index keisi).
            'jeda_menit' => ['nullable', 'array'],
            'jeda_menit.*' => ['nullable', 'integer', 'min:1', 'max:180'],
            'jeda_label' => ['nullable', 'array'],
            'jeda_label.*' => ['nullable', 'string', 'max:40'],
        ]);

        // Slug rapi (huruf kecil, spasi jadi underscore) -- konsisten dipakai
        // balik sebagai key kategori, idempotent kalau kategorinya emang
        // udah slug (edit kategori yang sudah ada nggak berubah apa-apa).
        $data['kategori'] = Str::slug($data['kategori'], '_');

        $baris = [];
        foreach (array_values($data['mulai']) as $i => $mulai) {
            $jedaMenit = $data['jeda_menit'][$i] ?? null;
            $baris[] = [
                'jam_ke' => $i + 1,
                'mulai' => $mulai,
                'selesai' => $data['selesai'][$i] ?? $mulai,
                'keterangan' => $data['keterangan'][$i] ?? null,
                'jeda_sebelum_menit' => $jedaMenit,
                'jeda_label' => $jedaMenit ? (trim($data['jeda_label'][$i] ?? '') ?: 'Jeda') : null,
            ];
        }

        $this->gantiBaris($data['kategori'], $baris);

        AuditLog::catat('Ubah Jam Pelajaran', "Ubah jam pelajaran kategori {$data['kategori']}");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', 'Jam pelajaran disimpan.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:50'],
            'mulai' => ['required', 'date_format:H:i'],
            'durasi_jp' => ['required', 'integer', 'min:20', 'max:120'],
            'jumlah_jp' => ['required', 'integer', 'min:1', 'max:20'],
            'jeda' => ['sometimes', 'array', 'max:10'],
            'jeda.*.setelah' => ['required', 'integer', 'min:1', 'max:19', 'distinct'],
            'jeda.*.durasi' => ['required', 'integer', 'min:1', 'max:180'],
            'jeda.*.label' => ['nullable', 'string', 'max:40'],
        ]);

        // Slug rapi -- samain sama save(), biar kategori yang sama ditulis beda
        // kapital/spasi (mis. "Bulan Ramadhan" vs "bulan_ramadhan") tetap
        // dianggap kategori yang sama persis, nggak kebentuk 2 kategori beda.
        $data['kategori'] = Str::slug($data['kategori'], '_');

        // request()->validate() TIDAK nge-cast tipe -- 'integer' cuma ngecek
        // isinya angka, hasilnya tetap STRING kayak dari form. Carbon versi
        // ini nolak string di addMinutes() (dulu 500 error "Argument #3 must
        // be int|float, string given"), jadi di-cast eksplisit ke int di sini.
        // "?? []" di foreach-by-reference SENGAJA dihindari -- operator "??"
        // selalu ngasih hasil BY VALUE (salinan), bukan referensi ke array
        // aslinya, jadi &$ref di situ ngubah salinan doang, $data['jeda'] asli
        // nggak ikut ke-cast (kejadian beneran, ketauan dari test).
        $data['durasi_jp'] = (int) $data['durasi_jp'];
        $data['jumlah_jp'] = (int) $data['jumlah_jp'];
        if (! empty($data['jeda'])) {
            foreach ($data['jeda'] as &$slotJedaRef) {
                $slotJedaRef['durasi'] = (int) $slotJedaRef['durasi'];
                $slotJedaRef['setelah'] = (int) $slotJedaRef['setelah'];
            }
            unset($slotJedaRef);
        }

        foreach ($data['jeda'] ?? [] as $slotJeda) {
            if ($slotJeda['setelah'] >= $data['jumlah_jp']) {
                return back()->withErrors(['jeda' => 'Jeda harus ditempatkan di antara jam pelajaran, bukan setelah JP terakhir.'])->withInput();
            }
        }

        $jedaSetelah = collect($data['jeda'] ?? [])->keyBy('setelah');
        $waktu = Carbon::createFromFormat('!H:i', $data['mulai']);
        $baris = [];

        for ($jamKe = 1; $jamKe <= $data['jumlah_jp']; $jamKe++) {
            $mulai = $waktu->copy();
            $selesai = $mulai->copy()->addMinutes($data['durasi_jp']);
            if ($selesai->format('Y-m-d') !== $mulai->format('Y-m-d')) {
                return back()->withErrors(['mulai' => 'Rentang jam pelajaran melewati tengah malam. Kurangi jumlah JP atau durasi jeda.'])->withInput();
            }

            $jedaSebelum = $jedaSetelah->get($jamKe - 1);
            $labelJeda = trim($jedaSebelum['label'] ?? '');
            $baris[] = [
                'jam_ke' => $jamKe,
                'mulai' => $mulai->format('H:i'),
                'selesai' => $selesai->format('H:i'),
                'jeda_sebelum_menit' => $jedaSebelum['durasi'] ?? null,
                'jeda_label' => $jedaSebelum ? ($labelJeda !== '' ? $labelJeda : 'Jeda') : null,
            ];

            $waktu = $selesai;
            if ($jeda = $jedaSetelah->get($jamKe)) {
                $waktu->addMinutes($jeda['durasi']);
            }
        }

        $this->gantiBaris($data['kategori'], $baris);

        AuditLog::catat('Generate Jam Pelajaran', "Buat {$data['jumlah_jp']} JP kategori {$data['kategori']} (durasi {$data['durasi_jp']} menit).");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', "{$data['jumlah_jp']} jam pelajaran berhasil dibuat otomatis.");
    }

    public function maju(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:50', 'exists:jam_pelajarans,kategori'],
            'jumlah_jp' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $baris = JamPelajaran::where('kategori', $data['kategori'])->orderBy('jam_ke')->get();
        if ($baris->isEmpty()) {
            return back()->with('error', 'Kategori ini belum memiliki jam pelajaran untuk digeser.');
        }
        if ($baris->count() < $data['jumlah_jp']) {
            return back()->with('error', 'Jumlah JP yang dimajukan melebihi jumlah JP dalam kategori ini.');
        }

        $hariTerkait = DB::table('jam_pelajaran_hari')->where('kategori', $data['kategori'])->pluck('hari')->all();
        if ($hariTerkait === []) {
            return back()->with('error', 'Gunakan kategori ini untuk hari sekolah terlebih dahulu sebelum memajukan JP.');
        }

        $jadwals = Jadwal::whereIn('hari', $hariTerkait)->orderBy('id')->get();
        $jadwalTakBisaDimajukan = $jadwals->first(fn (Jadwal $jadwal) => $jadwal->jam_ke_mulai <= $data['jumlah_jp'] || $jadwal->jam_ke_selesai <= $data['jumlah_jp']
        );
        if ($jadwalTakBisaDimajukan) {
            return back()->with('error', "Jadwal {$jadwalTakBisaDimajukan->hari} JP {$jadwalTakBisaDimajukan->jam_ke_mulai}–{$jadwalTakBisaDimajukan->jam_ke_selesai} tidak bisa dimajukan {$data['jumlah_jp']} JP karena hasilnya akan kurang dari JP1.");
        }

        $jadwalSnapshot = $jadwals->map(fn (Jadwal $jadwal) => [
            'id' => $jadwal->id,
            'jam_ke_mulai' => $jadwal->jam_ke_mulai,
            'jam_ke_selesai' => $jadwal->jam_ke_selesai,
        ]);
        $jpBaru = $baris->slice($data['jumlah_jp'])->values();

        // Jam-nya (bukan cuma nomor JP-nya) ikut dipadetin maju, ngisi slot yang
        // kosong ditinggal JP yang dihapus -- durasi tiap baris TETAP sama
        // persis, cuma jam mulainya yang maju. KECUALI baris yang emang punya
        // jeda sebelumnya (jeda_sebelum_menit keisi) -- jam-nya SENGAJA dikunci
        // di posisi asli, nggak ikut dipadetin, karena istirahat terikat jam
        // beneran (mis. jam makan), bukan urutan pelajaran. Baris-baris
        // SETELAH jeda itu lanjut dipadetin lagi dari situ.
        $cursor = $jpBaru->isNotEmpty() ? $baris->first()->mulai->copy() : null;
        $jpBaruDenganJam = $jpBaru->map(function (JamPelajaran $jp) use (&$cursor) {
            $durasiMenit = $jp->mulai->diffInMinutes($jp->selesai);
            if ($jp->jeda_sebelum_menit) {
                $cursor = $jp->mulai->copy();
            }
            $mulaiBaru = $cursor->copy();
            $selesaiBaru = $mulaiBaru->copy()->addMinutes($durasiMenit);
            $cursor = $selesaiBaru->copy();

            return ['jp' => $jp, 'mulai' => $mulaiBaru->format('H:i'), 'selesai' => $selesaiBaru->format('H:i')];
        });

        DB::transaction(function () use ($baris, $jpBaruDenganJam, $jadwals, $jadwalSnapshot, $data) {
            $this->simpanSnapshot($data['kategori'], $baris, $jadwalSnapshot);
            JamPelajaran::where('kategori', $data['kategori'])->delete();
            foreach ($jpBaruDenganJam as $i => $baru) {
                JamPelajaran::create([
                    'kategori' => $data['kategori'],
                    'jam_ke' => $i + 1,
                    'mulai' => $baru['mulai'],
                    'selesai' => $baru['selesai'],
                    'keterangan' => $baru['jp']->keterangan,
                    'jeda_sebelum_menit' => $baru['jp']->jeda_sebelum_menit,
                    'jeda_label' => $baru['jp']->jeda_label,
                ]);
            }

            foreach ($jadwals as $jadwal) {
                $jadwal->update([
                    'jam_ke_mulai' => $jadwal->jam_ke_mulai - $data['jumlah_jp'],
                    'jam_ke_selesai' => $jadwal->jam_ke_selesai - $data['jumlah_jp'],
                ]);
            }
        });

        AuditLog::catat('Majukan Jam Pelajaran', "Majukan kategori {$data['kategori']} dan {$jadwals->count()} jadwal kelas sebanyak {$data['jumlah_jp']} JP.");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', "JP dan {$jadwals->count()} jadwal kelas dimajukan {$data['jumlah_jp']} JP -- jamnya ikut maju mengisi slot kosong (waktu istirahat tetap berada pada jam aslinya). Slot nomor terakhir kini tidak digunakan.");
    }

    public function resetSebelumnya(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:50', 'exists:jam_pelajarans,kategori'],
        ]);

        $snapshot = DB::table('jam_pelajaran_snapshots')->where('kategori', $data['kategori'])->latest('id')->first();
        if (! $snapshot) {
            return back()->with('error', 'Belum ada jadwal sebelumnya yang bisa dipulihkan.');
        }

        $baris = json_decode($snapshot->baris, true) ?: [];
        $jadwalSebelumnya = json_decode($snapshot->jadwals ?? '[]', true) ?: [];
        if ($baris === []) {
            return back()->with('error', 'Salinan jadwal sebelumnya kosong; tidak ada perubahan yang dilakukan.');
        }

        DB::transaction(function () use ($data, $snapshot, $baris, $jadwalSebelumnya) {
            JamPelajaran::where('kategori', $data['kategori'])->delete();
            foreach ($baris as $jp) {
                JamPelajaran::create([
                    'kategori' => $data['kategori'],
                    'jam_ke' => $jp['jam_ke'],
                    'mulai' => $jp['mulai'],
                    'selesai' => $jp['selesai'],
                    'keterangan' => $jp['keterangan'] ?? null,
                    'jeda_sebelum_menit' => $jp['jeda_sebelum_menit'] ?? null,
                    'jeda_label' => $jp['jeda_label'] ?? null,
                ]);
            }
            foreach ($jadwalSebelumnya as $jadwal) {
                DB::table('jadwals')->where('id', $jadwal['id'])->update([
                    'jam_ke_mulai' => $jadwal['jam_ke_mulai'],
                    'jam_ke_selesai' => $jadwal['jam_ke_selesai'],
                    'updated_at' => now(),
                ]);
            }
            DB::table('jam_pelajaran_snapshots')->where('id', $snapshot->id)->delete();
        });

        AuditLog::catat('Pulihkan Jam Pelajaran', "Pulihkan kategori {$data['kategori']} ke konfigurasi sebelumnya.");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', 'Jadwal sebelumnya berhasil dipulihkan.');
    }

    /**
     * Simpan hari sekolah mana aja yang pakai kategori JP ini. Dulu cuma bisa
     * pilih 1 dari 2 "kelompok" (Senin-Kamis / Jumat) yang dipaksa dari kode,
     * padahal tabel jam_pelajaran_hari aslinya udah per-hari -- sekarang admin
     * bebas centang kombinasi hari apa aja (termasuk cuma 1 hari, atau semua).
     * Hari yang DICENTANG dipindah ke kategori ini (walau sebelumnya kepake
     * kategori lain); hari yang sebelumnya kategori ini tapi nggak dicentang
     * lagi jadi bebas (dihapus dari tabel, bisa dipakai kategori lain).
     */
    public function simpanKategoriHari(Request $request): RedirectResponse
    {
        $kategoriValid = JamPelajaran::query()->select('kategori')->distinct()->pluck('kategori')->all();
        $data = $request->validate([
            'kategori' => ['required', 'string', Rule::in($kategoriValid)],
            'hari' => ['nullable', 'array'],
            'hari.*' => ['string', 'in:senin,selasa,rabu,kamis,jumat'],
            'set' => ['nullable', 'string', 'max:50'],
        ]);
        $hariDipilih = $data['hari'] ?? [];

        DB::transaction(function () use ($hariDipilih, $data) {
            DB::table('jam_pelajaran_hari')->where('kategori', $data['kategori'])->delete();
            foreach ($hariDipilih as $hari) {
                DB::table('jam_pelajaran_hari')->updateOrInsert(
                    ['hari' => $hari],
                    ['kategori' => $data['kategori'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        $pesan = $hariDipilih === []
            ? 'Kategori ini nggak dipakai hari manapun sekarang.'
            : 'Hari yang memakai kategori JP ini berhasil disimpan.';
        AuditLog::catat('Atur Kategori JP per Hari', "Kategori {$data['kategori']} sekarang dipakai hari: ".($hariDipilih ? implode(', ', $hariDipilih) : '(tidak ada)').'.');

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['set'] ?: $data['kategori']])
            ->with('success', $pesan);
    }

    /** @param array<int, array{jam_ke: int, mulai: string, selesai: string, keterangan: ?string, jeda_sebelum_menit?: ?int, jeda_label?: ?string}> $baris */
    private function gantiBaris(string $kategori, array $baris): void
    {
        DB::transaction(function () use ($kategori, $baris) {
            $this->simpanSnapshot($kategori, JamPelajaran::where('kategori', $kategori)->orderBy('jam_ke')->get());
            JamPelajaran::where('kategori', $kategori)->delete();

            foreach ($baris as $data) {
                JamPelajaran::create($data + ['kategori' => $kategori]);
            }
        });
    }

    private function simpanSnapshot(string $kategori, iterable $baris, ?iterable $jadwals = null): void
    {
        $data = collect($baris)->map(fn (JamPelajaran $jp) => [
            'jam_ke' => $jp->jam_ke,
            'mulai' => $jp->mulai->format('H:i:s'),
            'selesai' => $jp->selesai->format('H:i:s'),
            'keterangan' => $jp->keterangan,
            'jeda_sebelum_menit' => $jp->jeda_sebelum_menit,
            'jeda_label' => $jp->jeda_label,
        ])->values()->all();

        if ($data !== []) {
            DB::table('jam_pelajaran_snapshots')->insert([
                'kategori' => $kategori,
                'baris' => json_encode($data, JSON_THROW_ON_ERROR),
                'jadwals' => $jadwals ? json_encode(collect($jadwals)->values()->all(), JSON_THROW_ON_ERROR) : null,
                'created_at' => now(),
            ]);
        }
    }

    /** Hapus 1 kategori beserta semua jamnya (dipakai buat kategori custom yang salah bikin). */
    public function destroyKategori(string $kategori): RedirectResponse
    {
        // Jangan hapus kalender JP yang masih dipakai oleh pemetaan hari.
        $hariTerkait = DB::table('jam_pelajaran_hari')->where('kategori', $kategori)->pluck('hari')->all();

        if (! empty($hariTerkait)) {
            $jumlahJadwal = Jadwal::whereIn('hari', $hariTerkait)->count();
            if ($jumlahJadwal > 0) {
                return back()->with('error', "Kategori ini masih digunakan oleh {$jumlahJadwal} jadwal pelajaran (hari ".implode(', ', $hariTerkait).'). Hapus atau pindahkan terlebih dahulu jadwalnya sebelum menghapus kategori jam ini.');
            }
        }

        JamPelajaran::where('kategori', $kategori)->delete();

        AuditLog::catat('Hapus Kategori Jam Pelajaran', "Hapus kategori {$kategori}");

        return redirect()->route('master.jam-pelajaran.index')->with('success', 'Kategori dihapus.');
    }
}
