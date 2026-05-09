<?php

namespace App\Filament\Resources\Siswas\Tables;

use App\Models\Kelas;
use App\Services\SiswaClassTransferService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->searchable(),
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('user.username')
                    ->label('Username')
                    ->searchable(),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable(),
                TextColumn::make('nama_ortu')
                    ->label('Orang Tua / Wali')
                    ->searchable(),
                TextColumn::make('no_telp')
                    ->label('No. Telepon')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('mutasiKelas')
                    ->label('Mutasi Kelas')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('gray')
                    ->form([
                        Select::make('kelas_id')
                            ->label('Kelas Tujuan')
                            ->options(fn (): array => Kelas::query()
                                ->where('status', 'active')
                                ->orderByDesc('tahun_ajaran')
                                ->orderBy('nama_kelas')
                                ->get()
                                ->mapWithKeys(fn (Kelas $kelas): array => [
                                    $kelas->id => "{$kelas->nama_kelas} ({$kelas->tahun_ajaran})",
                                ])
                                ->all())
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $targetClass = Kelas::query()->findOrFail($data['kelas_id']);
                        $result = app(SiswaClassTransferService::class)->moveStudentsToClass($records, $targetClass);

                        Notification::make()
                            ->title('Mutasi kelas selesai')
                            ->body(sprintf(
                                'Berhasil memindahkan %d siswa. Dilewati: %d tidak aktif, %d sudah berada di kelas tujuan.',
                                $result['moved'],
                                $result['skipped_inactive'],
                                $result['skipped_same_class'],
                            ))
                            ->success()
                            ->send();
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
