<?php

namespace App\Filament\Resources\BuktiPembayarans\Pages;

use App\Filament\Resources\BuktiPembayarans\BuktiPembayaranResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditBuktiPembayaran extends EditRecord
{
    protected static string $resource = BuktiPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['file_path'])) {
            $path = $data['file_path'];
            $data['file_name'] = basename($path);
            $data['file_type'] = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
            $data['file_size'] = Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : $this->record->file_size;
        }

        $data['updated_by'] = auth()->id();
        $data['updated_at'] = now();

        return $data;
    }
}
