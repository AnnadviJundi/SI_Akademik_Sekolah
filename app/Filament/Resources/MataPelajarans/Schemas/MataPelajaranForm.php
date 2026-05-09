<?php

namespace App\Filament\Resources\MataPelajarans\Schemas;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Services\AcademicPeriodService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MataPelajaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Mata Pelajaran')
                    ->schema([
                        TextInput::make('kode_mapel')
                            ->required(),
                        TextInput::make('nama_mapel')
                            ->required(),
                        TextInput::make('status')
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Section::make('Guru & Kelas Pengampu')
                    ->schema([
                        Repeater::make('pengampu')
                            ->label('Pengampu')
                            ->schema([
                                Select::make('guru_id')
                                    ->label('Guru')
                                    ->options(fn (): array => Guru::query()
                                        ->orderBy('nama')
                                        ->pluck('nama', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->options(fn (): array => Kelas::query()
                                        ->orderBy('nama_kelas')
                                        ->pluck('nama_kelas', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('semester_id')
                                    ->label('Semester')
                                    ->options(fn (): array => Semester::query()
                                        ->orderByDesc('tahun_ajaran')
                                        ->orderBy('semester')
                                        ->get()
                                        ->pluck('label', 'id')
                                        ->all())
                                    ->default(fn (): ?int => app(AcademicPeriodService::class)->getActiveSemester()?->id)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Pengampu')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
