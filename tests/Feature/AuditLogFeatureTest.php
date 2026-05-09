<?php

namespace Tests\Feature;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_creates_audit_log_record(): void
    {
        $user = $this->user('admin', 'audit.actor');
        $subject = $this->user('guru', 'audit.subject');

        $this->actingAs($user);

        app(AuditLogger::class)->log('users.updated', $subject, [
            'changes' => ['status' => 'active'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'users.updated',
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->id,
        ]);

        $auditLog = AuditLog::query()->sole();

        $this->assertSame(['changes' => ['status' => 'active']], $auditLog->properties);
    }

    public function test_audit_logger_allows_null_subject(): void
    {
        $user = $this->user('admin', 'audit.null-subject');

        $this->actingAs($user);

        app(AuditLogger::class)->log('auth.logged_out');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.logged_out',
            'subject_type' => null,
            'subject_id' => null,
        ]);
    }

    public function test_audit_logger_normalizes_nested_models_in_properties(): void
    {
        $user = $this->user('admin', 'audit.nested.actor');
        $subject = $this->user('guru', 'audit.nested.subject');
        $related = $this->user('siswa', 'audit.nested.related');

        $this->actingAs($user);

        app(AuditLogger::class)->log('users.synced', $subject, [
            'payload' => [
                'related' => $related,
                'items' => [
                    ['model' => $subject],
                ],
            ],
        ]);

        $auditLog = AuditLog::query()->sole();

        $this->assertSame([
            'payload' => [
                'related' => [
                    'type' => $related->getMorphClass(),
                    'id' => $related->getKey(),
                ],
                'items' => [
                    [
                        'model' => [
                            'type' => $subject->getMorphClass(),
                            'id' => $subject->getKey(),
                        ],
                    ],
                ],
            ],
        ], $auditLog->properties);
    }

    public function test_audit_logger_stores_null_user_id_for_guests(): void
    {
        app(AuditLogger::class)->log('system.heartbeat');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'system.heartbeat',
        ]);
    }

    public function test_successful_login_creates_an_audit_log(): void
    {
        $user = $this->user('admin', 'audit.login');

        Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->set('data.username', 'audit.login')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
        $this->assertSame(1, AuditLog::query()->where('action', 'login')->count());

        $auditLog = AuditLog::query()->where('action', 'login')->sole();

        $this->assertSame([
            'summary' => 'User logged in',
            'username' => 'audit.login',
            'role' => 'admin',
        ], $auditLog->properties);
    }

    public function test_logout_creates_an_audit_log(): void
    {
        $user = $this->user('admin', 'audit.logout');

        $this->actingAs($user)
            ->post(route('filament.admin.auth.logout'));

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
        $this->assertSame(1, AuditLog::query()->where('action', 'logout')->count());

        $auditLog = AuditLog::query()->where('action', 'logout')->sole();

        $this->assertSame([
            'summary' => 'User logged out',
            'username' => 'audit.logout',
            'role' => 'admin',
        ], $auditLog->properties);
    }

    public function test_failed_login_does_not_create_a_login_audit_log(): void
    {
        $this->user('admin', 'audit.failed-login');

        Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->set('data.username', 'audit.failed-login')
            ->set('data.password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors(['data.username']);

        $this->assertGuest();
        $this->assertSame(0, AuditLog::query()->where('action', 'login')->count());
    }

    public function test_admin_can_filter_audit_logs_by_user_and_action(): void
    {
        $admin = $this->user('admin', 'audit.filter-admin');
        $targetUser = $this->user('guru', 'audit.filter-target');
        $otherUser = $this->user('guru', 'audit.filter-other');

        $matchingLog = $this->createAuditLog(
            user: $targetUser,
            action: 'users.updated',
            createdAt: CarbonImmutable::parse('2026-05-09 10:00:00'),
            properties: ['summary' => 'Target updated profile'],
        );
        $otherActionLog = $this->createAuditLog(
            user: $targetUser,
            action: 'users.deleted',
            createdAt: CarbonImmutable::parse('2026-05-09 11:00:00'),
            properties: ['summary' => 'Target deleted profile'],
        );
        $otherUserLog = $this->createAuditLog(
            user: $otherUser,
            action: 'users.updated',
            createdAt: CarbonImmutable::parse('2026-05-09 12:00:00'),
            properties: ['summary' => 'Other updated profile'],
        );

        $this->actingAs($admin);

        Livewire::test(ListAuditLogs::class)
            ->filterTable('user_id', $targetUser->id)
            ->filterTable('action', 'users.updated')
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$otherActionLog, $otherUserLog]);
    }

    public function test_admin_can_filter_audit_logs_by_semester_period(): void
    {
        $admin = $this->user('admin', 'audit.semester-admin');
        $actor = $this->user('guru', 'audit.semester-actor');
        $semester = Semester::query()->create([
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $insideSemester = $this->createAuditLog(
            user: $actor,
            action: 'nilai.updated',
            createdAt: CarbonImmutable::parse('2026-09-10 08:30:00'),
            properties: ['summary' => 'Inside semester'],
        );
        $outsideSemester = $this->createAuditLog(
            user: $actor,
            action: 'nilai.updated',
            createdAt: CarbonImmutable::parse('2027-02-10 08:30:00'),
            properties: ['summary' => 'Outside semester'],
        );

        $this->actingAs($admin);

        Livewire::test(ListAuditLogs::class)
            ->filterTable('period', 'semester')
            ->filterTable('semester_id', $semester->id)
            ->assertCanSeeTableRecords([$insideSemester])
            ->assertCanNotSeeTableRecords([$outsideSemester]);
    }

    public function test_semester_period_filter_without_selected_or_active_semester_returns_no_rows(): void
    {
        $admin = $this->user('admin', 'audit.semester-empty-admin');
        $actor = $this->user('guru', 'audit.semester-empty-actor');

        $firstLog = $this->createAuditLog(
            user: $actor,
            action: 'users.updated',
            createdAt: CarbonImmutable::parse('2026-05-09 10:00:00'),
            properties: ['summary' => 'First visible without filter'],
        );
        $secondLog = $this->createAuditLog(
            user: $actor,
            action: 'users.deleted',
            createdAt: CarbonImmutable::parse('2026-05-09 11:00:00'),
            properties: ['summary' => 'Second visible without filter'],
        );

        $this->actingAs($admin);

        Livewire::test(ListAuditLogs::class)
            ->filterTable('period', 'semester')
            ->assertCanNotSeeTableRecords([$firstLog, $secondLog])
            ->assertCountTableRecords(0);
    }

    public function test_export_query_matches_table_search_state(): void
    {
        $admin = $this->user('admin', 'audit.export-admin');
        $actor = $this->user('guru', 'audit.export-target');

        $matchingLog = $this->createAuditLog(
            user: $actor,
            action: 'users.updated',
            createdAt: CarbonImmutable::parse('2026-05-09 10:00:00'),
            properties: ['summary' => 'Alpha search summary'],
        );
        $nonMatchingLog = $this->createAuditLog(
            user: $actor,
            action: 'users.deleted',
            createdAt: CarbonImmutable::parse('2026-05-09 10:05:00'),
            properties: ['summary' => 'Beta search summary'],
        );

        $this->actingAs($admin);

        $component = Livewire::test(ListAuditLogs::class)
            ->set('tableSearch', 'users.updated')
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$nonMatchingLog]);

        $this->assertSame(
            [$matchingLog->id],
            $component->instance()->getTableQueryForExport()->pluck('id')->all(),
        );
    }

    public function test_admin_can_export_filtered_audit_logs_to_pdf(): void
    {
        $admin = $this->user('admin', 'audit.export-pdf-admin');
        $actor = $this->user('guru', 'audit.export-pdf-target');

        $matchingLog = $this->createAuditLog(
            user: $actor,
            action: 'users.updated',
            createdAt: CarbonImmutable::parse('2026-05-09 10:00:00'),
            properties: ['summary' => 'Included in export'],
        );
        $this->createAuditLog(
            user: $actor,
            action: 'users.deleted',
            createdAt: CarbonImmutable::parse('2026-05-09 10:05:00'),
            properties: ['summary' => 'Excluded from export'],
        );

        $this->actingAs($admin);

        Livewire::test(ListAuditLogs::class)
            ->set('tableSearch', 'users.updated')
            ->assertCanSeeTableRecords([$matchingLog])
            ->call('exportPdf')
            ->assertFileDownloaded(contentType: 'application/pdf');
    }

    private function user(string $roleCode, string $username): User
    {
        $role = Role::query()->firstOrCreate(
            ['code' => $roleCode],
            ['name' => str($roleCode)->headline()],
        );

        return User::query()->create([
            'role_id' => $role->id,
            'username' => $username,
            'name' => str($username)->headline(),
            'email' => "{$username}@sekolah.test",
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
    }

    private function createAuditLog(User $user, string $action, CarbonImmutable $createdAt, array $properties = []): AuditLog
    {
        $auditLog = AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
            'properties' => $properties,
            'ip_address' => '127.0.0.1',
        ]);

        $auditLog->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $auditLog->fresh();
    }
}
