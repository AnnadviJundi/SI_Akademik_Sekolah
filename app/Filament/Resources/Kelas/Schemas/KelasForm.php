<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Services\KelasProvisioningService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kelas')
                    ->schema([
                        Select::make('jenjang')
                            ->options(array_combine(
                                array_keys(KelasProvisioningService::JENJANGS),
                                array_keys(KelasProvisioningService::JENJANGS),
                            ))
                            ->required()
                            ->live(),
                        Select::make('tingkat')
                            ->options(fn (Get $get): array => collect(KelasProvisioningService::JENJANGS[$get('jenjang')] ?? [])
                                ->mapWithKeys(fn (int $tingkat): array => [
                                    (string) $tingkat => app(KelasProvisioningService::class)->romanizeGrade($tingkat) ?? (string) $tingkat,
                                ])
                                ->all())
                            ->required()
                            ->live(),
                        TextInput::make('rombel')
                            ->required()
                            ->maxLength(10)
                            ->live(onBlur: true),
                        Select::make('tahun_ajaran')
                            ->options(app(KelasProvisioningService::class)->yearOptions())
                            ->required()
                            ->default(array_key_first(app(KelasProvisioningService::class)->yearOptions()))
                            ->searchable()
                            ->preload()
                            ->live(),
                        Placeholder::make('preview_kode_kelas')
                            ->label('Preview Kode Kelas')
                            ->content(fn (Get $get): string => app(KelasProvisioningService::class)->previewCode(
                                $get('jenjang'),
                                $get('tingkat'),
                                $get('rombel'),
                                $get('tahun_ajaran'),
                            )),
                        Placeholder::make('preview_nama_kelas')
                            ->label('Preview Nama Kelas')
                            ->content(fn (Get $get): string => app(KelasProvisioningService::class)->previewName(
                                $get('tingkat'),
                                $get('rombel'),
                                $get('tahun_ajaran'),
                            )),
                        Select::make('status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
            ]);
    }
}
