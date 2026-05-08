<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role_id')
                    ->relationship('role', 'name')
                    ->required(),
                TextInput::make('username')
                    ->required()
                    ->regex('/^[A-Za-z0-9._-]{5,30}$/')
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at')
                    ->hidden(),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->rule('regex:/[A-Za-z]/')
                    ->rule('regex:/[0-9]/'),
                Select::make('status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])
                    ->required()
                    ->default('active'),
                DateTimePicker::make('last_login_at')
                    ->disabled(),
            ]);
    }
}
