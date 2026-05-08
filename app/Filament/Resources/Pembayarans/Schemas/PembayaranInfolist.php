<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use App\Models\Pembayaran;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PembayaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('siswa.id')
                    ->label('Siswa'),
                TextEntry::make('semester.id')
                    ->label('Semester'),
                TextEntry::make('jenis_pembayaran'),
                TextEntry::make('jumlah_tagihan')
                    ->numeric(),
                TextEntry::make('jumlah_dibayar')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('verified_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Pembayaran $record): bool => $record->trashed()),
            ]);
    }
}
