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
                TextEntry::make('siswa.nama')
                    ->label('Nama Siswa'),
                TextEntry::make('kelas.nama_kelas')
                    ->label('Kelas'),
                TextEntry::make('mataPelajaran.nama_mapel')
                    ->label('Mata Pelajaran'),
                TextEntry::make('guru.nama')
                    ->label('Nama Guru')
                    ->placeholder('-'),
                TextEntry::make('semester.semester')
                    ->label('Semester'),
                TextEntry::make('semester.tahun_ajaran')
                    ->label('Tahun Ajaran'),
                TextEntry::make('jenis_nilai'),
                TextEntry::make('nilai')
                    ->numeric(),
                TextEntry::make('catatan')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
