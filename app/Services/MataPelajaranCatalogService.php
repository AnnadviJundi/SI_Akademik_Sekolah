<?php

namespace App\Services;

use App\Models\MataPelajaran;
use Illuminate\Support\Str;

class MataPelajaranCatalogService
{
    private const PRESET_CODES = [
        'Matematika' => 'MTK',
        'Bahasa Indonesia' => 'BIN',
        'Bahasa Inggris' => 'BIG',
        'Ilmu Pengetahuan Alam' => 'IPA',
        'Ilmu Pengetahuan Sosial' => 'IPS',
        'Pendidikan Pancasila dan Kewarganegaraan' => 'PPKN',
        'Pendidikan Agama Islam' => 'PAI',
        'Seni Budaya' => 'SBK',
        'Pendidikan Jasmani Olahraga dan Kesehatan' => 'PJOK',
        'Prakarya dan Kewirausahaan' => 'PKWU',
        'Teknologi Informasi dan Komunikasi' => 'TIK',
    ];

    public function suggestions(): array
    {
        return collect(self::PRESET_CODES)
            ->keys()
            ->merge(
                MataPelajaran::query()
                    ->orderBy('nama_mapel')
                    ->pluck('nama_mapel'),
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function generateCode(?string $name): string
    {
        $normalized = trim((string) $name);

        if ($normalized === '') {
            return '';
        }

        if (isset(self::PRESET_CODES[$normalized])) {
            return self::PRESET_CODES[$normalized];
        }

        $words = collect(preg_split('/\s+/', Str::upper($normalized)) ?: [])
            ->map(fn (string $word): string => preg_replace('/[^A-Z0-9]/', '', $word) ?: '')
            ->filter()
            ->values();

        if ($words->isEmpty()) {
            return '';
        }

        if ($words->count() === 1) {
            return Str::limit($words->first(), 3, '');
        }

        return $words
            ->map(function (string $word): string {
                return match ($word) {
                    'DAN', 'THE', 'OF' => '',
                    default => Str::substr($word, 0, 1),
                };
            })
            ->filter()
            ->join('');
    }
}
