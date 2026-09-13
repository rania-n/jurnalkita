<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\DispensasiDiputuskan;
use App\Notifications\JurnalPerluRevisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_notifikasi_saat_jurnal_diminta_revisi(): void
    {
        $guruUser = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $guruUser->id, 'nama' => 'Pak Guru']);

        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $sekretaris = User::factory()->role('siswa')->create();
        Siswa::create([
            'user_id' => $sekretaris->id, 'kelas_id' => $kelas->id, 'nis' => '001',
            'nama' => 'Ketua Kelas', 'jenis_kelamin' => 'L', 'no_absen' => 1, 'jabatan' => 'pengurus',
        ]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $jadwal = Jadwal::create([
            'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'guru_id' => $guru->id,
            'hari' => 'senin', 'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2,
        ]);
        $jurnal = Jurnal::create([
            'jadwal_id' => $jadwal->id, 'guru_id' => $guru->id, 'tanggal' => today(),
            'jam_ke_mulai' => 1, 'jam_ke_selesai' => 2, 'status_guru' => 'hadir', 'materi' => 'Bab 1',
        ]);

        Notification::fake();

        $this->actingAs($sekretaris)
            ->post("/sekretaris/jurnal/{$jurnal->id}/verifikasi", [
                'keputusan' => 'revisi', 'catatan' => 'Materi kurang lengkap',
            ])->assertRedirect('/sekretaris/jurnal');

        Notification::assertSentTo($guruUser, JurnalPerluRevisi::class);
    }

    public function test_guru_piket_dapat_notifikasi_saat_dispensasi_diputuskan_waka(): void
    {
        $piket = User::factory()->role('guru')->create();
        $guru = Guru::create(['user_id' => $piket->id, 'nama' => 'Guru Piket']);
        JadwalPiket::create(['guru_id' => $guru->id, 'hari' => 'senin']);

        $waka = User::factory()->role('waka')->create();
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $siswa = Siswa::create([
            'kelas_id' => $kelas->id, 'nis' => '001', 'nama' => 'Budi',
            'jenis_kelamin' => 'L', 'no_absen' => 1,
        ]);

        $d = Dispensasi::create([
            'siswa_id' => $siswa->id, 'diajukan_oleh_id' => $piket->id, 'piket_id' => $piket->id,
            'tanggal' => today(), 'alasan' => 'Lomba', 'status_piket' => 'approved',
        ]);
        $d->segarkanStatusAkhir();

        Notification::fake();

        $this->actingAs($waka)->post("/dispensasi/{$d->id}/waka", ['keputusan' => 'approved'])
            ->assertRedirect('/dispensasi');

        Notification::assertSentTo($piket, DispensasiDiputuskan::class);
    }

    public function test_halaman_notifikasi_tampil_dan_bisa_ditandai_dibaca(): void
    {
        $guruUser = User::factory()->role('guru')->create();
        Guru::create(['user_id' => $guruUser->id, 'nama' => 'Pak Guru']);

        $guruUser->notify(new class extends \Illuminate\Notifications\Notification
        {
            public function via($notifiable): array
            {
                return ['database'];
            }

            public function toArray($notifiable): array
            {
                return ['title' => 'Tes Notifikasi', 'body' => 'Isi tes', 'url' => '/guru'];
            }
        });

        $this->actingAs($guruUser)->get('/notifikasi')
            ->assertOk()->assertSee('Tes Notifikasi');

        $this->assertSame(1, $guruUser->unreadNotifications()->count());

        $this->actingAs($guruUser)->post('/notifikasi/tandai-semua-dibaca')->assertRedirect();

        $this->assertSame(0, $guruUser->unreadNotifications()->count());
    }
}
