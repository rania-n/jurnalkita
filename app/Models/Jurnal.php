<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurnal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'jadwal_id', 'guru_id', 'tanggal', 'jam_ke_mulai', 'jam_ke_selesai',
        'status_guru', 'materi', 'metode', 'tugas_tambahan', 'alasan', 'foto_bukti',
        'diisi_oleh_pengurus', 'status_verifikasi', 'verifikator_id', 'catatan_verifikasi',
        'lat', 'lng', 'jarak_meter', 'terlambat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'diisi_oleh_pengurus' => 'boolean',
            'terlambat' => 'boolean',
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    /** withTrashed() -- jurnal itu CATATAN SEJARAH, jadwal/gurunya bisa saja belakangan dihapus admin. */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class)->withTrashed();
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class)->withTrashed();
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'verifikator_id');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function isPending(): bool
    {
        return $this->status_verifikasi === 'pending';
    }

    /** Guru masih boleh mengubah jurnal selama belum terverifikasi (pending) atau saat diminta revisi. */
    public function bisaDiubah(): bool
    {
        return in_array($this->status_verifikasi, ['pending', 'revisi'], true);
    }

    /**
     * true kalau status "Terverifikasi"-nya OTOMATIS dari sistem (lewat
     * otomatisVerifikasiKalauLewatHari()), BUKAN pengurus kelas beneran
     * meriksa -- verifikator_id sengaja dibiarkan null cuma buat kasus ini,
     * verifikasi manusia asli SELALU ngisi verifikator_id.
     */
    public function otomatisDiverifikasi(): bool
    {
        return $this->status_verifikasi === 'terverifikasi' && $this->verifikator_id === null;
    }

    /**
     * Otomatis verifikasi kalau masih "pending" tapi tanggalnya udah kelewat
     * hari (bukan hari ini lagi) -- pengurus kelas nggak sempat periksa,
     * daripada numpuk jadi pending berhari-hari. verifikator_id SENGAJA
     * dibiarkan null (bukan verifikasi manusia beneran) -- view yang nampilin
     * nama verifikator udah null-safe (lihat guru/jurnal/_detail-fragment).
     */
    public function otomatisVerifikasiKalauLewatHari(): bool
    {
        if ($this->status_verifikasi !== 'pending' || $this->tanggal->copy()->startOfDay()->gte(today())) {
            return false;
        }

        $this->update([
            'status_verifikasi' => 'terverifikasi',
            'catatan_verifikasi' => 'Otomatis diverifikasi sistem — pengurus kelas tidak sempat memeriksa sampai hari berikutnya.',
        ]);

        return true;
    }

    /** Sapu semua jurnal pending yang udah kelewat hari sekaligus -- opsional dipersempit ke 1 kelas/guru. */
    public static function verifikasiSemuaYangKadaluarsa(?int $kelasId = null, ?int $guruId = null): int
    {
        $daftar = static::where('status_verifikasi', 'pending')
            ->whereDate('tanggal', '<', today())
            ->when($kelasId, fn ($q) => $q->whereHas('jadwal', fn ($q2) => $q2->where('kelas_id', $kelasId)))
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->get();

        foreach ($daftar as $j) {
            $j->otomatisVerifikasiKalauLewatHari();
        }

        return $daftar->count();
    }

    /** null = belum bisa dinilai (lokasi sekolah belum diatur / guru tidak kirim lokasi). */
    public function dalamRadiusSekolah(): ?bool
    {
        if ($this->jarak_meter === null) {
            return null;
        }

        return $this->jarak_meter <= PengaturanKehadiran::current()->radius_meter;
    }
}
