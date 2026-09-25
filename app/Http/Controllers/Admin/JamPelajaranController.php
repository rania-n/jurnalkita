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
    /** Simpan ulang seluruh baris untuk satu kategori (editor multi-baris). */
    public function save(Request $request): RedirectResponse
    {
        // Bag beda tergantung modal yang disubmit (dibedain dari ada/nggaknya
        // kategori_baru) -- 2 modal (Edit & Kategori Baru) share route ini, kalau
        // pakai bag default gagal validasi di satu modal ikut mbukain modal lain.
        $bag = $request->filled('kategori_baru') ? 'jpBaru' : 'jpEdit';

        $data = $request->validateWithBag($bag, [
            // Bukan enum lagi -- boleh kategori baru (mis. "Ramadhan", "Ujian"), bukan
            // cuma senin_kamis/jumat/khusus bawaan. Salah satu wajib: kategori (pilih yang
            // sudah ada) atau kategori_baru (ketik nama baru, lihat modal "Kategori Baru").
            'kategori' => ['required_without:kategori_baru', 'nullable', 'string', 'max:50'],
            'kategori_baru' => ['required_without:kategori', 'nullable', 'string', 'max:50'],
            'mulai' => ['required', 'array', 'min:1'],
            'mulai.*' => ['required', 'date_format:H:i'],
            'selesai' => ['required', 'array'],
            'selesai.*' => ['required', 'date_format:H:i'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:100'],
        ]);

        // Kategori baru ditulis manual admin (mis. "Ramadhan") -> disimpan sebagai slug
        // rapi (huruf kecil, spasi jadi underscore) supaya konsisten dipakai balik.
        if ($request->filled('kategori_baru')) {
            $data['kategori'] = Str::slug($request->string('kategori_baru'), '_');
        }

        $baris = [];
        foreach (array_values($data['mulai']) as $i => $mulai) {
            $baris[] = [
                'jam_ke' => $i + 1,
                'mulai' => $mulai,
                'selesai' => $data['selesai'][$i] ?? $mulai,
                'keterangan' => $data['keterangan'][$i] ?? null,
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
                'keterangan' => $jedaSebelum
                    ? ($labelJeda !== '' ? $labelJeda : 'Jeda').' ('.$jedaSebelum['durasi'].' menit)'
                    : null,
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

        DB::transaction(function () use ($baris, $jpBaru, $jadwals, $jadwalSnapshot, $data) {
            $this->simpanSnapshot($data['kategori'], $baris, $jadwalSnapshot);
            JamPelajaran::where('kategori', $data['kategori'])->delete();
            foreach ($jpBaru as $jp) {
                JamPelajaran::create([
                    'kategori' => $data['kategori'],
                    'jam_ke' => $jp->jam_ke - $data['jumlah_jp'],
                    'mulai' => $jp->mulai,
                    'selesai' => $jp->selesai,
                    'keterangan' => $jp->keterangan,
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
            ->with('success', "JP dan {$jadwals->count()} jadwal kelas dimajukan {$data['jumlah_jp']} JP. Slot terakhir kini tidak digunakan.");
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

    public function simpanKategoriHari(Request $request): RedirectResponse
    {
        $kategoriValid = JamPelajaran::query()->select('kategori')->distinct()->pluck('kategori')->all();
        $data = $request->validate([
            'kategori' => ['required', 'string', Rule::in($kategoriValid)],
            'kelompok_hari' => ['required', 'in:senin_kamis,jumat'],
            'set' => ['nullable', 'string', 'max:50'],
        ]);

        $hariUntukKelompok = $data['kelompok_hari'] === 'jumat'
            ? ['jumat']
            : ['senin', 'selasa', 'rabu', 'kamis'];

        DB::transaction(function () use ($hariUntukKelompok, $data) {
            foreach ($hariUntukKelompok as $hari) {
                DB::table('jam_pelajaran_hari')->updateOrInsert(
                    ['hari' => $hari],
                    ['kategori' => $data['kategori'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        AuditLog::catat('Atur Kategori JP per Hari', "Gunakan kategori {$data['kategori']} untuk kelompok {$data['kelompok_hari']}.");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['set'] ?: $data['kategori']])
            ->with('success', 'Kategori JP berhasil digunakan untuk kelompok hari tersebut.');
    }

    /** @param array<int, array{jam_ke: int, mulai: string, selesai: string, keterangan: ?string}> $baris */
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
                return back()->with('error', "Kategori ini masih dipakai {$jumlahJadwal} jadwal pelajaran (hari ".implode(', ', $hariTerkait).'). Hapus/pindahkan dulu jadwalnya sebelum menghapus kategori jam ini.');
            }
        }

        JamPelajaran::where('kategori', $kategori)->delete();

        AuditLog::catat('Hapus Kategori Jam Pelajaran', "Hapus kategori {$kategori}");

        return redirect()->route('master.jam-pelajaran.index')->with('success', 'Kategori dihapus.');
    }
}
