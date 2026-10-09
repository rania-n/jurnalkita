<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Semua endpoint polling /versi (initAutoRefresh() di app.js) wajib:
 *   1. balas 200 + {"versi": "..."} untuk role yang berhak -- termasuk tabel
 *      yang kolomnya beda-beda (audit_logs nggak punya updated_at, jurnals
 *      pakai soft delete, dll) supaya fingerprint-nya nggak bikin error 500;
 *   2. nilainya BERUBAH setelah ada perubahan data relevan, biar banner
 *      "Ada data baru dari perangkat lain" beneran kepakai.
 */
class VersiEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $guru;

    private User $sekretaris;

    private User $waka;

    private Guru $guruData;

    private Kelas $kelas;

    private Mapel $mapel;

    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->role('admin')->create();
        $this->guru = User::factory()->role('guru')->create();
        $this->guruData = Guru::create(['user_id' => $this->guru->id, 'nama' => 'Guru Uji']);
        JadwalPiket::create(['guru_id' => $this->guruData->id, 'hari' => 'senin']);

        $this->waka = User::factory()->role('waka')->create();

        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $this->jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guruData->id, 'hari' => 'senin',
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'ruang' => 'R 1',
        ]);

        // Pengurus kelas = siswa yang punya akun DAN jabatan 'pengurus'
        // (lihat User::isSekretaris()).
        $this->sekretaris = User::factory()->role('siswa')->create();
        $siswa = Siswa::create([
            'kelas_id' => $this->kelas->id, 'nis' => '001', 'nama' => 'Sekretaris Kelas',
            'jenis_kelamin' => 'L', 'user_id' => $this->sekretaris->id, 'jabatan' => 'pengurus',
        ]);
    }

    /** @return array<string, array{0: string, 1: string}> nama => [url, role yang boleh] */
    private function daftarVersi(): array
    {
        return [
            'admin.dashboard' => ['/admin/versi', 'admin'],
            'master.jadwal-pelajaran' => ['/admin/jadwal-pelajaran/versi', 'admin'],
            'master.akun.persetujuan' => ['/admin/akun-persetujuan/versi', 'admin'],
            'master.audit-log' => ['/admin/audit-log/versi', 'admin'],
            'jurnal (guru)' => ['/guru/jurnal/versi', 'guru'],
            'piket.monitor' => ['/piket/monitor/versi', 'guru'],
            'piket.presensi-siswa' => ['/piket/presensi-siswa/versi', 'guru'],
            'dispensasi' => ['/dispensasi/versi', 'guru'],
            'sekretaris.jurnal' => ['/sekretaris/jurnal/versi', 'siswa'],
        ];
    }

    public function test_semua_endpoint_versi_balas_200_dan_punya_versi(): void
    {
        foreach ($this->daftarVersi() as $nama => [$url, $role]) {
            $response = $this->actingAs($this->akun($role))->get($url);

            if ($response->status() === 403) {
                $this->fail("GET {$url} ({$nama}) kena 403 -- role '{$role}' nggak punya akses ke endpoint ini.");
            }

            $response->assertOk("GET {$url} ({$nama})");
            $this->assertIsString($response->json('versi'), "{$url} harus balikin string versi");
        }
    }

    public function test_audit_log_versi_tanpa_error_walau_tabelnya_tanpa_updated_at(): void
    {
        // audit_logs kolomnya hanya created_at (model-nya $timestamps = false).
        // Dulu fingerprint-nya max('updated_at') -> 500 Unknown column.
        AuditLog::catat('Uji Titik', 'Baris log pertama');

        $response = $this->actingAs($this->admin)->get('/admin/audit-log/versi');
        $response->assertOk();
        $this->assertStringNotContainsString('Unknown column', $response->getContent());
        $this->assertStringContainsString('created_at=', $response->json('versi'));
    }

    public function test_versi_audit_log_berubah_setelah_ada_log_baru(): void
    {
        $awal = $this->actingAs($this->admin)->get('/admin/audit-log/versi')->assertOk()->json('versi');

        AuditLog::catat('Uji Titik', 'Baris log kedua');

        $baru = $this->get('/admin/audit-log/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_riwayat_jurnal_guru_berubah_setelah_jurnal_baru(): void
    {
        $awal = $this->actingAs($this->guru)->get('/guru/jurnal/versi')->assertOk()->json('versi');

        Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guruData->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ]);

        $baru = $this->get('/guru/jurnal/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_dashboard_admin_berubah_setelah_jurnal_baru(): void
    {
        $awal = $this->actingAs($this->admin)->get('/admin/versi')->assertOk()->json('versi');

        Jurnal::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guruData->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ]);

        $baru = $this->get('/admin/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_monitor_piket_berubah_setelah_jurnal_baru(): void
    {
        // Monitor Piket default-nya rentang HARI INI, dan barisnya cuma
        // dijadwalkan untuk hari Senin-Jumat. Kalau test-nya jatuh hari
        // Sabtu/Minggu, rentangnya kosong dan versinya nggak akan pernah
        // berubah -- jadi dipin ke Senin, sama seperti PiketMonitorTest.
        $senin = now()->next(Carbon::MONDAY);
        $this->travelTo($senin);
        Carbon::setTestNow($senin);

        $jadwalSenin = Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guruData->id, 'hari' => 'senin',
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4, 'ruang' => 'R 1',
        ]);

        $awal = $this->actingAs($this->guru)->get('/piket/monitor/versi')->assertOk()->json('versi');

        Jurnal::create([
            'jadwal_id' => $jadwalSenin->id, 'guru_id' => $this->guruData->id, 'tanggal' => $senin,
            'jam_ke_mulai' => 3, 'jam_ke_selesai' => 4, 'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ]);

        $baru = $this->get('/piket/monitor/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_jadwal_pelajaran_berubah_setelah_jadwal_baru(): void
    {
        $awal = $this->actingAs($this->admin)->get('/admin/jadwal-pelajaran/versi')->assertOk()->json('versi');

        Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guruData->id, 'hari' => 'selasa',
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'ruang' => 'R 1',
        ]);

        $baru = $this->get('/admin/jadwal-pelajaran/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_persetujuan_akun_berubah_setelah_akun_baru(): void
    {
        $awal = $this->actingAs($this->admin)->get('/admin/akun-persetujuan/versi')->assertOk()->json('versi');

        User::factory()->pending()->create();

        $baru = $this->get('/admin/akun-persetujuan/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    public function test_versi_izin_keluar_ikut_berubah_kalau_anggota_grup_baru(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->buatSiswa()->id, 'diajukan_oleh_id' => $this->guru->id,
            'tanggal' => today(), 'alasan' => 'Keperluan keluarga',
            'status_piket' => 'approved', 'status_waka' => 'pending', 'status_akhir' => 'pending',
        ]);

        $awal = $this->actingAs($this->waka)->get('/dispensasi/versi')->assertOk()->json('versi');

        // Tambah anggota kelompok -- lihat Dispensasi::kelompokUtama(): anggota
        // grup nggak tampil sendiri di riwayat, tapi jumlah barisnya berubah,
        // jadi fingerprint-nya juga harus berubah (banner kepakai).
        Dispensasi::create([
            'siswa_id' => $this->buatSiswa()->id, 'diajukan_oleh_id' => $this->guru->id,
            'kelompok_id' => $dispensasi->kelompok_id, 'tanggal' => today(), 'alasan' => 'Bersama',
            'status_piket' => 'approved', 'status_waka' => 'pending', 'status_akhir' => 'pending',
        ]);

        $baru = $this->get('/dispensasi/versi')->assertOk()->json('versi');
        $this->assertNotSame($awal, $baru);
    }

    private function buatSiswa(): Siswa
    {
        return Siswa::create([
            'kelas_id' => $this->kelas->id, 'nis' => fake()->unique()->numerify('####'),
            'nama' => fake()->name(), 'jenis_kelamin' => 'L',
        ]);
    }

    private function akun(string $role): User
    {
        return match ($role) {
            'admin' => $this->admin,
            'guru' => $this->guru,
            'siswa' => $this->sekretaris,
            'waka' => $this->waka,
        };
    }
}
