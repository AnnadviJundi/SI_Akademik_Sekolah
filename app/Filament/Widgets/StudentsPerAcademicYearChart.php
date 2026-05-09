<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class StudentsPerAcademicYearChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Siswa per Tahun Ajaran';

    protected ?string $description = 'Tren jumlah siswa berdasarkan tahun ajaran pada data kelas.';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $studentsByYear = Siswa::query()
            ->selectRaw('kelas.tahun_ajaran as tahun_ajaran, COUNT(*) as total')
            ->join('kelas', 'kelas.id', '=', 'siswa.kelas_id')
            ->groupBy('kelas.tahun_ajaran')
            ->orderBy('kelas.tahun_ajaran')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $studentsByYear->pluck('total')->map(
                        fn (mixed $total): int => (int) $total
                    )->all(),
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $studentsByYear->pluck('tahun_ajaran')->all(),
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
