<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPelajaranTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kelas $kelas;

    private Mapel $mapel;

    private Guru $guruA;

    private Guru $guruB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->role('admin')->create();
        $this->kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $this->mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $this->guruA = Guru::create(['nama' => 'Bu Sarah']);
        $this->guruB = Guru::create(['nama' => 'Pak Herman']);

        JamPelajaran::create(['jam_ke' => 1, 'mulai' => '07:00', 'selesai' => '07:45', 'kategori' => 'senin_kamis']);
        JamPelajaran::create(['jam_ke' => 2, 'mulai' => '07:45', 'selesai' => '08:30', 'kategori' => 'senin_kamis']);
        JamPelajaran::create(['jam_ke' => 3, 'mulai' => '08:30', 'selesai' => '09:15', 'kategori' => 'senin_kamis']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'kelas_id' => $this->kelas->id,
            'mapel_id' => $this->mapel->id,
            'guru_id' => $this->guruA->id,
            'hari' => 'senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 1,
        ], $override);
    }

    public function test_jadwal_kelas_yang_bentrok_ditolak(): void
    {
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 2]));

        // Kelas & hari SAMA, guru BEDA, jam numpuk (JP 2) -> tetep harus ditolak,
        // biar guru-nya beda bukan berarti kelasnya boleh diisi 2 mapel sekaligus.
        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->payload([
            'guru_id' => $this->guruB->id, 'jam_ke_mulai' => 2, 'jam_ke_selesai' => 3,
        ]))->assertSessionHas('error');

        $this->assertSame(1, Jadwal::count());
    }

    public function test_jadwal_kelas_yang_tidak_bentrok_boleh(): void
    {
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 1]));

        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->payload([
            'guru_id' => $this->guruB->id, 'jam_ke_mulai' => 2, 'jam_ke_selesai' => 2,
        ]))->assertSessionMissing('error');

        $this->assertSame(2, Jadwal::count());
    }

    public function test_ubah_jadwal_tidak_bentrok_sama_dirinya_sendiri(): void
    {
        $jadwal = Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 1]));

        // Ubah jadwal ini sendiri (jam tetap sama) -- jangan dianggap "bentrok
        // sama dirinya sendiri" cuma karena id-nya dikirim balik.
        $this->actingAs($this->admin)->post('/admin/jadwal-pelajaran', $this->payload([
            'id' => $jadwal->id, 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 1, 'ruang' => 'R1',
        ]))->assertSessionMissing('error');

        $this->assertSame('R1', $jadwal->fresh()->ruang);
    }

    public function test_hari_penuh_untuk_kelas_terdeteksi_benar(): void
    {
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 3]));

        $this->assertSame(['senin'], Jadwal::hariPenuhUntukKelas($this->kelas->id));
    }

    public function test_hari_belum_penuh_kalau_masih_ada_celah_jp(): void
    {
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 2])); // JP 3 masih kosong

        $this->assertSame([], Jadwal::hariPenuhUntukKelas($this->kelas->id));
    }

    public function test_hari_penuh_mengecualikan_jadwal_yang_lagi_diedit(): void
    {
        $jadwal = Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 3]));

        // Kalau jadwal ini sendiri yang lagi diedit, JP-nya jangan ikut
        // dihitung "menuhin" -- soalnya dia sendiri yang mau digeser/diubah.
        $this->assertSame([], Jadwal::hariPenuhUntukKelas($this->kelas->id, $jadwal->id));
    }

    public function test_dropdown_hari_terkunci_pas_pilih_kelas_yang_udah_penuh(): void
    {
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 3]));

        // ?kelas_id= -- HARUS sama persis dengan nama field select Kelas di
        // modal (lihat onchange di view), bukan nama param lain. Kalau beda,
        // fitur "kunci hari penuh" nggak pernah kepicu pas dropdown beneran
        // diganti user (ini pernah kejadian -- ketauan pas nge-cek ulang).
        $response = $this->actingAs($this->admin)->get('/admin/jadwal-pelajaran?kelas_id='.$this->kelas->id);

        $response->assertOk()->assertSee('Senin (Penuh)');
        $response->assertSee('data-auto-open-jadwal', false);
    }

    public function test_ganti_kelas_di_dropdown_tidak_ikut_kefilter_tabel_oleh_field_kosong_lain(): void
    {
        // onchange di select Kelas cuma nge-set 1 query param ('kelas_id'),
        // BUKAN submit seluruh form modal -- kalau formnya yang disubmit,
        // field lain yang masih kosong (hari, mapel_id, dst) ikut kebawa jadi
        // query string dan bikin tabel jadwal kefilter jadi kosong padahal
        // datanya ada. Test ini mastiin skenario itu nggak kejadian lagi.
        Jadwal::create($this->payload(['jam_ke_mulai' => 1, 'jam_ke_selesai' => 1]));

        $response = $this->actingAs($this->admin)->get('/admin/jadwal-pelajaran?kelas_id='.$this->kelas->id);

        $response->assertOk()->assertDontSee('Tidak ada jadwal yang cocok');
    }
}
