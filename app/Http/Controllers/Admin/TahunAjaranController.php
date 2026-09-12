<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TahunAjaran;
use App\Support\KenaikanKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    public function naikKelas(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:20', Rule::unique('tahun_ajarans', 'nama')],
        ]);

        $lama = TahunAjaran::aktif();
        $hasil = KenaikanKelas::jalankan($data['nama']);

        AuditLog::catat(
            'Kenaikan Kelas',
            'Tahun ajaran '.($lama?->nama ?? '(belum ada)')." → {$data['nama']}: ".
            "{$hasil['kelas_naik']} kelas naik tingkat ({$hasil['siswa_naik']} siswa), ".
            "{$hasil['siswa_lulus']} siswa lulus.",
        );

        return back()->with('success', "Kelas naik ke tahun ajaran {$data['nama']}. {$hasil['siswa_naik']} siswa naik tingkat, {$hasil['siswa_lulus']} siswa lulus.");
    }
}
