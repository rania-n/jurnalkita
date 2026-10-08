<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\HariKhusus;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\PengaturanJurnal;
use App\Models\PresensiPiket;
use App\Models\Siswa;
use App\Support\Versi;
use App\Support\Waktu;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Monitor Piket — bukan jadwal piket pribadi (lihat guru/piket.blade.php),
 * tapi pantauan guru piket: hari ini, tiap jam pelajaran di tiap kelas,
 * gurunya masuk/tidak hadir, atau jurnalnya belum diisi sama sekali.
 * Dikelompokkan per kelas ATAU per guru (bar pilih, bukan filter dropdown).
 *
 * Melihat (index/versi/jurnalDetail) TERBUKA buat semua guru, bukan cuma
 * yang piket hari ini -- cukup dijaga middleware route ('guru,waka,admin'),
 * tanpa gate tambahan di sini. MENCATAT presensi siswa (lihat
 * pastikanBolehInputPresensi()) dan MENGEKSPOR (lihat pastikanBolehEkspor())
 * tetap dikunci guru piket/waka/admin -- sama kayak Shopee: lihat produk
 * bebas, checkout (aksi/unduh) baru butuh login/wewenang lebih.
 */
class PiketController extends Controller
{
    private const LABEL_STATUS = ['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak Hadir'];

    private function pastikanBolehInputPresensi(): void
    {
        abort_unless(auth()->user()?->isPiket(), 403, 'Hanya guru piket yang dapat mencatat surat izin siswa.');
    }

    /** Ekspor (PDF ringkas/detail) beda dari sekadar lihat -- tetap dikunci guru piket/waka/admin. */
    private function pastikanBolehEkspor(): void
    {
        $user = auth()->user();
        abort_unless(
            in_array($user->role, ['waka', 'admin'], true) || $user->isPiket(),
            403,
            'Hanya guru piket, Waka, atau Admin yang dapat mengekspor.'
        );
    }

    public function presensiSiswa(Request $request): View
    {
        $this->pastikanBolehInputPresensi();

        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->date('tanggal')) : today();
        $kelasList = Kelas::orderedByHierarchy()->get();
        $kelas = $request->filled('kelas_id') ? Kelas::findOrFail($request->integer('kelas_id')) : null;
        $siswas = $kelas?->siswas()->where('status', 'aktif')->orderBy('no_absen')->get() ?? collect();
        $catatanPresensi = PresensiPiket::whereDate('tanggal', $tanggal)
            ->when($kelas, fn ($query) => $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelas->id)))
            ->with('siswa.kelas', 'dicatatOleh')
            ->latest('updated_at')
            ->get();
        $presensiTerpilih = $request->filled('siswa_id')
            ? PresensiPiket::where('siswa_id', $request->integer('siswa_id'))
                ->whereDate('tanggal', $tanggal)
                ->when($kelas, fn ($query) => $query->whereHas('siswa', fn ($q) => $q->where('kelas_id', $kelas->id)))
                ->first()
            : null;

        return view('piket.presensi-siswa', compact('tanggal', 'kelasList', 'kelas', 'siswas', 'catatanPresensi', 'presensiTerpilih'));
    }

    public function simpanPresensiSiswa(Request $request): RedirectResponse
    {
        $this->pastikanBolehInputPresensi();

        $isUpdate = PresensiPiket::where('siswa_id', $request->input('siswa_id'))
            ->whereDate('tanggal', $request->input('tanggal'))->exists();

        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            // tanggal_selesai hanya relevan untuk sakit (surat dokter multi-hari).
            // Izin biasa dan terlambat hanya 1 hari.
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:sakit,izin,izin_terlambat'],
            // Terlambat wajib isi JP mulai masuk.
            'jam_masuk' => ['nullable', 'required_if:status,izin_terlambat', 'integer', 'min:1', 'max:15'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'surat' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $siswa = Siswa::where('status', 'aktif')->findOrFail($data['siswa_id']);
        $suratPath = $request->file('surat')?->store('presensi-piket', 'public');
        $jamMasuk = isset($data['jam_masuk']) ? (int) $data['jam_masuk'] : null;

        // Terlambat hanya berlaku 1 hari; sakit bisa multi-hari jika ada surat dokter.
        $tanggalMulai = Carbon::parse($data['tanggal']);
        $tanggalSelesai = ($data['status'] === 'sakit' && ! empty($data['tanggal_selesai']))
            ? Carbon::parse($data['tanggal_selesai'])
            : $tanggalMulai;
        $rentangTanggal = CarbonPeriod::create($tanggalMulai, $tanggalSelesai);

        DB::transaction(function () use ($data, $rentangTanggal, $siswa, $suratPath, $jamMasuk) {
            foreach ($rentangTanggal as $tgl) {
                $tglString = $tgl->toDateString();
                $presensi = PresensiPiket::updateOrCreate(
                    ['siswa_id' => $siswa->id, 'tanggal' => $tglString],
                    [
                        'status' => $data['status'],
                        'jam_masuk' => $data['status'] === 'izin_terlambat' ? $jamMasuk : null,
                        'catatan' => $data['catatan'] ?? null,
                        'surat_path' => $suratPath ?? PresensiPiket::where('siswa_id', $siswa->id)
                            ->whereDate('tanggal', $tglString)->value('surat_path'),
                        'dicatat_oleh_id' => auth()->id(),
                    ]
                );

                $jurnalsHariIni = Jurnal::whereDate('tanggal', $tglString)
                    ->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $siswa->kelas_id))
                    ->with('absensis', 'jadwal')
                    ->get();

                foreach ($jurnalsHariIni as $jurnal) {
                    // Untuk terlambat: JP sebelum jam_masuk = izin_terlambat,
                    // JP mulai jam_masuk ke atas = hadir.
                    if ($data['status'] === 'izin_terlambat' && $jamMasuk !== null) {
                        $statusAbsensi = $jurnal->jam_ke_selesai < $jamMasuk
                            ? 'izin_terlambat'
                            : 'hadir';
                    } else {
                        $statusAbsensi = $presensi->status;
                    }

                    Absensi::updateOrCreate(
                        ['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id],
                        ['status' => $statusAbsensi, 'catatan' => $presensi->catatan]
                    );
                }
            }
        });

        $keterangan = $data['status'] === 'izin_terlambat' && $jamMasuk
            ? "terlambat (masuk mulai JP {$jamMasuk})"
            : $data['status'];
        AuditLog::catat('Catat Presensi Siswa oleh Piket', "{$siswa->nama} dicatat {$keterangan} pada {$data['tanggal']}");

        return redirect()->route('piket.presensi-siswa.index', [
            'tanggal' => $data['tanggal'],
            'kelas_id' => $siswa->kelas_id,
            'siswa_id' => $siswa->id,
        ])->with('success', "Presensi {$siswa->nama} tersimpan dan disamakan ke jurnal kelas pada tanggal tersebut.");
    }

    /** Popup jurnal hari ini di halaman login; akses publik dikendalikan Admin. */
    public function popupHariIni(): View
    {
        abort_unless(PengaturanJurnal::tampilkanDiLogin(), 404);

        $jurnals = Jurnal::whereDate('tanggal', today())
            ->with('jadwal.kelas', 'jadwal.mapel', 'guru')
            ->get()
            ->sortBy(fn (Jurnal $jurnal) => $jurnal->jam_ke_mulai)
            ->values();

        return view('piket._popup-jurnal-hari-ini', compact('jurnals'));
    }

    /** Detail jurnal publik hanya aktif saat Admin menyalakan fitur login. */
    public function popupDetailHariIni(Jurnal $jurnal): View
    {
        abort_unless(PengaturanJurnal::tampilkanDiLogin(), 404);
        abort_unless($jurnal->tanggal?->isToday(), 404);

        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'guru', 'absensis.siswa');

        return view('piket._popup-jurnal-hari-ini-detail', compact('jurnal'));
    }

    public function index(Request $request): View
    {
        [$dari, $sampai] = $this->rentangTanggal($request);
        $mode = $request->get('mode') === 'guru' ? 'guru' : 'kelas';
        $baris = $this->barisRange($dari, $sampai);
        // Dihitung dari SEMUA baris (sebelum filter status) -- biar jumlah di
        // tiap pil tab tetap nunjukin angka aslinya, bukan kepotong status
        // yang lagi aktif (sama kayak pola $jumlahTab di Riwayat Jurnal).
        $rekapTotal = $baris->countBy('status');

        // Status filter sekarang lewat query string (?status=...), BUKAN
        // cuma JS di klien lagi -- biar nggak reset balik ke "Semua" tiap
        // ganti tanggal (reload halaman). Lihat statusAktif di view.
        $statusAktif = in_array($request->query('status'), ['sudah_diisi', 'hadir', 'tidak_hadir', 'terlambat', 'tidak_diisi', 'belum_diisi'], true)
            ? $request->query('status') : '';

        $barisTampil = $baris;
        if ($statusAktif) {
            if ($statusAktif === 'sudah_diisi') {
                $barisTampil = $baris->whereIn('status', ['hadir', 'tidak_hadir', 'terlambat'])->values();
            } else {
                $barisTampil = $baris->where('status', $statusAktif)->values();
            }
        }

        $grup = $barisTampil
            ->groupBy(fn ($b) => $mode === 'guru' ? $b['jadwal']->guru_id : $b['jadwal']->kelas_id)
            ->map(function (Collection $rows, $id) use ($mode) {
                $contoh = $rows->first()['jadwal'];

                return [
                    'id' => $id,
                    'label' => $mode === 'guru' ? $contoh->guru->nama : $contoh->kelas->nama,
                    // Diurutkan tanggal dulu baru jam -- rentang lebih dari 1
                    // hari bisa punya jadwal yang SAMA (JP-nya) di tanggal
                    // beda-beda, jangan sampai keurut cuma dari jam-nya doang.
                    'rows' => $rows->sortBy(fn ($b) => $b['tanggal'].sprintf('%02d', $b['jadwal']->jam_ke_mulai))->values(),
                    'rekap' => $rows->countBy('status'),
                ];
            })
            ->sortBy('label')
            ->values();

        return view('piket.monitor', [
            'dari' => $dari,
            'sampai' => $sampai,
            'mode' => $mode,
            'grup' => $grup,
            'rekapTotal' => $rekapTotal,
            'statusAktif' => $statusAktif,
        ]);
    }

    /** Endpoint ringan buat di-poll (initAutoRefresh()) -- lihat App\Support\Versi. */
    public function versi(Request $request): JsonResponse
    {
        [$dari, $sampai] = $this->rentangTanggal($request);
        $haris = collect(CarbonPeriod::create($dari, $sampai))
            ->map(fn ($d) => ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$d->dayOfWeek - 1] ?? null)
            ->filter()->unique();
        $jadwalIds = Jadwal::whereIn('hari', $haris)->pluck('id');

        return response()->json([
            'versi' => Versi::dari(Jurnal::whereIn('jadwal_id', $jadwalIds)
                ->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai)),
        ]);
    }

    /** Ekspor ringkas rentang tanggal terpilih, semua kelompok — cuma baris jam/status, tanpa presensi. */
    public function ekspor(Request $request)
    {
        $this->pastikanBolehEkspor();

        [$dari, $sampai] = $this->rentangTanggal($request);
        $baris = $this->barisRange($dari, $sampai);

        AuditLog::catat('Ekspor Ringkasan Piket', "Ekspor ringkas monitor piket {$dari->toDateString()} s/d {$sampai->toDateString()} ({$baris->count()} baris)");

        $totalHadir = $baris->filter(fn ($b) => $b['status'] === 'hadir')->count();
        $totalTidakHadir = $baris->filter(fn ($b) => $b['status'] === 'tidak_hadir')->count();
        $totalTerlambat = $baris->filter(fn ($b) => $b['status'] === 'terlambat')->count();
        $totalTidakDiisi = $baris->filter(fn ($b) => $b['status'] === 'tidak_diisi')->count();
        $totalBelumDiisi = $baris->filter(fn ($b) => $b['status'] === 'belum_diisi')->count();

        $rows = $baris
            ->sortBy(fn ($b) => $b['tanggal'].sprintf('%02d', $b['jadwal']->jam_ke_mulai))
            ->map(function ($b) {
                return [
                    'tanggal' => $b['tanggal'],
                    'jamKe' => "{$b['jadwal']->jam_ke_mulai}-{$b['jadwal']->jam_ke_selesai}",
                    'kelas' => $b['jadwal']->kelas->nama,
                    'mapel' => $b['jadwal']->mapel->nama,
                    'guru' => $b['jadwal']->guru->nama,
                    'statusLabel' => $b['statusLabel'],
                    'materi' => $b['jurnal']->materi ?? '-',
                ];
            })->values()->all();

        $pdf = Pdf::loadView('pdf.monitor-piket-ringkas', [
            'judul' => 'Laporan Ringkasan Monitor Piket',
            'dari' => $dari,
            'sampai' => $sampai,
            'rekap' => [
                'baris' => $rows,
                'totalHadir' => $totalHadir,
                'totalTidakHadir' => $totalTidakHadir,
                'totalTerlambat' => $totalTerlambat,
                'totalTidakDiisi' => $totalTidakDiisi,
                'totalBelumDiisi' => $totalBelumDiisi,
            ],
        ])->setPaper('a4', 'portrait');

        $namaFile = $dari->isSameDay($sampai)
            ? 'monitor-piket-ringkas-'.$dari->toDateString().'.pdf'
            : 'monitor-piket-ringkas-'.$dari->toDateString().'-sd-'.$sampai->toDateString().'.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * Ekspor lengkap satu kelompok (satu kelas ATAU satu guru) pada tanggal tsb:
     * tiap jadwal + materi/metode + daftar presensi siswa satu-satu.
     */
    public function eksporDetail(Request $request, string $tipe, int $id)
    {
        $this->pastikanBolehEkspor();
        abort_unless(in_array($tipe, ['kelas', 'guru'], true), 404);

        [$dari, $sampai] = $this->rentangTanggal($request);
        $rentangBeda = ! $dari->isSameDay($sampai);
        $baris = $this->barisRange($dari, $sampai, denganPresensi: true)
            ->filter(fn ($b) => ($tipe === 'guru' ? $b['jadwal']->guru_id : $b['jadwal']->kelas_id) === $id)
            ->sortBy(fn ($b) => $b['tanggal'].sprintf('%02d', $b['jadwal']->jam_ke_mulai));

        abort_if($baris->isEmpty(), 404);

        $label = $tipe === 'guru' ? $baris->first()['jadwal']->guru->nama : $baris->first()['jadwal']->kelas->nama;

        AuditLog::catat('Ekspor Detail Piket', "Ekspor detail monitor piket — {$tipe} {$label}, {$dari->toDateString()} s/d {$sampai->toDateString()}");

        $barisData = [];
        foreach ($baris as $b) {
            $jadwal = $b['jadwal'];
            $jam = "JP {$jadwal->jam_ke_mulai}-{$jadwal->jam_ke_selesai}";
            // Tanggal ikut ditempel ke depan kalau rentangnya lebih dari 1
            // hari -- biar tetap bisa dibedain baris ini punya hari yang mana.
            if ($rentangBeda) {
                $jam = \Illuminate\Support\Carbon::parse($b['tanggal'])->translatedFormat('d M').' · '.$jam;
            }
            $lawan = $tipe === 'guru' ? $jadwal->kelas->nama : $jadwal->guru->nama;

            if (! $b['jurnal']) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => 'Belum Diisi',
                    'materi' => '-',
                    'metode' => '-',
                    'noAbsen' => '-',
                    'siswa' => '-',
                    'statusSiswa' => '-',
                    'catatanSiswa' => '-',
                ];

                continue;
            }

            $jurnal = $b['jurnal'];
            $absensis = $jurnal->absensis->sortBy('siswa.no_absen');

            if ($absensis->isEmpty()) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => $b['statusLabel'],
                    'materi' => $jurnal->materi,
                    'metode' => $jurnal->metode ?? '-',
                    'noAbsen' => '-',
                    'siswa' => '-',
                    'statusSiswa' => '-',
                    'catatanSiswa' => '-',
                ];

                continue;
            }

            foreach ($absensis as $a) {
                $barisData[] = [
                    'jam' => $jam,
                    'mapel' => $jadwal->mapel->nama,
                    'lawan' => $lawan,
                    'statusGuru' => $b['statusLabel'],
                    'materi' => $jurnal->materi,
                    'metode' => $jurnal->metode ?? '-',
                    'noAbsen' => $a->siswa->no_absen ?? '-',
                    'siswa' => $a->siswa->nama,
                    'statusSiswa' => ucfirst($a->status),
                    'catatanSiswa' => $a->catatan ?? '-',
                ];
            }
        }

        $pdf = Pdf::loadView('pdf.monitor-piket-detail', [
            'judul' => "Laporan Detail Monitor Piket - {$label}",
            'dari' => $dari,
            'sampai' => $sampai,
            'tipe' => $tipe,
            'targetNama' => $label,
            'baris' => $barisData,
        ])->setPaper('a4', 'landscape');

        $namaFile = $rentangBeda
            ? "monitor-piket-{$tipe}-".Str::slug($label).'-'.$dari->toDateString().'-sd-'.$sampai->toDateString().'.pdf'
            : "monitor-piket-{$tipe}-".Str::slug($label).'-'.$dari->toDateString().'.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * Fragment HTML (bukan halaman penuh) buat popup "lihat detail" di Monitor
     * Piket -- guru piket/waka/admin boleh lihat jurnal GURU LAIN di sini
     * (read-only, beda dari JurnalController::show() yang dikunci milik sendiri).
     */
    public function jurnalDetail(Jurnal $jurnal): View
    {
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'guru', 'absensis.siswa');

        return view('piket._jurnal-detail-fragment', compact('jurnal'));
    }

    /**
     * Rentang tanggal (Dari/Sampai) -- default HARI INI doang (dari=sampai)
     * kalau nggak ada parameter. Nggak boleh ada yang tanggal masa depan
     * (lihat max= di view) -- diklem ke hari ini kalau kelolosan.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rentangTanggal(Request $request): array
    {
        $dari = $request->filled('dari')
            ? Carbon::parse($request->date('dari'))
            : ($request->filled('tanggal') ? Carbon::parse($request->date('tanggal')) : today());
        $sampai = $request->filled('sampai') ? Carbon::parse($request->date('sampai')) : $dari->copy();

        if ($dari->isFuture()) {
            $dari = today();
        }
        if ($sampai->isFuture()) {
            $sampai = today();
        }
        if ($sampai->lt($dari)) {
            $sampai = $dari->copy();
        }

        return [$dari, $sampai];
    }

    /** Gabungan baris() buat tiap tanggal dalam rentang $dari..$sampai (inklusif). */
    private function barisRange(Carbon $dari, Carbon $sampai, bool $denganPresensi = false): Collection
    {
        $hasil = collect();
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
            $hasil = $hasil->merge($this->baris($d->copy(), $denganPresensi));
        }

        return $hasil->values();
    }

    /**
     * Satu baris per jadwal (kelas+jam) pada hari yang sama dengan tanggal $tanggal,
     * digabung dengan jurnal (kalau sudah diisi) pada tanggal itu persis.
     */
    private function baris(Carbon $tanggal, bool $denganPresensi = false): Collection
    {
        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][$tanggal->dayOfWeek - 1] ?? null;

        if (! $hari) {
            return collect(); // Sabtu/Minggu — tidak ada jadwal pelajaran.
        }

        if (HariKhusus::untukTanggal($tanggal)?->jenis === 'tanpa_kbm') {
            return collect();
        }

        $jadwals = Jadwal::where('hari', $hari)
            ->with('kelas', 'mapel', 'guru')
            ->get()
            ->reject(fn (Jadwal $jadwal) => Waktu::jadwalDitiadakan($jadwal, $tanggal));

        $jurnalQuery = Jurnal::whereIn('jadwal_id', $jadwals->pluck('id'))->whereDate('tanggal', $tanggal);
        if ($denganPresensi) {
            $jurnalQuery->with('absensis.siswa');
        }
        $jurnals = $jurnalQuery->get()->keyBy('jadwal_id');

        return $jadwals
            ->sortBy([['kelas.nama', 'asc'], ['jam_ke_mulai', 'asc']])
            ->map(function (Jadwal $jadwal) use ($jurnals, $tanggal) {
                $jurnal = $jurnals->get($jadwal->id);

                if ($jurnal) {
                    if ($jurnal->terlambat) {
                        $status = 'terlambat';
                        $labelHadir = self::LABEL_STATUS[$jurnal->status_guru] ?? $jurnal->status_guru;
                        $statusLabel = "Terlambat ($labelHadir)";
                    } else {
                        $status = $jurnal->status_guru;
                        $statusLabel = self::LABEL_STATUS[$jurnal->status_guru] ?? $jurnal->status_guru;
                    }
                } else {
                    $isLewat = $tanggal->isPast() && ! $tanggal->isToday();
                    if (! $isLewat) {
                        $isLewat = Waktu::jamKeSelesai($jadwal->jam_ke_selesai, $tanggal)->isPast();
                    }

                    if ($isLewat) {
                        $status = 'tidak_diisi';
                        $statusLabel = 'Tidak Diisi';
                    } else {
                        $status = 'belum_diisi';
                        $statusLabel = 'Belum Diisi';
                    }
                }

                return [
                    'jadwal' => $jadwal,
                    'jurnal' => $jurnal,
                    'tanggal' => $tanggal->toDateString(),
                    'status' => $status,
                    'statusLabel' => $statusLabel,
                ];
            })
            ->values();
    }
}
