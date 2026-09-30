<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\PresensiPiket;
use App\Models\Siswa;
use App\Support\HariSekolah;
use App\Support\RekapKehadiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    private function kelas(): Kelas
    {
        return auth()->user()->kelasSekretaris() ?? abort(403, 'Akun tidak terhubung ke kelas.');
    }

    /** V1: daftar siswa sekelas + kehadiran hari ini (dari jurnal yang udah diisi). */
    public function siswa(): View
    {
        $kelas = $this->kelas();
        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        // Kehadiran TERAKHIR hari ini per siswa -- 1 siswa bisa punya
        // beberapa baris Absensi hari ini (beberapa jurnal/JP), diambil yang
        // paling baru diisi (kejadian paling relevan "sekarang" buat siswa
        // itu, mis. abis dispensasi keluar terus balik lagi jam berikutnya).
        $absensiHariIni = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereHas('jurnal', fn ($q) => $q->whereDate('tanggal', today()))
            ->with('jurnal')
            ->get()
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->sortBy('jurnal.jam_ke_mulai')->last());

        $presensiPiketHariIni = PresensiPiket::whereIn('siswa_id', $siswas->pluck('id'))
            ->whereDate('tanggal', today())
            ->get()
            ->keyBy('siswa_id');

        return view('sekretaris.siswa', compact('kelas', 'siswas', 'absensiHariIni', 'presensiPiketHariIni'));
    }

    /** V2: jadwal pelajaran kelas, seminggu, dikelompokkan per hari -- hari ini ditandai & dikasih status jurnal (sama pola kayak Guru\JadwalController). */
    public function jadwal(Request $request): View
    {
        $kelas = $this->kelas();
        $hari = $request->query('hari', 'semua');

        $jadwalPerHari = $kelas->jadwals()
            ->with('mapel', 'guru')
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        if ($hari !== 'semua') {
            // filter(), bukan only() -- Eloquent\Collection::only() ngasumsiin
            // itemnya Model (manggil getKey()), meledak di atas hasil groupBy()
            // yang itemnya sub-Collection. Lihat Guru\JadwalController.
            $jadwalPerHari = $jadwalPerHari->filter(fn ($v, $k) => $k === $hari);
        }

        $hariIni = HariSekolah::hariIni();

        // Jurnal HARI INI per jadwal_id -- biar tiap baris di hari ini bisa
        // nampilin status "Belum Diisi"/"Hadir"/"Tidak Hadir" (sama kayak di
        // dashboard), bukan cuma daftar jam kosong.
        $jurnalHariIni = $hariIni && ($jadwalPerHari->has($hariIni))
            ? Jurnal::whereIn('jadwal_id', $jadwalPerHari[$hariIni]->pluck('id'))->whereDate('tanggal', today())->get()->keyBy('jadwal_id')
            : collect();

        return view('sekretaris.jadwal', compact('kelas', 'jadwalPerHari', 'hari', 'hariIni', 'jurnalHariIni'));
    }

    /** Rekap kehadiran kelas, per siswa (bisa dilihat per hari atau per mapel). */
    public function rekap(Request $request): View
    {
        $kelas = $this->kelas();

        // Kosong (belum difilter) = tampilkan SELURUH riwayat, bukan
        // dibatasi bulan berjalan -- konsisten sama Rekap Kehadiran Kelas
        // milik Wali Kelas & Rekap Kehadiran Siswa milik Waka.
        $dari = $request->filled('dari') ? Carbon::parse($request->date('dari')) : null;
        $sampai = $request->filled('sampai') ? Carbon::parse($request->date('sampai')) : null;
        $tipe = $request->query('tipe', 'hari');

        $siswas = $kelas->siswas()->orderBy('no_absen')->get();

        $dataRekap = RekapKehadiran::untukKelas($kelas, $siswas, $dari, $sampai);
        $rekapHari = $dataRekap['rekapHari'];
        $rekapMapel = $dataRekap['rekapMapel'];
        $rekap = $tipe === 'mapel' ? $rekapMapel : $rekapHari;

        return view('sekretaris.rekap', compact('kelas', 'siswas', 'rekap', 'rekapHari', 'rekapMapel', 'dari', 'sampai', 'tipe'));
    }

    /** Fragment modal rincian kehadiran satu siswa (mapel & riwayat pertemuan). */
    public function rekapSiswaFragment(Siswa $siswa, Request $request): View
    {
        $kelas = $this->kelas();
        abort_unless($siswa->kelas_id === $kelas->id, 403, 'Siswa bukan anggota kelas ini.');

        $dari = $request->filled('dari') ? Carbon::parse($request->date('dari')) : null;
        $sampai = $request->filled('sampai') ? Carbon::parse($request->date('sampai')) : null;

        $detail = RekapKehadiran::detailSiswa($siswa, $dari, $sampai);

        return view('rekap._detail-siswa', $detail);
    }
}
