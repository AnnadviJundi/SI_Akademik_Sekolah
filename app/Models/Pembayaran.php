<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use SoftDeletes;

    public const STATUS = ['Belum Bayar', 'Menunggu Verifikasi', 'Lunas', 'Ditolak'];

    protected $table = 'pembayaran';

    protected $fillable = ['siswa_id', 'semester_id', 'jenis_pembayaran', 'jumlah_tagihan', 'jumlah_dibayar', 'status', 'verified_by', 'verified_at', 'created_by', 'updated_by'];

    protected static function booted(): void
    {
        static::creating(function (self $pembayaran): void {
            $pembayaran->created_by ??= auth()->id();
            $pembayaran->updated_by ??= auth()->id();
        });

        static::updating(function (self $pembayaran): void {
            $pembayaran->updated_by = auth()->id();
            if ($pembayaran->isDirty('status')) {
                $pembayaran->verified_by = auth()->id();
                $pembayaran->verified_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function bukti(): HasMany { return $this->hasMany(BuktiPembayaran::class); }
    public function latestBukti(): HasOne { return $this->hasOne(BuktiPembayaran::class)->latestOfMany('uploaded_at'); }
    public function verifiedByUser(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public function getBuktiFileAttribute(): ?string
    {
        return $this->latestBukti?->file_path;
    }
}
