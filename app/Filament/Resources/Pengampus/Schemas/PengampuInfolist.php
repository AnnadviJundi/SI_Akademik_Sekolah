<?php

namespace App\Filament\Resources\Pengampus\Schemas;

use App\Models\Pengampu;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PengampuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('guru.id')
                    ->label('Guru'),
                TextEntry::make('kelas.id')
                    ->label('Kelas'),
                TextEntry::make('mataPelajaran.id')
                    ->label('Mata pelajaran'),
                TextEntry::make('semester.id')
                    ->label('Semester'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Pengampu $record): bool => $record->trashed()),
            ]);
    }
}
