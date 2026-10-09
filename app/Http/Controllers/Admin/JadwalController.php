<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Support\Waktu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JadwalController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        if ($request->filled('ruang')) {
            $request->merge(['ruang' => preg_replace('/^r\s*(\d+)$/i', 'R $1', trim($request->input('ruang')))]);
        }

        $data = $request->validate([
            'id' => ['nullable', 'exists:jadwals,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mapels,id'],
            'guru_id' => ['required', 'exists:gurus,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:20'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:20', 'gte:jam_ke_mulai'],
            'ruang' => ['required', 'string', 'max:50', Rule::in(config('akademik.ruangan'))],
        ]);

        // Guru yang sedang piket tidak mengajar (jadwal piket berbasis shift jam,
        // bukan sehari penuh) — cegah admin double-booking guru yang sama.
        if ($pesan = $this->konflikPiket($data['guru_id'], $data['hari'], $data['jam_ke_mulai'], $data['jam_ke_selesai'])) {
            return back()->with('error', $pesan)->withInput();
        }

        // Satu guru fisiknya cuma bisa ngajar 1 kelas dalam satu waktu -- cegah
        // admin nambah jadwal yang jam-nya numpuk sama jadwal guru itu sendiri
        // di kelas lain, hari yang sama (edit -> jadwal dia sendiri dikecualikan).
        if ($pesan = $this->konflikJadwalGuru($data)) {
            return back()->with('error', $pesan)->withInput();
        }

        // Satu kelas fisiknya juga cuma bisa punya 1 mapel dalam satu waktu --
        // cegah 2 jadwal numpuk buat KELAS yang sama di jam yang sama (dulu
        // nggak dicek sama sekali -- cuma bentrok guru yang divalidasi).
        if ($pesan = $this->konflikJadwalKelas($data)) {
            return back()->with('error', $pesan)->withInput();
        }

        // Ruang kelas fisik cuma bisa dipakai 1 kelas dalam satu waktu
        if ($pesan = $this->konflikJadwalRuang($data)) {
            return back()->with('error', $pesan)->withInput();
        }

        $jadwal = $request->filled('id') ? Jadwal::findOrFail($data['id']) : new Jadwal;
        $baru = ! $jadwal->exists;

        $jadwal->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Jadwal' : 'Ubah Jadwal', "Jadwal {$jadwal->hari} kelas #{$jadwal->kelas_id}", $jadwal);

        return back()->with('success', $baru ? 'Jadwal ditambahkan.' : 'Jadwal diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        AuditLog::catat('Hapus Jadwal', "Hapus jadwal #{$jadwal->id}", $jadwal);

        return back()->with('success', 'Jadwal dihapus.');
    }

    /** Cek apakah jam jadwal ini numpuk sama jadwal MENGAJAR lain guru yang sama, hari yang sama. */
    private function konflikJadwalGuru(array $data): ?string
    {
        $bentrok = Jadwal::with('kelas')
            ->where('guru_id', $data['guru_id'])
            ->where('hari', $data['hari'])
            ->when($data['id'] ?? null, fn ($q, $id) => $q->whereKeyNot($id))
            ->where('jam_ke_mulai', '<=', $data['jam_ke_selesai'])
            ->where('jam_ke_selesai', '>=', $data['jam_ke_mulai'])
            ->first();

        if (! $bentrok) {
            return null;
        }

        return "Guru ini sudah memiliki jadwal lain pada jam yang sama hari {$data['hari']}: {$bentrok->kelas->nama} (JP {$bentrok->jam_ke_mulai}–{$bentrok->jam_ke_selesai}). Satu guru tidak dapat mengajar 2 kelas sekaligus — ubah jamnya atau pilih guru lain.";
    }

    /** Cek apakah jam jadwal ini numpuk sama jadwal MAPEL LAIN di kelas yang sama, hari yang sama. */
    private function konflikJadwalKelas(array $data): ?string
    {
        $bentrok = Jadwal::with('mapel', 'guru')
            ->where('kelas_id', $data['kelas_id'])
            ->where('hari', $data['hari'])
            ->when($data['id'] ?? null, fn ($q, $id) => $q->whereKeyNot($id))
            ->where('jam_ke_mulai', '<=', $data['jam_ke_selesai'])
            ->where('jam_ke_selesai', '>=', $data['jam_ke_mulai'])
            ->first();

        if (! $bentrok) {
            return null;
        }

        return "Kelas ini sudah memiliki jadwal lain pada jam yang sama hari {$data['hari']}: {$bentrok->mapel->nama} — {$bentrok->guru->nama} (JP {$bentrok->jam_ke_mulai}–{$bentrok->jam_ke_selesai}). Satu kelas tidak dapat memiliki 2 mata pelajaran sekaligus — ubah jamnya atau hapus/ubah jadwal yang lama terlebih dahulu.";
    }

    /** Cek apakah jam jadwal ini numpuk dengan jadwal lain yang menggunakan RUANG yang sama, hari yang sama. */
    private function konflikJadwalRuang(array $data): ?string
    {
        if (empty($data['ruang']) || $data['ruang'] === '-') {
            return null;
        }

        $ruangVarian = [$data['ruang']];
        if (preg_match('/^R\s*(\d+)$/', $data['ruang'], $matches)) {
            $ruangVarian[] = 'R' . $matches[1];
            $ruangVarian[] = 'R ' . $matches[1];
        }

        $bentrok = Jadwal::with('kelas')
            ->whereIn('ruang', $ruangVarian)
            ->where('hari', $data['hari'])
            ->when($data['id'] ?? null, fn ($q, $id) => $q->whereKeyNot($id))
            ->where('jam_ke_mulai', '<=', $data['jam_ke_selesai'])
            ->where('jam_ke_selesai', '>=', $data['jam_ke_mulai'])
            ->first();

        if (! $bentrok) {
            return null;
        }

        return "Ruang {$data['ruang']} sudah digunakan oleh kelas {$bentrok->kelas->nama} pada jam yang sama hari {$data['hari']} (JP {$bentrok->jam_ke_mulai}–{$bentrok->jam_ke_selesai}). Pilih ruang lain atau ubah jamnya.";
    }

    /** Cek apakah jam jadwal (jam ke-) bentrok dengan shift piket guru di hari yang sama. */
    private function konflikPiket(int $guruId, string $hari, int $jamMulai, int $jamSelesai): ?string
    {
        $kategori = Waktu::kategoriUntukHari($hari);

        $rentang = JamPelajaran::where('kategori', $kategori)
            ->whereBetween('jam_ke', [$jamMulai, $jamSelesai])
            ->selectRaw('min(mulai) as mulai, max(selesai) as selesai')
            ->first();

        $mulaiJadwal = $rentang?->getRawOriginal('mulai');
        $selesaiJadwal = $rentang?->getRawOriginal('selesai');
        if (! $mulaiJadwal || ! $selesaiJadwal) {
            return null; // jam pelajaran belum diatur admin -> tidak bisa dicek, jangan blokir
        }

        $bentrok = JadwalPiket::where('guru_id', $guruId)->where('hari', $hari)->get()
            ->first(function (JadwalPiket $p) use ($mulaiJadwal, $selesaiJadwal) {
                if (! $p->mulai || ! $p->selesai) {
                    return true; // piket tanpa jam spesifik -> dianggap sehari penuh
                }

                return $p->mulai->format('H:i:s') < $selesaiJadwal && $p->selesai->format('H:i:s') > $mulaiJadwal;
            });

        if (! $bentrok) {
            return null;
        }

        $jamPiket = $bentrok->mulai ? "jam {$bentrok->mulai->format('H:i')}–{$bentrok->selesai->format('H:i')}" : 'sehari penuh';

        return "Guru ini piket hari {$hari} ({$jamPiket}) — sesuai ketentuan, guru piket tidak mengajar saat bertugas. Pilih guru lain atau ganti jamnya.";
    }

    public function templateImport()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_jadwal_pelajaran.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Hari', 'Jam Mulai', 'Jam Selesai', 'Kelas', 'Mapel', 'Guru', 'Ruang']);
            fputcsv($file, ['Senin', '1', '2', 'X AK 1', 'Matematika', 'Budi Santoso', 'R1']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validateWithBag('import_jadwal', [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle);

        $sukses = 0;
        $gagal = 0;
        $baris = 1;

        $gurus = Guru::pluck('id', 'nama')->mapWithKeys(fn ($id, $nama) => [strtolower($nama) => $id])->toArray();
        $kelas = Kelas::pluck('id', 'nama')->mapWithKeys(fn ($id, $nama) => [strtolower($nama) => $id])->toArray();
        $mapels = Mapel::pluck('id', 'nama')->mapWithKeys(fn ($id, $nama) => [strtolower($nama) => $id])->toArray();

        $ruangTersedia = collect(config('akademik.ruangan'))->mapWithKeys(fn ($r) => [strtolower($r) => $r])->toArray();

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $baris++;
                if (count($row) < 6) {
                    $gagal++;

                    continue;
                }

                $hariInput = strtolower(trim($row[0]));
                $jamMulai = (int) trim($row[1]);
                $jamSelesai = (int) trim($row[2]);
                $namaKelas = strtolower(trim($row[3]));
                $namaMapel = strtolower(trim($row[4]));
                $namaGuru = strtolower(trim($row[5]));
                $namaRuang = isset($row[6]) ? strtolower(trim($row[6])) : '';

                if (! in_array($hariInput, ['senin', 'selasa', 'rabu', 'kamis', 'jumat']) || $jamMulai < 1 || $jamSelesai < 1 || $jamMulai > $jamSelesai) {
                    $gagal++;

                    continue;
                }

                if (! isset($gurus[$namaGuru]) || ! isset($kelas[$namaKelas]) || ! isset($mapels[$namaMapel])) {
                    $gagal++;

                    continue;
                }

                $ruang = '-';
                if ($namaRuang) {
                    $ruangNorm = preg_replace('/^r\s*(\d+)$/i', 'R$1', $namaRuang);
                    if (isset($ruangTersedia[strtolower($ruangNorm)])) {
                        $ruang = $ruangTersedia[strtolower($ruangNorm)];
                    }
                }

                $data = [
                    'kelas_id' => $kelas[$namaKelas],
                    'mapel_id' => $mapels[$namaMapel],
                    'guru_id' => $gurus[$namaGuru],
                    'hari' => $hariInput,
                    'jam_ke_mulai' => $jamMulai,
                    'jam_ke_selesai' => $jamSelesai,
                    'ruang' => $ruang,
                ];

                if ($this->konflikPiket($data['guru_id'], $data['hari'], $data['jam_ke_mulai'], $data['jam_ke_selesai']) ||
                    $this->konflikJadwalGuru($data) ||
                    $this->konflikJadwalKelas($data) ||
                    $this->konflikJadwalRuang($data)) {
                    $gagal++;

                    continue;
                }

                Jadwal::create($data);
                $sukses++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat memproses file CSV: '.$e->getMessage());
        }

        fclose($handle);

        AuditLog::catat('Import Jadwal', "Import jadwal pelajaran: {$sukses} berhasil, {$gagal} gagal/bentrok");

        $pesan = "Import selesai! {$sukses} jadwal berhasil ditambahkan.";
        if ($gagal > 0) {
            $pesan .= " {$gagal} baris dilewati karena format salah, data master tidak ditemukan, atau jadwal bentrok.";
        }

        return back()->with($gagal > 0 ? 'warning' : 'success', $pesan);
    }
}
