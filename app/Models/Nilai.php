<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nilai extends Model
{
    use SoftDeletes;

    public const JENIS = ['tugas', 'harian', 'uts', 'uas', 'nilai_akhir'];

    protected $table = 'nilai';

    protected $fillable = ['siswa_id', 'kelas_id', 'mata_pelajaran_id', 'guru_id', 'semester_id', 'jenis_nilai', 'nilai', 'catatan', 'created_by', 'updated_by'];

    protected static function booted(): void
    {
        static::creating(function (self $nilai): void {
            $nilai->created_by ??= auth()->id();
            $nilai->updated_by ??= auth()->id();
            if (! $nilai->guru_id && auth()->user()?->guru) {
                $nilai->guru_id = auth()->user()->guru->id;
            }
        });

        static::updating(function (self $nilai): void {
            $nilai->updated_by = auth()->id();
        });
    }

    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function mataPelajaran(): BelongsTo { return $this->belongsTo(MataPelajaran::class); }
    public function guru(): BelongsTo { return $this->belongsTo(Guru::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
