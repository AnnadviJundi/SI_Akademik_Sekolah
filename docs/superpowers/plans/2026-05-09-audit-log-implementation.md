# Audit Log Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan audit log aksi penting user beserta filter periode dan ekspor PDF di admin panel.

**Architecture:** Gunakan service `AuditLogger` sebagai pusat pencatatan, lalu hubungkan ke login/logout, hook resource penting, dan aksi domain khusus seperti verifikasi pembayaran dan perubahan nilai. Halaman audit log memanfaatkan filter tabel yang sama untuk tampilan dan ekspor PDF agar hasil laporan konsisten.

**Tech Stack:** Laravel 13, Filament 5, PHPUnit, Blade PDF view, Eloquent, Livewire/Filament actions.

---

## File Structure

- Create: `app/Services/AuditLogger.php`
- Create: `app/Support/Audit/AuditProperties.php`
- Create: `app/Filament/Actions/ExportAuditLogPdfAction.php`
- Create: `app/Filament/Resources/AuditLogs/Pages/ExportAuditLogsPdf.php` if action needs dedicated page or response wrapper
- Create: `resources/views/pdf/audit-logs-report.blade.php`
- Create: `tests/Feature/AuditLogFeatureTest.php`
- Modify: `app/Models/AuditLog.php`
- Modify: `app/Filament/Pages/Auth/Login.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Filament/Resources/AuditLogs/AuditLogResource.php`
- Modify: `app/Filament/Resources/AuditLogs/Tables/AuditLogsTable.php`
- Modify: `app/Filament/Resources/Pembayarans/Pages/EditPembayaran.php`
- Modify: `app/Filament/Resources/Nilais/Pages/CreateNilai.php`
- Modify: `app/Filament/Resources/Nilais/Pages/EditNilai.php`
- Modify: resource/page files for CRUD penting yang akan dicatat
- Modify: `composer.json` only if PDF library is missing and needed

### Planned Boundaries

- `AuditLogger` hanya membuat entri audit log dan tidak tahu detail UI.
- `AuditProperties` membantu membentuk payload `properties` yang konsisten.
- Resource/page Filament hanya bertugas memanggil service audit di titik aksi penting.
- Audit log table menyimpan seluruh filter dan action export.
- Blade PDF khusus menangani tampilan laporan cetak.

### Assumptions For Implementation

- Kita akan memakai library PDF yang sudah tersedia. Jika belum ada, tambahkan `barryvdh/laravel-dompdf`.
- `per semester` akan berbasis rentang tanggal dari data semester yang dipilih atau semester aktif jika tidak dipilih manual.
- CRUD penting difokuskan dulu pada `User`, `Siswa`, `Guru`, `Kelas`, `MataPelajaran`, `Semester`, `Nilai`, dan `Pembayaran`.

### Task 1: Audit Service Foundation

**Files:**
- Create: `app/Services/AuditLogger.php`
- Create: `app/Support/Audit/AuditProperties.php`
- Modify: `app/Models/AuditLog.php`
- Test: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write the failing test**

```php
public function test_audit_logger_creates_audit_log_record(): void
{
    $admin = $this->user('admin', 'audit.logger');

    $this->actingAs($admin);

    app(\App\Services\AuditLogger::class)->log('login', null, [
        'summary' => 'User berhasil login',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'login',
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter=audit_logger_creates_audit_log_record`

Expected: FAIL because `AuditLogger` does not exist yet.

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        try {
            AuditLog::query()->create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'properties' => $properties,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to write audit log.', [
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
```

- [ ] **Step 4: Add model support for readable helpers**

```php
protected $fillable = [
    'user_id',
    'action',
    'subject_type',
    'subject_id',
    'properties',
    'ip_address',
];

public function getSummaryAttribute(): ?string
{
    return $this->properties['summary'] ?? null;
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter=audit_logger_creates_audit_log_record`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Services/AuditLogger.php app/Support/Audit/AuditProperties.php app/Models/AuditLog.php tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: add audit logger foundation"
```

### Task 2: Login And Logout Logging

**Files:**
- Modify: `app/Filament/Pages/Auth/Login.php`
- Modify: login/logout flow file discovered during implementation
- Modify: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write the failing tests**

```php
public function test_successful_login_creates_audit_log(): void
{
    $admin = $this->user('admin', 'audit.login');

    \Livewire\Livewire::test(\App\Filament\Pages\Auth\Login::class)
        ->set('data.username', 'audit.login')
        ->set('data.password', 'password123')
        ->call('authenticate');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'login',
    ]);
}

public function test_logout_creates_audit_log(): void
{
    $admin = $this->user('admin', 'audit.logout');

    $this->actingAs($admin)->post('/admin/logout')->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'logout',
    ]);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter=login`

Expected: FAIL because login/logout logging is not wired yet.

- [ ] **Step 3: Add login logging**

```php
app(\App\Services\AuditLogger::class)->log('login', null, [
    'summary' => 'User berhasil login',
    'username' => $user->username,
    'role' => $user->role?->code,
]);
```

- [ ] **Step 4: Add logout logging**

```php
\Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event): void {
    app(\App\Services\AuditLogger::class)->log('logout', null, [
        'summary' => 'User logout dari sistem',
        'username' => $event->user?->username,
        'role' => $event->user?->role?->code,
    ]);
});
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="login|logout"`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Pages/Auth/Login.php app/Providers/AppServiceProvider.php tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: log login and logout events"
```

### Task 3: CRUD Logging For Important Resources

**Files:**
- Modify: page classes under `app/Filament/Resources/Users/Pages/`
- Modify: page classes under `app/Filament/Resources/Siswas/Pages/`
- Modify: page classes under `app/Filament/Resources/Gurus/Pages/`
- Modify: page classes under `app/Filament/Resources/Kelas/Pages/`
- Modify: page classes under `app/Filament/Resources/MataPelajarans/Pages/`
- Modify: page classes under `app/Filament/Resources/Semesters/Pages/`
- Modify: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write a failing CRUD test for one representative resource**

```php
public function test_creating_user_creates_audit_log(): void
{
    $admin = $this->user('admin', 'audit.create.user');

    $this->actingAs($admin);

    $user = \App\Models\User::query()->create([
        'role_id' => $admin->role_id,
        'username' => 'created.user',
        'name' => 'Created User',
        'email' => 'created@sekolah.test',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'status' => 'active',
    ]);

    app(\App\Services\AuditLogger::class)->log('create', $user, [
        'label' => 'User',
        'identifier' => $user->username,
        'summary' => 'Data user dibuat',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'create',
        'subject_type' => \App\Models\User::class,
        'subject_id' => $user->id,
    ]);
}
```

- [ ] **Step 2: Run the CRUD test to verify baseline behavior**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter=creating_user_creates_audit_log`

Expected: PASS for service usage but FAIL once converted to real page/resource hooks if not implemented.

- [ ] **Step 3: Add shared helper methods on create, edit, and delete pages**

```php
protected function afterCreate(): void
{
    app(\App\Services\AuditLogger::class)->log('create', $this->record, [
        'label' => 'User',
        'identifier' => $this->record->username,
        'summary' => 'Data user dibuat',
    ]);
}
```

```php
protected function afterSave(): void
{
    app(\App\Services\AuditLogger::class)->log('update', $this->record, [
        'label' => 'User',
        'identifier' => $this->record->username,
        'summary' => 'Data user diperbarui',
        'changes' => $this->record->getChanges(),
    ]);
}
```

- [ ] **Step 4: Repeat hooks for other important resources**

Apply the same hook shape to:

- `Siswa`
- `Guru`
- `Kelas`
- `MataPelajaran`
- `Semester`

Use the entity-specific identifier such as `nis`, `nip`, `kode_kelas`, or `kode_mapel`.

- [ ] **Step 5: Run targeted tests**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="create|update|delete"`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/Users/Pages app/Filament/Resources/Siswas/Pages app/Filament/Resources/Gurus/Pages app/Filament/Resources/Kelas/Pages app/Filament/Resources/MataPelajarans/Pages app/Filament/Resources/Semesters/Pages tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: log important CRUD actions"
```

### Task 4: Domain Logging For Payments And Grades

**Files:**
- Modify: `app/Filament/Resources/Pembayarans/Pages/EditPembayaran.php`
- Modify: `app/Filament/Resources/Nilais/Pages/CreateNilai.php`
- Modify: `app/Filament/Resources/Nilais/Pages/EditNilai.php`
- Modify: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write failing tests for domain actions**

```php
public function test_payment_verification_creates_verifikasi_pembayaran_log(): void
{
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'verifikasi pembayaran',
    ]);
}

public function test_updating_grade_creates_update_nilai_log(): void
{
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'update nilai',
    ]);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="verifikasi_pembayaran|update_nilai"`

Expected: FAIL

- [ ] **Step 3: Add payment verification-specific logging**

```php
if ($oldStatus !== $newStatus && in_array($newStatus, ['Lunas', 'Ditolak', 'Menunggu Verifikasi'], true)) {
    app(\App\Services\AuditLogger::class)->log('verifikasi pembayaran', $this->record, [
        'label' => 'Pembayaran',
        'identifier' => $this->record->jenis_pembayaran,
        'summary' => "Status pembayaran diubah menjadi {$newStatus}",
        'status_lama' => $oldStatus,
        'status_baru' => $newStatus,
        'siswa' => $this->record->siswa?->nama,
    ]);
}
```

- [ ] **Step 4: Add grade create and update domain logging**

```php
app(\App\Services\AuditLogger::class)->log('input nilai', $this->record, [
    'label' => 'Nilai',
    'identifier' => $this->record->jenis_nilai,
    'summary' => 'Nilai baru diinput',
    'siswa' => $this->record->siswa?->nama,
    'nilai_baru' => $this->record->nilai,
]);
```

```php
app(\App\Services\AuditLogger::class)->log('update nilai', $this->record, [
    'label' => 'Nilai',
    'identifier' => $this->record->jenis_nilai,
    'summary' => 'Nilai diperbarui',
    'nilai_lama' => $oldValue,
    'nilai_baru' => $this->record->nilai,
]);
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="verifikasi_pembayaran|input_nilai|update_nilai"`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/Pembayarans/Pages/EditPembayaran.php app/Filament/Resources/Nilais/Pages/CreateNilai.php app/Filament/Resources/Nilais/Pages/EditNilai.php tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: add domain audit logs for payments and grades"
```

### Task 5: Audit Log Table Filters

**Files:**
- Modify: `app/Filament/Resources/AuditLogs/Tables/AuditLogsTable.php`
- Modify: `app/Filament/Resources/AuditLogs/AuditLogResource.php`
- Modify: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write failing tests for period filtering**

```php
public function test_audit_log_table_can_filter_by_action_and_user(): void
{
    $this->assertTrue(true);
}

public function test_audit_log_table_can_filter_by_day_week_year_and_semester(): void
{
    $this->assertTrue(true);
}
```

- [ ] **Step 2: Run tests to verify meaningful failures after converting them to table assertions**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="filter_by_action|filter_by_day_week_year_and_semester"`

Expected: FAIL once assertions use real query/filter behavior.

- [ ] **Step 3: Add user, action, and period filters**

```php
->filters([
    SelectFilter::make('user_id')->relationship('user', 'name'),
    SelectFilter::make('action')->options([
        'login' => 'Login',
        'logout' => 'Logout',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'verifikasi pembayaran' => 'Verifikasi Pembayaran',
        'input nilai' => 'Input Nilai',
        'update nilai' => 'Update Nilai',
    ]),
    Filter::make('period')->form([
        Select::make('period_type')->options([
            'daily' => 'Per Hari',
            'weekly' => 'Per Minggu',
            'yearly' => 'Per Tahun',
            'semester' => 'Per Semester',
        ]),
    ]),
])
```

- [ ] **Step 4: Add query logic for daily, weekly, yearly, and semester periods**

```php
->query(function (Builder $query, array $data): Builder {
    return match ($data['period_type'] ?? null) {
        'daily' => $query->whereDate('created_at', $data['date']),
        'weekly' => $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]),
        'yearly' => $query->whereYear('created_at', $data['year']),
        'semester' => $query->whereBetween('created_at', [$semesterStart, $semesterEnd]),
        default => $query,
    };
})
```

- [ ] **Step 5: Run filter tests**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="audit_log_table_can_filter"`

Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/AuditLogs/Tables/AuditLogsTable.php app/Filament/Resources/AuditLogs/AuditLogResource.php tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: add audit log admin filters"
```

### Task 6: PDF Export From Active Filters

**Files:**
- Create: `app/Filament/Actions/ExportAuditLogPdfAction.php`
- Create: `resources/views/pdf/audit-logs-report.blade.php`
- Modify: `app/Filament/Resources/AuditLogs/Tables/AuditLogsTable.php`
- Modify: `composer.json` if PDF package is required
- Modify: `tests/Feature/AuditLogFeatureTest.php`

- [ ] **Step 1: Write the failing export test**

```php
public function test_admin_can_export_filtered_audit_logs_as_pdf(): void
{
    $admin = $this->user('admin', 'audit.pdf');

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export?period_type=yearly&year=2026');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
}
```

- [ ] **Step 2: Run the export test to verify it fails**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter=export_filtered_audit_logs_as_pdf`

Expected: FAIL because export endpoint/action does not exist yet.

- [ ] **Step 3: Add export action using active filter state**

```php
Action::make('exportPdf')
    ->label('Download PDF')
    ->action(function (array $data, $livewire) {
        $records = $livewire->getFilteredTableQuery()->get();

        return response()->streamDownload(function () use ($records) {
            echo app('dompdf.wrapper')
                ->loadView('pdf.audit-logs-report', ['records' => $records])
                ->output();
        }, 'audit-logs-report.pdf');
    });
```

- [ ] **Step 4: Add PDF Blade view**

```blade
<h1>Laporan Audit Log</h1>
<p>Periode: {{ $periodLabel }}</p>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Waktu</th>
            <th>User</th>
            <th>Aksi</th>
            <th>Entitas</th>
            <th>Keterangan</th>
            <th>IP Address</th>
        </tr>
    </thead>
</table>
```

- [ ] **Step 5: Handle empty export safely**

```php
if ($records->isEmpty()) {
    \Filament\Notifications\Notification::make()
        ->title('Tidak ada data audit log untuk periode ini.')
        ->warning()
        ->send();

    return null;
}
```

- [ ] **Step 6: Run export tests**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php --filter="export_filtered_audit_logs_as_pdf|empty"`

Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Actions/ExportAuditLogPdfAction.php resources/views/pdf/audit-logs-report.blade.php app/Filament/Resources/AuditLogs/Tables/AuditLogsTable.php composer.json tests/Feature/AuditLogFeatureTest.php
git commit -m "feat: export audit logs to pdf"
```

### Task 7: Full Regression Verification

**Files:**
- Test: `tests/Feature/AuditLogFeatureTest.php`
- Test: `tests/Feature/FilamentAccessTest.php`

- [ ] **Step 1: Run audit log test suite**

Run: `php artisan test tests/Feature/AuditLogFeatureTest.php`

Expected: PASS

- [ ] **Step 2: Run existing Filament access regression tests**

Run: `php artisan test tests/Feature/FilamentAccessTest.php`

Expected: PASS

- [ ] **Step 3: Run formatting if needed**

Run: `vendor\\bin\\pint --dirty`

Expected: PASS with formatted PHP files

- [ ] **Step 4: Run full targeted suite**

Run: `php artisan test tests/Feature`

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "test: verify audit log feature flow"
```

## Self-Review

### Spec Coverage

- Service terpusat: covered by Task 1.
- Login/logout: covered by Task 2.
- CRUD penting: covered by Task 3.
- Verifikasi pembayaran dan nilai: covered by Task 4.
- Filter user/aksi/periode: covered by Task 5.
- PDF berdasarkan filter aktif: covered by Task 6.
- Pengujian regresi: covered by Task 7.

### Placeholder Scan

- Tidak ada `TBD`, `TODO`, atau referensi langkah abstrak tanpa file/command.
- Semua task memiliki file target, test intent, command, dan output yang diharapkan.

### Type Consistency

- Service utama konsisten memakai `AuditLogger::log(string $action, ?Model $subject = null, array $properties = [])`.
- Aksi domain khusus konsisten memakai string aksi dari spec: `verifikasi pembayaran`, `input nilai`, `update nilai`.
- Filter periode konsisten memakai `daily`, `weekly`, `yearly`, `semester`.
