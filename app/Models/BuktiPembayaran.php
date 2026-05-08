<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuktiPembayaran extends Model
{
    use SoftDeletes;

    protected $table = 'bukti_pembayaran';

    public $timestamps = false;

    protected $fillable = ['pembayaran_id', 'file_name', 'file_path', 'file_type', 'file_size', 'uploaded_by', 'uploaded_at', 'updated_by', 'updated_at'];

    protected static function booted(): void
    {
        static::creating(function (self $bukti): void {
            $bukti->uploaded_by ??= auth()->id();
            $bukti->uploaded_at ??= now();
        });

        static::updating(function (self $bukti): void {
            $bukti->updated_by = auth()->id();
            $bukti->updated_at = now();
        });
    }

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class);
    }
}
