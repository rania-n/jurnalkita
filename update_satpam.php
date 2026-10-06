<?php

$file = 'app/Http/Controllers/SatpamController.php';
$content = file_get_contents($file);
$search = '    public function scan(Request $request): View';
$replace = "    public function dashboard(Request \$request): View
    {
        \$dispensasiKeluar = Dispensasi::with('siswa.kelas')
            ->where('status_akhir', 'approved')
            ->where('jenis', 'izin_keluar')
            ->whereDate('tanggal', today())
            ->whereNull('waktu_kembali')
            ->get();

        return view('dashboards.satpam', compact('dispensasiKeluar'));
    }

    public function konfirmasiKembali(Dispensasi \$dispensasi, Request \$request)
    {
        \$dispensasi->update(['waktu_kembali' => now()]);
        return redirect()->route('satpam.dashboard')->with('success', 'Siswa berhasil dikonfirmasi kembali ke sekolah.');
    }

    public function scan(Request \$request): View";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
