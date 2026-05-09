<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    public function getActiveSemester(): ?Semester
    {
        return Semester::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    public function getActiveAcademicYear(): ?string
    {
        return $this->getActiveSemester()?->tahun_ajaran;
    }

    public function activateSemester(Semester $semester): Semester
    {
        return DB::transaction(function () use ($semester): Semester {
            Semester::query()
                ->whereKeyNot($semester->getKey())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            if (! $semester->is_active) {
                $semester->forceFill(['is_active' => true])->save();
            }

            return $semester->fresh();
        });
    }
}
