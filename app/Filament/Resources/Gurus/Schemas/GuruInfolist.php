<?php

namespace App\Filament\Resources\Gurus\Schemas;

use App\Models\Guru;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GuruInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('nip')
                    ->placeholder('-'),
                TextEntry::make('nama'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Guru $record): bool => $record->trashed()),
            ]);
    }
}
