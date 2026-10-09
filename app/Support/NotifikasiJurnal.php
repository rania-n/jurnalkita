<?php

namespace App\Support;

use App\Models\Jurnal;
use App\Models\User;
use App\Notifications\GuruTidakHadir;
use App\Notifications\GuruTidakHadirDiKelasAnda;

/**
 * Notifikasi in-app saat guru menandai dirinya tidak hadir (atau kelas
 * terpaksa diisi jurnal pengganti). Dipusatkan biar semua jalur -- simpan,
 * simpan-massal, ubah jurnal, sampai pengganti -- ngirim penerima yang sama:
 * Waka (oversight) + pengurus kelas + wali kelas.
 */
class NotifikasiJurnal
{
    /**
     * @param  int|null  $kecualiUserId  lewati user ini (mis. pengurus yang
     *                                   barusan ngisi jurnal pengganti sendiri,
     *                                   biar nggak dapat notifikasi aksinya).
     */
    public static function guruTidakHadir(Jurnal $jurnal, ?int $kecualiUserId = null): void
    {
        foreach (User::where('role', 'waka')->get() as $waka) {
            if ($kecualiUserId && $waka->id === $kecualiUserId) {
                continue;
            }
            $waka->notify(new GuruTidakHadir($jurnal));
        }

        $kelas = $jurnal->jadwal->kelas;
        $penerima = collect([$kelas?->pengurusUser(), $kelas?->wali?->user])
            ->filter()
            ->unique('id');

        foreach ($penerima as $user) {
            if ($kecualiUserId && $user->id === $kecualiUserId) {
                continue;
            }
            $user->notify(new GuruTidakHadirDiKelasAnda($jurnal));
        }
    }
}
