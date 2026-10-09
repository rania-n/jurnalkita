<?php

namespace App\Support;

use App\Models\HariKhusus;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Waktu
{
    /** Kategori jam pelajaran untuk sebuah tanggal. */
    public static function kategori(?Carbon $tanggal = null): string
    {
        $hari = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'][($tanggal ?? now())->dayOfWeek];

        return self::kategoriUntukHari($hari);
    }

    /** Kategori JP yang dipakai pada hari tertentu; mendukung kategori admin tambahan. */
    public static function kategoriUntukHari(string $hari): string
    {
        $hari = strtolower($hari);

        return self::$cacheKategoriHari[$hari] ??= (DB::table('jam_pelajaran_hari')
            ->where('hari', $hari)->value('kategori') ?? ($hari === 'jumat' ? 'jumat' : 'senin_kamis'));
    }

    /**
     * Jam ke- (JP) yang sedang berjalan sekarang berdasarkan konfigurasi jam pelajaran.
     * Kalau di luar semua rentang, pakai JP terakhir yang sudah dimulai, atau $default.
     */
    public static function jpSekarang(int $default = 1): int
    {
        $kategori = self::kategori();
        $sekarang = now()->format('H:i:s');

        return JamPelajaran::where('kategori', $kategori)
            ->where('mulai', '<=', $sekarang)->where('selesai', '>=', $sekarang)
            ->value('jam_ke')
            ?? JamPelajaran::where('kategori', $kategori)
                ->where('mulai', '<=', $sekarang)->orderByDesc('mulai')
                ->value('jam_ke')
            ?? $default;
    }

    /**
     * Jam ke- (JP) yang BENERAN sedang berjalan detik ini -- null kalau di luar
     * semua jam pelajaran (sebelum JP1 mulai, pas istirahat/setelah pulang, atau
     * tengah malam). Beda sama jpSekarang(): itu ada fallback (jam terakhir yang
     * sudah lewat, atau default) buat kebutuhan UI "tebakan awal", jadi SELALU
     * balikin angka walau lagi bukan jam sekolah sama sekali -- nggak cocok buat
     * ngambil keputusan "ini beneran jadwal yang lagi berlangsung", karena bisa
     * salah kunci ke jadwal yang sama sekali nggak relevan (mis. jam 1 pagi
     * kebaca seolah "JP1" gara-gara fallback-nya).
     */
    public static function jpAktifSekarang(): ?int
    {
        // Sabtu/Minggu bukan hari sekolah sama sekali (lihat HariSekolah) --
        // tanpa pengecekan ini, kategori() jatuh ke fallback 'senin_kamis'
        // (dibuat buat kasus lain: hari sekolah yang belum diberi kategori
        // eksplisit), jadi jam pelajaran Senin-Kamis ketebak "aktif" tiap
        // akhir pekan cuma karena jamnya kebetulan sama.
        if (! HariSekolah::hariIni()) {
            return null;
        }

        $batasPulang = self::batasPulangCepat();
        $sekarang = now()->format('H:i:s');
        if ($batasPulang && $sekarang >= $batasPulang) {
            return null;
        }

        return JamPelajaran::where('kategori', self::kategori())
            ->where('mulai', '<=', $sekarang)
            ->where('selesai', '>=', $sekarang)
            ->value('jam_ke');
    }

    /**
     * True kalau sekarang masih dalam rentang jam sekolah hari ini -- dari
     * mulainya JP pertama sampai selesainya JP terakhir (termasuk jam
     * istirahat di antaranya). Dipakai buat nentuin kapan guru BOLEH bebas
     * pilih jadwal lain buat isi jurnal (susulan/testing): cuma kalau
     * BENERAN di luar jam sekolah (belum masuk, atau udah pulang) -- bukan
     * pas istirahat, yang tetep dihitung "masih jam sekolah" walau nggak
     * ada JP yang aktif.
     */
    public static function dalamJamSekolah(): bool
    {
        // Sama alasannya kayak di jpAktifSekarang() -- Sabtu/Minggu memang
        // nggak pernah dianggap jam sekolah, terlepas dari jam berapa pun.
        if (! HariSekolah::hariIni()) {
            return false;
        }

        $kategori = self::kategori();
        $mulaiPertama = JamPelajaran::where('kategori', $kategori)->min('mulai');
        $selesaiTerakhir = JamPelajaran::where('kategori', $kategori)->max('selesai');

        if (! $mulaiPertama || ! $selesaiTerakhir) {
            return false;
        }

        $batasPulang = self::batasPulangCepat();
        if ($batasPulang) {
            $selesaiTerakhir = min($selesaiTerakhir, $batasPulang);
        }

        $sekarang = now()->format('H:i:s');

        return $sekarang >= $mulaiPertama && $sekarang <= $selesaiTerakhir;
    }

    /**
     * Status satu JP (atau rentang JP, mis. "JP 8-10") HARI INI dibanding
     * jam sekarang -- 'lewat' | 'berlangsung' | 'istirahat' | 'belum'. Null
     * kalau data jam pelajarannya nggak ketemu. Dipakai buat badge di daftar
     * jadwal hari ini.
     *
     * 'istirahat' khusus buat jadwal rentang beberapa JP (mis. JP 8-10) yang
     * di antara JP-JP penyusunnya ada jeda istirahat, dan detik ini pas lagi
     * di jeda itu -- BUKAN 'berlangsung' (nggak ada JP yang beneran aktif),
     * tapi juga bukan 'lewat'/'belum' (sebagian rentangnya udah/belum
     * kejalani). Match langsung sama jpAktifSekarang() (dipakai juga buat
     * ngunci/ngeblokir form Isi Jurnal), biar badge-nya nggak pernah bilang
     * "Berlangsung" padahal tombol "Isi Jurnal"-nya bakal keblokir.
     */
    public static function statusJpHariIni(int $jamKeMulai, ?int $jamKeSelesai = null): ?string
    {
        if (! HariSekolah::hariIni()) {
            return 'ditiadakan';
        }

        $jamKeSelesai ??= $jamKeMulai;
        $kategori = self::kategori();
        $awal = JamPelajaran::where('kategori', $kategori)->where('jam_ke', $jamKeMulai)->value('mulai');
        $akhir = JamPelajaran::where('kategori', $kategori)->where('jam_ke', $jamKeSelesai)->value('selesai');

        if (! $awal || ! $akhir) {
            return null;
        }

        $batasPulang = self::batasPulangCepat();
        if ($batasPulang && $awal->format('H:i:s') >= $batasPulang) {
            return 'ditiadakan';
        }
        if ($batasPulang && $akhir->format('H:i:s') > $batasPulang) {
            $akhir = $akhir->copy()->setTimeFromTimeString($batasPulang);
        }

        // $awal/$akhir kecast 'datetime:H:i' oleh model (Carbon, bukan string) --
        // format eksplisit dulu ke 'H:i' sebelum dibandingin, jangan langsung
        // dibandingin ke string now(), soalnya perbandingan objek-vs-string di
        // PHP nggak sama hasilnya kayak niatnya.
        $sekarang = now()->format('H:i');

        if ($sekarang < $awal->format('H:i')) {
            return 'belum';
        }
        if ($sekarang > $akhir->format('H:i')) {
            return 'lewat';
        }

        $jpAktif = self::jpAktifSekarang();

        return ($jpAktif !== null && $jamKeMulai <= $jpAktif && $jamKeSelesai >= $jpAktif) ? 'berlangsung' : 'istirahat';
    }

    /**
     * Cari jadwal BERIKUTNYA (yang belum dimulai) dari daftar jadwal hari ini,
     * buat disorot di dashboard. Jadwal yang lagi BENERAN berlangsung detik ini
     * sengaja TIDAK dipilih di sini -- itu sudah cukup terlihat & bisa diisi
     * langsung dari daftar jadwal biasa di bawah kartu sorotan, jadi kartu ini
     * dikhususkan buat "kasih tahu apa selanjutnya", bukan mengulang yang
     * sudah kelihatan. Null kalau $jadwals kosong atau semua jadwalnya sudah
     * berlangsung/lewat hari ini (dashboard cukup nggak nampilkan kartu
     * sorotan sama sekali).
     *
     * $jadwals harus sudah diurutkan ASCENDING berdasarkan jam_ke_mulai (semua
     * pemanggil sudah begitu -- query jadwal hari ini selalu orderBy jam_ke_mulai).
     */
    public static function jadwalSorotan(Collection $jadwals): ?array
    {
        foreach ($jadwals as $jadwal) {
            if (self::statusJpHariIni($jadwal->jam_ke_mulai, $jadwal->jam_ke_selesai) === 'belum') {
                return ['jadwal' => $jadwal, 'status' => 'berikutnya'];
            }
        }

        return null;
    }

    /**
     * Cache JamPelajaran per kategori + kategori-per-hari + HariKhusus.
     * Monitor Piket bisa nge-loop ribuan baris dalam satu request (rentang
     * tanggal panjang); tanpa cache ini tiap baris nembak beberapa query
     * JamPelajaran/HariKhusus yang isinya sama terus, dan itu yang bikin
     * halaman nge-hang. Di-cache per-request aja (static), aman karena
     * request PHP dibuang tiap selesai.
     *
     * @var array<string, Collection<int, JamPelajaran>>
     */
    private static array $cacheJamKategori = [];

    /** @var array<string, string> */
    private static array $cacheKategoriHari = [];

    /** @var array<string, ?HariKhusus> */
    private static array $cacheHariKhusus = [];

    /** Semua JamPelajaran satu kategori, keyed by jam_ke (sekali query per kategori). */
    private static function jamKategori(string $kategori): Collection
    {
        return self::$cacheJamKategori[$kategori] ??= JamPelajaran::where('kategori', $kategori)
            ->get(['jam_ke', 'mulai', 'selesai'])
            ->keyBy('jam_ke');
    }

    /** Jam mulai (hari ini, sebagai Carbon lengkap) buat JP tertentu. Null kalau JP-nya tidak ada. */
    public static function mulaiJpHariIni(int $jamKe): ?Carbon
    {
        $mulai = self::jamKategori(self::kategori())->get($jamKe)?->mulai;

        return $mulai ? now()->copy()->setTimeFromTimeString($mulai->format('H:i:s')) : null;
    }

    /**
     * Rentang jam beneran (mis. "07:00–08:30") buat satu JP atau rentang JP,
     * dipakai nemenin label "JP 1–2" di halaman jurnal biar keliatan jam
     * aslinya, bukan cuma nomor JP doang. Null kalau data jam pelajarannya
     * nggak ketemu (mis. kategori custom yang belum diatur).
     */
    public static function rentangJam(int $jamKeMulai, ?int $jamKeSelesai = null, ?Carbon $tanggal = null): ?string
    {
        return self::rentangJamDenganKategori(self::kategori($tanggal), $jamKeMulai, $jamKeSelesai);
    }

    /**
     * Sama kayak rentangJam(), tapi buat kasus yang cuma tau NAMA HARI
     * (mis. 'selasa') bukan tanggal konkret -- dipakai di form Isi Jurnal
     * pas milih jadwal dari dropdown, jadwalnya sendiri bisa dari hari
     * apa saja terlepas dari hari ini beneran hari apa.
     */
    public static function rentangJamUntukHari(string $hari, int $jamKeMulai, ?int $jamKeSelesai = null): ?string
    {
        return self::rentangJamDenganKategori(self::kategoriUntukHari($hari), $jamKeMulai, $jamKeSelesai);
    }

    /** true kalau tanggal itu termasuk hari khusus "tanpa_kbm" (cache per-request). */
    public static function tanpaKbm(Carbon $tanggal): bool
    {
        return self::hariKhusus($tanggal)?->jenis === 'tanpa_kbm';
    }

    /** true kalau jadwal ini gugur oleh kalender khusus pada tanggal target. */
    public static function jadwalDitiadakan(Jadwal $jadwal, Carbon $tanggal): bool
    {
        $hariKhusus = self::hariKhusus($tanggal);
        if (! $hariKhusus) {
            return false;
        }
        if ($hariKhusus->jenis === 'tanpa_kbm') {
            return true;
        }

        $mulai = self::jamKategori(self::kategoriUntukHari($jadwal->hari))->get($jadwal->jam_ke_mulai)?->mulai;

        return $mulai && $mulai->format('H:i:s') >= $hariKhusus->jam_selesai->format('H:i:s');
    }

    /** HariKhusus satu tanggal, di-cache per-request (lihat catatan cache di atas). */
    private static function hariKhusus(Carbon|string|null $tanggal = null): ?HariKhusus
    {
        $kunci = ($tanggal instanceof Carbon ? $tanggal->toDateString() : ($tanggal ?? today()->toDateString()));

        // array_key_exists, BUKAN ??: kebanyakan hari MEMANG nggak punya
        // HariKhusus (hasilnya null), dan ??= nggak nyimpen null -- kalau
        // dibiarin, tiap baris malah nembak query ulang terus (dulu kayak
        // gitu sampai ribuan query cuma buat tanggal yang sama).
        if (! array_key_exists($kunci, self::$cacheHariKhusus)) {
            self::$cacheHariKhusus[$kunci] = HariKhusus::untukTanggal($tanggal);
        }

        return self::$cacheHariKhusus[$kunci];
    }

    private static function batasPulangCepat(): ?string
    {
        $hariKhusus = self::hariKhusus(today());

        return $hariKhusus?->jenis === 'pulang_cepat'
            ? $hariKhusus->jam_selesai?->format('H:i:s')
            : null;
    }

    /** Jam mulai (format "H:i") satu JP tertentu pada hari tertentu. */
    public static function jamMulaiUntukHari(string $hari, int $jamKe): ?string
    {
        return self::jamKategori(self::kategoriUntukHari($hari))->get($jamKe)?->mulai?->format('H:i');
    }

    /** Jam selesai (format "H:i") satu JP tertentu pada hari tertentu. */
    public static function jamSelesaiUntukHari(string $hari, int $jamKe): ?string
    {
        return self::jamKategori(self::kategoriUntukHari($hari))->get($jamKe)?->selesai?->format('H:i');
    }

    private static function rentangJamDenganKategori(string $kategori, int $jamKeMulai, ?int $jamKeSelesai = null): ?string
    {
        $jam = self::jamKategori($kategori);
        $awal = $jam->get($jamKeMulai);
        $akhir = $jamKeSelesai && $jamKeSelesai !== $jamKeMulai ? $jam->get($jamKeSelesai) : $awal;

        if (! $awal || ! $akhir) {
            return null;
        }

        return $awal->mulai->format('H:i').'–'.$akhir->selesai->format('H:i');
    }
}
