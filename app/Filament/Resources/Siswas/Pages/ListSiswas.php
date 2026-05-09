<?php

namespace App\Filament\Resources\Siswas\Pages;

use App\Filament\Resources\Siswas\SiswaResource;
use App\Services\KelasProvisioningService;
use App\Services\SiswaClassTransferService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('promoteStudents')
                ->label('Naik Kelas Massal')
                ->icon('heroicon-o-arrow-up')
                ->color('gray')
                ->form([
                    Select::make('tahun_ajaran')
                        ->label('Tahun Ajaran Tujuan')
                        ->options(app(KelasProvisioningService::class)->yearOptions())
                        ->default(array_key_first(app(KelasProvisioningService::class)->yearOptions()))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $result = app(SiswaClassTransferService::class)
                        ->promoteActiveStudentsToAcademicYear($data['tahun_ajaran']);

                    Notification::make()
                        ->title('Naik kelas massal selesai')
                        ->body(sprintf(
                            'Berhasil memindahkan %d siswa. Dilewati: %d kelas akhir, %d kelas tujuan tidak ditemukan, %d sudah berada di tahun ajaran tujuan.',
                            $result['promoted'],
                            $result['skipped_final_grade'],
                            $result['skipped_missing_target'],
                            $result['skipped_same_year'],
                        ))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
