<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengaturanJurnal;
use App\Notifications\JurnalPerluDiperiksa;
use App\Support\PresensiDefault;
use App\Support\Versi;
use App\Support\Waktu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JurnalController extends Controller
{
    /** Preset Metode Pembelajaran -- isinya sering diulang-ulang tiap guru isi jurnal. */
    public const METODE_LABEL = [
        'ceramah' => 'Ceramah',
        'diskusi' => 'Diskusi',
        'praktik' => 'Praktik',
        'ulangan' => 'Ulangan/Tes',
        'presentasi' => 'Presentasi',
        'lainnya' => 'Lainnya',
    ];

    /** Alasan Tidak Hadir -- cuma 2 kategori (bar, bukan teks bebas lagi). */
    public const ALASAN_LABEL = [
        'sakit' => 'Sakit',
        'izin' => 'Izin',
    ];

    private function guru()
    {
        return auth()->user()->guru ?? abort(403, 'Akun tidak terhubung ke data guru.');
    }

    private function milikSendiri(Jurnal $jurnal): void
    {
        abort_unless($jurnal->guru_id === $this->guru()->id, 403);
    }

    /**
     * Boleh isi jurnal buat jadwal ini SEKARANG, dengan mode & tanggal target
     * yang udah ditentuin? Sama persis aturannya kayak yang nentuin
     * $jurnalDiblokirIstirahat di create() -- dicek ulang di sini (bukan
     * cuma di tampilan) biar nggak bisa dibobol lewat request manual pas
     * istirahat/jam yang bukan jadwalnya. Aturan jam CUMA berlaku buat mode
     * 'disiplin' & tanggal HARI INI -- mode lain (atau tanggal kemarin, yang
     * emang cuma nyala kalau mode-nya udah ngizinin) selalu boleh.
     */
    private function jadwalBolehDiisi(Jadwal $jadwal, string $mode, Carbon $tanggal): bool
    {
        if ($mode !== 'disiplin' || ! $tanggal->isToday()) {
            return true;
        }

        $jpAktif = Waktu::jpAktifSekarang();
        if ($jpAktif !== null && $jadwal->jam_ke_mulai <= $jpAktif && $jadwal->jam_ke_selesai >= $jpAktif) {
            return true;
        }

        return ! Waktu::dalamJamSekolah();
    }

    /**
     * Jadwal ini diisi buat tanggal berapa? Default HARI INI -- kecuali mode
     * 'bebas_kemarin' aktif DAN hari jadwal ini persis sama kayak hari
     * kemarin (bukan cuma "dipilih dari tab Kemarin", biar nggak bisa
     * dibobol lewat request manual jadwal_id yang nggak sesuai).
     */
    private function tanggalUntukJadwal(Jadwal $jadwal, string $mode): Carbon
    {
        if ($mode === 'bebas_kemarin') {
            $kemarin = now()->subDay();
            $hariKemarin = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$kemarin->dayOfWeek - 1] ?? null;
            if ($jadwal->hari === $hariKemarin) {
                return $kemarin;
            }
        }

        return now();
    }

    /* --------------------------------------------------------------- Riwayat */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');
        // Default-nya HARI INI (bukan seluruh riwayat tanpa batas) -- guru
        // paling sering buka Riwayat buat ngecek jurnal yang BARUSAN diisi
        // hari itu. Filter jelas kok kelihatan keisi tanggal hari ini di
        // kotaknya (bukan kosong tersembunyi), tetap bisa diganti manual
        // kalau mau lihat tanggal lain.
        $dari = $request->filled('dari') ? $request->query('dari') : today()->toDateString();
        $sampai = $request->query('sampai');

        // "Dari" diisi tapi "Sampai" kosong -> anggap nyari HARI ITU doang
        // (bukan "dari tanggal itu sampai sekarang"). Ini backup di sisi
        // server -- JS di view juga langsung ngisi field "Sampai"-nya biar
        // kelihatan di form, tapi validasi/query yang beneran dipakai tetap
        // di sini.
        if ($dari && ! $sampai) {
            $sampai = $dari;
        }

        $guru = $this->guru();

        // Sapu jurnal pending guru ini yang udah kelewat hari -- otomatis
        // terverifikasi (lihat Jurnal::otomatisVerifikasiKalauLewatHari()),
        // biar Riwayat-nya langsung akurat walau pengurus kelas belum
        // sempat buka menu Verifikasi Jurnal-nya sendiri.
        Jurnal::verifikasiSemuaYangKadaluarsa(guruId: $guru->id);

        // Pilihan dropdown Kelas & Mapel cuma yang PERNAH diajar guru ini
        // (dari jadwalnya), bukan semua kelas/mapel sekolah -- nggak ada
        // gunanya nawarin kelas yang dia sendiri nggak pernah pegang.
        $kelasList = Kelas::whereIn('id', $guru->jadwals()->distinct()->pluck('kelas_id'))->orderBy('nama')->get();
        $mapelList = Mapel::whereIn('id', $guru->jadwals()->distinct()->pluck('mapel_id'))->orderBy('nama')->get();

        $jurnals = $guru->jurnals()
            ->with('jadwal.kelas', 'jadwal.mapel')
            ->when($status !== 'semua', fn ($q) => $q->where('status_verifikasi', $status))
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($request->filled('kelas_id'), fn ($q) => $q->whereHas('jadwal', fn ($q2) => $q2->where('kelas_id', $request->query('kelas_id'))))
            ->when($request->filled('mapel_id'), fn ($q) => $q->whereHas('jadwal', fn ($q2) => $q2->where('mapel_id', $request->query('mapel_id'))))
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        // Halaman detail jurnal (jurnal.show) udah dihapus -- semua "lihat
        // detail" sekarang lewat popup di halaman ini. Tempat lain yang dulu
        // redirect/link ke jurnal.show (habis submit, notifikasi revisi,
        // dst) sekarang ke sini bawa ?lihat=<id>, popup-nya kebuka otomatis
        // -- nggak peduli jurnalnya ada di halaman pagination yang mana.
        $lihatJurnal = $request->filled('lihat')
            ? $guru->jurnals()->with('jadwal.kelas', 'jadwal.mapel')->find($request->query('lihat'))
            : null;

        // ?ubah=1 (dari redirect gagal-validasi di update()) butuh 2 loncatan
        // request: halaman ini DULU, baru abis itu fetch AJAX ke fragment
        // form-nya (lihat index.blade.php). Flash session (old input +
        // $errors) bawaan Laravel cuma nyampe 1 loncatan -- tanpa keep() di
        // sini, pas fragment-nya di-fetch, errornya udah keburu ilang & form
        // balik nampilin data lama (bukan yang barusan diketik + errornya).
        if ($request->boolean('ubah') && $lihatJurnal) {
            $request->session()->keep(['errors', '_old_input']);
        }

        return view('guru.jurnal.index', compact('jurnals', 'status', 'dari', 'sampai', 'kelasList', 'mapelList', 'lihatJurnal'));
    }

    /** Endpoint ringan buat di-poll (initAutoRefresh()) -- lihat App\Support\Versi. */
    public function versi(): JsonResponse
    {
        return response()->json(['versi' => Versi::dari($this->guru()->jurnals())]);
    }

    /* ------------------------------------------------------------ Form baru */
    public function create(Request $request): View
    {
        $guru = $this->guru();
        $mode = PengaturanJurnal::mode();

        // Tab "Kemarin" cuma nyala kalau admin udah ngizinin mode-nya
        // (bebas_kemarin) DAN guru eksplisit milih tab itu. Selain itu SELALU
        // hari ini -- ini juga yang nentuin tanggal jurnal ke-simpen nanti.
        $pakaiKemarin = $mode === 'bebas_kemarin' && $request->query('hari') === 'kemarin';
        $tanggalAktif = $pakaiKemarin ? now()->subDay() : now();
        $hariAktif = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggalAktif->dayOfWeek - 1] ?? null;

        $jadwals = $hariAktif
            ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hariAktif)->orderBy('jam_ke_mulai')->get()
            : collect();

        // Semua jadwal guru (fallback kalau tidak ada jadwal di hari aktif)
        $semuaJadwal = $guru->jadwals()->with('kelas', 'mapel')->orderBy('hari')->orderBy('jam_ke_mulai')->get();

        // Jadwal yang di TANGGAL AKTIF udah ada jurnalnya nggak boleh dipilih
        // lagi dari sini -- backend (store()) juga nolak kalau dipaksa submit,
        // tapi ini biar guru nggak keburu isi form panjang dulu baru ditolak
        // pas submit. Baru bisa kepilih lagi kalau jurnalnya dihapus (destroy()).
        $idJadwalSudahDiisi = Jurnal::whereIn('jadwal_id', $semuaJadwal->pluck('id'))
            ->whereDate('tanggal', $tanggalAktif->toDateString())
            ->pluck('jadwal_id');
        $jumlahSudahDiisiHariIni = $jadwals->whereIn('id', $idJadwalSudahDiisi)->count();

        $jadwals = $jadwals->reject(fn ($j) => $idJadwalSudahDiisi->contains($j->id))->values();
        $semuaJadwal = $semuaJadwal->reject(fn ($j) => $idJadwalSudahDiisi->contains($j->id))->values();

        $daftarJadwal = $jadwals->isNotEmpty() ? $jadwals : $semuaJadwal;

        $jpSekarang = Waktu::jpSekarang();
        // Aturan kunci-ke-JP-aktif & blokir-pas-istirahat CUMA berlaku buat
        // mode 'disiplin' & pas lagi lihat tab HARI INI (bukan Kemarin -- jam
        // pelajaran kemarin udah lewat semua, nggak ada "JP aktif" buat itu).
        // Jadwal yang jam-nya nyakup JP yang BENERAN lagi jalan detik ini
        // (bukan cuma "hari ini" -- guru bisa punya beberapa jadwal hari ini
        // di jam berbeda). Sengaja pakai jpAktifSekarang() (null kalau lagi
        // bukan jam pelajaran sama sekali -- sebelum JP1, istirahat, atau
        // tengah malam), BUKAN jpSekarang() yang punya fallback -- soalnya
        // fallback itu bikin "jam 1 pagi" kebaca kayak "JP1" terus salah
        // ngunci ke jadwal yang nggak relevan sama sekali cuma karena
        // kebetulan nomernya cocok.
        $jpAktif = Waktu::jpAktifSekarang();
        $modeDisiplinHariIni = $mode === 'disiplin' && ! $pakaiKemarin;
        $jadwalJpIni = ($modeDisiplinHariIni && $jpAktif !== null)
            ? $jadwals->filter(fn ($j) => $j->jam_ke_mulai <= $jpAktif && $j->jam_ke_selesai >= $jpAktif)
            : collect();

        // Jadwal yang diminta eksplisit lewat ?jadwal=... (tombol "Isi Jurnal"
        // di kartu jadwal per-JP di Beranda) HARUS lolos pengecekan yang SAMA
        // kayak submit (jadwalBolehDiisi()) -- dulu ID-nya langsung di-trust
        // apa adanya tanpa dicek ulang sama sekali, beda sama jalur tanpa
        // parameter (menu "Isi Jurnal" biasa) yang emang auto-pilih murni dari
        // $jadwalJpIni sehingga otomatis bener. Akibatnya kartu jadwal yang JP-
        // nya "Sudah Lewat" tetap bisa kebuka form isinya. Kalau ternyata nggak
        // lolos, anggap SAMA kayak nggak ada parameter -- biar auto-pilih ke JP
        // yang BENERAN aktif sekarang (atau keblokir kalau emang lagi nggak ada
        // JP aktif sama sekali), bukan malah nolak mentah-mentah padahal guru
        // ini beneran lagi ada jadwal aktif (cuma bukan yang dia klik).
        $jadwalDiminta = $request->filled('jadwal') ? $semuaJadwal->firstWhere('id', (int) $request->jadwal) : null;
        if ($jadwalDiminta && ! $this->jadwalBolehDiisi($jadwalDiminta, $mode, $tanggalAktif)) {
            $jadwalDiminta = null;
        }
        $adaJadwalDiminta = $jadwalDiminta !== null;

        $jadwalTerkunci = ! $adaJadwalDiminta && $jadwalJpIni->count() === 1;

        // Di luar jam yang beneran cocok jadi jadwal SENDIRI (istirahat, atau
        // jam ini emang bukan jadwal dia), tapi MASIH dalam rentang jam sekolah
        // (jam ke-1 s/d jam terakhir) -> jangan kasih akses milih jadwal lain,
        // itu celah buat isi jurnal jam yang belum/nggak beneran dijalani. Baru
        // bebas milih (buat susulan/testing) kalau BENERAN udah di luar jam
        // sekolah (pulang sekolah, atau sebelum jam ke-1 mulai). Mode selain
        // 'disiplin' (atau tab Kemarin) nggak pernah diblokir sama sekali.
        $jurnalDiblokirIstirahat = $modeDisiplinHariIni && Waktu::dalamJamSekolah() && ! $adaJadwalDiminta && $jadwalJpIni->count() !== 1;

        $jadwalTerpilih = $jurnalDiblokirIstirahat
            ? null
            : ($adaJadwalDiminta
                ? $jadwalDiminta
                : ($jadwalTerkunci
                    ? $jadwalJpIni->first()
                    // Cuma ada 1 opsi -> browser otomatis milih itu (placeholder "Pilih
                    // jadwal" disembunyikan) walau URL nggak bawa ?jadwal=. Server ikut
                    // anggap terpilih dari awal, biar presensinya langsung kelihatan
                    // tanpa guru harus "milih ulang" jadwal yang sebenarnya cuma satu.
                    : ($daftarJadwal->count() === 1 ? $daftarJadwal->first() : null)));

        $siswas = collect();
        $presensiAwal = [];
        $jurnalSebelumnya = null;
        if ($jadwalTerpilih) {
            $jadwalTerpilih->loadMissing('kelas.siswas');
            $siswas = $jadwalTerpilih->kelas->siswas->sortBy('no_absen')->values();
            $presensiAwal = PresensiDefault::untukKelas(
                $siswas, $jadwalTerpilih->kelas_id, $tanggalAktif->toDateString(), $jadwalTerpilih->jam_ke_mulai, $jadwalTerpilih->jam_ke_selesai
            );

            // Jurnal terakhir di jadwal yang SAMA (kelas+mapel+guru+slot ini
            // juga) sebelum tanggal aktif -- biasanya minggu lalu, tapi bisa
            // lebih lama kalau ada libur di antaranya. Dipakai cuma buat
            // acuan tampilan (nggak dipakai isi otomatis field manapun).
            $jurnalSebelumnya = Jurnal::where('jadwal_id', $jadwalTerpilih->id)
                ->where('tanggal', '<', $tanggalAktif->toDateString())
                ->latest('tanggal')
                ->first();
        }

        return view('guru.jurnal.create', [
            'jadwals' => $daftarJadwal,
            'jadwalTerpilih' => $jadwalTerpilih,
            'jadwalTerkunci' => $jadwalTerkunci,
            'jurnalDiblokirIstirahat' => $jurnalDiblokirIstirahat,
            'jumlahSudahDiisiHariIni' => $jumlahSudahDiisiHariIni,
            'jurnalSebelumnya' => $jurnalSebelumnya,
            'jpSekarang' => $jpSekarang,
            'jpMaks' => 13,
            'siswas' => $siswas,
            'presensiAwal' => $presensiAwal,
            'metodeLabel' => self::METODE_LABEL,
            'metodeTerpilih' => old('metode_pilihan'),
            'metodeCustom' => old('metode_custom'),
            'alasanLabel' => self::ALASAN_LABEL,
            'modeJurnal' => $mode,
            'pakaiKemarin' => $pakaiKemarin,
            'tanggalAktif' => $tanggalAktif,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = $this->guru();

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15'],
            'status_guru' => ['required', 'in:hadir,tidak_hadir'],
            'materi' => ['required_if:status_guru,hadir', 'nullable', 'string'],
            'metode_pilihan' => ['required_if:status_guru,hadir', 'nullable', 'in:'.implode(',', array_keys(self::METODE_LABEL))],
            'metode_custom' => ['required_if:metode_pilihan,lainnya', 'nullable', 'string', 'max:255'],
            'tugas_tambahan' => ['required_if:status_guru,tidak_hadir', 'nullable', 'string'],
            'alasan' => ['required_if:status_guru,tidak_hadir', 'nullable', 'in:'.implode(',', array_keys(self::ALASAN_LABEL))],
            'presensi' => ['nullable', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
            'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
            // Wajib cuma kalau Hadir (bukti "beneran di kelas", lewat kamera
            // langsung). Kalau Tidak Hadir, opsional -- boleh lampirin foto
            // surat izin/sakit dari galeri (bukan wajib jepret kamera), lihat
            // x-ui.upload di view. mimes (bukan "image") biar PDF juga lolos.
            'foto_bukti' => [
                $request->input('status_guru') === 'hadir' ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096',
            ],
        ]);

        $data['metode'] = $this->hitungMetode($data);
        if (isset($data['alasan'])) {
            $data['alasan'] = self::ALASAN_LABEL[$data['alasan']] ?? $data['alasan'];
        }

        $mode = PengaturanJurnal::mode();
        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        abort_unless($jadwal->guru_id === $guru->id, 403);

        $tanggal = $this->tanggalUntukJadwal($jadwal, $mode);
        abort_unless($this->jadwalBolehDiisi($jadwal, $mode, $tanggal), 403, 'Belum waktunya isi jurnal untuk jadwal ini -- tunggu jam pelajarannya berlangsung.');

        // Jam mulai & selesai SELALU ikut jadwal yang dipilih (bukan input klien) --
        // ini yang beneran dijadwalkan, guru nggak bisa ngarang jam sendiri lewat
        // request manual walau field di form udah dikunci di sisi tampilan.
        $data['jam_ke_mulai'] = $jadwal->jam_ke_mulai;
        $data['jam_ke_selesai'] = $jadwal->jam_ke_selesai;

        // Cegah jurnal ganda untuk jadwal yang sama di tanggal yang sama.
        $sudahAda = Jurnal::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->first();
        if ($sudahAda) {
            return redirect()->route('jurnal.index', ['lihat' => $sudahAda->id])
                ->with('info', 'Jurnal untuk jadwal ini di tanggal itu sudah dibuat.');
        }

        $presensiSubmit = $data['presensi'] ?? [];
        $presensiFallback = PresensiDefault::untukKelas(
            $jadwal->kelas->siswas, $jadwal->kelas_id, $tanggal->toDateString(), $data['jam_ke_mulai'], $data['jam_ke_selesai']
        );
        $fotoPath = $request->file('foto_bukti')?->store('jurnal-bukti', 'public');

        $jurnal = DB::transaction(function () use ($data, $jadwal, $guru, $presensiSubmit, $presensiFallback, $fotoPath, $tanggal) {
            $jurnal = Jurnal::create([
                ...collect($data)->except(['presensi', 'foto_bukti', 'metode_pilihan', 'metode_custom'])->all(),
                'guru_id' => $guru->id,
                'tanggal' => $tanggal->toDateString(),
                'foto_bukti' => $fotoPath,
            ]);

            // Presensi ikut isi jurnal sendiri, bukan langkah terpisah lagi. Kalau
            // guru nggak sempat sentuh grid presensinya (mis. kirim manual lewat
            // API), tetap jatuh ke default (dispensasi/carry-over/hadir, lihat
            // PresensiDefault).
            foreach ($jadwal->kelas->siswas as $siswa) {
                if (isset($presensiSubmit[$siswa->id])) {
                    $isi = $presensiSubmit[$siswa->id];
                } else {
                    $isi = $presensiFallback[$siswa->id] ?? ['status' => 'hadir', 'catatan' => null];
                }

                Absensi::create([
                    'jurnal_id' => $jurnal->id,
                    'siswa_id' => $siswa->id,
                    'status' => $isi['status'],
                    'catatan' => $isi['catatan'] ?? null,
                ]);
            }

            return $jurnal;
        });

        AuditLog::catat('Tambah Jurnal', "Jurnal {$jadwal->mapel->nama} — {$jadwal->kelas->nama}", $jurnal);

        $jadwal->kelas->pengurusUser()?->notify(new JurnalPerluDiperiksa($jurnal));

        return redirect()->route('jurnal.index', ['lihat' => $jurnal->id])
            ->with('success', 'Jurnal & presensi tersimpan.');
    }

    /* --------------------------------------------------- Tidak hadir, massal */

    /**
     * Guru izin/sakit seharian & megang lebih dari 1 kelas -- daripada
     * bolak-balik isi form yang sama persis per kelas, di sini bisa ditandai
     * SEKALIGUS buat semua jadwal hari ini yang belum ada jurnalnya (checklist
     * "Kelas yang Ditandai" di jurnal.create, muncul setelah pilih "Ya, Semua
     * Kelas"). Alasan berlaku sama ke semua (memang soal kondisi gurunya
     * sendiri), Tugas Tambahan punya 1 teks default yang bisa di-override per
     * kelas kalau ternyata beda.
     */
    public function storeMassal(Request $request): RedirectResponse
    {
        $guru = $this->guru();

        $data = $request->validate([
            'jadwal_ids' => ['required', 'array', 'min:1'],
            'jadwal_ids.*' => ['integer', 'exists:jadwals,id'],
            'alasan' => ['required', 'in:'.implode(',', array_keys(self::ALASAN_LABEL))],
            'tugas_tambahan' => ['required', 'string'],
            'tugas_khusus' => ['nullable', 'array'],
            'tugas_khusus.*' => ['nullable', 'string'],
            // Opsional -- surat izin/sakit (kalau ada), BUKAN wajib jepret
            // kamera kayak "foto suasana kelas" di form biasa (guru nggak di
            // sekolah). 1 file yang sama dipakai buat semua kelas tercentang.
            // Nama field disamain sama form isi 1 kelas ("foto_bukti") --
            // sekarang satu form yang sama dipakai buat dua-duanya, jadi nggak
            // perlu ganti nama field pakai JS pas mode massal aktif.
            'foto_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $jadwals = Jadwal::whereIn('id', $data['jadwal_ids'])->with('kelas.siswas', 'mapel')->get();
        abort_if($jadwals->contains(fn ($j) => $j->guru_id !== $guru->id), 403);

        $tanggal = now()->toDateString();
        $sudahAda = Jurnal::whereIn('jadwal_id', $jadwals->pluck('id'))->whereDate('tanggal', $tanggal)->pluck('jadwal_id');
        $jadwals = $jadwals->reject(fn ($j) => $sudahAda->contains($j->id))->values();

        if ($jadwals->isEmpty()) {
            return redirect()->route('jurnal.index')
                ->with('info', 'Kelas yang dipilih sudah ada jurnalnya semua (mungkin baru saja diisi dari tab lain).');
        }

        $alasan = self::ALASAN_LABEL[$data['alasan']];
        $suratPath = $request->file('foto_bukti')?->store('jurnal-bukti', 'public');

        // Nggak lewat jadwalBolehDiisi() (kunci-ke-JP-aktif buat mode
        // 'disiplin') SENGAJA -- itu buat nyegah klaim "udah ngajarin materi"
        // yang belum beneran kejalanin, sedangkan di sini nggak ada klaim
        // hadir sama sekali (isinya deklarasi ke depan "saya nggak masuk"),
        // termasuk buat jadwal JP yang belum kejalanin hari ini.
        $dibuat = DB::transaction(function () use ($jadwals, $data, $guru, $tanggal, $alasan, $suratPath) {
            return $jadwals->map(function ($jadwal) use ($data, $guru, $tanggal, $alasan, $suratPath) {
                $tugasKhusus = trim($data['tugas_khusus'][$jadwal->id] ?? '');

                $jurnal = Jurnal::create([
                    'jadwal_id' => $jadwal->id,
                    'guru_id' => $guru->id,
                    'tanggal' => $tanggal,
                    'jam_ke_mulai' => $jadwal->jam_ke_mulai,
                    'jam_ke_selesai' => $jadwal->jam_ke_selesai,
                    'status_guru' => 'tidak_hadir',
                    'tugas_tambahan' => $tugasKhusus !== '' ? $tugasKhusus : $data['tugas_tambahan'],
                    'alasan' => $alasan,
                    'foto_bukti' => $suratPath,
                ]);

                // Guru nggak di kelas manapun buat nentuin presensi manual --
                // ikut default (dispensasi hari ini / carry-over jurnal lain
                // kelas ini hari ini / Hadir), sama kayak store() biasa.
                // Pengurus kelas yang koreksi kalau ada yang beda pas Verifikasi.
                $presensiDefault = PresensiDefault::untukKelas(
                    $jadwal->kelas->siswas, $jadwal->kelas_id, $tanggal, $jadwal->jam_ke_mulai, $jadwal->jam_ke_selesai
                );
                foreach ($jadwal->kelas->siswas as $siswa) {
                    $isi = $presensiDefault[$siswa->id] ?? ['status' => 'hadir', 'catatan' => null];
                    Absensi::create([
                        'jurnal_id' => $jurnal->id,
                        'siswa_id' => $siswa->id,
                        'status' => $isi['status'],
                        'catatan' => $isi['catatan'] ?? null,
                    ]);
                }

                return $jurnal;
            });
        });

        foreach ($dibuat as $jurnal) {
            AuditLog::catat('Tambah Jurnal (massal)', "Jurnal {$jurnal->jadwal->mapel->nama} — {$jurnal->jadwal->kelas->nama}", $jurnal);
            $jurnal->jadwal->kelas->pengurusUser()?->notify(new JurnalPerluDiperiksa($jurnal));
        }

        return redirect()->route('jurnal.index')
            ->with('success', $dibuat->count().' jurnal berhasil dibuat sekaligus.');
    }

    /**
     * Isi popup "Ubah Jurnal" -- SELALU nempel di popup "Lihat" yang sama
     * (tombol "Ubah Jurnal" di _detail-fragment nge-swap isi popup ke
     * fragment ini, bukan buka popup/halaman baru). Nggak ada lagi halaman
     * penuh terpisah buat ini -- sama kayak showFragment(), sengaja dihapus.
     */
    /* ------------------------------------------------------------- Ubah jurnal */
    public function editFragment(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        $jurnal->load('jadwal.kelas.siswas', 'jadwal.mapel', 'absensis');

        $siswas = $jurnal->jadwal->kelas->siswas->sortBy('no_absen')->values();
        $presensiAwal = $jurnal->absensis->mapWithKeys(fn ($a) => [
            $a->siswa_id => ['status' => $a->status, 'catatan' => $a->catatan],
        ])->all();

        $jurnalSebelumnya = Jurnal::where('jadwal_id', $jurnal->jadwal_id)
            ->where('tanggal', '<', $jurnal->tanggal)
            ->latest('tanggal')
            ->first();

        // Metode lama disimpan sebagai teks bebas -- cocokkan ke preset kalau ada,
        // kalau nggak (atau metode custom dari sebelum ada preset ini) anggap "Lainnya".
        $metodeTerpilih = null;
        $metodeCustom = null;
        if ($jurnal->metode) {
            $cocok = array_search($jurnal->metode, self::METODE_LABEL, true);
            $metodeTerpilih = $cocok !== false ? $cocok : 'lainnya';
            $metodeCustom = $metodeTerpilih === 'lainnya' ? $jurnal->metode : null;
        }

        // Sama pola kayak metode -- alasan disimpan sebagai teks label ("Sakit"/
        // "Izin"), dicocokkan balik ke key bar-nya. Data lama (sebelum alasan
        // jadi bar, masih teks bebas) nggak bakal cocok -- wajar, radio-nya
        // biarin kosong, guru pilih ulang salah satu pas ubah jurnal ini.
        $alasanTerpilih = old('alasan', array_search($jurnal->alasan, self::ALASAN_LABEL, true) ?: null);

        return view('guru.jurnal._edit-fragment', [
            ...compact('jurnal', 'siswas', 'presensiAwal', 'jurnalSebelumnya', 'alasanTerpilih'),
            'metodeLabel' => self::METODE_LABEL,
            'metodeTerpilih' => old('metode_pilihan', $metodeTerpilih),
            'metodeCustom' => old('metode_custom', $metodeCustom),
            'alasanLabel' => self::ALASAN_LABEL,
        ]);
    }

    public function update(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        // Jam mulai tidak ikut diubah — pakai nilai jurnal untuk validasi jam selesai.
        $request->merge(['jam_ke_mulai' => $jurnal->jam_ke_mulai]);

        $sudahRevisi = $jurnal->status_verifikasi === 'revisi';

        // Form-nya nempel di popup (bukan halaman sendiri lagi) -- kalau
        // validasi gagal, redirect default (back()) bakal lompat ke halaman
        // APAPUN yang lagi kebuka Riwayat Jurnal-nya TANPA popup ini otomatis
        // kebuka lagi & errornya nggak kelihatan sama sekali. Tangkap manual
        // biar bisa arahin balik ke popup Ubah jurnal ini juga (?ubah=1),
        // lengkap sama pesan error & isian sebelumnya -- lihat
        // resources/views/guru/jurnal/index.blade.php buat auto-buka-nya.
        try {
            $data = $request->validate([
                'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
                'status_guru' => ['required', 'in:hadir,tidak_hadir'],
                'materi' => ['required_if:status_guru,hadir', 'nullable', 'string'],
                'metode_pilihan' => ['required_if:status_guru,hadir', 'nullable', 'in:'.implode(',', array_keys(self::METODE_LABEL))],
                'metode_custom' => ['required_if:metode_pilihan,lainnya', 'nullable', 'string', 'max:255'],
                'tugas_tambahan' => ['required_if:status_guru,tidak_hadir', 'nullable', 'string'],
                'alasan' => ['required_if:status_guru,tidak_hadir', 'nullable', 'in:'.implode(',', array_keys(self::ALASAN_LABEL))],
                'presensi' => ['nullable', 'array'],
                'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
                'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
                // Foto wajib cuma kalau status_guru Hadir DAN belum ada foto dari
                // sebelumnya (guru cuma ubah data lain nggak wajib upload ulang).
                // Tidak Hadir -> selalu opsional (surat izin/sakit, bukan wajib
                // jepret kamera) -- sama alasannya kayak store().
                'foto_bukti' => [
                    ($jurnal->foto_bukti || $request->input('status_guru') !== 'hadir') ? 'nullable' : 'required',
                    'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096',
                ],
            ]);
        } catch (ValidationException $e) {
            return redirect()->route('jurnal.index', ['lihat' => $jurnal->id, 'ubah' => 1])
                ->withErrors($e->errors())->withInput();
        }

        // Jam selesai SELALU ikut jadwal aslinya, sama kayak di store() -- field
        // di form udah dikunci di sisi tampilan, ini jaga-jaga di server juga.
        $data['jam_ke_selesai'] = $jurnal->jam_ke_selesai;

        $data['metode'] = $this->hitungMetode($data);
        if (isset($data['alasan'])) {
            $data['alasan'] = self::ALASAN_LABEL[$data['alasan']] ?? $data['alasan'];
        }

        DB::transaction(function () use ($jurnal, $data, $request) {
            $jurnal->update(collect($data)->except(['presensi', 'foto_bukti', 'jam_ke_mulai', 'metode_pilihan', 'metode_custom'])->all());

            // Presensi yang nggak disentuh (mis. request minimal dari test/API)
            // tetap dipertahankan seperti sebelumnya, bukan direset.
            foreach ($jurnal->absensis as $absensi) {
                $isi = $data['presensi'][$absensi->siswa_id]
                    ?? ['status' => $absensi->status, 'catatan' => $absensi->catatan];
                $absensi->update(['status' => $isi['status'], 'catatan' => $isi['catatan'] ?? null]);
            }

            if ($request->hasFile('foto_bukti')) {
                $jurnal->update(['foto_bukti' => $request->file('foto_bukti')->store('jurnal-bukti', 'public')]);
            }

            $this->kembalikanKePending($jurnal);
        });

        AuditLog::catat('Ubah Jurnal', "Ubah jurnal #{$jurnal->id}", $jurnal);

        if ($sudahRevisi) {
            $jurnal->jadwal->kelas->pengurusUser()?->notify(new JurnalPerluDiperiksa($jurnal, hasilRevisi: true));
        }

        return redirect()->route('jurnal.index', ['lihat' => $jurnal->id])->with('success', 'Jurnal & presensi diperbarui.');
    }

    /**
     * Detail jurnal -- SELALU popup (fragment HTML tanpa layout), dibuka dari
     * Riwayat Jurnal lewat AJAX. Nggak ada lagi halaman penuh buat ini --
     * sengaja dihapus (dulu ada, masih bisa diakses langsung lewat URL
     * walau harusnya cuma popup, bikin bingung).
     */
    public function showFragment(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'absensis.siswa', 'verifikator');

        return view('guru.jurnal._detail-fragment', compact('jurnal'));
    }

    /** Hapus jurnal (soft delete). Hanya selama belum diverifikasi pengurus kelas. */
    public function destroy(Jurnal $jurnal): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa dihapus.');

        $label = $jurnal->jadwal->mapel->nama.' — '.$jurnal->jadwal->kelas->nama;
        $jurnal->delete();

        AuditLog::catat('Hapus Jurnal', "Hapus jurnal #{$jurnal->id} ({$label})", $jurnal);

        return redirect()->route('jurnal.index')->with('success', 'Jurnal dihapus.');
    }

    /** Setelah guru merevisi, jurnal kembali antre untuk diperiksa pengurus kelas. */
    private function kembalikanKePending(Jurnal $jurnal): void
    {
        if ($jurnal->status_verifikasi === 'revisi') {
            $jurnal->update([
                'status_verifikasi' => 'pending',
                'catatan_verifikasi' => null,
                'verifikator_id' => null,
            ]);
        }
    }

    /** Metode Pembelajaran dipilih lewat chip preset (`metode_pilihan`) atau ketik bebas (`metode_custom` saat "Lainnya"). Kolom `metode` di DB tetap teks biasa. */
    private function hitungMetode(array $data): ?string
    {
        if (($data['metode_pilihan'] ?? null) === 'lainnya') {
            return $data['metode_custom'] ?? null;
        }

        return self::METODE_LABEL[$data['metode_pilihan'] ?? ''] ?? null;
    }
}
