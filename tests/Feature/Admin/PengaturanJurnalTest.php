<?php

namespace Tests\Feature\Admin;

use App\Models\PengaturanJurnal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanJurnalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->role('admin')->create();
    }

    public function test_default_mode_disiplin(): void
    {
        $this->assertSame('disiplin', PengaturanJurnal::mode());
    }

    public function test_halaman_pengaturan_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/pengaturan-jurnal')
            ->assertOk()->assertSee('Disiplin')->assertSee('Bebas isi hari ini')->assertSee('Bebas selamanya');
    }

    public function test_admin_bisa_ubah_mode(): void
    {
        $this->actingAs($this->admin)->post('/admin/pengaturan-jurnal', ['mode' => 'bebas_selamanya'])
            ->assertRedirect();

        $this->assertSame('bebas_selamanya', PengaturanJurnal::mode());
    }

    public function test_mode_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin)->post('/admin/pengaturan-jurnal', ['mode' => 'ngasal'])
            ->assertSessionHasErrors('mode');

        $this->assertSame('disiplin', PengaturanJurnal::mode());
    }

    public function test_bukan_admin_dilempar_ke_beranda_sendiri(): void
    {
        $guru = User::factory()->role('guru')->create();

        $this->actingAs($guru)->get('/admin/pengaturan-jurnal')
            ->assertRedirect(route($guru->homeRoute()));
    }
}
