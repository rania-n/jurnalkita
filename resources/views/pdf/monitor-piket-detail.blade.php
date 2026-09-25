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
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
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
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 4px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.data-table td {
            padding: 4.5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-hadir { background-color: #dcfce7; color: #15803d; }
        .badge-sakit { background-color: #dbeafe; color: #1d4ed8; }
        .badge-izin { background-color: #fef3c7; color: #b45309; }
        .badge-alpha { background-color: #fee2e2; color: #b91c1c; }
        .badge-dispen { background-color: #f3e8ff; color: #7e22ce; }
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
        <h1>Laporan Detail Monitor Piket — {{ $tipe === 'guru' ? 'Guru' : 'Kelas' }}: {{ $targetNama }}</h1>
        <p>Aplikasi Presensi & Jurnal Mengajar — jurnalkita</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Hari / Tanggal:</strong> {{ $tanggal->translatedFormat('l, d F Y') }}</td>
                <td style="width: 50%; text-align: right;"><strong>Total Baris:</strong> {{ count($baris) }} baris</td>
            </tr>
            <tr>
                <td style="width: 50%;"><strong>Target:</strong> {{ ucfirst($tipe) }} {{ $targetNama }}</td>
                <td style="width: 50%; text-align: right;"><strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">No</th>
                <th style="width: 45px;" class="text-center">Jam</th>
                <th style="width: 100px;">Mata Pelajaran</th>
                <th style="width: 90px;">{{ $tipe === 'guru' ? 'Kelas' : 'Guru Pengajar' }}</th>
                <th style="width: 65px;" class="text-center">Status Guru</th>
                <th style="width: 110px;">Materi</th>
                <th style="width: 70px;">Metode</th>
                <th style="width: 30px;" class="text-center">Absen</th>
                <th style="width: 100px;">Nama Siswa</th>
                <th style="width: 65px;" class="text-center">Status Siswa</th>
                <th>Catatan Siswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $index => $b)
                @php
                    $statusSiswaClass = match (strtolower($b['statusSiswa'])) {
                        'hadir' => 'badge-hadir',
                        'sakit' => 'badge-sakit',
                        'izin' => 'badge-izin',
                        'alpha' => 'badge-alpha',
                        'dispensasi' => 'badge-dispen',
                        default => '',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $b['jam'] }}</td>
                    <td>{{ $b['mapel'] }}</td>
                    <td>{{ $b['lawan'] }}</td>
                    <td class="text-center">{{ $b['statusGuru'] }}</td>
                    <td>{{ $b['materi'] }}</td>
                    <td>{{ $b['metode'] }}</td>
                    <td class="text-center">{{ $b['noAbsen'] }}</td>
                    <td><strong>{{ $b['siswa'] }}</strong></td>
                    <td class="text-center">
                        @if ($b['statusSiswa'] !== '-')
                            <span class="badge {{ $statusSiswaClass }}">{{ $b['statusSiswa'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $b['catatanSiswa'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada data detail monitor piket untuk sesi ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>Dokumen resmi hasil cetak sistem jurnalkita</span>
    </div>
</body>
</html>
