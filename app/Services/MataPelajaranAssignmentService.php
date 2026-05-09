<?php

namespace App\Services;

use App\Models\MataPelajaran;
use App\Models\Pengampu;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MataPelajaranAssignmentService
{
    public function create(array $data): MataPelajaran
    {
        return DB::transaction(function () use ($data): MataPelajaran {
            $mataPelajaran = MataPelajaran::query()->create($this->mataPelajaranPayload($data));

            $this->syncPengampu($mataPelajaran, $data['pengampu'] ?? []);

            return $mataPelajaran->load(['pengampu.guru', 'pengampu.kelas', 'pengampu.semester']);
        });
    }

    public function update(MataPelajaran $mataPelajaran, array $data): MataPelajaran
    {
        return DB::transaction(function () use ($mataPelajaran, $data): MataPelajaran {
            $mataPelajaran->update($this->mataPelajaranPayload($data));
            $this->syncPengampu($mataPelajaran, $data['pengampu'] ?? []);

            return $mataPelajaran->fresh(['pengampu.guru', 'pengampu.kelas', 'pengampu.semester']);
        });
    }

    private function mataPelajaranPayload(array $data): array
    {
        $payload = Arr::only($data, [
            'kode_mapel',
            'nama_mapel',
            'status',
        ]);

        if (blank($payload['kode_mapel'] ?? null)) {
            $payload['kode_mapel'] = app(MataPelajaranCatalogService::class)->generateCode($payload['nama_mapel'] ?? null);
        }

        return $payload;
    }

    private function syncPengampu(MataPelajaran $mataPelajaran, array $assignments): void
    {
        $normalizedAssignments = collect($assignments)
            ->map(fn (array $assignment): array => [
                'guru_id' => $assignment['guru_id'] ?? null,
                'kelas_id' => $assignment['kelas_id'] ?? null,
                'semester_id' => $assignment['semester_id'] ?? null,
            ])
            ->filter(fn (array $assignment): bool => filled($assignment['guru_id']) && filled($assignment['kelas_id']) && filled($assignment['semester_id']))
            ->unique(fn (array $assignment): string => implode('-', $assignment))
            ->values();

        $currentAssignments = $mataPelajaran->pengampu()
            ->get(['id', 'guru_id', 'kelas_id', 'semester_id'])
            ->keyBy(fn (Pengampu $pengampu): string => $this->assignmentSignature($pengampu->only([
                'guru_id',
                'kelas_id',
                'semester_id',
            ])));

        $normalizedAssignments->each(function (array $assignment) use ($mataPelajaran, $currentAssignments): void {
            $signature = $this->assignmentSignature($assignment);

            if ($currentAssignments->has($signature)) {
                $currentAssignments->forget($signature);

                return;
            }

            $mataPelajaran->pengampu()->create([
                ...$assignment,
                'mata_pelajaran_id' => $mataPelajaran->id,
            ]);
        });

        if ($currentAssignments->isNotEmpty()) {
            $mataPelajaran->pengampu()->whereKey($currentAssignments->pluck('id'))->delete();
        }
    }

    private function assignmentSignature(array $assignment): string
    {
        return implode('-', [
            $assignment['guru_id'] ?? '',
            $assignment['kelas_id'] ?? '',
            $assignment['semester_id'] ?? '',
        ]);
    }
}
