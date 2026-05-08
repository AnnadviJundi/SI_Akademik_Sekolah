<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use App\Filament\Resources\Nilais\NilaiResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_filament_panel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_admin_can_access_master_data_resource(): void
    {
        $admin = $this->user('admin', 'admin.test');

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_filament_login_accepts_username(): void
    {
        $admin = $this->user('admin', 'admin.login');

        Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->set('data.username', 'admin.login')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_access_user_resource(): void
    {
        $guru = $this->user('guru', 'guru.test');

        $this->actingAs($guru)->get('/admin/users')->assertForbidden();
    }

    public function test_siswa_can_access_nilais_resource_but_only_their_own_data(): void
    {
        [$siswaUser, $siswa] = $this->student('siswa.one', 'S001');
        [, $otherSiswa] = $this->student('siswa.two', 'S002');
        [$semester, $kelas, $mapel] = $this->core();
        $admin = $this->user('admin', 'admin.owner');

        Nilai::query()->create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'semester_id' => $semester->id,
            'jenis_nilai' => 'uts',
            'nilai' => 90,
            'created_by' => $admin->id,
        ]);
        Nilai::query()->create([
            'siswa_id' => $otherSiswa->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'semester_id' => $semester->id,
            'jenis_nilai' => 'uts',
            'nilai' => 70,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($siswaUser)->get('/admin/nilais')
            ->assertOk()
            ->assertSee('90');

        $this->assertSame(['90.00'], NilaiResource::getEloquentQuery()->pluck('nilai')->map(fn ($value) => number_format((float) $value, 2, '.', ''))->all());
    }

    private function user(string $roleCode, string $username): User
    {
        $role = Role::query()->firstOrCreate(['code' => $roleCode], ['name' => str($roleCode)->headline()]);

        return User::query()->create([
            'role_id' => $role->id,
            'username' => $username,
            'name' => str($username)->headline(),
            'email' => "{$username}@sekolah.test",
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
    }

    private function student(string $username, string $nis): array
    {
        [$semester, $kelas] = $this->core();
        $user = $this->user('siswa', $username);
        $siswa = Siswa::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'nama' => $user->name,
            'kelas_id' => $kelas->id,
            'status' => 'active',
        ]);

        return [$user, $siswa];
    }

    private function core(): array
    {
        $kelas = Kelas::query()->firstOrCreate(['kode_kelas' => 'VII-A'], [
            'nama_kelas' => 'VII A',
            'tingkat' => 'VII',
            'tahun_ajaran' => '2026/2027',
        ]);
        $mapel = MataPelajaran::query()->firstOrCreate(['kode_mapel' => 'MTK'], [
            'nama_mapel' => 'Matematika',
        ]);
        $semester = Semester::query()->firstOrCreate([
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ], ['is_active' => true]);

        return [$semester, $kelas, $mapel];
    }
}
