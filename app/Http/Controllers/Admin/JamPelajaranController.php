<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JamPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JamPelajaranController extends Controller
{
    /** Simpan ulang seluruh baris untuk satu kategori (editor multi-baris). */
    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Bukan enum lagi -- boleh kategori baru (mis. "Ramadhan", "Ujian"), bukan
            // cuma senin_kamis/jumat/khusus bawaan. Salah satu wajib: kategori (pilih yang
            // sudah ada) atau kategori_baru (ketik nama baru, lihat modal "Kategori Baru").
            'kategori' => ['required_without:kategori_baru', 'nullable', 'string', 'max:50'],
            'kategori_baru' => ['required_without:kategori', 'nullable', 'string', 'max:50'],
            'mulai' => ['required', 'array', 'min:1'],
            'mulai.*' => ['required', 'date_format:H:i'],
            'selesai' => ['required', 'array'],
            'selesai.*' => ['required', 'date_format:H:i'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:100'],
        ]);

        // Kategori baru ditulis manual admin (mis. "Ramadhan") -> disimpan sebagai slug
        // rapi (huruf kecil, spasi jadi underscore) supaya konsisten dipakai balik.
        if ($request->filled('kategori_baru')) {
            $data['kategori'] = Str::slug($request->string('kategori_baru'), '_');
        }

        DB::transaction(function () use ($data) {
            JamPelajaran::where('kategori', $data['kategori'])->delete();

            foreach (array_values($data['mulai']) as $i => $mulai) {
                JamPelajaran::create([
                    'jam_ke' => $i + 1,
                    'mulai' => $mulai,
                    'selesai' => $data['selesai'][$i] ?? $mulai,
                    'keterangan' => $data['keterangan'][$i] ?? null,
                    'kategori' => $data['kategori'],
                ]);
            }
        });

        AuditLog::catat('Ubah Jam Pelajaran', "Ubah jam pelajaran kategori {$data['kategori']}");

        return redirect()->route('master.jam-pelajaran.index', ['set' => $data['kategori']])
            ->with('success', 'Jam pelajaran disimpan.');
    }

    /** Hapus 1 kategori beserta semua jamnya (dipakai buat kategori custom yang salah bikin). */
    public function destroyKategori(string $kategori): RedirectResponse
    {
        JamPelajaran::where('kategori', $kategori)->delete();

        AuditLog::catat('Hapus Kategori Jam Pelajaran', "Hapus kategori {$kategori}");

        return redirect()->route('master.jam-pelajaran.index')->with('success', 'Kategori dihapus.');
    }
}
