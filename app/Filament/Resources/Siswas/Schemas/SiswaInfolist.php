<?php

namespace App\Filament\Resources\Siswas\Schemas;

use App\Models\Siswa;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Siswa')
                    ->schema([
                        ViewEntry::make('foto_path')
                            ->label('Foto')
                            ->view('filament.siswas.student-photo-entry'),
                        TextEntry::make('nama'),
                        TextEntry::make('nis'),
                        TextEntry::make('kelas.nama_kelas')
                            ->label('Kelas')
                            ->placeholder('-'),
                        TextEntry::make('alamat')
                            ->placeholder('-'),
                        TextEntry::make('nama_ortu')
                            ->label('Nama Orang Tua / Wali')
                            ->placeholder('-'),
                        TextEntry::make('no_telp')
                            ->label('No. Telepon')
                            ->placeholder('-'),
                        TextEntry::make('status'),
                    ])
                    ->columns(2),
                Section::make('Akun Siswa')
                    ->schema([
                        TextEntry::make('user.username')
                            ->label('Username'),
                        TextEntry::make('user.email')
                            ->label('Email address')
                            ->placeholder('-'),
                        TextEntry::make('user.role.name')
                            ->label('Role'),
                    ])
                    ->columns(2),
                Section::make('Riwayat')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Siswa $record): bool => $record->trashed()),
                    ])
                    ->columns(2),
            ]);
    }
}
