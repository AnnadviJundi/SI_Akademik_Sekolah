<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'properties', 'ip_address'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(string $key, mixed $default = null): mixed
    {
        return data_get($this->properties, $key, $default);
    }

    public function scopeApplyAdminFilters(Builder $query, array $filters): Builder
    {
        $userId = $this->filterValue($filters, 'user_id');
        $action = $this->filterValue($filters, 'action');
        $period = $this->filterValue($filters, 'period');
        $semesterId = $this->filterValue($filters, 'semester_id');

        $query
            ->when(filled($userId), fn (Builder $builder) => $builder->where('user_id', $userId))
            ->when(filled($action), fn (Builder $builder) => $builder->where('action', $action));

        if ($period === 'daily') {
            $query->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
        }

        if ($period === 'weekly') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        if ($period === 'yearly') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        }

        if ($period === 'semester' || filled($semesterId)) {
            $semester = Semester::query()->find($semesterId)
                ?? Semester::query()->where('is_active', true)->latest('id')->first();

            if (! $semester) {
                $query->whereRaw('1 = 0');

                return $query;
            }

            [$start, $end] = $semester->approximatePeriodRange();

            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query;
    }

    public function getActionLabelAttribute(): string
    {
        return Str::of($this->action)
            ->replace(['.', '_'], ' ')
            ->headline()
            ->value();
    }

    public function getSubjectLabelAttribute(): string
    {
        if (blank($this->subject_type) || blank($this->subject_id)) {
            return '-';
        }

        $subject = Str::of(class_basename($this->subject_type))
            ->replace(['_', '-'], ' ')
            ->headline();

        return "{$subject} #{$this->subject_id}";
    }

    public function getSummaryLabelAttribute(): string
    {
        return $this->property('summary')
            ?? $this->property('description')
            ?? $this->action_label;
    }

    private function filterValue(array $filters, string $name, mixed $default = null): mixed
    {
        return data_get($filters, "{$name}.value", $default);
    }
}
