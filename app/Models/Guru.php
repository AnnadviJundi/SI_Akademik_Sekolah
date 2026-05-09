<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    use SoftDeletes;

    protected $table = 'guru';

    protected $fillable = ['user_id', 'nip', 'nama', 'alamat', 'no_telp', 'foto_path', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pengampu(): HasMany
    {
        return $this->hasMany(Pengampu::class);
    }

    protected function mataPelajaranList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->teachingItems('mataPelajaran', 'nama_mapel'),
        );
    }

    protected function kelasAjarList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->teachingItems('kelas', 'nama_kelas'),
        );
    }

    private function teachingItems(string $relation, string $field): string
    {
        $this->loadMissing("pengampu.{$relation}");

        return $this->pengampu
            ->map(fn (Pengampu $pengampu) => $pengampu->{$relation}?->{$field})
            ->filter()
            ->unique()
            ->values()
            ->join(', ');
    }
}
