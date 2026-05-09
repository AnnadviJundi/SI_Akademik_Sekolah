<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Audit Logs</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        p {
            margin: 0 0 16px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <h1>Audit Logs</h1>
    <p>Generated at {{ $generatedAt->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Aksi</th>
                <th>Entitas / Subject</th>
                <th>Ringkasan Aktivitas</th>
                <th>IP</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($auditLogs as $auditLog)
                <tr>
                    <td>{{ $auditLog->user?->name ?? '-' }}</td>
                    <td>{{ $auditLog->action_label }}</td>
                    <td>{{ $auditLog->subject_label }}</td>
                    <td>{{ $auditLog->summary_label }}</td>
                    <td>{{ $auditLog->ip_address ?? '-' }}</td>
                    <td>{{ $auditLog->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada audit log untuk filter yang aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
