<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        @page {
            size: A4 landscape;
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
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
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
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
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
        <h1>Laporan Dispensasi Siswa</h1>
        <p>Aplikasi Presensi & Jurnal Mengajar — jurnalkita</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('l, d F Y H:i') }}</td>
                <td style="width: 50%; text-align: right;"><strong>Total Data:</strong> {{ $daftar->count() }} pengajuan</td>
            </tr>
            @if (!empty($filterInfo))
            <tr>
                <td colspan="2"><strong>Filter:</strong> {{ $filterInfo }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 120px;">Nama Siswa</th>
                <th style="width: 65px;">Kelas</th>
                <th style="width: 80px;">Waktu</th>
                <th>Alasan Dispensasi</th>
                <th style="width: 90px;">Diajukan Oleh</th>
                <th style="width: 65px;" class="text-center">Status</th>
                <th style="width: 100px;">Catatan Waka</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftar as $index => $d)
                @php
                    $jam = $d->jam_ke_mulai
                        ? ($d->jam_ke_selesai ? "Jam {$d->jam_ke_mulai}-{$d->jam_ke_selesai}" : "Jam {$d->jam_ke_mulai} s/d selesai")
                        : 'Sehari penuh';
                    $statusClass = match ($d->status) {
                        'disetujui' => 'badge-success',
                        'ditolak' => 'badge-danger',
                        default => 'badge-warning',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $d->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td><strong>{{ $d->siswa->nama ?? '-' }}</strong><br><span style="color:#64748b; font-size:8px;">NIS: {{ $d->siswa->nis ?? '-' }}</span></td>
                    <td>{{ $d->siswa->kelas->nama ?? '-' }}</td>
                    <td>{{ $jam }}</td>
                    <td>{{ $d->alasan }}</td>
                    <td>{{ $d->pengaju->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ $d->status }}</span>
                    </td>
                    <td>{{ $d->catatan_waka ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada data dispensasi yang sesuai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>Dokumen resmi hasil cetak sistem jurnalkita</span>
    </div>
</body>
</html>
