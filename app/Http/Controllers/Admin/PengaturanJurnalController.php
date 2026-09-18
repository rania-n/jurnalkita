<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PengaturanJurnal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pengaturan skala sekolah buat aturan JAM isi jurnal guru -- lihat
 * migration create_pengaturan_jurnals_table buat penjelasan tiap mode.
 * Berguna misalnya buat kelas yang lagi PKL (nggak rutin masuk kelas tiap
 * hari, kadang butuh isi susulan) tanpa harus melonggarkan aturan buat
 * SEMUA kelas selamanya -- admin yang atur, bukan hardcode di kode.
 */
class PengaturanJurnalController extends Controller
{
    public function index(): View
    {
        return view('admin.pengaturan-jurnal.index', [
            'pengaturan' => PengaturanJurnal::ambil(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:'.implode(',', array_keys(PengaturanJurnal::MODE_LABEL))],
        ]);

        $pengaturan = PengaturanJurnal::ambil();
        $pengaturan->update($data);

        AuditLog::catat('Ubah Pengaturan Isi Jurnal', 'Mode isi jurnal diubah ke: '.PengaturanJurnal::MODE_LABEL[$data['mode']]);

        return redirect()->route('master.pengaturan-jurnal.index')->with('success', 'Pengaturan disimpan.');
    }
}
