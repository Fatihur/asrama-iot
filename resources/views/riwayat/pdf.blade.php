<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Kejadian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .meta {
            margin-bottom: 20px;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .bg-red { background-color: #fee2e2; color: #991b1b; }
        .bg-gray { background-color: #f3f4f6; color: #1f2937; }
        .bg-green { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Riwayat Kejadian</h1>
        <p>Sistem Monitoring Asrama IoT</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="meta">
        <strong>Filter:</strong><br>
        Periode: {{ $dateFrom ? $dateFrom : 'Awal' }} s/d {{ $dateTo ? $dateTo : 'Sekarang' }}<br>
        Jenis Kejadian: {{ $eventType ? $eventType : 'Semua' }}<br>
        Lantai: {{ $floor ? $floor : 'Semua' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Device</th>
                <th>Lantai</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Sirine</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $index => $r)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $r->timestamp->format('d/m/Y H:i:s') }}</td>
                <td>{{ $r->device_id }}</td>
                <td>{{ $r->floor }}</td>
                <td>{{ $r->event_type }}</td>
                <td>
                    <span class="badge {{ $r->resolve_status === 'RESOLVED' ? 'bg-green' : 'bg-red' }}">
                        {{ $r->resolve_status }}
                    </span>
                </td>
                <td>{{ $r->sirine_status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data kejadian</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
