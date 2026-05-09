<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class SiswaClassTransferService
{
    public function __construct(
        private readonly KelasProvisioningService $kelasProvisioningService,
    ) {
    }

    public function promoteActiveStudentsToAcademicYear(string $tahunAjaran): array
    {
        return DB::transaction(function () use ($tahunAjaran): array {
            $promoted = 0;
            $skippedFinalGrade = 0;
            $skippedMissingTarget = 0;
            $skippedSameYear = 0;

            $students = Siswa::query()
                ->with('kelas')
                ->where('status', 'active')
                ->get();

            foreach ($students as $siswa) {
                if (! $siswa->kelas) {
                    $skippedMissingTarget++;

                    continue;
                }

                if ($siswa->kelas->tahun_ajaran === $tahunAjaran) {
                    $skippedSameYear++;

                    continue;
                }

                $kelasState = $this->kelasProvisioningService->inferFormState($siswa->kelas);
                $currentGrade = (int) ($kelasState['tingkat'] ?? 0);

                if (! $currentGrade || in_array($currentGrade, [6, 9, 12], true)) {
                    $skippedFinalGrade++;

                    continue;
                }

                $targetCode = $this->kelasProvisioningService->previewCode(
                    $kelasState['jenjang'],
                    $currentGrade + 1,
                    $kelasState['rombel'],
                    $tahunAjaran,
                );

                $targetClass = Kelas::query()->where('kode_kelas', $targetCode)->first();

                if (! $targetClass) {
                    $skippedMissingTarget++;

                    continue;
                }

                $siswa->update(['kelas_id' => $targetClass->id]);
                $promoted++;
            }

            return [
                'promoted' => $promoted,
                'skipped_final_grade' => $skippedFinalGrade,
                'skipped_missing_target' => $skippedMissingTarget,
                'skipped_same_year' => $skippedSameYear,
            ];
        });
    }

    public function moveStudentsToClass(EloquentCollection $students, Kelas $targetClass): array
    {
        return DB::transaction(function () use ($students, $targetClass): array {
            $moved = 0;
            $skippedInactive = 0;
            $skippedSameClass = 0;

            foreach ($students as $siswa) {
                if ($siswa->status !== 'active') {
                    $skippedInactive++;

                    continue;
                }

                if ((int) $siswa->kelas_id === (int) $targetClass->id) {
                    $skippedSameClass++;

                    continue;
                }

                $siswa->update(['kelas_id' => $targetClass->id]);
                $moved++;
            }

            return [
                'moved' => $moved,
                'skipped_inactive' => $skippedInactive,
                'skipped_same_class' => $skippedSameClass,
            ];
        });
    }
}
