<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiswaController extends Controller
{
    /**
     * Detail satu siswa buat admin -- data + akunnya emang saling terhubung
     * (kelas, riwayat kehadiran dari jurnal), jadi ditampilkan kumpul di
     * sini. Sama semangatnya kayak Guru\SiswaController@show, cuma di sini
     * admin boleh lihat siswa mana saja (nggak dibatasi kelas yang diajar)
     * dan read-only sama persis (perubahan tetap lewat modal Ubah atau form
     * PKL massal, bukan dari halaman ini).
     */
    public function show(Siswa $siswa): View
    {
        $siswa->load('kelas');

        $absensis = Absensi::where('siswa_id', $siswa->id)
            ->with('jurnal.jadwal.mapel')
            ->whereHas('jurnal')
            ->get()
            ->sortByDesc(fn ($a) => $a->jurnal->tanggal)
            ->values();

        $rekap = $absensis->countBy('status');

        return view('admin.siswa.show', compact('siswa', 'absensis', 'rekap'));
    }

    /**
     * Saran Nomor Presensi otomatis -- dihitung dari BANYAKNYA siswa aktif di
     * kelas yang sama, yang namanya alfabetis lebih dulu dari nama ini (+1).
     * Cuma SARAN (admin masih bebas timpa manual) -- nggak menjamin unik
     * (nomor lama nggak digeser ulang cuma gara-gara ada siswa baru).
     */
    public function noAbsenOtomatis(Request $request): JsonResponse
    {
        $kelasId = $request->integer('kelas_id');
        $nama = trim((string) $request->query('nama'));

        if (! $kelasId || $nama === '') {
            return response()->json(['no_absen' => null]);
        }

        $urutan = Siswa::where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->when($request->filled('kecuali_id'), fn ($q) => $q->where('id', '!=', $request->integer('kecuali_id')))
            ->whereRaw('LOWER(nama) < ?', [strtolower($nama)])
            ->count();

        return response()->json(['no_absen' => $urutan + 1]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:siswas,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswas', 'nis')->ignore($request->id)->withoutTrashed()],
            'nama' => ['required', 'string', 'max:255'],
            'no_absen' => ['nullable', 'integer', 'min:1', 'max:99'],
            // no_hp SENGAJA tidak ada di sini -- diisi sekali lewat Manajemen Akun
            // ("Buat Akun"/"Ubah Akun"), biar nggak ada 2 tempat isi nomor yang beda.
            'jenis_kelamin' => ['required', 'in:L,P'],
            // Cuma 1 pengurus kelas per kelas (akunnya boleh dipakai di banyak HP
            // sekaligus, itu bukan masalah -- yang dibatasi jumlah ORANGnya).
            'jabatan' => ['required', 'in:anggota,pengurus', function ($attribute, $value, $fail) use ($request) {
                if ($value !== 'pengurus') {
                    return;
                }
                $sudahAdaPengurus = Siswa::where('kelas_id', $request->input('kelas_id'))
                    ->where('jabatan', 'pengurus')
                    ->when($request->filled('id'), fn ($q) => $q->where('id', '!=', $request->integer('id')))
                    ->exists();
                if ($sudahAdaPengurus) {
                    $fail('Kelas ini sudah memiliki pengurus kelas. Ubah pengurus lama menjadi "Anggota" terlebih dahulu apabila ingin menggantinya.');
                }
            }],
            'status' => ['nullable', 'in:aktif,lulus,pindah'],
        ]);
        // Checkbox HTML nggak ngirim apa-apa pas nggak dicentang, jadi nggak
        // bisa divalidasi lewat $request->validate() biasa -- dibaca manual.
        $data['pkl'] = $request->boolean('pkl');

        $siswa = $request->filled('id') ? Siswa::findOrFail($data['id']) : new Siswa;
        $baru = ! $siswa->exists;

        $siswa->fill($data)->save();

        AuditLog::catat($baru ? 'Tambah Siswa' : 'Ubah Siswa', "Siswa: {$siswa->nama}", $siswa);

        return back()->with('success', $baru ? 'Siswa ditambahkan.' : 'Siswa diperbarui.');
    }

    /**
     * Ubah status PKL banyak siswa sekaligus -- buat kelas yang PKL-nya cuma
     * SEBAGIAN siswa (beda dari status PKL per kelas yang berarti SEMUA
     * siswa kelas itu). Milih siswanya pakai cari-checkbox yang nama
     * opsinya udah disisipin nama kelas (lihat index.blade.php) -- jadi
     * ketik nama kelas di situ otomatis nyaring ke siswa sekelas itu doang,
     * nggak perlu filter kelas terpisah.
     */
    public function updatePklBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pkl' => ['required', 'boolean'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
        ]);

        $siswas = Siswa::where('status', 'aktif')->whereIn('id', $data['siswa_ids'])->get(['id', 'nama']);
        if ($siswas->count() !== count($data['siswa_ids'])) {
            return back()->with('error', 'Pilih siswa yang masih aktif. Siswa yang sudah lulus/pindah tidak diubah.');
        }

        Siswa::whereIn('id', $siswas->pluck('id'))->update(['pkl' => $data['pkl']]);

        $label = $data['pkl'] ? 'PKL' : 'bukan PKL';
        AuditLog::catat('Ubah Status PKL Siswa Massal', "{$siswas->count()} siswa diubah menjadi {$label}.");

        return back()->with('success', "Status PKL {$siswas->count()} siswa berhasil diperbarui menjadi {$label}.");
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $nama = $siswa->nama;
        $siswa->delete();

        AuditLog::catat('Hapus Siswa', "Hapus siswa: {$nama}", $siswa);

        return back()->with('success', 'Siswa dihapus.');
    }
}
