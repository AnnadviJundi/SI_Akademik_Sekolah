<?php

namespace App\Services;

use App\Models\Pengampu;
use App\Models\Siswa;

class NilaiFormOptionsService
{
    public function siswaOptions(?int $kelasId): array
    {
        return Siswa::query()
            ->when($kelasId, fn ($query) => $query->where('kelas_id', $kelasId))
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public function mataPelajaranOptions(?int $kelasId, ?int $semesterId): array
    {
        return Pengampu::query()
            ->when($kelasId, fn ($query) => $query->where('kelas_id', $kelasId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
            ->with('mataPelajaran')
            ->get()
            ->mapWithKeys(fn (Pengampu $pengampu): array => $pengampu->mataPelajaran ? [
                $pengampu->mata_pelajaran_id => $pengampu->mataPelajaran->nama_mapel,
            ] : [])
            ->all();
    }

    public function guruOptions(?int $kelasId, ?int $semesterId, ?int $mataPelajaranId = null): array
    {
        return Pengampu::query()
            ->when($kelasId, fn ($query) => $query->where('kelas_id', $kelasId))
            ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
            ->when($mataPelajaranId, fn ($query) => $query->where('mata_pelajaran_id', $mataPelajaranId))
            ->with('guru')
            ->get()
            ->mapWithKeys(fn (Pengampu $pengampu): array => $pengampu->guru ? [
                $pengampu->guru_id => $pengampu->guru->nama,
            ] : [])
            ->all();
    }
}
