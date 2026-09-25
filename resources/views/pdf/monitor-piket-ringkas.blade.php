<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #64748b;
        }
        .meta {
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #475569;
        }
        .meta table {
            width: 100%;
        }
        .meta td {
            padding: 1px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-muted { background-color: #f1f5f9; color: #64748b; }
        .footer {
            position: fixed;
            bottom: -5mm;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Ringkasan Monitor Piket</h1>
        <p>Aplikasi Presensi & Jurnal Mengajar — jurnalkita</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Hari / Tanggal:</strong> {{ $tanggal->translatedFormat('l, d F Y') }}</td>
                <td style="width: 50%; text-align: right;"><strong>Total Jadwal:</strong> {{ count($rekap['baris']) }} JP</td>
            </tr>
            <tr>
                <td style="width: 50%;"><strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d/m/Y H:i') }}</td>
                <td style="width: 50%; text-align: right;">
                    <span style="color: #15803d; font-weight: bold;">Hadir: {{ $rekap['totalHadir'] ?? 0 }}</span> &nbsp;|&nbsp;
                    <span style="color: #b91c1c; font-weight: bold;">Tidak Hadir: {{ $rekap['totalTidakHadir'] ?? 0 }}</span> &nbsp;|&nbsp;
                    <span style="color: #64748b;">Belum Diisi: {{ $rekap['totalBelumDiisi'] ?? 0 }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 45px;" class="text-center">Jam</th>
                <th style="width: 70px;">Kelas</th>
                <th style="width: 130px;">Mata Pelajaran</th>
                <th style="width: 120px;">Guru Pengajar</th>
                <th style="width: 75px;" class="text-center">Status</th>
                <th>Materi Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap['baris'] as $index => $b)
                @php
                    $statusClass = match (true) {
                        str_contains(strtolower($b['statusLabel']), 'hadir') && !str_contains(strtolower($b['statusLabel']), 'tidak') => 'badge-success',
                        str_contains(strtolower($b['statusLabel']), 'tidak') => 'badge-danger',
                        default => 'badge-muted',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">JP {{ $b['jamKe'] }}</td>
                    <td><strong>{{ $b['kelas'] }}</strong></td>
                    <td>{{ $b['mapel'] }}</td>
                    <td>{{ $b['guru'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ $b['statusLabel'] }}</span>
                    </td>
                    <td>{{ $b['materi'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada data jadwal pelajaran untuk tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>Dokumen resmi hasil cetak sistem jurnalkita</span>
    </div>
</body>
</html>
