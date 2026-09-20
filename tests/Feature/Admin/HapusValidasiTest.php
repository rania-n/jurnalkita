<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit "cek semua kemungkinan bug validasi hapus" -- soft delete di app ini
 * TIDAK memicu cascadeOnDelete/nullOnDelete di migration (itu cuma jalan pas
 * hard delete beneran), jadi tiap destroy() yang datanya masih dirujuk entitas
 * lain WAJIB divalidasi dulu, bukan cuma diandalkan ke FK level database.
 */
class HapusValidasiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_guru_tidak_bisa_dihapus_kalau_masih_punya_jadwal_mengajar(): void
    {
        $guru = Guru::create(['nama' => 'Pak Budi']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $this->actingAs($this->admin())->delete("/admin/guru/{$guru->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('gurus', ['id' => $guru->id, 'deleted_at' => null]);
    }

    public function test_guru_tidak_bisa_dihapus_kalau_masih_jadi_guru_pendamping(): void
    {
        $utama = Guru::create(['nama' => 'Pak Utama']);
        $pendamping = Guru::create(['nama' => 'Bu Pendamping']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $utama->id,
            'guru_pendamping_id' => $pendamping->id, 'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $this->actingAs($this->admin())->delete("/admin/guru/{$pendamping->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('gurus', ['id' => $pendamping->id, 'deleted_at' => null]);
    }

    public function test_guru_tidak_bisa_dihapus_kalau_masih_punya_jadwal_piket(): void
    {
        $guru = Guru::create(['nama' => 'Pak Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $this->actingAs($this->admin())->delete("/admin/guru/{$guru->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('gurus', ['id' => $guru->id, 'deleted_at' => null]);
    }

    public function test_guru_tidak_bisa_dihapus_kalau_masih_jadi_wali_kelas(): void
    {
        $guru = Guru::create(['nama' => 'Bu Wali']);
        Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $guru->id]);

        $this->actingAs($this->admin())->delete("/admin/guru/{$guru->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('gurus', ['id' => $guru->id, 'deleted_at' => null]);
    }

    public function test_guru_tanpa_keterikatan_apapun_boleh_dihapus(): void
    {
        $guru = Guru::create(['nama' => 'Pak Bebas']);

        $this->actingAs($this->admin())->delete("/admin/guru/{$guru->id}")
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSoftDeleted('gurus', ['id' => $guru->id]);
    }

    public function test_kelas_tidak_bisa_dihapus_kalau_masih_punya_siswa(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        $this->actingAs($this->admin())->delete("/admin/kelas/{$kelas->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'deleted_at' => null]);
    }

    public function test_kelas_tidak_bisa_dihapus_kalau_masih_punya_jadwal(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $guru = Guru::create(['nama' => 'Pak Budi']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $this->actingAs($this->admin())->delete("/admin/kelas/{$kelas->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'deleted_at' => null]);
    }

    public function test_kelas_kosong_boleh_dihapus(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->delete("/admin/kelas/{$kelas->id}")
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSoftDeleted('kelas', ['id' => $kelas->id]);
    }

    public function test_mapel_tidak_bisa_dihapus_kalau_dipakai_jadwal(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $guru = Guru::create(['nama' => 'Pak Budi']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $this->actingAs($this->admin())->delete("/admin/mapel/{$mapel->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('mapels', ['id' => $mapel->id, 'deleted_at' => null]);
    }

    public function test_mapel_tidak_bisa_dihapus_kalau_jadi_mapel_utama_guru(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Guru::create(['nama' => 'Pak Budi', 'mapel_utama_id' => $mapel->id]);

        $this->actingAs($this->admin())->delete("/admin/mapel/{$mapel->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('mapels', ['id' => $mapel->id, 'deleted_at' => null]);
    }

    public function test_mapel_tidak_bisa_dihapus_kalau_jadi_mapel_tambahan_guru(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $guru = Guru::create(['nama' => 'Pak Budi']);
        $guru->mapels()->attach($mapel->id);

        $this->actingAs($this->admin())->delete("/admin/mapel/{$mapel->id}")
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('mapels', ['id' => $mapel->id, 'deleted_at' => null]);
    }

    public function test_mapel_tanpa_keterikatan_boleh_dihapus(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);

        $this->actingAs($this->admin())->delete("/admin/mapel/{$mapel->id}")
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSoftDeleted('mapels', ['id' => $mapel->id]);
    }

    public function test_kategori_jam_pelajaran_bawaan_tidak_bisa_dihapus_kalau_masih_dipakai_jadwal(): void
    {
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $guru = Guru::create(['nama' => 'Pak Budi']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 1,
        ]);

        $this->actingAs($this->admin())->delete('/admin/jam-pelajaran/senin_kamis')
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'senin_kamis', 'jam_ke' => 1]);
    }

    public function test_kategori_jam_pelajaran_bawaan_boleh_dihapus_kalau_tidak_ada_jadwal_terkait(): void
    {
        JamPelajaran::create(['kategori' => 'jumat', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40']);

        $this->actingAs($this->admin())->delete('/admin/jam-pelajaran/jumat')
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame(0, JamPelajaran::where('kategori', 'jumat')->count());
    }

    public function test_kategori_custom_boleh_dihapus_walau_ada_jadwal_hari_terkait(): void
    {
        JamPelajaran::create(['kategori' => 'ramadhan', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $guru = Guru::create(['nama' => 'Pak Budi']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 1,
        ]);

        $this->actingAs($this->admin())->delete('/admin/jam-pelajaran/ramadhan')
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame(0, JamPelajaran::where('kategori', 'ramadhan')->count());
    }

    public function test_riwayat_jurnal_tetap_kebaca_walau_guru_jadwal_atau_mapelnya_sudah_dihapus(): void
    {
        $guru = Guru::create(['nama' => 'Pak Riwayat']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 1,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => now(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 1, 'status_guru' => 'hadir',
            'materi' => 'Uji', 'status_verifikasi' => 'pending',
        ]);

        // hapus paksa lewat model (bukan lewat route yang sekarang sudah divalidasi)
        // buat simulasikan data lama dari SEBELUM guard ini ada.
        $jadwal->delete();
        $guru->delete();
        $mapel->delete();
        $kelas->delete();

        $jurnal->refresh();
        $this->assertNotNull($jurnal->jadwal);
        $this->assertNotNull($jurnal->guru);
        $this->assertNotNull($jurnal->jadwal->kelas);
        $this->assertNotNull($jurnal->jadwal->mapel);
        $this->assertNotNull($jurnal->jadwal->guru);
    }
}
