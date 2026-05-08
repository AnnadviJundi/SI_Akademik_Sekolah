<?php

namespace App\Filament\Resources\BuktiPembayarans\Pages;

use App\Filament\Resources\BuktiPembayarans\BuktiPembayaranResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBuktiPembayaran extends ViewRecord
{
    protected static string $resource = BuktiPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
