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

    public function updateSemester(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'semester' => ['required', 'integer', 'in:1,2'],
        ]);

        $tahunAjaran = TahunAjaran::aktif();
        if (! $tahunAjaran) {
            return back()->with('error', 'Tahun ajaran aktif belum tersedia.');
        }

        $semesterLama = $tahunAjaran->semester;
        $tahunAjaran->update(['semester' => $data['semester']]);

        AuditLog::catat(
            'Perubahan Semester',
            "Tahun ajaran {$tahunAjaran->nama}: Semester {$semesterLama} → Semester {$data['semester']}.",
        );

        return back()->with('success', "Semester tahun ajaran {$tahunAjaran->nama} diubah ke Semester {$data['semester']}.");
    }
}
