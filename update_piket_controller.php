<?php

$file = 'app/Http/Controllers/PiketController.php';
$content = file_get_contents($file);

$search = "    public function simpanPresensiSiswa(Request \$request): RedirectResponse
    {
        \$this->pastikanBolehInputPresensi();

        \$data = \$request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:sakit,izin'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        \$siswa = Siswa::where('status', 'aktif')->findOrFail(\$data['siswa_id']);
        \$suratPath = \$request->file('surat')?->store('presensi-piket', 'public');

        DB::transaction(function () use (\$data, \$siswa, \$suratPath) {
            \$presensi = PresensiPiket::updateOrCreate(
                ['siswa_id' => \$siswa->id, 'tanggal' => \$data['tanggal']],
                [
                    'status' => \$data['status'],
                    'catatan' => \$data['catatan'] ?? null,
                    'surat_path' => \$suratPath ?? PresensiPiket::where('siswa_id', \$siswa->id)
                        ->whereDate('tanggal', \$data['tanggal'])->value('surat_path'),
                    'dicatat_oleh_id' => auth()->id(),
                ]
            );

            \$jurnalsHariIni = Jurnal::whereDate('tanggal', \$data['tanggal'])
                ->whereHas('jadwal', fn (\$query) => \$query->where('kelas_id', \$siswa->kelas_id))
                ->with('absensis')
                ->get();

            foreach (\$jurnalsHariIni as \$jurnal) {
                Absensi::updateOrCreate(
                    ['jurnal_id' => \$jurnal->id, 'siswa_id' => \$siswa->id],
                    ['status' => \$presensi->status, 'catatan' => \$presensi->catatan]
                );
            }
        });

        AuditLog::catat('Catat Presensi Siswa oleh Piket', \"{\$siswa->nama} dicatat {\$data['status']} pada {\$data['tanggal']}\");";

$replace = "    public function simpanPresensiSiswa(Request \$request): RedirectResponse
    {
        \$this->pastikanBolehInputPresensi();

        \$isUpdate = \App\Models\PresensiPiket::where('siswa_id', \$request->input('siswa_id'))
            ->whereDate('tanggal', \$request->input('tanggal'))->exists();

        \$data = \$request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:sakit,izin,izin_keluar,izin_terlambat'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'surat' => [\$isUpdate ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        \$siswa = Siswa::where('status', 'aktif')->findOrFail(\$data['siswa_id']);
        \$suratPath = \$request->file('surat')?->store('presensi-piket', 'public');
        
        \$tanggalMulai = \Carbon\Carbon::parse(\$data['tanggal']);
        \$tanggalSelesai = !empty(\$data['tanggal_selesai']) ? \Carbon\Carbon::parse(\$data['tanggal_selesai']) : \$tanggalMulai;
        \$rentangTanggal = \Carbon\CarbonPeriod::create(\$tanggalMulai, \$tanggalSelesai);

        DB::transaction(function () use (\$data, \$rentangTanggal, \$siswa, \$suratPath) {
            foreach (\$rentangTanggal as \$tgl) {
                \$tglString = \$tgl->toDateString();
                \$presensi = PresensiPiket::updateOrCreate(
                    ['siswa_id' => \$siswa->id, 'tanggal' => \$tglString],
                    [
                        'status' => \$data['status'],
                        'catatan' => \$data['catatan'] ?? null,
                        'surat_path' => \$suratPath ?? PresensiPiket::where('siswa_id', \$siswa->id)
                            ->whereDate('tanggal', \$tglString)->value('surat_path'),
                        'dicatat_oleh_id' => auth()->id(),
                    ]
                );

                \$jurnalsHariIni = Jurnal::whereDate('tanggal', \$tglString)
                    ->whereHas('jadwal', fn (\$query) => \$query->where('kelas_id', \$siswa->kelas_id))
                    ->with('absensis')
                    ->get();

                foreach (\$jurnalsHariIni as \$jurnal) {
                    Absensi::updateOrCreate(
                        ['jurnal_id' => \$jurnal->id, 'siswa_id' => \$siswa->id],
                        ['status' => \$presensi->status, 'catatan' => \$presensi->catatan]
                    );
                }
            }
        });

        AuditLog::catat('Catat Presensi Siswa oleh Piket', \"{\$siswa->nama} dicatat {\$data['status']} pada rentang {\$data['tanggal']} sampai {\$data['tanggal_selesai']}\");";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
