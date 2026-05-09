<?php

namespace App\Filament\Resources\Nilais\Schemas;

use App\Services\AcademicPeriodService;
use App\Services\NilaiFormOptionsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state): void {
                        $options = app(NilaiFormOptionsService::class);
                        $kelasId = $options->kelasIdForStudent($state);

                        $set('kelas_id', $kelasId);
                        $set('mata_pelajaran_id', null);
                        $set('guru_id', null);
                    })
                    ->required(),
                Select::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('mata_pelajaran_id', null);
                        $set('guru_id', null);
                    })
                    ->required(),
                Select::make('mata_pelajaran_id')
                    ->options(fn (Get $get): array => app(NilaiFormOptionsService::class)->mataPelajaranOptions(
                        $get('kelas_id'),
                        $get('semester_id'),
                    ))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('guru_id', null))
                    ->required(),
                Select::make('guru_id')
                    ->options(fn (Get $get): array => app(NilaiFormOptionsService::class)->guruOptions(
                        $get('kelas_id'),
                        $get('semester_id'),
                        $get('mata_pelajaran_id'),
                    ))
                    ->searchable()
                    ->preload(),
                Select::make('semester_id')
                    ->relationship('semester', 'semester')
                    ->default(fn (): ?int => app(AcademicPeriodService::class)->getActiveSemester()?->id)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('mata_pelajaran_id', null);
                        $set('guru_id', null);
                    })
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
