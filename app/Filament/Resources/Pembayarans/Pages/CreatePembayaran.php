<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Models\BuktiPembayaran;
use App\Filament\Resources\Pembayarans\PembayaranResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    protected ?string $uploadedProofPath = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->uploadedProofPath = $data['bukti_file'] ?? null;

        unset($data['bukti_file']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (blank($this->uploadedProofPath)) {
            return;
        }

        BuktiPembayaran::query()->create([
            'pembayaran_id' => $this->record->id,
            'file_name' => basename($this->uploadedProofPath),
            'file_path' => $this->uploadedProofPath,
            'file_type' => Str::lower(pathinfo($this->uploadedProofPath, PATHINFO_EXTENSION)),
            'file_size' => Storage::disk('public')->size($this->uploadedProofPath),
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);
    }
}
