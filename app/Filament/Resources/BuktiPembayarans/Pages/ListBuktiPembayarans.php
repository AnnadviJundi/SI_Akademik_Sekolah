<?php

namespace App\Filament\Resources\BuktiPembayarans\Pages;

use App\Filament\Resources\BuktiPembayarans\BuktiPembayaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuktiPembayarans extends ListRecords
{
    protected static string $resource = BuktiPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
