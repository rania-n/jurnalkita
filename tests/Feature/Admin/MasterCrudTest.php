<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role('admin')->create();
    }

    public function test_admin_can_create_update_delete_mapel(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mapel', ['kode' => 'X1', 'nama' => 'Uji'])
            ->assertRedirect();
        $this->assertDatabaseHas('mapels', ['kode' => 'X1', 'nama' => 'Uji']);

        $mapel = Mapel::first();

        $this->actingAs($admin)->post('/admin/mapel', ['id' => $mapel->id, 'kode' => 'X1', 'nama' => 'Uji Baru'])
            ->assertRedirect();
        $this->assertSame('Uji Baru', $mapel->fresh()->nama);

        $this->actingAs($admin)->delete("/admin/mapel/{$mapel->id}")->assertRedirect();
        $this->assertSoftDeleted('mapels', ['id' => $mapel->id]);
    }

    public function test_kode_mapel_dibuat_otomatis_kalau_dikosongkan(): void
    {
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matematika'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Matematika', 'kode' => 'MAT']);

        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Bahasa Indonesia'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Bahasa Indonesia', 'kode' => 'BI']);
    }

    public function test_kode_mapel_otomatis_tetap_boleh_diketik_manual(): void
    {
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matematika', 'kode' => 'MTK'])->assertRedirect();
        $this->assertDatabaseHas('mapels', ['nama' => 'Matematika', 'kode' => 'MTK']);
    }

    public function test_kode_mapel_otomatis_tidak_bentrok_kalau_sudah_dipakai(): void
    {
        Mapel::create(['kode' => 'MAT', 'nama' => 'Matematika']);

        // "Matriks" -> 3 huruf depan "MAT" juga, sama kayak yang sudah ada -> harus digeser.
        $this->actingAs($this->admin())->post('/admin/mapel', ['nama' => 'Matriks'])->assertRedirect();

        $this->assertDatabaseHas('mapels', ['nama' => 'Matriks', 'kode' => 'MAT2']);
    }

    public function test_validation_blocks_bad_input(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/kelas', ['tingkat' => 'ZZ', 'jurusan' => ''])
            ->assertSessionHasErrors(['tingkat', 'jurusan']);

        $this->assertDatabaseCount('kelas', 0);
    }

    public function test_kelas_name_is_generated(): void
    {
        $wali = Guru::create(['nama' => 'Bu Wali']);

        $this->actingAs($this->admin())->post('/admin/kelas', [
            'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 2, 'wali_id' => $wali->id, 'status' => 'aktif',
        ])->assertRedirect();

        $this->assertDatabaseHas('kelas', ['nama' => 'X RPL 2', 'jurusan' => 'RPL', 'nomor' => 2]);
    }

    public function test_kelas_xii_baru_otomatis_diberi_status_pkl(): void
    {
        $wali = Guru::create(['nama' => 'Bu Wali']);

        $this->actingAs($this->admin())->post('/admin/kelas', [
            'tingkat' => 'XII', 'jurusan' => 'RPL', 'nomor' => 1, 'wali_id' => $wali->id, 'status' => 'aktif',
        ])->assertRedirect();

        $this->assertDatabaseHas('kelas', ['nama' => 'XII RPL 1', 'status' => 'pkl']);
    }

    public function test_admin_bisa_mengubah_status_beberapa_kelas_aktif_sekaligus(): void
    {
        $kelasSatu = Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL', 'status' => 'aktif']);
        $kelasDua = Kelas::create(['nama' => 'XI TKJ 1', 'tingkat' => 'XI', 'jurusan' => 'TKJ', 'status' => 'aktif']);

        $this->actingAs($this->admin())->patch('/admin/kelas/status-massal', [
            'kelas_ids' => [$kelasSatu->id, $kelasDua->id], 'status' => 'pkl',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('pkl', $kelasSatu->fresh()->status);
        $this->assertSame('pkl', $kelasDua->fresh()->status);
    }

    public function test_kelas_xii_tahun_ajaran_aktif_tidak_bisa_diubah_ke_status_aktif(): void
    {
        $kelas = Kelas::create(['nama' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan' => 'RPL', 'status' => 'pkl']);

        $this->actingAs($this->admin())->patch('/admin/kelas/status-massal', [
            'kelas_ids' => [$kelas->id], 'status' => 'aktif',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('pkl', $kelas->fresh()->status);
    }

    public function test_pengaturan_status_massal_melindungi_kelas_arsip(): void
    {
        $tahunLama = TahunAjaran::create(['nama' => '2025/2026']);
        $tahunAktif = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $kelasLama = Kelas::create([
            'nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL',
            'tahun_ajaran_id' => $tahunLama->id, 'status' => 'aktif',
        ]);
        $kelasAktif = Kelas::create([
            'nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL',
            'tahun_ajaran_id' => $tahunAktif->id, 'status' => 'aktif',
        ]);

        $this->actingAs($this->admin())->patch('/admin/kelas/status-massal', [
            'kelas_ids' => [$kelasLama->id, $kelasAktif->id], 'status' => 'pkl',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('aktif', $kelasLama->fresh()->status);
        $this->assertSame('aktif', $kelasAktif->fresh()->status);
    }

    public function test_detail_kelas_menampilkan_roster_dan_jadwal(): void
    {
        $wali = Guru::create(['nama' => 'Bu Wali']);
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL', 'wali_id' => $wali->id]);
        Siswa::create(['kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L', 'no_absen' => 1]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $wali->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);

        $response = $this->actingAs($this->admin())->get("/admin/kelas/{$kelas->id}");

        $response->assertOk()
            ->assertSee('Bu Wali')
            ->assertSee('Budi')
            ->assertSee('Matematika')
            ->assertSee('Senin');
    }

    public function test_tombol_detail_kelas_ada_di_daftar(): void
    {
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->actingAs($this->admin())->get('/admin/kelas')
            ->assertOk()->assertSee(route('master.kelas.show', $kelas), false);
    }

    public function test_urutan_kelas_menggunakan_nomor_numerik_dan_tingkat(): void
    {
        Kelas::create(['nama' => 'X RPL 10', 'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 10]);
        Kelas::create(['nama' => 'X TKI 1', 'tingkat' => 'X', 'jurusan' => 'TKI', 'nomor' => 1]);
        Kelas::create(['nama' => 'X AN 1', 'tingkat' => 'X', 'jurusan' => 'AN', 'nomor' => 1]);
        Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL', 'nomor' => 1]);
        Kelas::create(['nama' => 'X RPL 2', 'tingkat' => 'X', 'jurusan' => 'RPL', 'nomor' => 2]);
        Kelas::create(['nama' => 'X PSPT 1', 'tingkat' => 'X', 'jurusan' => 'PSPT', 'nomor' => 1]);

        $this->actingAs($this->admin())->get('/admin/kelas')
            ->assertOk()
            ->assertSeeInOrder(['X TKI 1', 'X RPL 2', 'X RPL 10', 'X PSPT 1', 'X AN 1', 'XI RPL 1']);
    }

    public function test_status_pkl_bisa_disimpan_dan_ditampilkan(): void
    {
        $wali = Guru::create(['nama' => 'Bu Wali']);

        $this->actingAs($this->admin())->post('/admin/kelas', [
            'tingkat' => 'XII', 'jurusan' => 'RPL', 'nomor' => 1, 'wali_id' => $wali->id, 'status' => 'pkl',
        ])->assertRedirect();

        $this->assertDatabaseHas('kelas', ['nama' => 'XII RPL 1', 'status' => 'pkl']);
        $this->actingAs($this->admin())->get('/admin/kelas')->assertOk()->assertSee('PKL');
    }

    public function test_guru_utama_and_tambahan_mapel(): void
    {
        $utama = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $tambahan = Mapel::create(['kode' => 'FIS', 'nama' => 'Fisika']);

        $this->actingAs($this->admin())->post('/admin/guru', [
            'nama' => 'Bu Test',
            'mapel_utama_id' => $utama->id,
            'mapel_tambahan' => [$tambahan->id, $utama->id],
        ])->assertRedirect();

        $guru = Guru::first();
        $this->assertSame($utama->id, $guru->mapel_utama_id);
        $this->assertEqualsCanonicalizing([$tambahan->id], $guru->mapels->pluck('id')->all());
        $this->assertDatabaseHas('audit_logs', ['aksi' => 'Tambah Guru']);
    }

    public function test_non_admin_cannot_write(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->post('/admin/mapel', ['kode' => 'Z', 'nama' => 'Z'])
            ->assertRedirect(route('guru.dashboard'));
        $this->assertDatabaseCount('mapels', 0);
    }

    public function test_jam_pelajaran_bulk_save_replaces_rows(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori' => 'jumat',
            'mulai' => ['07:00', '07:40'],
            'selesai' => ['07:40', '08:20'],
        ])->assertRedirect();

        $this->assertDatabaseCount('jam_pelajarans', 2);
        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'jumat', 'jam_ke' => 1]);
    }

    public function test_generator_jp_membuat_jam_40_menit_dan_menyisipkan_jeda(): void
    {
        // Semua nilai SENGAJA dikirim sebagai string (kayak form HTML beneran
        // -- browser selalu ngirim string, bukan int PHP) -- pernah 500 karena
        // Carbon::addMinutes() versi ini nolak string, validate() nggak
        // nge-cast tipe otomatis. Lihat fix-nya di generate().
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran/generate', [
            'kategori' => 'senin_kamis', 'mulai' => '07:00', 'durasi_jp' => '40', 'jumlah_jp' => '4',
            'jeda' => [['setelah' => '2', 'durasi' => '20', 'label' => 'MBG']],
        ])->assertRedirect(route('master.jam-pelajaran.index', ['set' => 'senin_kamis']))
            ->assertSessionHas('success');

        $this->assertSame(4, JamPelajaran::where('kategori', 'senin_kamis')->count());
        $this->assertDatabaseHas('jam_pelajarans', [
            'kategori' => 'senin_kamis', 'jam_ke' => 3, 'mulai' => '08:40', 'selesai' => '09:20',
            'jeda_sebelum_menit' => 20, 'jeda_label' => 'MBG',
        ]);
    }

    public function test_generator_menolak_jeda_setelah_jp_terakhir(): void
    {
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40']);

        $this->actingAs($this->admin())->post('/admin/jam-pelajaran/generate', [
            'kategori' => 'senin_kamis', 'mulai' => '07:00', 'durasi_jp' => 40, 'jumlah_jp' => 2,
            'jeda' => [['setelah' => 2, 'durasi' => 20, 'label' => 'Istirahat']],
        ])->assertSessionHasErrors('jeda');

        $this->assertDatabaseCount('jam_pelajarans', 1);
    }

    /**
     * Kasus nyata: JP1 "Kegiatan" (07.00-07.40) nggak ada hari itu -> Matematika
     * yang tadinya JP2 (07.50-08.30, TANPA jeda dari JP1) maju jadi JP1 ngisi
     * slot 07.00-07.40 -- bukan cuma nomornya doang, jamnya beneran maju.
     * JP3 yang emang abis istirahat (keterangan "Jeda ...") jam-nya TETAP di
     * posisi asli (09.10), nggak ikut dipadetin ke jam berapa pun.
     */
    public function test_maju_geser_jam_beneran_kecuali_baris_abis_jeda(): void
    {
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40', 'keterangan' => 'Kegiatan']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '07:50', 'selesai' => '08:30']);
        JamPelajaran::create(['kategori' => 'senin_kamis', 'jam_ke' => 3, 'mulai' => '09:10', 'selesai' => '09:50', 'jeda_sebelum_menit' => 20, 'jeda_label' => 'Istirahat']);
        DB::table('jam_pelajaran_hari')->updateOrInsert(['hari' => 'senin'], ['kategori' => 'senin_kamis', 'updated_at' => now(), 'created_at' => now()]);

        $this->actingAs($this->admin())->post('/admin/jam-pelajaran/maju', [
            'kategori' => 'senin_kamis', 'jumlah_jp' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        // Matematika (dulu JP2) jadi JP1, ngisi 07.00-07.40 (durasi 40 menit tetap).
        // JamPelajaran pakai SoftDeletes -- filter whereNull('deleted_at') biar
        // nggak ketuker sama baris lama yang cuma disoft-delete, bukan beneran hilang.
        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'senin_kamis', 'jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:40', 'deleted_at' => null]);
        // JP abis-jeda (dulu JP3) jadi JP2, jamnya TETAP 09.10 (nggak dipadetin ke 07.40).
        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'senin_kamis', 'jam_ke' => 2, 'mulai' => '09:10', 'selesai' => '09:50', 'deleted_at' => null]);
    }

    public function test_admin_bisa_tambah_kategori_jam_pelajaran_baru(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori' => 'Bulan Ramadhan',
            'mulai' => ['07:30'], 'selesai' => ['08:00'],
        ])->assertRedirect();

        $this->assertDatabaseHas('jam_pelajarans', ['kategori' => 'bulan_ramadhan', 'jam_ke' => 1]);

        $this->actingAs($this->admin())->get('/admin/jam-pelajaran?set=bulan_ramadhan')
            ->assertOk()->assertSee('Bulan Ramadhan');
    }

    public function test_admin_bisa_hapus_kategori_custom(): void
    {
        $this->actingAs($this->admin())->post('/admin/jam-pelajaran', [
            'kategori' => 'Ujian',
            'mulai' => ['07:30'], 'selesai' => ['08:00'],
        ]);

        $this->actingAs($this->admin())->delete('/admin/jam-pelajaran/ujian')->assertRedirect();

        $this->assertSame(0, JamPelajaran::where('kategori', 'ujian')->count());
    }
}
