<?php

namespace App\Filament\Resources\Gurus\Schemas;

use App\Models\Guru;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class GuruForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Guru')
                    ->schema([
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required(),
                        TextInput::make('nama')
                            ->required(),
                        Textarea::make('alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('no_telp')
                            ->label('No. Telepon')
                            ->tel(),
                        FileUpload::make('foto_path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('guru-photos')
                            ->image()
                            ->imageEditor()
                            ->downloadable()
                            ->openable(),
                        TextInput::make('status')
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Section::make('Akun Guru')
                    ->schema([
                        TextInput::make('username')
                            ->required()
                            ->label('Username')
                            ->regex('/^[A-Za-z0-9._-]{5,30}$/')
                            ->rules(fn (?Guru $record): array => [
                                Rule::unique('users', 'username')->ignore($record?->user_id),
                            ]),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->rules(fn (?Guru $record): array => [
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
