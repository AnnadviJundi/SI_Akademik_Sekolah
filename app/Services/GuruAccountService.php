<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Pengampu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GuruAccountService
{
    public function create(array $data): Guru
    {
        return DB::transaction(function () use ($data): Guru {
            $guruRole = Role::query()->firstOrCreate(
                ['code' => 'guru'],
                ['name' => 'Guru'],
            );

            $user = User::query()->create([
                'role_id' => $guruRole->id,
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'] ?: null,
                'password' => $data['password'],
                'status' => $data['status'],
            ]);

            $guru = Guru::query()->create([
                ...$this->guruPayload($data),
                'user_id' => $user->id,
            ]);

            $this->syncPengampu($guru, $data['pengampu'] ?? []);

            return $guru->load(['user.role', 'pengampu.kelas', 'pengampu.mataPelajaran']);
        });
    }

    public function update(Guru $guru, array $data): Guru
    {
        return DB::transaction(function () use ($guru, $data): Guru {
            $guruRole = Role::query()->firstOrCreate(
                ['code' => 'guru'],
                ['name' => 'Guru'],
            );

            $userPayload = [
                'role_id' => $guruRole->id,
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'] ?: null,
                'status' => $data['status'],
            ];

            if (filled($data['password'] ?? null)) {
                $userPayload['password'] = $data['password'];
            }

            $guru->user()->update($userPayload);
            $guru->update($this->guruPayload($data));
            $this->syncPengampu($guru, $data['pengampu'] ?? []);

            return $guru->fresh(['user.role', 'pengampu.kelas', 'pengampu.mataPelajaran']);
        });
    }

    private function guruPayload(array $data): array
    {
        return Arr::only($data, [
            'nip',
            'nama',
            'alamat',
            'no_telp',
            'foto_path',
            'status',
        ]);
    }

    private function syncPengampu(Guru $guru, array $assignments): void
    {
        $normalizedAssignments = collect($assignments)
            ->map(fn (array $assignment): array => [
                'kelas_id' => $assignment['kelas_id'] ?? null,
                'mata_pelajaran_id' => $assignment['mata_pelajaran_id'] ?? null,
                'semester_id' => $assignment['semester_id'] ?? null,
            ])
            ->filter(fn (array $assignment): bool => filled($assignment['kelas_id']) && filled($assignment['mata_pelajaran_id']) && filled($assignment['semester_id']))
            ->unique(fn (array $assignment): string => implode('-', $assignment))
            ->values();

        $currentAssignments = $guru->pengampu()
            ->get(['id', 'kelas_id', 'mata_pelajaran_id', 'semester_id'])
            ->keyBy(fn (Pengampu $pengampu): string => $this->pengampuSignature($pengampu->only([
                'kelas_id',
                'mata_pelajaran_id',
                'semester_id',
            ])));

        $normalizedAssignments->each(function (array $assignment) use ($guru, $currentAssignments): void {
            $signature = $this->pengampuSignature($assignment);

            if ($currentAssignments->has($signature)) {
                $currentAssignments->forget($signature);

                return;
            }

            $guru->pengampu()->create($assignment);
        });

        if ($currentAssignments->isNotEmpty()) {
            $guru->pengampu()->whereKey($currentAssignments->pluck('id'))->delete();
        }
    }

    private function pengampuSignature(array $assignment): string
    {
        return implode('-', [
            $assignment['kelas_id'] ?? '',
            $assignment['mata_pelajaran_id'] ?? '',
            $assignment['semester_id'] ?? '',
        ]);
    }
}
