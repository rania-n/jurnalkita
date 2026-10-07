<?php
$file = 'resources/views/guru/jurnal/_presensi-grid.blade.php';
$content = file_get_contents($file);

$search = "    \$statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    \$tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];";

$replace = "    \$statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'izin_keluar' => 'Izin Keluar', 'izin_terlambat' => 'Terlambat', 'alpha' => 'Alpha', 'dispensasi' => 'Dispensasi'];
    \$tones = ['hadir' => 'hadir', 'sakit' => 'sakit', 'izin' => 'izin', 'izin_keluar' => 'alpha', 'izin_terlambat' => 'alpha', 'alpha' => 'alpha', 'dispensasi' => 'dispen'];";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
