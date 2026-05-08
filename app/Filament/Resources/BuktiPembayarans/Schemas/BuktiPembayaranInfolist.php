<?php

namespace App\Filament\Resources\BuktiPembayarans\Schemas;

use App\Models\BuktiPembayaran;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BuktiPembayaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('pembayaran.id')
                    ->label('Pembayaran'),
                TextEntry::make('file_name'),
                TextEntry::make('file_path'),
                TextEntry::make('file_type'),
                TextEntry::make('file_size')
                    ->numeric(),
                TextEntry::make('uploaded_by')
                    ->numeric(),
                TextEntry::make('uploaded_at')
                    ->dateTime(),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (BuktiPembayaran $record): bool => $record->trashed()),
            ]);
    }
}
