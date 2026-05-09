<?php

namespace App\Filament\Resources\Gurus\Schemas;

use App\Models\Guru;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuruInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Guru')
                    ->schema([
                        ViewEntry::make('foto_path')
                            ->label('Foto')
                            ->view('filament.gurus.teacher-photo-entry'),
                        TextEntry::make('nama'),
                        TextEntry::make('nip')
                            ->label('NIP')
                            ->placeholder('-'),
                        TextEntry::make('alamat')
                            ->placeholder('-'),
                        TextEntry::make('no_telp')
                            ->label('No. Telepon')
                            ->placeholder('-'),
                        TextEntry::make('status'),
                    ])
                    ->columns(2),
                Section::make('Akun Guru')
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
                Section::make('Pengampu')
                    ->schema([
                        TextEntry::make('mata_pelajaran_list')
                            ->label('Mata Pelajaran')
                            ->placeholder('-'),
                        TextEntry::make('kelas_ajar_list')
                            ->label('Kelas Ajar')
                            ->placeholder('-'),
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
                            ->visible(fn (Guru $record): bool => $record->trashed()),
                    ])
                    ->columns(2),
            ]);
    }
}
