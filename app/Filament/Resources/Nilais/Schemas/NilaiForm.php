<?php

namespace App\Filament\Resources\Nilais\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Nilai;

class NilaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('siswa_id')
                    ->relationship('siswa', 'nama')
                    ->required(),
                Select::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->required(),
                Select::make('mata_pelajaran_id')
                    ->relationship('mataPelajaran', 'nama_mapel')
                    ->required(),
                Select::make('guru_id')
                    ->relationship('guru', 'nama'),
                Select::make('semester_id')
                    ->relationship('semester', 'semester')
                    ->required(),
                Select::make('jenis_nilai')
                    ->options(array_combine(Nilai::JENIS, array_map(fn (string $jenis) => str($jenis)->headline()->toString(), Nilai::JENIS)))
                    ->required(),
                TextInput::make('nilai')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Textarea::make('catatan')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->hidden(),
                TextInput::make('updated_by')
                    ->hidden(),
            ]);
    }
}
