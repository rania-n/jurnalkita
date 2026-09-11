<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispensasiController extends Controller
{
    private function pastikanPiket(): void
    {
        abort_unless(auth()->user()->isPiket(), 403, 'Hanya guru piket yang dapat mengakses ini.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->get('tab', 'semua');

        $query = Dispensasi::with('siswa.kelas', 'pengaju')->latest('tanggal')->latest('id');

        if ($user->role === 'waka') {
            // Waka: hanya yang sudah lolos piket
            $query->where('status_piket', 'approved');
        } else {
            // Guru: hanya pengajuan yang ia buat sendiri (sebagai piket).
            $query->where('diajukan_oleh_id', $user->id);
        }

        $query->when($tab === 'menunggu', fn ($q) => $q->where('status_akhir', 'pending'))
            ->when($tab === 'disetujui', fn ($q) => $q->where('status_akhir', 'approved'))
            ->when($tab === 'ditolak', fn ($q) => $q->where('status_akhir', 'rejected'));

        return view('dispensasi.index', [
            'items' => $query->paginate(15),
            'tab' => $tab,
            'bolehAjukan' => $user->isPiket(),
        ]);
    }

    public function create(): View
    {
        $this->pastikanPiket();

        return view('dispensasi.create', [
            'kelasList' => Kelas::with('siswas:id,kelas_id,nama,nis')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanPiket();

        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'jam_ke_mulai' => ['nullable', 'required_with:jam_ke_selesai', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['nullable', 'required_with:jam_ke_mulai', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'alasan' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $dispensasi = Dispensasi::create([
            ...collect($data)->except('surat')->all(),
            'diajukan_oleh_id' => auth()->id(),
            'surat_path' => $request->file('surat')?->store('dispensasi-surat', 'public'),
            // Pengaju = guru piket, jadi tahap piket otomatis lolos.
            'status_piket' => 'approved',
            'piket_id' => auth()->id(),
        ]);
        $dispensasi->segarkanStatusAkhir();

        AuditLog::catat('dispensasi.ajukan', "Ajukan dispensasi siswa #{$dispensasi->siswa_id}", $dispensasi);

        return redirect()->route('dispensasi.index')
            ->with('success', 'Dispensasi diajukan. Menunggu persetujuan Waka Kesiswaan.');
    }

    public function show(Dispensasi $dispensasi): View
    {
        $user = auth()->user();

        // Guru piket hanya boleh melihat pengajuannya sendiri; waka boleh semua.
        abort_unless($user->role === 'waka' || $dispensasi->diajukan_oleh_id === $user->id, 403);

        $dispensasi->load('siswa.kelas', 'pengaju', 'waka');

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi,
            'bisaWaka' => $user->role === 'waka' && $dispensasi->status_waka === 'pending',
            'bisaBatal' => $dispensasi->diajukan_oleh_id === $user->id
                && $dispensasi->status_waka === 'pending',
        ]);
    }

    /**
     * Batalkan pengajuan (soft delete).
     * Hanya oleh guru piket yang mengajukan, dan selama Waka belum memutuskan.
     */
    public function destroy(Dispensasi $dispensasi): RedirectResponse
    {
        $this->pastikanPiket();
        abort_unless(
            $dispensasi->diajukan_oleh_id === auth()->id(),
            403,
            'Hanya guru piket yang mengajukan yang bisa membatalkan.'
        );
        abort_unless(
            $dispensasi->status_waka === 'pending',
            403,
            'Sudah diputuskan Waka Kesiswaan, tidak bisa dibatalkan.'
        );

        $nama = $dispensasi->siswa->nama;
        $dispensasi->delete();

        AuditLog::catat('dispensasi.batal', "Batalkan dispensasi {$nama}", $dispensasi);

        return redirect()->route('dispensasi.index')->with('success', 'Pengajuan dispensasi dibatalkan.');
    }

    public function approveWaka(Dispensasi $dispensasi, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'waka', 403);
        abort_unless($dispensasi->status_waka === 'pending', 403);

        $data = $request->validate([
            'keputusan' => ['required', 'in:approved,rejected'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $dispensasi->update([
            'status_waka' => $data['keputusan'],
            'waka_id' => auth()->id(),
            'catatan_waka' => $data['catatan'] ?? null,
        ]);
        $dispensasi->segarkanStatusAkhir();

        AuditLog::catat('dispensasi.waka', "Waka {$data['keputusan']} dispensasi #{$dispensasi->id}", $dispensasi);

        return redirect()->route('dispensasi.index')->with(
            'success',
            $dispensasi->status_akhir === 'approved'
                ? 'Dispensasi disetujui. Presensi siswa otomatis diperbarui.'
                : 'Keputusan Waka disimpan.'
        );
    }
}
