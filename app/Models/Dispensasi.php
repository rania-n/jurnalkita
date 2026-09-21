<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Dispensasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'siswa_id', 'diajukan_oleh_id', 'tanggal', 'tanggal_selesai', 'jam_ke_mulai', 'jam_ke_selesai',
        'alasan', 'surat_path', 'no_hp',
        'status_piket', 'piket_id', 'catatan_piket',
        'status_waka', 'waka_id', 'catatan_waka',
        'status_akhir',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'tanggal_selesai' => 'date'];
    }

    /** true kalau dispensasinya lebih dari 1 hari. */
    public function multiHari(): bool
    {
        return $this->tanggal_selesai && ! $this->tanggal_selesai->isSameDay($this->tanggal);
    }

    /** Semua tanggal yang tercakup (1 hari kalau tanggal_selesai kosong). */
    public function rentangTanggal(): CarbonPeriod
    {
        return CarbonPeriod::create($this->tanggal, $this->tanggal_selesai ?? $this->tanggal);
    }

    /** true kalau tanggal ini (default hari ini) masih dalam rentang berlaku dispensasi. */
    public function berlakuPada(?Carbon $tanggal = null): bool
    {
        $tanggal ??= now();
        $akhir = $this->tanggal_selesai ?? $this->tanggal;

        return $tanggal->isBetween($this->tanggal->startOfDay(), $akhir->copy()->endOfDay());
    }

    /** Label tanggal buat ditampilkan — rentang kalau multi hari. */
    public function labelTanggal(): string
    {
        return $this->multiHari()
            ? $this->tanggal->translatedFormat('d M Y').' – '.$this->tanggal_selesai->translatedFormat('d M Y')
            : $this->tanggal->translatedFormat('d M Y');
    }

    /** Label jam buat ditampilkan — "sampai selesai hari itu" kalau jam_ke_selesai kosong. */
    public function labelJam(): string
    {
        return match (true) {
            ! $this->jam_ke_mulai => 'Sehari penuh',
            (bool) $this->jam_ke_selesai => "JP {$this->jam_ke_mulai}–{$this->jam_ke_selesai}",
            default => "JP {$this->jam_ke_mulai} sampai selesai",
        };
    }

    /**
     * true kalau sudah disetujui TAPI batas akhir tanggalnya sudah lewat -- beda dari
     * berlakuPada() yang juga false buat dispensasi yang tanggalnya BELUM mulai
     * (mis. diajukan buat besok). Ini murni "tanggal akhirnya sudah lewat".
     */
    public function sudahKadaluarsa(): bool
    {
        if ($this->status_akhir !== 'approved') {
            return false;
        }

        return ($this->tanggal_selesai ?? $this->tanggal)->copy()->endOfDay()->isPast();
    }

    /** Scope: dispensasi approved yang batas akhirnya sudah lewat -- tab "Kadaluarsa". */
    public function scopeKadaluarsa(Builder $query): Builder
    {
        return $query->where('status_akhir', 'approved')
            ->whereRaw('coalesce(tanggal_selesai, tanggal) < ?', [today()->toDateString()]);
    }

    /** Scope: dispensasi approved yang masih berlaku hari ini/akan datang -- tab "Disetujui". */
    public function scopeMasihBerlaku(Builder $query): Builder
    {
        return $query->where('status_akhir', 'approved')
            ->whereRaw('coalesce(tanggal_selesai, tanggal) >= ?', [today()->toDateString()]);
    }

    /**
     * true kalau Waka BELUM sempat mutusin dan HARI tanggal MULAI dispensasi ini
     * udah beneran lewat (BUKAN hari ini lagi, tapi kemarin atau sebelumnya) --
     * udah nggak relevan lagi diproses. Sengaja BUKAN "hari ini" juga dianggap
     * lewat -- dispensasi yang diajukan & harusnya diputuskan HARI INI JUGA
     * (kasus paling umum: piket ajukan pas ada siswa mau izin langsung) tetap
     * harus bisa diproses sepanjang hari itu. Beda dari sudahKadaluarsa() yang
     * khusus buat dispensasi yang UDAH disetujui.
     */
    public function sudahLewatBatasKeputusan(): bool
    {
        return $this->status_waka === 'pending' && $this->tanggal->copy()->startOfDay()->lt(today());
    }

    /**
     * Otomatis batalkan (tolak) kalau udah lewat batas keputusan tanpa Waka sempat
     * mutusin -- dipanggil tiap kali dispensasi ini dibuka/diproses, biar nggak
     * nyangkut jadi "Menunggu" selamanya walau tanggalnya udah lama lewat. Setelah
     * ini, status_waka === 'rejected' otomatis, jadi tombol Setuju/Tolak/Batalkan
     * ilang dengan sendirinya (semua gate-nya udah cek status_waka === 'pending').
     */
    public function batalkanKalauKadaluarsa(): bool
    {
        if (! $this->sudahLewatBatasKeputusan()) {
            return false;
        }

        $this->update([
            'status_waka' => 'rejected',
            'catatan_waka' => 'Otomatis dibatalkan sistem — melewati tanggal berlaku tanpa keputusan Waka Kesiswaan.',
        ]);
        $this->segarkanStatusAkhir();

        return true;
    }

    /**
     * Sapu semua dispensasi pending yang udah kadaluarsa sekaligus -- dipanggil
     * pas Riwayat Dispensasi dibuka (lihat DispensasiController::index()), biar
     * tab "Menunggu"/"Ditolak" ke-update duluan sebelum ditampilkan, nggak nunggu
     * satu-satu baru kesapu pas dibuka detailnya.
     */
    public static function batalkanSemuaKadaluarsa(): int
    {
        $daftar = static::where('status_waka', 'pending')
            ->whereDate('tanggal', '<=', today())
            ->get();

        foreach ($daftar as $d) {
            $d->batalkanKalauKadaluarsa();
        }

        return $daftar->count();
    }

    /**
     * withTrashed() -- dispensasi itu CATATAN SEJARAH, harus tetap kebaca
     * utuh walau siswanya belakangan di-soft-delete (pindah/keluar/data
     * diganti data asli, dsb). Tanpa ini, dispensasi lama yang nyantol ke
     * siswa yang udah dihapus bakal error null pas ditampilin.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    /** withTrashed() -- dispensasi lama tetap harus kebaca siapa yang ajukan/proses walau akunnya belakangan dihapus admin. */
    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh_id')->withTrashed();
    }

    public function piket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'piket_id')->withTrashed();
    }

    public function waka(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waka_id')->withTrashed();
    }

    /** Hitung ulang status akhir; jika disetujui, terapkan ke presensi siswa. */
    public function segarkanStatusAkhir(): void
    {
        $this->status_akhir = match (true) {
            $this->status_piket === 'rejected' || $this->status_waka === 'rejected' => 'rejected',
            $this->status_piket === 'approved' && $this->status_waka === 'approved' => 'approved',
            default => 'pending',
        };
        $this->save();

        if ($this->status_akhir === 'approved') {
            $this->terapkanKeAbsensi();
        }
    }

    /** Set absensi siswa jadi "dispensasi" untuk jurnal di tanggal & jam yang sesuai. */
    public function terapkanKeAbsensi(): void
    {
        foreach ($this->rentangTanggal() as $tanggal) {
            Absensi::where('siswa_id', $this->siswa_id)
                ->whereHas('jurnal', function ($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                    if ($this->jam_ke_mulai) {
                        // jam_ke_selesai kosong = berlaku sampai akhir hari (tidak dibatasi jam terakhir).
                        $q->where('jam_ke_selesai', '>=', $this->jam_ke_mulai);
                        if ($this->jam_ke_selesai) {
                            $q->where('jam_ke_mulai', '<=', $this->jam_ke_selesai);
                        }
                    }
                })
                ->update(['status' => 'dispensasi', 'catatan' => $this->alasan ?: 'Dispensasi (disetujui)']);
        }
    }
}
