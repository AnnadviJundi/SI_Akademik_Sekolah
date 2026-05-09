<?php

namespace App\Services;

use App\Models\Guru;
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
}
