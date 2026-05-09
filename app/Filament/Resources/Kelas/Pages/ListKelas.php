<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Filament\Resources\Kelas\KelasResource;
use App\Services\AcademicPeriodService;
use App\Services\KelasProvisioningService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListKelas extends ListRecords
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('promoteAcademicYear')
                ->label('Naik Tahun Ajaran')
                ->icon('heroicon-o-arrow-up')
                ->color('gray')
                ->form([
                    Select::make('tahun_ajaran')
                        ->label('Tahun Ajaran Tujuan')
                        ->options(app(KelasProvisioningService::class)->yearOptions())
                        ->default(fn (): ?string => app(AcademicPeriodService::class)->getActiveAcademicYear()
                            ?? array_key_first(app(KelasProvisioningService::class)->yearOptions()))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $result = app(KelasProvisioningService::class)
                        ->promoteActiveClassesToAcademicYear($data['tahun_ajaran']);

                    Notification::make()
                        ->title('Generate kelas tahun ajaran baru selesai')
                        ->body(sprintf(
                            'Berhasil membuat %d kelas baru dan melewati %d kelas yang sudah ada.',
                            $result['created'],
                            $result['skipped'],
                        ))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
