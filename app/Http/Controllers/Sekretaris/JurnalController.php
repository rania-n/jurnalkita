<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Mapel;
use App\Notifications\JurnalPerluRevisi;
use App\Support\PresensiDefault;
use App\Support\Versi;
use App\Support\Waktu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JurnalController extends Controller
{
    private function kelas()
    {
        $user = auth()->user();

        abort_unless($user->isSekretaris(), 403, 'Hanya pengurus kelas yang dapat mengakses ini.');

        return $user->kelasSekretaris() ?? abort(403, 'Akun tidak terhubung ke kelas.');
    }

    private function pastikanKelasSaya(Jurnal $jurnal): void
    {
        abort_unless($jurnal->jadwal->kelas_id === $this->kelas()->id, 403);
    }

    public function index(Request $request): View
    {
        $kelas = $this->kelas();
        $status = $request->get('status');
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        // "Dari" diisi tapi "Sampai" kosong -> anggap nyari HARI ITU doang,
        // sama pola kayak Riwayat Jurnal Guru.
        if ($dari && ! $sampai) {
            $sampai = $dari;
        }

        // Sapu jurnal pending kelas ini yang udah kelewat hari -- otomatis
        // terverifikasi, biar nggak numpuk pending berhari-hari kalau
        // pengurus kelas nggak sempat periksa (lihat Jurnal::otomatisVerifikasiKalauLewatHari()).
        Jurnal::verifikasiSemuaYangKadaluarsa(kelasId: $kelas->id);

        // Cuma mapel yang PERNAH diajarkan di kelas ini -- nggak ada gunanya
        // nawarin mapel sekolah lain yang nggak nyangkut di kelas ini.
        $mapelList = Mapel::whereIn('id', $kelas->jadwals()->distinct()->pluck('mapel_id'))->orderBy('nama')->get();

        $jurnals = Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
            ->with('jadwal.mapel', 'guru')
            ->when($status, fn ($q) => $q->where('status_verifikasi', $status))
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($request->filled('mapel_id'), fn ($q) => $q->whereHas('jadwal', fn ($q2) => $q2->where('mapel_id', $request->query('mapel_id'))))
            // Yang masih "Perlu diperiksa" dimunculin paling atas duluan
            // (apapun tanggalnya) -- biar nggak kelewat/ketumpuk jurnal lama
            // yang udah diperiksa, baru diurutkan tanggal terbaru.
            ->orderByRaw("status_verifikasi = 'pending' desc")
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        // Habis submit verifikasi/revisi (verifikasi()) atau dari notifikasi
        // (JurnalPerluDiperiksa) -> balik ke sini bawa ?lihat=<id>, popup
        // detailnya kebuka otomatis -- nggak peduli jurnalnya ada di halaman
        // pagination yang mana.
        $lihatJurnal = $request->filled('lihat')
            ? Jurnal::with('jadwal.mapel')->find($request->integer('lihat'))
            : null;

        return view('sekretaris.jurnal.index', [
            'jurnals' => $jurnals,
            'kelas' => $kelas,
            'status' => $status,
            'dari' => $dari,
            'sampai' => $sampai,
            'mapelList' => $mapelList,
            'lihatJurnal' => $lihatJurnal,
            'jumlahPending' => Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))
                ->where('status_verifikasi', 'pending')->count(),
        ]);
    }

    /** Endpoint ringan buat di-poll (initAutoRefresh()) -- lihat App\Support\Versi. */
    public function versi(): JsonResponse
    {
        $kelas = $this->kelas();

        return response()->json([
            'versi' => Versi::dari(Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))),
        ]);
    }

    /**
     * Detail jurnal -- SELALU popup (fragment HTML tanpa layout), dibuka dari
     * Verifikasi Jurnal lewat AJAX. Nggak ada lagi halaman penuh buat ini --
     * sengaja dihapus (dulu ada, masih bisa diakses langsung lewat URL walau
     * harusnya cuma popup, bikin bingung -- sama pola kayak Jurnal Guru &
     * Dispensasi yang udah dibereskan duluan).
     */
    public function showFragment(Jurnal $jurnal): View
    {
        $this->pastikanKelasSaya($jurnal);
        $jurnal->otomatisVerifikasiKalauLewatHari();
        $jurnal->load('jadwal.mapel', 'guru', 'absensis.siswa');

        return view('sekretaris.jurnal._detail-fragment', compact('jurnal'));
    }

    public function verifikasi(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->pastikanKelasSaya($jurnal);

        $data = $request->validate([
            'keputusan' => ['required', 'in:terima,revisi'],
            'catatan' => ['nullable', 'string', 'max:500', 'required_if:keputusan,revisi'],
        ]);

        $jurnal->update([
            'status_verifikasi' => $data['keputusan'] === 'terima' ? 'terverifikasi' : 'revisi',
            'verifikator_id' => auth()->user()->siswa->id,
            'catatan_verifikasi' => $data['catatan'] ?? null,
        ]);

        AuditLog::catat(
            $data['keputusan'] === 'terima' ? 'Verifikasi Jurnal' : 'Minta Revisi Jurnal',
            "Jurnal #{$jurnal->id} — ".($data['keputusan'] === 'terima' ? 'terverifikasi' : 'minta revisi'),
            $jurnal
        );

        if ($data['keputusan'] === 'revisi' && $jurnal->guru->user) {
            $jurnal->guru->user->notify(new JurnalPerluRevisi($jurnal));
        }

        // Pesan sukses ikut beda buat jurnal Tidak Hadir -- "disetujui" (bukan
        // "diverifikasi"), samain sama istilah tombolnya (lihat
        // sekretaris/jurnal/_detail-fragment.blade.php & Jurnal::verifikasiAbsen()).
        $pesanSukses = $data['keputusan'] === 'terima'
            ? ($jurnal->verifikasiAbsen() ? 'Laporan tidak hadir disetujui.' : 'Jurnal diverifikasi.')
            : 'Permintaan revisi dikirim ke guru.';

        return redirect()->route('sekretaris.jurnal.index', ['lihat' => $jurnal->id])
            ->with('success', $pesanSukses);
    }

    /* ---------------------------------------- Jurnal pengganti (guru tidak sempat) */
    public function createPengganti(): View
    {
        $kelas = $this->kelas();
        $hariIni = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;

        $jadwals = $kelas->jadwals()->with('mapel', 'guru')
            ->when($hariIni, fn ($q) => $q->where('hari', $hariIni))
            ->orderBy('jam_ke_mulai')->get();

        // Pengurus kelas ADA di kelas itu, jadi dia yang paling tau siapa yang
        // beneran hadir/nggak hari ini -- presensinya diisi bareng, bukan asal
        // ditandai hadir semua. Default-nya ikut PresensiDefault (dispensasi
        // hari ini / presensi dari jurnal lain kelas ini hari ini / "Hadir")
        // -- sama aturannya kayak form Isi Jurnal punya Guru. Jam mulai/selesai
        // nggak dikasih di sini (belum tau jadwal mana yang bakal dipilih di
        // form ini -- presensinya di-render sebelum jadwalnya kepilih), jadi
        // dispensasi yang dicek sepanjang hari itu, bukan yang spesifik 1 JP.
        $siswas = $kelas->siswas()->orderBy('no_absen')->get();
        $presensiAwal = PresensiDefault::untukKelas($siswas, $kelas->id, now()->toDateString());

        // Peta jam_ke -> mulai/selesai buat hari ini -- dikirim ke JS biar bisa
        // nampilin "Waktunya 07:00-08:30" yang otomatis update tiap jam ke-
        // dipilih/diubah manual (baik lewat jadwal maupun select JP langsung).
        $jamPelajaranHariIni = JamPelajaran::where('kategori', Waktu::kategori())
            ->get(['jam_ke', 'mulai', 'selesai'])
            ->mapWithKeys(fn ($jp) => [$jp->jam_ke => ['mulai' => $jp->mulai->format('H:i'), 'selesai' => $jp->selesai->format('H:i')]]);

        return view('sekretaris.jurnal.pengganti', compact('kelas', 'jadwals', 'siswas', 'presensiAwal', 'jamPelajaranHariIni'));
    }

    public function storePengganti(Request $request): RedirectResponse
    {
        $kelas = $this->kelas();

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            // Sama kayak Guru\JurnalController: yang wajib Tugas Tambahan +
            // Alasan (bukan Materi -- itu khusus status hadir, nggak relevan
            // di sini). status_guru sendiri nggak lagi dipilih dari form --
            // pengganti = guru nggak hadir, jadi SELALU 'tidak_hadir' (lihat
            // di bawah).
            'tugas_tambahan' => ['required', 'string'],
            'alasan' => ['required', 'string'],
            'presensi' => ['required', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
            'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
        ]);
        $data['status_guru'] = 'tidak_hadir';

        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        abort_unless($jadwal->kelas_id === $kelas->id, 403);

        // Jam mulai SELALU ikut jadwal aslinya (bukan input form) -- sama kayak
        // aturan Guru\JurnalController::store(). jam_ke_selesai boleh lebih lama
        // dari jadwal aslinya kalau memang begitu kenyataannya.
        $data['jam_ke_mulai'] = $jadwal->jam_ke_mulai;
        if ($data['jam_ke_selesai'] < $data['jam_ke_mulai']) {
            $data['jam_ke_selesai'] = $jadwal->jam_ke_selesai;
        }

        $sudahAda = Jurnal::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        if ($sudahAda) {
            return redirect()->route('sekretaris.jurnal.index', ['lihat' => $sudahAda->id])
                ->with('info', 'Jurnal untuk jadwal ini hari ini sudah ada.');
        }

        $presensiFallback = PresensiDefault::untukKelas(
            $jadwal->kelas->siswas, $jadwal->kelas_id, now()->toDateString(), $data['jam_ke_mulai'], $data['jam_ke_selesai']
        );

        $jurnal = DB::transaction(function () use ($data, $jadwal, $presensiFallback) {
            $jurnal = Jurnal::create([
                ...collect($data)->except('presensi')->all(),
                'guru_id' => $jadwal->guru_id,
                'tanggal' => now()->toDateString(),
                'diisi_oleh_pengurus' => true,
                'status_verifikasi' => 'terverifikasi',
                'verifikator_id' => auth()->user()->siswa->id,
            ]);

            foreach ($jadwal->kelas->siswas as $siswa) {
                $isi = $data['presensi'][$siswa->id] ?? $presensiFallback[$siswa->id] ?? ['status' => 'hadir', 'catatan' => null];
                Absensi::create([
                    'jurnal_id' => $jurnal->id,
                    'siswa_id' => $siswa->id,
                    'status' => $isi['status'],
                    'catatan' => $isi['catatan'] ?? null,
                ]);
            }

            return $jurnal;
        });

        AuditLog::catat('Jurnal Pengganti', "Pengurus kelas mengisi jurnal pengganti #{$jurnal->id}", $jurnal);

        return redirect()->route('sekretaris.jurnal.index', ['lihat' => $jurnal->id])
            ->with('success', 'Jurnal pengganti tersimpan. Guru akan melihatnya di riwayat.');
    }
}
