<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\Resources\Pembayarans\PembayaranResource;
use App\Models\BuktiPembayaran;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditPembayaran extends EditRecord
{
    protected static string $resource = PembayaranResource::class;

    protected ?string $uploadedProofPath = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['bukti_file'] = $this->record->latestBukti?->file_path;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->uploadedProofPath = $data['bukti_file'] ?? null;

        unset($data['bukti_file']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (blank($this->uploadedProofPath)) {
            return;
        }

        $proof = $this->record->latestBukti;

        if ($proof && $proof->file_path === $this->uploadedProofPath) {
            return;
        }

        if ($proof && filled($proof->file_path) && Storage::disk('public')->exists($proof->file_path)) {
            Storage::disk('public')->delete($proof->file_path);
        }

        if ($proof) {
            $proof->update([
                'file_name' => basename($this->uploadedProofPath),
                'file_path' => $this->uploadedProofPath,
                'file_type' => Str::lower(pathinfo($this->uploadedProofPath, PATHINFO_EXTENSION)),
                'file_size' => Storage::disk('public')->size($this->uploadedProofPath),
            ]);

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
