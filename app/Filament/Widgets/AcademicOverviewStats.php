<?php

namespace App\Filament\Widgets;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AcademicOverviewStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Ringkasan Akademik';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $studentTrend = Siswa::query()
            ->selectRaw('COUNT(*) as aggregate')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->groupBy('kelas.tahun_ajaran')
            ->orderBy('kelas.tahun_ajaran')
            ->pluck('aggregate')
            ->map(fn (mixed $value): int => (int) $value)
            ->all();

        return [
            Stat::make('Total Siswa', number_format(Siswa::query()->count()))
                ->description('Siswa terdaftar di seluruh kelas')
                ->color('primary')
                ->chart($studentTrend),
            Stat::make('Total Guru', number_format(Guru::query()->count()))
                ->description('Guru aktif dan terdata')
                ->color('success'),
            Stat::make('Total Kelas', number_format(Kelas::query()->count()))
                ->description('Kelas aktif lintas tahun ajaran')
                ->color('info'),
            Stat::make('Pembayaran Menunggu Verifikasi', number_format(
                Pembayaran::query()->where('status', 'Menunggu Verifikasi')->count()
            ))
                ->description('Perlu dicek oleh admin atau staf TU')
                ->color('warning'),
        ];
    }
}
