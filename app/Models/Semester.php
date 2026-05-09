<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = ['tahun_ajaran', 'semester', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function approximatePeriodRange(): array
    {
        [$startYear, $endYear] = array_pad(
            array_map('intval', explode('/', (string) $this->tahun_ajaran)),
            2,
            null,
        );

        $startYear ??= (int) $this->created_at?->format('Y') ?: (int) now()->format('Y');
        $endYear ??= $startYear + 1;

        $semesterName = str($this->semester)->lower()->value();

        if (str_contains($semesterName, 'genap')) {
            return [
                CarbonImmutable::create($endYear, 1, 1)->startOfDay(),
                CarbonImmutable::create($endYear, 6, 30)->endOfDay(),
            ];
        }

        return [
            CarbonImmutable::create($startYear, 7, 1)->startOfDay(),
            CarbonImmutable::create($startYear, 12, 31)->endOfDay(),
        ];
    }

    public function getLabelAttribute(): string
    {
        return "{$this->tahun_ajaran} - {$this->semester}";
    }
}
