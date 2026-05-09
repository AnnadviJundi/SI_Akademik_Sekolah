<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use App\Services\AcademicPeriodService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Pembayaran;

class PembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pembayaran')
                    ->schema([
                        Select::make('siswa_id')
                            ->relationship('siswa', 'nama')
                            ->required(),
                        Select::make('semester_id')
                            ->relationship('semester', 'semester')
                            ->default(fn (): ?int => app(AcademicPeriodService::class)->getActiveSemester()?->id)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('jenis_pembayaran')
                            ->required(),
                        TextInput::make('jumlah_tagihan')
                            ->required()
                            ->numeric(),
                        TextInput::make('jumlah_dibayar')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        Select::make('status')
                            ->options(array_combine(Pembayaran::STATUS, Pembayaran::STATUS))
                            ->required()
                            ->default('Belum Bayar'),
                        TextInput::make('verified_by')
                            ->hidden(),
                        DateTimePicker::make('verified_at')
                            ->disabled(),
                        TextInput::make('created_by')
                            ->hidden(),
                        TextInput::make('updated_by')
                            ->hidden(),
                    ])
                    ->columns(2),
                Section::make('Bukti Pembayaran')
                    ->schema([
                        FileUpload::make('bukti_file')
                            ->label('Upload Bukti')
                            ->disk('public')
                            ->directory('bukti-pembayaran')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->imagePreviewHeight('240')
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
