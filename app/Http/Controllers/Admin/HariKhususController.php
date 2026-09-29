<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HariKhusus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HariKhususController extends Controller
{
    public function index(): View
    {
        return view('admin.hari-khusus.index', [
            'hariKhusus' => HariKhusus::query()->orderBy('tanggal')->get(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('hariKhusus', [
            'id' => ['nullable', 'integer', 'exists:hari_khususes,id'],
            'tanggal' => ['required', 'date', Rule::unique('hari_khususes', 'tanggal')->ignore($request->integer('id'))],
            'nama' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(array_keys(HariKhusus::JENIS))],
            'jam_selesai' => ['exclude_unless:jenis,pulang_cepat', 'required', 'date_format:H:i'],
        ]);

        $hariKhusus = $request->filled('id') ? HariKhusus::findOrFail($data['id']) : new HariKhusus;
        $hariKhusus->fill([
            ...$data,
            'jam_selesai' => $data['jenis'] === 'pulang_cepat' ? $data['jam_selesai'] : null,
        ])->save();

        AuditLog::catat(
            $hariKhusus->wasRecentlyCreated ? 'Tambah Hari Khusus' : 'Ubah Hari Khusus',
            "{$hariKhusus->nama} pada {$hariKhusus->tanggal->translatedFormat('d M Y')} ({$hariKhusus->jenis})",
            $hariKhusus,
        );

        return redirect()->route('master.hari-khusus.index')->with('success', 'Hari khusus berhasil disimpan.');
    }

    public function destroy(HariKhusus $hariKhusus): RedirectResponse
    {
        AuditLog::catat('Hapus Hari Khusus', "{$hariKhusus->nama} pada {$hariKhusus->tanggal->translatedFormat('d M Y')}", $hariKhusus);
        $hariKhusus->delete();

        return back()->with('success', 'Hari khusus dihapus.');
    }
}
