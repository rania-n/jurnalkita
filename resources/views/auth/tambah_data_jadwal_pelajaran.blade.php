<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Pelajaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #e5e5e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Mobile App Container */
        .app-container {
            width: 390px;
            height: 844px;
            border-radius: 12px;
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Status Bar Mockup */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            height: 44px;
            flex-shrink: 0;
        }

        .time {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .status-icons {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .status-icons svg {
            fill: #1B2A4A;
        }

        /* Header Area */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px;
            flex-shrink: 0;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            width: 308px;
        }

        .header-title {
            font-weight: 700;
            font-size: 20px;
            color: #1B2A4A;
            line-height: 24px;
        }

        .header-subtitle {
            font-weight: 400;
            font-size: 13px;
            color: #5A6E7F;
            line-height: 16px;
        }

        .btn-back {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 34px;
            height: 34px;
            background: #E2E8F0;
            border-radius: 100px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            flex-shrink: 0;
        }

        /* Content Area / Form */
        .content {
            flex: 1;
            padding: 0 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .content::-webkit-scrollbar {
            display: none;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .form-row {
            display: flex;
            flex-direction: row;
            gap: 12px;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
        }

        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #EBEFF4;
            border-radius: 10px;
            padding: 0 40px 0 16px;
            font-weight: 400;
            font-size: 14px;
            color: #5E6F8D;
            outline: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 9L12 15L18 9' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .form-select:invalid {
            color: #5E6F8D;
        }
        
        .form-select option {
            color: #1B2A4A;
        }

        /* Footer / Button Area */
        .footer {
            padding: 0 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 21px;
            flex-shrink: 0;
            background: #F4F6F9;
        }

        .btn-submit {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
        }

        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            padding-bottom: 8px;
        }

        .home-indicator {
            width: 139px;
            height: 5px;
            background: #1B2A4A;
            border-radius: 100px;
        }
    </style>
</head>
<body>

    <div class="app-container">

        <!-- Header -->
        <header class="header">
            <div class="header-text">
                <h1 class="header-title">Tambah Jadwal Pelajaran</h1>
                <p class="header-subtitle">Kelola jadwal kelas per hari & jam pelajaran</p>
            </div>
            <a href="{{ route('data_jadwal_pelajaran') }}" class="btn-back">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content (Forms) -->
        <main class="content">
            
            <div class="form-group">
                <label class="form-label">Hari</label>
                <select class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Hari (e.g. Senin)</option>
                    <option value="senin">Senin</option>
                    <option value="selasa">Selasa</option>
                    <option value="rabu">Rabu</option>
                    <option value="kamis">Kamis</option>
                    <option value="jumat">Jumat</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Kelas</label>
                <select class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Kelas (e.g. X RPL 1)</option>
                    <option value="xrpl1">X RPL 1</option>
                    <option value="xrpl2">X RPL 2</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Jam Mulai</label>
                    <select class="form-select" required>
                        <option value="" disabled selected hidden>JP-1</option>
                        <option value="1">JP-1</option>
                        <option value="2">JP-2</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Jam Selesai</label>
                    <select class="form-select" required>
                        <option value="" disabled selected hidden>JP-3</option>
                        <option value="2">JP-2</option>
                        <option value="3">JP-3</option>
                        <option value="4">JP-4</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mata Pelajaran</label>
                <select class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
                    <option value="mtk">Matematika</option>
                    <option value="pbo">PBO</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Guru Pengajar</label>
                <select class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Guru Pengajar</option>
                    <option value="guru1">Guru A</option>
                    <option value="guru2">Guru B</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Ruangan</label>
                <select class="form-select" required>
                    <option value="" disabled selected hidden>Pilih Ruangan (e.g. Lab 1)</option>
                    <option value="lab1">Lab 1</option>
                    <option value="lab2">Lab 2</option>
                </select>
            </div>

        </main>

        <!-- Footer -->
        <footer class="footer">
            <button class="btn-submit">Tambah Jadwal</button>
            <div class="home-indicator-wrapper">
                <div class="home-indicator"></div>
            </div>
        </footer>

    </div>

</body>
</html>