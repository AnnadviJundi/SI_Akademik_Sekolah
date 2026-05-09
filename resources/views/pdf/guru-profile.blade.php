<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Profil Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 18px; }
        .row { margin-bottom: 8px; }
        .label { display: inline-block; width: 140px; font-weight: bold; }
        .photo { margin: 16px 0 20px; }
        .photo img { width: 220px; border: 1px solid #d1d5db; border-radius: 12px; }
        .section-title { margin-top: 18px; margin-bottom: 10px; font-size: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="title">Profil Guru</div>

    @if ($photoUrl && file_exists($photoUrl))
        <div class="photo">
            <img src="{{ $photoUrl }}" alt="Foto guru">
        </div>
    @endif

    <div class="row"><span class="label">Nama</span>{{ $guru->nama }}</div>
    <div class="row"><span class="label">NIP</span>{{ $guru->nip ?: '-' }}</div>
    <div class="row"><span class="label">Username</span>{{ $guru->user?->username ?: '-' }}</div>
    <div class="row"><span class="label">Email</span>{{ $guru->user?->email ?: '-' }}</div>
    <div class="row"><span class="label">Alamat</span>{{ $guru->alamat ?: '-' }}</div>
    <div class="row"><span class="label">No. Telepon</span>{{ $guru->no_telp ?: '-' }}</div>
    <div class="row"><span class="label">Status</span>{{ $guru->status ?: '-' }}</div>

    <div class="section-title">Pengampu</div>

    @if ($guru->pengampu->isEmpty())
        <div>-</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Semester</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($guru->pengampu as $pengampu)
                    <tr>
                        <td>{{ $pengampu->mataPelajaran?->nama_mapel ?: '-' }}</td>
                        <td>{{ $pengampu->kelas?->nama_kelas ?: '-' }}</td>
                        <td>{{ $pengampu->semester?->label ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
