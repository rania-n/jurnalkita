<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /** Hanya jurnal guru hadir yang perlu masuk antrean pemeriksaan pengurus. */
    public function scopeInReviewQueue(Builder $query): Builder
    {
        return $query->where('status_guru', '!=', 'tidak_hadir')
            ->where('status_verifikasi', 'pending');
    }

    public function menungguPemeriksaan(): bool
    {
        return ! $this->verifikasiAbsen() && $this->status_verifikasi === 'pending';
    }

    /** Guru masih boleh mengubah jurnal selama belum terverifikasi (pending) atau saat diminta revisi. */
    public function bisaDiubah(): bool
    {
        return in_array($this->status_verifikasi, ['pending', 'revisi'], true);
    }

    /** True untuk status otomatis tanpa pemeriksa manusia, seperti laporan guru tidak hadir. */
    public function otomatisDiverifikasi(): bool
    {
        return $this->status_verifikasi === 'terverifikasi' && $this->verifikator_id === null;
    }

    /** Ringkasan status yang ditampilkan pada kartu riwayat guru. */
    public function statusRingkas(): array
    {
        $status = $this->verifikasiAbsen()
            ? ['status' => 'terverifikasi', 'label' => 'Disetujui']
            : match ($this->status_verifikasi) {
                'terverifikasi' => ['status' => 'terverifikasi', 'label' => 'Terverifikasi'],
                'revisi' => ['status' => 'revisi', 'label' => 'Perlu Revisi'],
                default => ['status' => 'pending', 'label' => 'Belum diperiksa'],
            };

        return $this->verifikasiAbsen()
            ? [$status, ['status' => 'tugas', 'label' => 'Tugas']]
            : [$status];
    }

    /**
     * true kalau guru TIDAK HADIR -- jurnalnya nggak punya materi/isi buat
     * "diverifikasi" beneran, pengurus kelas cuma perlu tau & catat laporan
     * absennya. Label & tombol aksi di view sengaja dibedain dari jurnal
     * "Hadir" biasa (lihat labelStatusVerifikasi()) biar nggak kesan aneh
     * "verifikasi" sesuatu yang isinya kosong.
     */
    public function verifikasiAbsen(): bool
    {
        return $this->status_guru === 'tidak_hadir';
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
