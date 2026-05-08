<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kelas')
                    ->required(),
                TextInput::make('nama_kelas')
                    ->required(),
                TextInput::make('tingkat')
                    ->required(),
                TextInput::make('tahun_ajaran')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
            ]);
    }
}
