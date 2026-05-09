<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataPelajaran extends Model
{
    use SoftDeletes;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['kode_mapel', 'nama_mapel', 'status'];

    public function pengampu(): HasMany
    {
        return $this->hasMany(Pengampu::class);
    }

    protected function guruPengampuList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->teachingItems('guru', 'nama'),
        );
    }

    protected function kelasDiampuList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->teachingItems('kelas', 'nama_kelas'),
        );
    }

    protected function pengampuSummary(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $this->loadMissing(['pengampu.guru', 'pengampu.kelas']);

                return $this->pengampu
                    ->map(function (Pengampu $pengampu): ?string {
                        $guru = $pengampu->guru?->nama;
                        $kelas = $pengampu->kelas?->nama_kelas;

                        if (! $guru && ! $kelas) {
                            return null;
                        }

                        if (! $guru) {
                            return $kelas;
                        }

                        if (! $kelas) {
                            return $guru;
                        }

                        return "{$guru} ({$kelas})";
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->join(', ');
            },
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
