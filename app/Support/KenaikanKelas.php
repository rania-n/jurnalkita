<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

/**
 * Proses naik tahun ajaran: X naik ke XI, XI naik ke XII (kelas baru dibuat,
 * siswa aktif dipindah ke kelas barunya), XII lulus (siswa aktif ditandai lulus,
 * kelasnya tetap apa adanya — jadi arsip tahun lama, tetap bisa dilihat).
 *
 * Kelas & siswa tahun lama TIDAK diubah namanya/tingkatnya — supaya riwayat
 * jurnal/absensi/dispensasi tahun lalu tetap menunjuk ke kelas yang benar
 * secara historis (mis. jurnal tahun lalu tetap "milik" kelas X RPL 1, bukan
 * ikut berubah jadi XI RPL 1).
 */
class KenaikanKelas
{
    private const URUTAN_TINGKAT = ['X' => 'XI', 'XI' => 'XII'];

    /**
     * @return array{tahun_ajaran: TahunAjaran, kelas_naik: int, siswa_naik: int, siswa_lulus: int}
     */
    public static function jalankan(string $namaTahunBaru): array
    {
        return DB::transaction(function () use ($namaTahunBaru) {
            $kelasLama = Kelas::aktif()->get();

            TahunAjaran::where('aktif', true)->update(['aktif' => false]);
            $baru = TahunAjaran::create(['nama' => $namaTahunBaru, 'aktif' => true]);

            $kelasNaik = 0;
            $siswaNaik = 0;
            $siswaLulus = 0;

            foreach ($kelasLama as $kelas) {
                $tingkatBaru = self::URUTAN_TINGKAT[$kelas->tingkat] ?? null;

                if ($tingkatBaru === null) {
                    // XII -> lulus, tidak ada kelas baru dibuat.
                    $siswaLulus += Siswa::where('kelas_id', $kelas->id)->where('status', 'aktif')
                        ->update(['status' => 'lulus']);

                    continue;
                }

                $kelasBaru = Kelas::create([
                    'nama' => trim("{$tingkatBaru} {$kelas->jurusan} ".($kelas->nomor ?? '')),
                    'tingkat' => $tingkatBaru,
                    'jurusan' => $kelas->jurusan,
                    'nomor' => $kelas->nomor,
                    'wali_id' => $kelas->wali_id,
                    'tahun_ajaran_id' => $baru->id,
                ]);

                $siswaNaik += Siswa::where('kelas_id', $kelas->id)->where('status', 'aktif')
                    ->update(['kelas_id' => $kelasBaru->id]);

                $kelasNaik++;
            }

            return [
                'tahun_ajaran' => $baru,
                'kelas_naik' => $kelasNaik,
                'siswa_naik' => $siswaNaik,
                'siswa_lulus' => $siswaLulus,
            ];
        });
    }
}
