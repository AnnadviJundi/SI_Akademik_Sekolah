<?php

namespace App\Filament\Resources\Pengampus\Pages;

use App\Filament\Resources\Pengampus\PengampuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengampus extends ListRecords
{
    protected static string $resource = PengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
