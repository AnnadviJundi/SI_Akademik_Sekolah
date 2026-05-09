<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->formatStateUsing(fn (string $state): string => Str::of($state)->replace(['.', '_'], ' ')->headline()->value())
                    ->searchable(),
                TextColumn::make('subject_label')
                    ->label('Entitas / Subject')
                    ->searchable(['subject_type', 'subject_id']),
                TextColumn::make('summary_label')
                    ->label('Ringkasan Aktivitas')
                    ->wrap()
                    ->limit(80),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->sinceTooltip()
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options(fn (): array => AuditLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->mapWithKeys(fn (string $action, string $key): array => [
                            $key => Str::of($action)->replace(['.', '_'], ' ')->headline()->value(),
                        ])
                        ->all())
                    ->searchable(),
                SelectFilter::make('period')
                    ->label('Periode')
                    ->options([
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'yearly' => 'Tahunan',
                        'semester' => 'Semester',
                    ])
                    ->query(fn (Builder $query, array $data, HasTable $livewire): Builder => (new AuditLog())->scopeApplyAdminFilters($query, [
                        'period' => $data,
                        'semester_id' => data_get($livewire->tableFilters, 'semester_id', []),
                    ])),
                SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->options(fn (): array => Semester::query()
                        ->orderByDesc('tahun_ajaran')
                        ->orderBy('semester')
                        ->get()
                        ->mapWithKeys(fn (Semester $semester): array => [$semester->id => $semester->label])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->query(fn (Builder $query, array $data, HasTable $livewire): Builder => (new AuditLog())->scopeApplyAdminFilters($query, [
                        'period' => data_get($livewire->tableFilters, 'period', []),
                        'semester_id' => $data,
                    ])),
            ])
            ->deferFilters(false)
            ->recordActions([
                ViewAction::make(),
            ])
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action('exportPdf'),
            ]);
    }
}
