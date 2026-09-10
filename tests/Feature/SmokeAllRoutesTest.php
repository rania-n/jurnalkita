<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurnal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Buka setiap halaman GET sebagai role yang tepat memakai data seeder.
 * Menangkap error 500 (view rusak, relasi null, dll) sebelum commit.
 */
class SmokeAllRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_admin_pages(): void
    {
        $admin = $this->user('admin@jurnalkita.test');
        foreach ([
            '/admin', '/admin/guru', '/admin/kelas', '/admin/siswa', '/admin/mapel',
            '/admin/jadwal-pelajaran', '/admin/jam-pelajaran', '/admin/jadwal-piket', '/admin/akun',
            '/profil',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk("GET {$url}");
        }
    }

    public function test_guru_pages(): void
    {
        $guru = $this->user('guru1@jurnalkita.test');
        foreach (['/guru', '/guru/jurnal', '/guru/jurnal/tambah', '/guru/piket', '/dispensasi', '/dispensasi-ajukan/baru', '/profil'] as $url) {
            $this->actingAs($guru)->get($url)->assertOk("GET {$url}");
        }

        $jurnal = Jurnal::where('guru_id', $guru->guru->id)->first();
        if ($jurnal) {
            $this->actingAs($guru)->get("/guru/jurnal/{$jurnal->id}")->assertOk();
            $this->actingAs($guru)->get("/guru/jurnal/{$jurnal->id}/presensi")->assertOk();
        }
    }

    public function test_sekretaris_pages(): void
    {
        $sekre = $this->user('kelas1@jurnalkita.test');
        foreach (['/sekretaris', '/sekretaris/jurnal', '/sekretaris/jurnal/pengganti', '/profil'] as $url) {
            $this->actingAs($sekre)->get($url)->assertOk("GET {$url}");
        }

        $kelas = $sekre->kelasSekretaris();
        $jurnal = Jurnal::whereHas('jadwal', fn ($q) => $q->where('kelas_id', $kelas->id))->first();
        if ($jurnal) {
            $this->actingAs($sekre)->get("/sekretaris/jurnal/{$jurnal->id}")->assertOk();
        }
    }

    public function test_waka_pages(): void
    {
        $waka = $this->user('waka@jurnalkita.test');
        foreach (['/waka', '/dispensasi', '/profil'] as $url) {
            $this->actingAs($waka)->get($url)->assertOk("GET {$url}");
        }

        $disp = Dispensasi::where('status_piket', 'approved')->first();
        if ($disp) {
            $this->actingAs($waka)->get("/dispensasi/{$disp->id}")->assertOk();
        }
    }
}
