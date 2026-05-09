<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use App\Models\Pembayaran;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class PembayaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('siswa.nama')
                    ->label('Nama Siswa'),
                TextEntry::make('semester.tahun_ajaran')
                    ->label('Tahun Ajaran'),
                TextEntry::make('semester.semester')
                    ->label('Semester'),
                TextEntry::make('jenis_pembayaran'),
                TextEntry::make('jumlah_tagihan')
                    ->numeric(),
                TextEntry::make('jumlah_dibayar')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('verifiedByUser.name')
                    ->label('Petugas Penangan')
                    ->placeholder('-'),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                ViewEntry::make('latestBukti.file_path')
                    ->label('Bukti Pembayaran')
                    ->view('filament.pembayarans.payment-proof-entry'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Pembayaran $record): bool => $record->trashed()),
            ]);
    }
}
