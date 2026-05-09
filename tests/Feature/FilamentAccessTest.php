<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\BuktiPembayaran;
use App\Models\Pembayaran;
use App\Models\Pengampu;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use App\Services\GuruAccountService;
use App\Services\KelasProvisioningService;
use App\Filament\Resources\Nilais\NilaiResource;
use App\Filament\Widgets\StudentsPerAcademicYearChart;
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

    public function test_admin_dashboard_shows_student_chart_and_summary_stats(): void
    {
        $admin = $this->user('admin', 'admin.dashboard');
        $guruUser = $this->user('guru', 'guru.dashboard');
        $semester = Semester::query()->create([
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $kelas2024 = Kelas::query()->create([
            'kode_kelas' => 'VII-24',
            'nama_kelas' => 'VII 2024',
            'tingkat' => 'VII',
            'tahun_ajaran' => '2024/2025',
            'status' => 'active',
        ]);
        $kelas2025 = Kelas::query()->create([
            'kode_kelas' => 'VIII-25',
            'nama_kelas' => 'VIII 2025',
            'tingkat' => 'VIII',
            'tahun_ajaran' => '2025/2026',
            'status' => 'active',
        ]);
        $kelas2026 = Kelas::query()->create([
            'kode_kelas' => 'IX-26',
            'nama_kelas' => 'IX 2026',
            'tingkat' => 'IX',
            'tahun_ajaran' => '2026/2027',
            'status' => 'active',
        ]);

        Guru::query()->create([
            'user_id' => $guruUser->id,
            'nip' => '198801012026011999',
            'nama' => $guruUser->name,
            'status' => 'active',
        ]);

        $this->createStudent('siswa.chart.one', 'CH001', $kelas2024->id);
        $this->createStudent('siswa.chart.two', 'CH002', $kelas2025->id);
        $this->createStudent('siswa.chart.three', 'CH003', $kelas2025->id);
        [, $siswaAktif] = $this->createStudent('siswa.chart.four', 'CH004', $kelas2026->id);
        [, $siswaTagihan] = $this->createStudent('siswa.chart.five', 'CH005', $kelas2026->id);

        Pembayaran::query()->create([
            'siswa_id' => $siswaTagihan->id,
            'semester_id' => $semester->id,
            'jenis_pembayaran' => 'SPP',
            'jumlah_tagihan' => 500000,
            'jumlah_dibayar' => 0,
            'status' => 'Menunggu Verifikasi',
            'created_by' => $admin->id,
        ]);
        Pembayaran::query()->create([
            'siswa_id' => $siswaAktif->id,
            'semester_id' => $semester->id,
            'jenis_pembayaran' => 'Daftar Ulang',
            'jumlah_tagihan' => 750000,
            'jumlah_dibayar' => 750000,
            'status' => 'Lunas',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Ringkasan Akademik')
            ->assertSee('Total Siswa')
            ->assertSee('5')
            ->assertSee('Total Guru')
            ->assertSee('1')
            ->assertSee('Total Kelas')
            ->assertSee('3')
            ->assertSee('Pembayaran Menunggu Verifikasi')
            ->assertSee('Siswa per Tahun Ajaran');

        $chartData = $this->callProtected(new StudentsPerAcademicYearChart(), 'getData');

        $this->assertSame(['2024/2025', '2025/2026', '2026/2027'], $chartData['labels']);
        $this->assertSame([1, 2, 2], $chartData['datasets'][0]['data']);
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

    public function test_pembayaran_view_shows_student_class_handler_and_proof(): void
    {
        $admin = $this->user('admin', 'admin.payment.view');
        [$semester, $kelas] = $this->core();
        [, $siswa] = $this->createStudent('siswa.payment.view', 'PAY001', $kelas->id);
        $handler = $this->user('staf_tu', 'petugas.payment.view');

        $pembayaran = Pembayaran::query()->create([
            'siswa_id' => $siswa->id,
            'semester_id' => $semester->id,
            'jenis_pembayaran' => 'SPP',
            'jumlah_tagihan' => 500000,
            'jumlah_dibayar' => 500000,
            'status' => 'Lunas',
            'verified_by' => $handler->id,
            'verified_at' => now(),
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        BuktiPembayaran::query()->create([
            'pembayaran_id' => $pembayaran->id,
            'file_name' => 'bukti.png',
            'file_path' => 'bukti-pembayaran/bukti.png',
            'file_type' => 'png',
            'file_size' => 1024,
            'uploaded_by' => $admin->id,
            'uploaded_at' => now(),
        ]);

        $this->actingAs($admin)->get("/admin/pembayarans/{$pembayaran->id}")
            ->assertOk()
            ->assertSee($siswa->nama)
            ->assertSee($handler->name)
            ->assertSee($kelas->nama_kelas)
            ->assertSee('Download Bukti');
    }

    public function test_bukti_pembayaran_resource_is_not_accessible_anymore(): void
    {
        $admin = $this->user('admin', 'admin.bukti.disabled');

        $this->actingAs($admin)->get('/admin/bukti-pembayarans')->assertForbidden();
    }

    public function test_create_guru_page_uses_inline_account_fields_instead_of_user_dropdown(): void
    {
        $admin = $this->user('admin', 'admin.guru.create');

        $this->actingAs($admin)->get('/admin/gurus/create')
            ->assertOk()
            ->assertDontSee('data[user_id]')
            ->assertSee('Username')
            ->assertSee('Password')
            ->assertSee('Alamat')
            ->assertSee('No. Telepon')
            ->assertSee('Foto')
            ->assertSee('Mata Pelajaran')
            ->assertSee('Kelas')
            ->assertSee('Semester');
    }

    public function test_guru_account_service_creates_guru_with_guru_role_profile_fields_and_pengampu(): void
    {
        $service = app(GuruAccountService::class);
        [$semester, $kelas, $mapel] = $this->core();

        $guru = $service->create([
            'nip' => '198801012026011234',
            'nama' => 'Rina Marlina',
            'username' => 'rina.marlina',
            'email' => 'rina@sekolah.test',
            'password' => 'password123',
            'alamat' => 'Jl. Melati No. 10',
            'no_telp' => '081234567890',
            'foto_path' => 'guru-photos/rina.jpg',
            'status' => 'active',
            'pengampu' => [[
                'kelas_id' => $kelas->id,
                'mata_pelajaran_id' => $mapel->id,
                'semester_id' => $semester->id,
            ]],
        ]);

        $this->assertSame('Rina Marlina', $guru->nama);
        $this->assertSame('Jl. Melati No. 10', $guru->alamat);
        $this->assertSame('081234567890', $guru->no_telp);
        $this->assertSame('guru-photos/rina.jpg', $guru->foto_path);
        $this->assertSame('guru', $guru->user->role->code);
        $this->assertSame('rina.marlina', $guru->user->username);
        $this->assertSame('rina@sekolah.test', $guru->user->email);
        $this->assertSame('active', $guru->user->status);
        $this->assertTrue(Hash::check('password123', $guru->user->password));
        $this->assertCount(1, $guru->pengampu);
        $this->assertSame($kelas->id, $guru->pengampu->first()->kelas_id);
        $this->assertSame($mapel->id, $guru->pengampu->first()->mata_pelajaran_id);
        $this->assertSame($semester->id, $guru->pengampu->first()->semester_id);
    }

    public function test_guru_detail_shows_subjects_and_classes_taught(): void
    {
        $admin = $this->user('admin', 'admin.guru.view');
        $guruUser = $this->user('guru', 'guru.mapel');
        [$semester, $kelas, $mapel] = $this->core();
        $kelasB = Kelas::query()->create([
            'kode_kelas' => 'VII-B',
            'nama_kelas' => 'VII B',
            'tingkat' => 'VII',
            'tahun_ajaran' => '2026/2027',
            'status' => 'active',
        ]);
        $mapelIpa = MataPelajaran::query()->create([
            'kode_mapel' => 'IPA',
            'nama_mapel' => 'Ilmu Pengetahuan Alam',
            'status' => 'active',
        ]);

        $guru = Guru::query()->create([
            'user_id' => $guruUser->id,
            'nip' => '198801012026011235',
            'nama' => 'Guru Mapel',
            'alamat' => 'Jl. Guru',
            'no_telp' => '081111111111',
            'foto_path' => 'guru-photos/guru-mapel.jpg',
            'status' => 'active',
        ]);

        Pengampu::query()->create([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'semester_id' => $semester->id,
        ]);
        Pengampu::query()->create([
            'guru_id' => $guru->id,
            'kelas_id' => $kelasB->id,
            'mata_pelajaran_id' => $mapelIpa->id,
            'semester_id' => $semester->id,
        ]);

        $this->actingAs($admin)->get("/admin/gurus/{$guru->id}")
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Ilmu Pengetahuan Alam')
            ->assertSee('VII A')
            ->assertSee('VII B')
            ->assertSee('081111111111')
            ->assertSee('storage/guru-photos/guru-mapel.jpg');
    }

    public function test_guru_index_does_not_show_photo_column(): void
    {
        $admin = $this->user('admin', 'admin.guru.index');
        $guruUser = $this->user('guru', 'guru.index');

        Guru::query()->create([
            'user_id' => $guruUser->id,
            'nip' => '198801012026011236',
            'nama' => 'Guru Index',
            'foto_path' => 'guru-photos/guru-index.jpg',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/admin/gurus')
            ->assertOk()
            ->assertDontSee('storage/guru-photos/guru-index.jpg');
    }

    public function test_guru_view_shows_large_photo_download_and_pdf_export(): void
    {
        $admin = $this->user('admin', 'admin.guru.pdf');
        $guruUser = $this->user('guru', 'guru.pdf');

        $guru = Guru::query()->create([
            'user_id' => $guruUser->id,
            'nip' => '198801012026011237',
            'nama' => 'Guru PDF',
            'foto_path' => 'guru-photos/guru-pdf.jpg',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get("/admin/gurus/{$guru->id}")
            ->assertOk()
            ->assertSee('storage/guru-photos/guru-pdf.jpg')
            ->assertSee('Download Foto')
            ->assertSee('Export PDF');

        $this->actingAs($admin)->get("/admin/gurus/{$guru->id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guru_index_shows_pdf_export_and_can_download_pdf(): void
    {
        $admin = $this->user('admin', 'admin.guru.list.pdf');
        $guruUser = $this->user('guru', 'guru.list.pdf');

        Guru::query()->create([
            'user_id' => $guruUser->id,
            'nip' => '198801012026011238',
            'nama' => 'Guru List PDF',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/admin/gurus')
            ->assertOk()
            ->assertSee('Export PDF');

        $this->actingAs($admin)->get('/admin/reports/gurus/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_create_kelas_page_uses_structured_fields_and_not_manual_name_inputs(): void
    {
        $admin = $this->user('admin', 'admin.kelas.create');

        $this->actingAs($admin)->get('/admin/kelas/create')
            ->assertOk()
            ->assertSee('Jenjang')
            ->assertSee('Tingkat')
            ->assertSee('Rombel')
            ->assertSee('Tahun ajaran')
            ->assertDontSee('data[kode_kelas]')
            ->assertDontSee('data[nama_kelas]');
    }

    public function test_kelas_provisioning_service_generates_class_identity_and_semesters(): void
    {
        $kelas = app(KelasProvisioningService::class)->create([
            'jenjang' => 'SMP',
            'tingkat' => '7',
            'rombel' => 'A',
            'tahun_ajaran' => '2027/2028',
            'status' => 'active',
        ]);

        $this->assertSame('SMP-VIIA-2027', $kelas->kode_kelas);
        $this->assertSame('VII A - 2027/2028', $kelas->nama_kelas);
        $this->assertSame('VII', $kelas->tingkat);
        $this->assertSame('2027/2028', $kelas->tahun_ajaran);

        $this->assertDatabaseHas('semester', [
            'tahun_ajaran' => '2027/2028',
            'semester' => 'Ganjil',
        ]);
        $this->assertDatabaseHas('semester', [
            'tahun_ajaran' => '2027/2028',
            'semester' => 'Genap',
        ]);
    }

    public function test_kelas_provisioning_service_accepts_roman_grade_input(): void
    {
        $kelas = app(KelasProvisioningService::class)->create([
            'jenjang' => 'SMA',
            'tingkat' => 'XI',
            'rombel' => 'B',
            'tahun_ajaran' => '2028/2029',
            'status' => 'active',
        ]);

        $this->assertSame('SMA-XIB-2028', $kelas->kode_kelas);
        $this->assertSame('XI B - 2028/2029', $kelas->nama_kelas);
        $this->assertSame('XI', $kelas->tingkat);
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

    private function createStudent(string $username, string $nis, int $kelasId): array
    {
        $user = $this->user('siswa', $username);
        $siswa = Siswa::query()->create([
            'user_id' => $user->id,
            'nis' => $nis,
            'nama' => $user->name,
            'kelas_id' => $kelasId,
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

    private function callProtected(object $instance, string $method): mixed
    {
        $reflection = new \ReflectionMethod($instance, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($instance);
    }
}
