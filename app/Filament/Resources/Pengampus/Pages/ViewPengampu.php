<?php

namespace App\Filament\Resources\Pengampus\Pages;

use App\Filament\Resources\Pengampus\PengampuResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengampu extends ViewRecord
{
    protected static string $resource = PengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
