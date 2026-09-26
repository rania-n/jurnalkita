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
            padding: 6px 4px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
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
        <h1>Rekap Kehadiran Siswa</h1>
        <p>Aplikasi Presensi & Jurnal Mengajar — jurnalkita</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Periode:</strong> {{ $dari->translatedFormat('d M Y') }} s/d {{ $sampai->translatedFormat('d M Y') }}</td>
                <td style="width: 50%; text-align: right;"><strong>Kelas:</strong> {{ $kelasNama }}</td>
            </tr>
            <tr>
                <td style="width: 50%;"><strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d/m/Y H:i') }}</td>
                <td style="width: 50%; text-align: right;"><strong>Total Siswa:</strong> {{ count($daftar) }} siswa</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 75px;">Kelas</th>
                <th style="width: 30px;" class="text-center">Absen</th>
                <th style="width: 65px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 35px;" class="text-center">H</th>
                <th style="width: 35px;" class="text-center">S</th>
                <th style="width: 35px;" class="text-center">I</th>
                <th style="width: 35px;" class="text-center">A</th>
                <th style="width: 40px;" class="text-center">Disp</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftar as $index => $d)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $d['kelas'] }}</td>
                    <td class="text-center">{{ $d['no_absen'] ?: '-' }}</td>
                    <td>{{ $d['nis'] }}</td>
                    <td><strong>{{ $d['nama'] }}</strong></td>
                    <td class="text-center" style="color: #15803d; font-weight: bold;">{{ $d['hadir'] }}</td>
                    <td class="text-center" style="color: #1d4ed8;">{{ $d['sakit'] }}</td>
                    <td class="text-center" style="color: #b45309;">{{ $d['izin'] }}</td>
                    <td class="text-center" style="color: #b91c1c; font-weight: bold;">{{ $d['alpha'] }}</td>
                    <td class="text-center" style="color: #7e22ce;">{{ $d['dispensasi'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada data siswa untuk rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>Keterangan: H = Hadir, S = Sakit, I = Izin, A = Alpha, Disp = Dispensasi</span>
    </div>
</body>
</html>
