<?php

namespace App\Filament\Resources\Nilais\Schemas;

use App\Models\Nilai;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NilaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('siswa.id')
                    ->label('Siswa'),
                TextEntry::make('kelas.id')
                    ->label('Kelas'),
                TextEntry::make('mataPelajaran.id')
                    ->label('Mata pelajaran'),
                TextEntry::make('guru.id')
                    ->label('Guru')
                    ->placeholder('-'),
                TextEntry::make('semester.id')
                    ->label('Semester'),
                TextEntry::make('jenis_nilai'),
                TextEntry::make('nilai')
                    ->numeric(),
                TextEntry::make('catatan')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                    ->visible(fn (Nilai $record): bool => $record->trashed()),
            ]);
    }
}
