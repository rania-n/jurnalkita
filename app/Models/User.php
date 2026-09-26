<?php

namespace App\Models;

use App\Support\HariSekolah;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    // email_verified_at ikut fillable -- dipakai AkunController buat nandain
    // akun otomatis terverifikasi begitu dibuat/disetujui admin (SMTP belum
    // ada, jadi verifikasi lewat klik link di email nggak akan pernah bisa
    // jalan). Semua pemanggil create()/update() di app ini pakai array
    // eksplisit (bukan $request->all() mentah), jadi aman ditambahin ke sini.
    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'no_hp', 'nip', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    /** Jadwal shift Waka Kesiswaan (kalau role-nya waka). */
    public function jadwalWakas(): HasMany
    {
        return $this->hasMany(JadwalWaka::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Guru yang menjabat sebagai staff piket (punya jadwal piket) — permanen, dipakai buat akses fitur. */
    public function isPiket(): bool
    {
        return $this->role === 'guru'
            && $this->guru?->jadwalPikets()->exists();
    }

    /**
     * Guru piket yang KEBAGIAN JADWAL HARI INI (bukan cuma "pernah dapat piket") —
     * dipakai buat nav, biar menu Piket/Dispensasi cuma nongol di hari dia
     * beneran bertugas. Beda dari isPiket() yang tetap dipakai buat akses fitur
     * (boleh ajukan/lihat dispensasi kapan saja, bukan cuma pas hari piketnya).
     */
    public function piketHariIni(): bool
    {
        if (! HariSekolah::hariIni() || ! $this->isPiket()) {
            return false;
        }

        // berlakuPada() -- piket sekarang per TANGGAL SPESIFIK (ulang tiap 2
        // minggu, bukan tiap minggu di hari yang sama); baris lama (tanggal
        // kosong) tetap dianggap berulang tiap minggu (lihat JadwalPiket).
        return $this->guru->jadwalPikets()->berlakuPada(today())->exists();
    }

    /** Waka yang PUNYA jadwal shift sama sekali -- kalau kosong berarti belum diatur (semua Waka dianggap standby). */
    public function isWakaBershift(): bool
    {
        return $this->role === 'waka' && $this->jadwalWakas()->exists();
    }

    /**
     * Waka yang KEBAGIAN SHIFT HARI INI. Kalau sekolah belum mengatur jadwal shift Waka
     * sama sekali (jadwal_wakas kosong total), semua Waka dianggap bertugas -- supaya
     * fitur dispensasi tidak mendadak macet gara-gara belum ada yang setting jadwal.
     */
    public function wakaBertugasHariIni(): bool
    {
        if ($this->role !== 'waka') {
            return false;
        }

        if (! static::where('role', 'waka')->whereHas('jadwalWakas')->exists()) {
            return true; // belum ada Waka yang diatur jadwalnya -- anggap semua standby
        }

        $hari = HariSekolah::hariIni();

        return $hari && $this->jadwalWakas()->where('hari', $hari)->exists();
    }

    /**
     * Waka yang bertugas HARI INI, dipakai buat rute link WA persetujuan dispensasi --
     * biar yang dikirimi bukan Waka sembarangan, tapi yang beneran gilirannya.
     * Fallback ke Waka manapun yang punya no_hp kalau tidak ada yang cocok hari ini
     * (mis. weekend, atau belum diatur jadwalnya).
     */
    public static function wakaUntukHariIni(): ?self
    {
        $kandidat = static::where('role', 'waka')->whereNotNull('no_hp')->get();

        return $kandidat->first(fn (self $w) => $w->wakaBertugasHariIni()) ?? $kandidat->first();
    }

    /** Guru yang jadi wali kelas (wali_id di kelas manapun) -- bukan role, cuma atribut. */
    public function isWali(): bool
    {
        return $this->role === 'guru' && $this->guru && $this->guru->kelasWali()->exists();
    }

    /** Kelas-kelas yang diampu guru ini sebagai wali (biasanya cuma 1, tapi bisa lebih). */
    public function kelasWaliList(): Collection
    {
        return $this->guru?->kelasWali()->orderedByHierarchy()->get() ?? collect();
    }

    /** Siswa pengurus kelas (akun kelas / "sekretaris"). */
    public function isSekretaris(): bool
    {
        return $this->role === 'siswa' && $this->siswa?->jabatan === 'pengurus';
    }

    /** Kelas yang diampu pengurus kelas ini. */
    public function kelasSekretaris(): ?Kelas
    {
        return $this->siswa?->kelas;
    }

    /**
     * Label peran buat ditampilin (header, profil, dll) -- SATU sumber kebenaran,
     * dulu ada 4 versi beda-beda nyebar di beberapa view (nggak sinkron: "Waka"
     * vs "Waka Kesiswaan", "Admin" vs "Administrator", dst).
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'guru' => $this->piketHariIni() ? 'Guru Piket' : 'Guru',
            'siswa' => $this->kelasSekretaris() ? 'Pengurus '.$this->kelasSekretaris()->nama : 'Pengurus Kelas',
            'waka' => 'Waka Kesiswaan',
            default => 'Pengguna',
        };
    }

    /** Nama folder/route dashboard sesuai peran efektif. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'waka' => 'waka.dashboard',
            'siswa' => 'sekretaris.dashboard',
            default => 'guru.dashboard',
        };
    }
}
