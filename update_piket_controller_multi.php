<?php

$file = '/home/fitra/jurnalkita/app/Http/Controllers/PiketController.php';
$content = file_get_contents($file);

// Replace validation rules
$content = preg_replace(
    '/\'siswa_id\' => \[\'required\', \'exists:siswas,id\'\],/',
    "'siswa_ids' => ['required', 'array', 'min:1'],\n            'siswa_ids.*' => ['exists:siswas,id'],",
    $content
);
$content = preg_replace(
    '/\'status\' => \[\'required\', \'in:sakit,izin,izin_terlambat\'\],/',
    "'status' => ['required', 'in:sakit,izin,izin_terlambat,dispensasi'],\n            'jam_ke_mulai' => ['nullable', 'integer', 'min:1', 'max:20'],\n            'jam_ke_selesai' => ['nullable', 'integer', 'min:1', 'max:20'],",
    $content
);

// Replace logic
$replacement = <<<PHP
        \$siswas = Siswa::where('status', 'aktif')->whereIn('id', \$data['siswa_ids'])->get();
        \$suratPath = \$request->file('surat')?->store('presensi-piket', 'public');
        \$jamMasuk = isset(\$data['jam_masuk']) ? (int) \$data['jam_masuk'] : null;
        \$jamKeMulai = isset(\$data['jam_ke_mulai']) ? (int) \$data['jam_ke_mulai'] : null;
        \$jamKeSelesai = isset(\$data['jam_ke_selesai']) ? (int) \$data['jam_ke_selesai'] : null;

        // Terlambat & izin biasa hanya berlaku 1 hari; sakit & dispensasi bisa multi-hari.
        \$tanggalMulai = Carbon::parse(\$data['tanggal']);
        \$tanggalSelesai = (in_array(\$data['status'], ['sakit', 'dispensasi']) && ! empty(\$data['tanggal_selesai']))
            ? Carbon::parse(\$data['tanggal_selesai'])
            : \$tanggalMulai;
        \$rentangTanggal = CarbonPeriod::create(\$tanggalMulai, \$tanggalSelesai);

        DB::transaction(function () use (\$data, \$rentangTanggal, \$siswas, \$suratPath, \$jamMasuk, \$jamKeMulai, \$jamKeSelesai) {
            foreach (\$siswas as \$siswa) {
                foreach (\$rentangTanggal as \$tgl) {
                    \$tglString = \$tgl->toDateString();
                    \$presensi = PresensiPiket::updateOrCreate(
                        ['siswa_id' => \$siswa->id, 'tanggal' => \$tglString],
                        [
                            'status' => \$data['status'],
                            'jam_masuk' => \$data['status'] === 'izin_terlambat' ? \$jamMasuk : null,
                            'jam_ke_mulai' => \$data['status'] === 'dispensasi' ? \$jamKeMulai : null,
                            'jam_ke_selesai' => \$data['status'] === 'dispensasi' ? \$jamKeSelesai : null,
                            'catatan' => \$data['catatan'] ?? null,
                            'surat_path' => \$suratPath ?? PresensiPiket::where('siswa_id', \$siswa->id)
                                ->whereDate('tanggal', \$tglString)->value('surat_path'),
                            'dicatat_oleh_id' => auth()->id(),
                        ]
                    );

                    \$jurnalsHariIni = Jurnal::whereDate('tanggal', \$tglString)
                        ->whereHas('jadwal', fn (\$query) => \$query->where('kelas_id', \$siswa->kelas_id))
                        ->with('absensis', 'jadwal')
                        ->get();

                    foreach (\$jurnalsHariIni as \$jurnal) {
                        // Untuk terlambat: JP sebelum jam_masuk = izin_terlambat, JP mulai jam_masuk ke atas = hadir.
                        if (\$data['status'] === 'izin_terlambat' && \$jamMasuk !== null) {
                            \$statusAbsensi = \$jurnal->jam_ke_selesai < \$jamMasuk
                                ? 'izin_terlambat'
                                : 'hadir';
                        } elseif (\$data['status'] === 'dispensasi' && (\$jamKeMulai !== null || \$jamKeSelesai !== null)) {
                            // Untuk dispensasi parsial: jika jurnal beririsan dengan jam dispensasi, maka dispensasi. Jika tidak, abaikan (bisa hadir/kosong).
                            \$isOverlap = true;
                            if (\$jamKeMulai !== null && \$jurnal->jam_ke_selesai < \$jamKeMulai) {
                                \$isOverlap = false; // Jurnal selesai sebelum dispensasi mulai
                            }
                            if (\$jamKeSelesai !== null && \$jurnal->jam_ke_mulai > \$jamKeSelesai) {
                                \$isOverlap = false; // Jurnal mulai setelah dispensasi selesai
                            }
                            \$statusAbsensi = \$isOverlap ? 'dispensasi' : 'hadir';
                        } else {
                            \$statusAbsensi = \$presensi->status;
                        }

                        // Jangan override status jadi 'hadir' jika sudah diabsen oleh guru sebelumnya (kecuali memang tujuannya membatalkan absen). 
                        // Tapi karena ini Piket, Piket bisa override. Kita override saja.
                        Absensi::updateOrCreate(
                            ['jurnal_id' => \$jurnal->id, 'siswa_id' => \$siswa->id],
                            ['status' => \$statusAbsensi, 'catatan' => \$presensi->catatan]
                        );
                    }
                }
            }
        });

        \$keterangan = \$data['status'] === 'izin_terlambat' && \$jamMasuk
            ? "terlambat (masuk mulai JP {\$jamMasuk})"
            : (\$data['status'] === 'dispensasi' && \$jamKeMulai ? "dispensasi (mulai JP {\$jamKeMulai})" : \$data['status']);
        AuditLog::catat('Catat Presensi Siswa oleh Piket', "{\$siswas->count()} siswa dicatat {\$keterangan} pada {\$data['tanggal']}");

        return redirect()->route('piket.presensi-siswa.index', [
            'tanggal' => \$data['tanggal'],
            'kelas_id' => \$request->kelas_id,
        ])->with('success', "Presensi {\$siswas->count()} siswa tersimpan dan disamakan ke jurnal kelas pada tanggal tersebut.");
PHP;

$content = preg_replace(
    '/\$siswa = Siswa::where\(\'status\', \'aktif\'\)->findOrFail\(\$data\[\'siswa_id\'\]\);.*?->with\(\'success\', "Presensi \{\$siswa->nama\} tersimpan dan disamakan ke jurnal kelas pada tanggal tersebut\."\);/s',
    $replacement,
    $content
);

file_put_contents($file, $content);

echo "Success";
