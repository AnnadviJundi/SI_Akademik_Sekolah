<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Pembayaran;

class PembayaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('siswa_id')
                    ->relationship('siswa', 'nama')
                    ->required(),
                Select::make('semester_id')
                    ->relationship('semester', 'semester')
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
            ]);
    }
}
