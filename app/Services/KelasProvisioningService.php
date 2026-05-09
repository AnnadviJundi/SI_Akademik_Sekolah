<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KelasProvisioningService
{
    public const JENJANGS = [
        'SD' => [1, 2, 3, 4, 5, 6],
        'SMP' => [7, 8, 9],
        'SMA' => [10, 11, 12],
    ];

    public function create(array $data): Kelas
    {
        return DB::transaction(function () use ($data): Kelas {
            $payload = $this->buildPayload($data);

            $kelas = Kelas::query()->create($payload);

            $this->ensureAcademicYearSemesters($kelas->tahun_ajaran);

            return $kelas;
        });
    }

    public function update(Kelas $kelas, array $data): Kelas
    {
        return DB::transaction(function () use ($kelas, $data): Kelas {
            $payload = $this->buildPayload($data, $kelas);

            $kelas->update($payload);

            $this->ensureAcademicYearSemesters($kelas->tahun_ajaran);

            return $kelas->fresh();
        });
    }

    public function yearOptions(): array
    {
        $currentStartYear = $this->startYearForDate(now()->month, (int) now()->year);
        $generated = collect(range($currentStartYear - 1, $currentStartYear + 3))
            ->mapWithKeys(fn (int $startYear): array => [$this->formatAcademicYear($startYear) => $this->formatAcademicYear($startYear)]);

        $existing = Semester::query()
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran', 'tahun_ajaran');

        return $existing
            ->union($generated)
            ->sortKeysDesc()
            ->all();
    }

    public function inferFormState(?Kelas $kelas): array
    {
        if (! $kelas) {
            return [
                'jenjang' => null,
                'tingkat' => null,
                'rombel' => null,
            ];
        }

        $tingkat = $this->normalizeGrade($kelas->tingkat);
        $jenjang = $this->jenjangForGrade($tingkat);

        return [
            'jenjang' => $jenjang,
            'tingkat' => $tingkat ? (string) $tingkat : null,
            'rombel' => $this->extractRombel($kelas),
        ];
    }

    public function previewCode(?string $jenjang, mixed $tingkat, ?string $rombel, ?string $tahunAjaran): string
    {
        $grade = $this->normalizeGrade($tingkat);
        $rombel = $this->normalizeRombel($rombel);
        $yearStart = $this->academicYearStart($tahunAjaran);

        if (! $jenjang || ! $grade || ! $rombel || ! $yearStart) {
            return '-';
        }

        return sprintf('%s-%s%s-%s', $jenjang, $grade, $rombel, $yearStart);
    }

    public function previewName(mixed $tingkat, ?string $rombel, ?string $tahunAjaran): string
    {
        $grade = $this->normalizeGrade($tingkat);
        $rombel = $this->normalizeRombel($rombel);

        if (! $grade || ! $rombel || blank($tahunAjaran)) {
            return '-';
        }

        return sprintf('%s%s - %s', $grade, $rombel, $tahunAjaran);
    }

    private function buildPayload(array $data, ?Kelas $ignore = null): array
    {
        $jenjang = Arr::get($data, 'jenjang');
        $tingkat = $this->normalizeGrade(Arr::get($data, 'tingkat'));
        $rombel = $this->normalizeRombel(Arr::get($data, 'rombel'));
        $tahunAjaran = Arr::get($data, 'tahun_ajaran');

        if (! $this->isGradeValidForJenjang($jenjang, $tingkat)) {
            throw ValidationException::withMessages([
                'tingkat' => 'Tingkat tidak sesuai dengan jenjang yang dipilih.',
            ]);
        }

        $kodeKelas = $this->previewCode($jenjang, $tingkat, $rombel, $tahunAjaran);
        $namaKelas = $this->previewName($tingkat, $rombel, $tahunAjaran);

        if ($kodeKelas === '-' || $namaKelas === '-') {
            throw ValidationException::withMessages([
                'kode_kelas' => 'Data kelas belum lengkap.',
            ]);
        }

        $query = Kelas::query()->where('kode_kelas', $kodeKelas);

        if ($ignore?->exists) {
            $query->whereKeyNot($ignore->getKey());
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'rombel' => 'Kelas dengan kombinasi tahun ajaran, tingkat, dan rombel ini sudah ada.',
            ]);
        }

        return [
            'kode_kelas' => $kodeKelas,
            'nama_kelas' => $namaKelas,
            'tingkat' => (string) $tingkat,
            'tahun_ajaran' => $tahunAjaran,
            'status' => Arr::get($data, 'status', 'active'),
        ];
    }

    private function ensureAcademicYearSemesters(string $tahunAjaran): void
    {
        Semester::query()->firstOrCreate(
            ['tahun_ajaran' => $tahunAjaran, 'semester' => 'Ganjil'],
            ['is_active' => true],
        );

        Semester::query()->firstOrCreate(
            ['tahun_ajaran' => $tahunAjaran, 'semester' => 'Genap'],
            ['is_active' => false],
        );
    }

    private function isGradeValidForJenjang(?string $jenjang, ?int $tingkat): bool
    {
        if (! $jenjang || ! $tingkat) {
            return false;
        }

        return in_array($tingkat, self::JENJANGS[$jenjang] ?? [], true);
    }

    private function normalizeGrade(mixed $grade): ?int
    {
        if (blank($grade)) {
            return null;
        }

        if (is_numeric($grade)) {
            return (int) $grade;
        }

        $value = strtoupper(trim((string) $grade));

        return match ($value) {
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
            default => is_numeric($value) ? (int) $value : null,
        };
    }

    private function jenjangForGrade(?int $grade): ?string
    {
        if (! $grade) {
            return null;
        }

        foreach (self::JENJANGS as $jenjang => $grades) {
            if (in_array($grade, $grades, true)) {
                return $jenjang;
            }
        }

        return null;
    }

    private function normalizeRombel(?string $rombel): ?string
    {
        if (blank($rombel)) {
            return null;
        }

        return strtoupper(trim($rombel));
    }

    private function academicYearStart(?string $tahunAjaran): ?string
    {
        if (blank($tahunAjaran)) {
            return null;
        }

        return explode('/', $tahunAjaran)[0] ?? null;
    }

    private function startYearForDate(int $month, int $year): int
    {
        return $month >= 7 ? $year : $year - 1;
    }

    private function formatAcademicYear(int $startYear): string
    {
        return sprintf('%d/%d', $startYear, $startYear + 1);
    }

    private function extractRombel(Kelas $kelas): ?string
    {
        if (preg_match('/^(\d+)([A-Z0-9]+)\s-\s\d{4}\/\d{4}$/', $kelas->nama_kelas, $matches)) {
            return $matches[2];
        }

        if (preg_match('/^([A-Z]+)-\d+([A-Z0-9]+)-\d{4}$/', $kelas->kode_kelas, $matches)) {
            return $matches[2];
        }

        if (preg_match('/([A-Z0-9]+)$/', (string) $kelas->nama_kelas, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }
}
