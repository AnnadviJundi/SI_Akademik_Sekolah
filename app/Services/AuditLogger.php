<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\Audit\AuditProperties;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogger
{
    public function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        try {
            AuditLog::query()->create([
                'user_id' => Auth::id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'properties' => AuditProperties::from($properties),
                'ip_address' => request()?->ip(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Failed to write audit log.', [
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
