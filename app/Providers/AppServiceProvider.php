<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $this->logAuthAuditEventUser($event->user, 'login', 'User logged in');
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $this->logAuthAuditEventUser($event->user, 'logout', 'User logged out');
        });
    }

    private function logAuthAuditEventUser(mixed $authUser, string $action, string $summary): void
    {
        if (! $authUser instanceof User) {
            return;
        }

        $authUser->loadMissing('role');

        app(AuditLogger::class)->log($action, $authUser, [
            'summary' => $summary,
            'username' => $authUser->username,
            'role' => $authUser->role?->code,
        ]);
    }
}
