<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\Response;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function exportPdf(): Response
    {
        $auditLogs = $this->getTableQueryForExport()
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('filament.audit-logs.export', [
            'auditLogs' => $auditLogs,
            'generatedAt' => now(),
        ]);

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            'audit-logs.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
