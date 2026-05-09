<?php

namespace App\Filament\Resources\Pembayarans\Pages;

use App\Filament\RoleGate;
use App\Filament\Resources\Pembayarans\PembayaranResource;
use App\Services\PembayaranReviewService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPembayaran extends ViewRecord
{
    protected static string $resource = PembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verifikasi')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => RoleGate::has('admin', 'staf_tu') && $this->record->status === 'Menunggu Verifikasi')
                ->action(fn () => app(PembayaranReviewService::class)->verify($this->record))
                ->after(fn () => $this->redirect(PembayaranResource::getUrl('view', ['record' => $this->record]))),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => RoleGate::has('admin', 'staf_tu') && $this->record->status === 'Menunggu Verifikasi')
                ->action(fn () => app(PembayaranReviewService::class)->reject($this->record))
                ->after(fn () => $this->redirect(PembayaranResource::getUrl('view', ['record' => $this->record]))),
            EditAction::make(),
        ];
    }
}
