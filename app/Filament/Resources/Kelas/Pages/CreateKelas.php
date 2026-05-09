<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use App\Services\KelasProvisioningService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateKelas extends CreateRecord
{
    protected static string $resource = KelasResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(KelasProvisioningService::class)->create($data);
    }
}
