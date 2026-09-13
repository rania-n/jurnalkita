<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    /**
     * Pilihan simpel yang dulu masih dipertanyakan ("unduh manual? terjadwal?
     * disimpan ke mana?"): unduh langsung on-demand, TANPA disimpan di server
     * (tidak ada file backup yang numpuk / perlu dijaga aksesnya) dan TANPA
     * penjadwalan (di luar kebutuhan aplikasi sekolah sekecil ini -- admin
     * tinggal klik kapan perlu).
     */
    public function index(): View
    {
        $riwayat = AuditLog::with('user')
            ->where('aksi', 'Unduh Backup Database')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.backup.index', compact('riwayat'));
    }

    public function download(): Response
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = (string) config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $process = new Process([
            'mysqldump',
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--single-transaction',
            '--skip-lock-tables',
            $database,
        ], null, $password !== '' ? ['MYSQL_PWD' => $password] : null);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        AuditLog::catat('Unduh Backup Database', "Backup database {$database} diunduh");

        $filename = 'backup-jurnalkita-'.now()->format('Y-m-d_H-i-s').'.sql';

        return response($process->getOutput(), 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
