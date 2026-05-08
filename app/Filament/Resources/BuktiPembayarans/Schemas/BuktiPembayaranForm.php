<?php

namespace App\Filament\Resources\BuktiPembayarans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BuktiPembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pembayaran_id')
                    ->relationship('pembayaran', 'jenis_pembayaran')
                    ->required(),
                TextInput::make('file_name')
                    ->hidden(),
                FileUpload::make('file_path')
                    ->label('Bukti Pembayaran')
                    ->disk('public')
                    ->directory('bukti-pembayaran')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(5120)
                    ->downloadable()
                    ->openable()
                    ->required(),
                TextInput::make('file_type')
                    ->hidden(),
                TextInput::make('file_size')
                    ->hidden(),
                TextInput::make('uploaded_by')
                    ->hidden(),
                DateTimePicker::make('uploaded_at')
                    ->hidden(),
                TextInput::make('updated_by')
                    ->hidden(),
            ]);
    }
}
