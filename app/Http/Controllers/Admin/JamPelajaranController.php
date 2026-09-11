<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JamPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JamPelajaranController extends Controller
{
    /** Simpan ulang seluruh baris untuk satu kategori (editor multi-baris). */
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'in:senin_kamis,jumat,khusus'],
            'mulai' => ['required', 'array'],
            'mulai.*' => ['required', 'date_format:H:i'],
            'selesai' => ['required', 'array'],
            'selesai.*' => ['required', 'date_format:H:i'],
        ]);

        DB::transaction(function () use ($data) {
            JamPelajaran::where('kategori', $data['kategori'])->delete();

            foreach (array_values($data['mulai']) as $i => $mulai) {
                JamPelajaran::create([
                    'jam_ke' => $i + 1,
                    'mulai' => $mulai,
                    'selesai' => $data['selesai'][$i] ?? $mulai,
                    'kategori' => $data['kategori'],
                ]);
            }
        });

        AuditLog::catat('Ubah Jam Pelajaran', "Ubah jam pelajaran kategori {$data['kategori']}");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', 'Jam pelajaran disimpan.');
    }
}
