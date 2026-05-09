<?php

namespace App\Filament\Resources\Pembayarans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
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
                    ->label('Siswa')
                    ->searchable(),
                TextColumn::make('semester.tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable(),
                TextColumn::make('semester.semester')
                    ->label('Semester')
                    ->badge(),
                TextColumn::make('jenis_pembayaran')
                    ->searchable(),
                ImageColumn::make('latestBukti.file_path')
                    ->label('Bukti')
                    ->disk('public')
                    ->defaultImageUrl(null)
                    ->getStateUsing(fn ($record): ?string => in_array(
                        strtolower((string) $record->latestBukti?->file_type),
                        ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        true,
                    ) ? $record->latestBukti?->file_path : null)
                    ->visibility('public')
                    ->square(),
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
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
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
