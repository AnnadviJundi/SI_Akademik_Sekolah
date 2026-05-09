<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SiswaAccountService
{
    public function create(array $data): Siswa
    {
        return DB::transaction(function () use ($data): Siswa {
            $siswaRole = Role::query()->firstOrCreate(
                ['code' => 'siswa'],
                ['name' => 'Siswa'],
            );

            $user = User::query()->create([
                'role_id' => $siswaRole->id,
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'] ?: null,
                'password' => $data['password'],
                'status' => $this->normalizeStatus($data['status'] ?? true),
            ]);

            return Siswa::query()->create([
                ...$this->siswaPayload($data),
                'user_id' => $user->id,
            ])->load(['user.role', 'kelas']);
        });
    }

    public function update(Siswa $siswa, array $data): Siswa
    {
        return DB::transaction(function () use ($siswa, $data): Siswa {
            $siswaRole = Role::query()->firstOrCreate(
                ['code' => 'siswa'],
                ['name' => 'Siswa'],
            );

            $userPayload = [
                'role_id' => $siswaRole->id,
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'] ?: null,
                'status' => $this->normalizeStatus($data['status'] ?? true),
            ];

            if (filled($data['password'] ?? null)) {
                $userPayload['password'] = $data['password'];
            }

            $siswa->user()->update($userPayload);
            $siswa->update($this->siswaPayload($data));

            return $siswa->fresh(['user.role', 'kelas']);
        });
    }

    private function siswaPayload(array $data): array
    {
        return [
            ...Arr::only($data, [
                'nis',
                'nama',
                'alamat',
                'nama_ortu',
                'no_telp',
                'foto_path',
                'kelas_id',
            ]),
            'status' => $this->normalizeStatus($data['status'] ?? true),
        ];
    }

    private function normalizeStatus(bool|string|null $status): string
    {
        if (is_string($status)) {
            return $status === 'active' ? 'active' : 'inactive';
        }

        return $status ? 'active' : 'inactive';
    }
}
