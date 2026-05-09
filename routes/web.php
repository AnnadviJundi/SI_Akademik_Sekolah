<?php

use App\Filament\RoleGate;
use App\Models\Guru;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware(['web', 'auth'])->get('/admin/gurus/{guru}/pdf', function (Guru $guru) {
    abort_unless(RoleGate::admin(), 403);

    $guru->loadMissing(['user.role', 'pengampu.kelas', 'pengampu.mataPelajaran', 'pengampu.semester']);

    $pdf = Pdf::loadView('pdf.guru-profile', [
        'guru' => $guru,
        'photoUrl' => filled($guru->foto_path) ? public_path('storage/' . $guru->foto_path) : null,
    ]);

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="guru-' . str($guru->nama)->slug() . '.pdf"',
    ]);
})->name('gurus.pdf');

Route::middleware(['web', 'auth'])->get('/admin/reports/gurus/pdf', function () {
    abort_unless(RoleGate::admin(), 403);

    $gurus = Guru::query()
        ->with(['user.role', 'pengampu.kelas', 'pengampu.mataPelajaran', 'pengampu.semester'])
        ->orderBy('nama')
        ->get();

    $pdf = Pdf::loadView('pdf.guru-list', [
        'gurus' => $gurus,
        'generatedAt' => now(),
    ])->setPaper('a4', 'landscape');

    return response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="daftar-guru.pdf"',
    ]);
})->name('gurus.list.pdf');
