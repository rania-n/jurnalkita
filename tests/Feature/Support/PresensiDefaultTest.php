<?php

namespace Tests\Feature\Support;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Support\HariSekolah;
use App\Support\PresensiDefault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug nyata yang ketauan: dispensasi "JP sekian sampai selesai hari itu"
 * (jam_ke_selesai kosong) nggak pernah ke-match ke jurnal yang jamnya
 * overlap, gara-gara query lama langsung banding "jam_ke_selesai >= jamMulai"
 * di SQL tanpa mikirin NULL (NULL >= angka apa pun hasilnya NULL/dianggap
 * false). Siswa yang harusnya default "Dispensasi" malah default "Hadir".
 */
class PresensiDefaultTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->siswa = Siswa::create(['kelas_id' => $this->kelas->id, 'nis' => '001', 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
    }

    private function dispensasiApproved(array $override = []): Dispensasi
    {
        $piket = User::factory()->role('guru')->create();

        return Dispensasi::create(array_merge([
            'siswa_id' => $this->siswa->id, 'diajukan_oleh_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Ke dokter',
            'status_piket' => 'approved', 'status_waka' => 'approved', 'status_akhir' => 'approved',
        ], $override));
    }

    public function test_dispensasi_sampai_selesai_hari_itu_kena_ke_jurnal_yang_overlap(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 9, 'jam_ke_selesai' => null]);

        // Jurnal JP 7-10 -- overlap sama dispensasi JP9-selesai (9 ada di rentang 7-10).
        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertSame('dispensasi', $hasil[$this->siswa->id]['status']);
    }

    public function test_dispensasi_sampai_selesai_hari_itu_tidak_kena_ke_jurnal_sebelum_jam_mulainya(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 9, 'jam_ke_selesai' => null]);

        // Jurnal JP 1-2 -- selesai sebelum dispensasi (JP9-selesai) mulai, nggak overlap.
        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 1, 2);

        $this->assertNotSame('dispensasi', $hasil[$this->siswa->id]['status']);
    }

    public function test_dispensasi_jam_tertentu_yang_overlap_tetap_kena_seperti_biasa(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 8, 'jam_ke_selesai' => 9]);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertSame('dispensasi', $hasil[$this->siswa->id]['status']);
    }

    public function test_dispensasi_jam_tertentu_yang_tidak_overlap_tidak_kena(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 2]);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertNotSame('dispensasi', $hasil[$this->siswa->id]['status']);
    }

    public function test_dispensasi_sepanjang_hari_selalu_kena_apapun_jam_jurnalnya(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => null, 'jam_ke_selesai' => null]);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 1, 2);

        $this->assertSame('dispensasi', $hasil[$this->siswa->id]['status']);
    }

    /**
     * Skenario asli yang dilaporin: dispensasi JP6-8, jurnal JP7-10 -- tumpang
     * tindih di JP7-8 doang, JP9-10 di luar jangkauan dispensasi. Statusnya
     * TETAP "Dispensasi" (ada tumpang tindih), tapi catatannya dikasih tau
     * sampai jam berapa beneran berlakunya, biar guru inget cek manual sisa
     * jamnya (siswa mungkin udah balik sebelum jurnal ini kelar).
     */
    public function test_dispensasi_yang_cuma_nutup_sebagian_jurnal_tetap_dispensasi_tapi_dikasih_catatan_jam(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 6, 'jam_ke_selesai' => 8, 'alasan' => 'Ke dokter gigi']);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertSame('dispensasi', $hasil[$this->siswa->id]['status']);
        $this->assertStringContainsString('Ke dokter gigi', $hasil[$this->siswa->id]['catatan']);
        $this->assertStringContainsString('JP 6–8', $hasil[$this->siswa->id]['catatan']);
    }

    public function test_dispensasi_yang_nutup_penuh_jurnal_tidak_dikasih_catatan_tambahan(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 7, 'jam_ke_selesai' => 10, 'alasan' => 'Lomba']);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertSame('dispensasi', $hasil[$this->siswa->id]['status']);
        $this->assertSame('Lomba', $hasil[$this->siswa->id]['catatan']);
    }

    public function test_siswa_tanpa_dispensasi_default_hadir(): void
    {
        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 7, 10);

        $this->assertSame('hadir', $hasil[$this->siswa->id]['status']);
    }

    /**
     * Bug lain yang ketauan: rule #2 ("ikut presensi dari jurnal LAIN yang
     * udah diisi hari ini") nurunin status "dispensasi" apa adanya dari
     * jurnal sebelumnya, TANPA mikir jam-nya udah lewat atau belum. Sakit/
     * izin/alpha wajar dianggap sepanjang hari, tapi dispensasi ada batas
     * waktunya -- kalau jurnal SEBELUMNYA (JP6-8) nyatet dispensasi tapi
     * jurnal yang lagi diisi (JP9) udah lewat dari jam itu, harusnya balik
     * "Hadir", BUKAN ikut-ikutan "Dispensasi" dari jurnal sebelumnya.
     */
    public function test_dispensasi_yang_udah_lewat_jamnya_tidak_ikut_kewarisin_dari_jurnal_sebelumnya(): void
    {
        $this->dispensasiApproved(['jam_ke_mulai' => 6, 'jam_ke_selesai' => 8]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $guru = User::factory()->role('guru')->create();
        $guruModel = Guru::create(['user_id' => $guru->id, 'nama' => 'Pak Guru']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guruModel->id,
            'hari' => HariSekolah::hariIni() ?? 'senin', 'jam_ke_mulai' => 6, 'jam_ke_selesai' => 8,
        ]);
        $jurnalJp68 = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guruModel->id, 'tanggal' => today(),
            'jam_ke_mulai' => 6, 'jam_ke_selesai' => 8, 'status_guru' => 'hadir',
            'materi' => 'Uji', 'status_verifikasi' => 'pending',
        ]);
        // Simulasikan hasil presensi JP6-8 yang bener2 "dispensasi" (sesuai jamnya).
        Absensi::create([
            'jurnal_id' => $jurnalJp68->id, 'siswa_id' => $this->siswa->id,
            'status' => 'dispensasi', 'catatan' => 'Ke dokter',
        ]);

        // Sekarang isi jurnal BARU buat JP9 -- dispensasinya (JP6-8) udah lewat.
        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 9, 9);

        $this->assertSame('hadir', $hasil[$this->siswa->id]['status']);
    }

    /** Status LAIN (sakit/izin/alpha) dari jurnal sebelumnya tetap boleh diwariskan -- itu emang wajar sepanjang hari. */
    public function test_status_sakit_dari_jurnal_sebelumnya_tetap_kewarisin(): void
    {
        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $guru = User::factory()->role('guru')->create();
        $guruModel = Guru::create(['user_id' => $guru->id, 'nama' => 'Pak Guru']);
        $jadwal = Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guruModel->id,
            'hari' => HariSekolah::hariIni() ?? 'senin', 'jam_ke_mulai' => 6, 'jam_ke_selesai' => 8,
        ]);
        $jurnalJp68 = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guruModel->id, 'tanggal' => today(),
            'jam_ke_mulai' => 6, 'jam_ke_selesai' => 8, 'status_guru' => 'hadir',
            'materi' => 'Uji', 'status_verifikasi' => 'pending',
        ]);
        Absensi::create([
            'jurnal_id' => $jurnalJp68->id, 'siswa_id' => $this->siswa->id,
            'status' => 'sakit', 'catatan' => null,
        ]);

        $hasil = PresensiDefault::untukKelas(collect([$this->siswa]), $this->kelas->id, today()->toDateString(), 9, 9);

        $this->assertSame('sakit', $hasil[$this->siswa->id]['status']);
    }
}
