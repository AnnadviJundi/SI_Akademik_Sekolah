<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Models\Kelas;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KelasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_kelas'),
                TextEntry::make('nama_kelas'),
                TextEntry::make('tingkat'),
                TextEntry::make('tahun_ajaran'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Kelas $record): bool => $record->trashed()),
            ]);
    }
}
