<?php

namespace App\Filament\Resources\MataPelajarans\Schemas;

use App\Models\MataPelajaran;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MataPelajaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_mapel'),
                TextEntry::make('nama_mapel'),
                TextEntry::make('guru_pengampu_list')
                    ->label('Nama Guru Pengampu')
                    ->placeholder('-'),
                TextEntry::make('kelas_diampu_list')
                    ->label('Kelas Diampu')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (MataPelajaran $record): bool => $record->trashed()),
            ]);
    }
}
