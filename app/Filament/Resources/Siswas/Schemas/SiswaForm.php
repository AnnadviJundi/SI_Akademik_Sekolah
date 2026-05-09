<?php

namespace App\Filament\Resources\Siswas\Schemas;

use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Siswa')
                    ->schema([
                        TextInput::make('nis')
                            ->required(),
                        TextInput::make('nama')
                            ->required(),
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(fn (): array => Kelas::query()
                                ->orderByDesc('tahun_ajaran')
                                ->orderBy('nama_kelas')
                                ->get()
                                ->mapWithKeys(fn (Kelas $kelas): array => [
                                    $kelas->id => "{$kelas->nama_kelas} ({$kelas->tahun_ajaran})",
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('nama_ortu')
                            ->label('Nama Orang Tua / Wali'),
                        TextInput::make('no_telp')
                            ->label('No. Telepon')
                            ->tel(),
                        FileUpload::make('foto_path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('siswa-photos')
                            ->image()
                            ->imageEditor()
                            ->downloadable()
                            ->openable(),
                        Toggle::make('status')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
                Section::make('Akun Siswa')
                    ->schema([
                        TextInput::make('username')
                            ->required()
                            ->label('Username')
                            ->regex('/^[A-Za-z0-9._-]{5,30}$/')
                            ->rules(fn (?Siswa $record): array => [
                                Rule::unique('users', 'username')->ignore($record?->user_id),
                            ]),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->rules(fn (?Siswa $record): array => [
                                Rule::unique('users', 'email')->ignore($record?->user_id),
                            ]),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->rule('regex:/[A-Za-z]/')
                            ->rule('regex:/[0-9]/')
                            ->dehydrateStateUsing(fn (?string $state) => blank($state) ? null : $state)
                            ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Kosongkan jika password tidak diubah.' : null),
                    ])
                    ->columns(2),
            ]);
    }
}
