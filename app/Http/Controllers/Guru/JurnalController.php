<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Notifications\JurnalPerluDiperiksa;
use App\Support\PresensiDefault;
use App\Support\Waktu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        'tanya_jawab' => 'Tanya Jawab',
        'lainnya' => 'Lainnya',
    ];

    private function guru()
    {
        return auth()->user()->guru ?? abort(403, 'Akun tidak terhubung ke data guru.');
    }

    private function milikSendiri(Jurnal $jurnal): void
    {
        abort_unless($jurnal->guru_id === $this->guru()->id, 403);
    }

    /* --------------------------------------------------------------- Riwayat */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');

        $jurnals = $this->guru()->jurnals()
            ->with('jadwal.kelas', 'jadwal.mapel')
            ->when($status !== 'semua', fn ($q) => $q->where('status_verifikasi', $status))
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        return view('guru.jurnal.index', compact('jurnals', 'status'));
    }

    /* ------------------------------------------------------------ Form baru */
    public function create(Request $request): View
    {
        $guru = $this->guru();
        $hariIni = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'][now()->dayOfWeek - 1] ?? null;
        $jadwals = $hariIni
            ? $guru->jadwals()->with('kelas', 'mapel')->where('hari', $hariIni)->orderBy('jam_ke_mulai')->get()
            : collect();

        // Semua jadwal guru (fallback kalau tidak ada jadwal hari ini)
        $semuaJadwal = $guru->jadwals()->with('kelas', 'mapel')->orderBy('hari')->orderBy('jam_ke_mulai')->get();

        $daftarJadwal = $jadwals->isNotEmpty() ? $jadwals : $semuaJadwal;

        $jpSekarang = Waktu::jpSekarang();
        // Jadwal yang jam-nya nyakup JP yang BENERAN lagi jalan detik ini (bukan
        // cuma "hari ini" -- guru bisa punya beberapa jadwal hari ini di jam
        // berbeda). Sengaja pakai jpAktifSekarang() (null kalau lagi bukan jam
        // pelajaran sama sekali -- sebelum JP1, istirahat, atau tengah malam),
        // BUKAN jpSekarang() yang punya fallback -- soalnya fallback itu bikin
        // "jam 1 pagi" kebaca kayak "JP1" terus salah ngunci ke jadwal yang
        // nggak relevan sama sekali cuma karena kebetulan nomernya cocok.
        $jpAktif = Waktu::jpAktifSekarang();
        $jadwalJpIni = $jpAktif !== null
            ? $jadwals->filter(fn ($j) => $j->jam_ke_mulai <= $jpAktif && $j->jam_ke_selesai >= $jpAktif)
            : collect();
        $jadwalTerkunci = ! $request->filled('jadwal') && $jadwalJpIni->count() === 1;

        $jadwalTerpilih = $request->filled('jadwal')
            ? $semuaJadwal->firstWhere('id', (int) $request->jadwal)
            : ($jadwalTerkunci
                ? $jadwalJpIni->first()
                // Cuma ada 1 opsi -> browser otomatis milih itu (placeholder "Pilih
                // jadwal" disembunyikan) walau URL nggak bawa ?jadwal=. Server ikut
                // anggap terpilih dari awal, biar presensinya langsung kelihatan
                // tanpa guru harus "milih ulang" jadwal yang sebenarnya cuma satu.
                : ($daftarJadwal->count() === 1 ? $daftarJadwal->first() : null));

        $siswas = collect();
        $presensiAwal = [];
        if ($jadwalTerpilih) {
            $jadwalTerpilih->loadMissing('kelas.siswas');
            $siswas = $jadwalTerpilih->kelas->siswas->sortBy('no_absen')->values();
            $presensiAwal = PresensiDefault::untukKelas(
                $siswas, $jadwalTerpilih->kelas_id, now()->toDateString(), $jadwalTerpilih->jam_ke_mulai, $jadwalTerpilih->jam_ke_selesai
            );
        }

        return view('guru.jurnal.create', [
            'jadwals' => $daftarJadwal,
            'jadwalTerpilih' => $jadwalTerpilih,
            'jadwalTerkunci' => $jadwalTerkunci,
            'jpSekarang' => $jpSekarang,
            'jpMaks' => 13,
            'siswas' => $siswas,
            'presensiAwal' => $presensiAwal,
            'metodeLabel' => self::METODE_LABEL,
            'metodeTerpilih' => old('metode_pilihan'),
            'metodeCustom' => old('metode_custom'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guru = $this->guru();

        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15'],
            'status_guru' => ['required', 'in:hadir,tugas,tidak_hadir'],
            'materi' => ['required_if:status_guru,hadir', 'nullable', 'string'],
            'metode_pilihan' => ['nullable', 'in:'.implode(',', array_keys(self::METODE_LABEL))],
            'metode_custom' => ['nullable', 'string', 'max:255'],
            'tugas_tambahan' => ['required_if:status_guru,tugas,tidak_hadir', 'nullable', 'string'],
            'alasan' => ['required_if:status_guru,tugas,tidak_hadir', 'nullable', 'string'],
            'presensi' => ['nullable', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
            'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
            'foto_bukti' => ['required', 'image', 'max:4096'],
        ]);

        $data['metode'] = $this->hitungMetode($data);

        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        abort_unless($jadwal->guru_id === $guru->id, 403);

        // Jam mulai & selesai SELALU ikut jadwal yang dipilih (bukan input klien) --
        // ini yang beneran dijadwalkan, guru nggak bisa ngarang jam sendiri lewat
        // request manual walau field di form udah dikunci di sisi tampilan.
        $data['jam_ke_mulai'] = $jadwal->jam_ke_mulai;
        $data['jam_ke_selesai'] = $jadwal->jam_ke_selesai;

        // Cegah jurnal ganda untuk jadwal yang sama di hari yang sama.
        $sudahAda = Jurnal::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        if ($sudahAda) {
            return redirect()->route('jurnal.show', $sudahAda)
                ->with('info', 'Jurnal untuk jadwal ini hari ini sudah dibuat.');
        }

        $presensiSubmit = $data['presensi'] ?? [];
        $presensiFallback = PresensiDefault::untukKelas(
            $jadwal->kelas->siswas, $jadwal->kelas_id, now()->toDateString(), $data['jam_ke_mulai'], $data['jam_ke_selesai']
        );
        $fotoPath = $request->file('foto_bukti')?->store('jurnal-bukti', 'public');

        $jurnal = DB::transaction(function () use ($data, $jadwal, $guru, $presensiSubmit, $presensiFallback, $fotoPath) {
            $jurnal = Jurnal::create([
                ...collect($data)->except(['presensi', 'foto_bukti', 'metode_pilihan', 'metode_custom'])->all(),
                'guru_id' => $guru->id,
                'tanggal' => now()->toDateString(),
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

        return redirect()->route('jurnal.show', $jurnal)
            ->with('success', 'Jurnal & presensi tersimpan.');
    }

    /* ------------------------------------------------------------- Ubah jurnal */
    public function edit(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        $jurnal->load('jadwal.kelas.siswas', 'jadwal.mapel', 'absensis');

        $siswas = $jurnal->jadwal->kelas->siswas->sortBy('no_absen')->values();
        $presensiAwal = $jurnal->absensis->mapWithKeys(fn ($a) => [
            $a->siswa_id => ['status' => $a->status, 'catatan' => $a->catatan],
        ])->all();

        // Metode lama disimpan sebagai teks bebas -- cocokkan ke preset kalau ada,
        // kalau nggak (atau metode custom dari sebelum ada preset ini) anggap "Lainnya".
        $metodeTerpilih = null;
        $metodeCustom = null;
        if ($jurnal->metode) {
            $cocok = array_search($jurnal->metode, self::METODE_LABEL, true);
            $metodeTerpilih = $cocok !== false ? $cocok : 'lainnya';
            $metodeCustom = $metodeTerpilih === 'lainnya' ? $jurnal->metode : null;
        }

        return view('guru.jurnal.edit', [
            ...compact('jurnal', 'siswas', 'presensiAwal'),
            'metodeLabel' => self::METODE_LABEL,
            'metodeTerpilih' => old('metode_pilihan', $metodeTerpilih),
            'metodeCustom' => old('metode_custom', $metodeCustom),
        ]);
    }

    public function update(Jurnal $jurnal, Request $request): RedirectResponse
    {
        $this->milikSendiri($jurnal);
        abort_unless($jurnal->bisaDiubah(), 403, 'Jurnal sudah diverifikasi, tidak bisa diubah.');

        // Jam mulai tidak ikut diubah — pakai nilai jurnal untuk validasi jam selesai.
        $request->merge(['jam_ke_mulai' => $jurnal->jam_ke_mulai]);

        $sudahRevisi = $jurnal->status_verifikasi === 'revisi';

        $data = $request->validate([
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'status_guru' => ['required', 'in:hadir,tugas,tidak_hadir'],
            'materi' => ['required_if:status_guru,hadir', 'nullable', 'string'],
            'metode_pilihan' => ['nullable', 'in:'.implode(',', array_keys(self::METODE_LABEL))],
            'metode_custom' => ['nullable', 'string', 'max:255'],
            'tugas_tambahan' => ['required_if:status_guru,tugas,tidak_hadir', 'nullable', 'string'],
            'alasan' => ['required_if:status_guru,tugas,tidak_hadir', 'nullable', 'string'],
            'presensi' => ['nullable', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,sakit,izin,alpha,dispensasi'],
            'presensi.*.catatan' => ['nullable', 'string', 'max:255'],
            // Foto wajib -- kecuali jurnal ini udah punya foto dari sebelumnya
            // (guru cuma ubah data lain, nggak wajib upload ulang fotonya).
            'foto_bukti' => [$jurnal->foto_bukti ? 'nullable' : 'required', 'image', 'max:4096'],
        ]);

        // Jam selesai SELALU ikut jadwal aslinya, sama kayak di store() -- field
        // di form udah dikunci di sisi tampilan, ini jaga-jaga di server juga.
        $data['jam_ke_selesai'] = $jurnal->jam_ke_selesai;

        $data['metode'] = $this->hitungMetode($data);

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

        return redirect()->route('jurnal.show', $jurnal)->with('success', 'Jurnal & presensi diperbarui.');
    }

    /* ---------------------------------------------------------------- Detail */
    public function show(Jurnal $jurnal): View
    {
        $this->milikSendiri($jurnal);
        $jurnal->load('jadwal.kelas', 'jadwal.mapel', 'absensis.siswa', 'verifikator');

        return view('guru.jurnal.show', compact('jurnal'));
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
