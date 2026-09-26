<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\JadwalPiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class JadwalPiketController extends Controller
{
    /**
     * Piket asli gilirannya per TANGGAL SPESIFIK, ulang tiap 2 minggu --
     * bukan "tiap Senin selamanya". Dua alur beda di sini:
     *   - Ubah (ada 'id'): 1 baris doang, tanggal/jam/keterangan-nya diubah
     *     langsung -- field ulang_setiap_minggu/jumlah_kali DIABAIKAN sama
     *     sekali, ngedit 1 baris nggak boleh diam-diam nggandain baris baru.
     *   - Tambah (nggak ada 'id'): generate BANYAK baris sekaligus dari
     *     tanggal awal + tiap berapa minggu + berapa kali, biar admin nggak
     *     perlu isi manual satu-satu tiap 2 minggu.
     */
    public function save(Request $request): RedirectResponse
    {
        if ($request->filled('id')) {
            return $this->ubahSatuBaris($request);
        }

        return $this->tambahBanyakBaris($request);
    }

    private function ubahSatuBaris(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required', 'exists:jadwal_pikets,id'],
            'guru_id' => ['required', 'exists:gurus,id'],
            'tanggal' => ['required', 'date'],
            'mulai' => ['nullable', 'date_format:H:i'],
            'selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $tanggal = Carbon::parse($data['tanggal']);
        $hari = JadwalPiket::HARI_URUT[$tanggal->dayOfWeek - 1] ?? null;
        if (! $hari) {
            return back()->with('error', 'Tanggal itu jatuh di hari Sabtu/Minggu -- pilih tanggal hari sekolah (Senin-Jumat).')->withInput();
        }

        $sudahAda = JadwalPiket::where('guru_id', $data['guru_id'])
            ->whereDate('tanggal', $tanggal)
            ->where('mulai', $data['mulai'] ?? null)
            ->where('selesai', $data['selesai'] ?? null)
            ->whereKeyNot($data['id'])
            ->exists();
        if ($sudahAda) {
            return back()->with('error', 'Jadwal piket ini sudah ada -- guru, tanggal, dan jamnya sama persis seperti yang sudah tersimpan.')->withInput();
        }

        $piket = JadwalPiket::findOrFail($data['id']);
        $piket->fill([
            'guru_id' => $data['guru_id'],
            'hari' => $hari,
            'tanggal' => $tanggal->toDateString(),
            'mulai' => $data['mulai'] ?? null,
            'selesai' => $data['selesai'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ])->save();

        AuditLog::catat('Ubah Jadwal Piket', "Jadwal piket {$piket->guru->nama} — {$tanggal->translatedFormat('d M Y')}", $piket);

        return back()->with('success', 'Jadwal piket diperbarui.');
    }

    private function tambahBanyakBaris(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Bisa lebih dari 1 guru sekaligus -- kadang piket hari itu emang
            // digilir bareng beberapa guru, daripada isi form yang sama
            // berkali-kali satu-satu per guru.
            'guru_ids' => ['required', 'array', 'min:1'],
            'guru_ids.*' => ['exists:gurus,id'],
            'tanggal' => ['required', 'date'],
            'ulang_setiap_minggu' => ['required', 'integer', 'min:1', 'max:8'],
            'jumlah_kali' => ['required', 'integer', 'min:1', 'max:52'],
            'mulai' => ['nullable', 'date_format:H:i'],
            'selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $tanggalAwal = Carbon::parse($data['tanggal']);
        if (! in_array($tanggalAwal->dayOfWeek, [1, 2, 3, 4, 5], true)) {
            return back()->with('error', 'Tanggal itu jatuh di hari Sabtu/Minggu -- pilih tanggal hari sekolah (Senin-Jumat).')->withInput();
        }

        // Hitung dulu SEMUA tanggal targetnya, cek satu-satu apa udah ada
        // yang bentrok (guru+tanggal+jam sama persis) SEBELUM nyimpen apa
        // pun -- biar nggak nyetok separuh jalan terus baru ketauan gagal.
        $tanggalTarget = [];
        for ($i = 0; $i < $data['jumlah_kali']; $i++) {
            $tanggalTarget[] = $tanggalAwal->copy()->addWeeks($i * $data['ulang_setiap_minggu']);
        }

        // Dicek per-guru (bukan digabung) biar pesan errornya jelas nyebut
        // guru mana yang bentrok, bukan cuma tanggalnya doang.
        $pesanBentrok = [];
        foreach ($data['guru_ids'] as $guruId) {
            // whereDate() (BUKAN whereIn polos) -- kolom tanggal kesimpen sebagai
            // datetime lengkap ("2026-09-21 00:00:00") di sebagian driver DB
            // (mis. SQLite pas testing), jadi whereIn(['2026-09-21']) gagal
            // cocok walau tanggalnya beneran sama (perbandingan string mentah,
            // bukan tanggal). whereDate() ngebandingin cuma bagian tanggalnya.
            $bentrok = JadwalPiket::where('guru_id', $guruId)
                ->where('mulai', $data['mulai'] ?? null)
                ->where('selesai', $data['selesai'] ?? null)
                ->where(function ($q) use ($tanggalTarget) {
                    foreach ($tanggalTarget as $t) {
                        $q->orWhereDate('tanggal', $t);
                    }
                })
                ->pluck('tanggal');
            if ($bentrok->isNotEmpty()) {
                $nama = Guru::find($guruId)?->nama ?? "#{$guruId}";
                $daftar = $bentrok->map(fn ($t) => $t->translatedFormat('d M Y'))->implode(', ');
                $pesanBentrok[] = "{$nama} ({$daftar})";
            }
        }
        if (! empty($pesanBentrok)) {
            return back()->with('error', 'Sudah ada jadwal piket pada jam yang sama untuk: '.implode('; ', $pesanBentrok).'. Ubah tanggal mulai atau hapus terlebih dahulu jadwal yang bentrok.')->withInput();
        }

        $baris = [];
        foreach ($data['guru_ids'] as $guruId) {
            foreach ($tanggalTarget as $tanggal) {
                $baris[] = [
                    'guru_id' => $guruId,
                    'hari' => JadwalPiket::HARI_URUT[$tanggal->dayOfWeek - 1],
                    'tanggal' => $tanggal->toDateString(),
                    'mulai' => $data['mulai'] ?? null,
                    'selesai' => $data['selesai'] ?? null,
                    'keterangan' => $data['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        JadwalPiket::insert($baris);

        $jumlahGuru = count($data['guru_ids']);
        AuditLog::catat(
            'Tambah Jadwal Piket',
            "Jadwal piket {$jumlahGuru} guru — {$data['jumlah_kali']}x mulai {$tanggalAwal->translatedFormat('d M Y')}, tiap {$data['ulang_setiap_minggu']} minggu"
        );

        return back()->with('success', count($baris)." jadwal piket ditambahkan ({$jumlahGuru} guru × {$data['jumlah_kali']}x, mulai {$tanggalAwal->translatedFormat('d M Y')}).");
    }

    public function destroy(JadwalPiket $jadwalPiket): RedirectResponse
    {
        $jadwalPiket->delete();

        AuditLog::catat('Hapus Jadwal Piket', "Hapus jadwal piket #{$jadwalPiket->id}", $jadwalPiket);

        return back()->with('success', 'Jadwal piket dihapus.');
    }
}
