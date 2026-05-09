<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .meta { margin-bottom: 14px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="title">Daftar Guru</div>
    <div class="meta">Dicetak: {{ $generatedAt->format('d M Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>NIP</th>
                <th>Username</th>
                <th>No. Telepon</th>
                <th>Mata Pelajaran</th>
                <th>Kelas Ajar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($gurus as $guru)
                <tr>
                    <td>{{ $guru->nama ?: '-' }}</td>
                    <td>{{ $guru->nip ?: '-' }}</td>
                    <td>{{ $guru->user?->username ?: '-' }}</td>
                    <td>{{ $guru->no_telp ?: '-' }}</td>
                    <td>{{ $guru->mata_pelajaran_list ?: '-' }}</td>
                    <td>{{ $guru->kelas_ajar_list ?: '-' }}</td>
                    <td>{{ $guru->status ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
