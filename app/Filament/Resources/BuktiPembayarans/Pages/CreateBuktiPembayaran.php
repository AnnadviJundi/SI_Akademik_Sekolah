<?php

namespace App\Filament\Resources\BuktiPembayarans\Pages;

use App\Filament\Resources\BuktiPembayarans\BuktiPembayaranResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateBuktiPembayaran extends CreateRecord
{
    protected static string $resource = BuktiPembayaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['file_path'];

        $data['file_name'] = basename($path);
        $data['file_type'] = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $data['file_size'] = Storage::disk('public')->size($path);
        $data['uploaded_by'] = auth()->id();
        $data['uploaded_at'] = now();

        return $data;
    }
}
