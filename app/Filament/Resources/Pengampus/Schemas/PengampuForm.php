<?php

namespace App\Filament\Resources\Pengampus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PengampuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('guru_id')
                    ->relationship('guru', 'id')
                    ->required(),
                Select::make('kelas_id')
                    ->relationship('kelas', 'id')
                    ->required(),
                Select::make('mata_pelajaran_id')
                    ->relationship('mataPelajaran', 'id')
                    ->required(),
                Select::make('semester_id')
                    ->relationship('semester', 'id')
                    ->required(),
            ]);
    }
}
