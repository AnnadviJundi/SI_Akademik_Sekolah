<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Pembayaran;
use App\Models\Pengampu;
use App\Models\Profil;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect([
            'admin' => 'Admin',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            'staf_tu' => 'Staf TU',
        ])->map(fn (string $name, string $code) => Role::query()->firstOrCreate(['code' => $code], ['name' => $name]));

        $admin = User::query()->updateOrCreate(['username' => 'admin'], [
            'role_id' => $roles['admin']->id,
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $tu = User::query()->updateOrCreate(['username' => 'staf.tu'], [
            'role_id' => $roles['staf_tu']->id,
            'name' => 'Staf TU',
            'email' => 'tu@sekolah.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $guruUser = User::query()->updateOrCreate(['username' => 'guru.matematika'], [
            'role_id' => $roles['guru']->id,
            'name' => 'Budi Santoso',
            'email' => 'guru@sekolah.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $siswaUser = User::query()->updateOrCreate(['username' => 'siswa.andi'], [
            'role_id' => $roles['siswa']->id,
            'name' => 'Andi Saputra',
            'email' => 'andi@sekolah.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $kelas = Kelas::query()->updateOrCreate(['kode_kelas' => 'VII-A'], [
            'nama_kelas' => 'VII A',
            'tingkat' => 'VII',
            'tahun_ajaran' => '2026/2027',
            'status' => 'active',
        ]);

        $mapel = MataPelajaran::query()->updateOrCreate(['kode_mapel' => 'MTK'], [
            'nama_mapel' => 'Matematika',
            'status' => 'active',
        ]);

        $semester = Semester::query()->updateOrCreate([
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ], ['is_active' => true]);

        $guru = Guru::query()->updateOrCreate(['user_id' => $guruUser->id], [
            'nip' => '198801012026011001',
            'nama' => $guruUser->name,
            'status' => 'active',
        ]);

        $siswa = Siswa::query()->updateOrCreate(['user_id' => $siswaUser->id], [
            'nis' => 'SIS001',
            'nama' => $siswaUser->name,
            'kelas_id' => $kelas->id,
            'status' => 'active',
        ]);

        Pengampu::query()->updateOrCreate([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'semester_id' => $semester->id,
        ]);

        Nilai::query()->updateOrCreate([
            'siswa_id' => $siswa->id,
            'semester_id' => $semester->id,
            'mata_pelajaran_id' => $mapel->id,
            'jenis_nilai' => 'nilai_akhir',
        ], [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'nilai' => 88,
            'catatan' => 'Sampel nilai awal',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        Pembayaran::query()->updateOrCreate([
            'siswa_id' => $siswa->id,
            'semester_id' => $semester->id,
            'jenis_pembayaran' => 'SPP',
        ], [
            'jumlah_tagihan' => 500000,
            'jumlah_dibayar' => 250000,
            'status' => 'Menunggu Verifikasi',
            'created_by' => $tu->id,
            'updated_by' => $tu->id,
        ]);

        Profil::query()->updateOrCreate(['id' => 1], [
            'nama_sekolah' => 'SMA Akademik Nusantara',
            'alamat' => 'Jl. Pendidikan No. 1',
            'telepon' => '021-123456',
            'email' => 'info@sekolah.test',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }
}
