<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use App\Filament\RoleGate;
use App\Models\Pembayaran;
use App\Services\PembayaranReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PembayaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->searchable(),
                TextColumn::make('semester.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable(),
                TextColumn::make('semester.semester')
                    ->label('Semester')
                    ->badge(),
                TextColumn::make('jenis_pembayaran')
                    ->searchable(),
                TextColumn::make('jumlah_tagihan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jumlah_dibayar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('verifiedByUser.name')
                    ->label('Petugas Penangan')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),
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
                Action::make('verify')
                    ->label('Verifikasi')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Pembayaran $record): bool => RoleGate::has('admin', 'staf_tu') && $record->status === 'Menunggu Verifikasi')
                    ->action(fn (Pembayaran $record) => app(PembayaranReviewService::class)->verify($record)),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Pembayaran $record): bool => RoleGate::has('admin', 'staf_tu') && $record->status === 'Menunggu Verifikasi')
                    ->action(fn (Pembayaran $record) => app(PembayaranReviewService::class)->reject($record)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
