<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Dispensasi</title>
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
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Status Bar */
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
            color: #4A5568;
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
            flex-shrink: 0;
        }

        /* Main Content (Form) */
        .content {
            flex: 1;
            padding: 0 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
            padding-bottom: 20px;
        }
        
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Form Elements */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .form-control {
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            outline: none;
            appearance: none;
        }

        .form-control::placeholder {
            color: #4A5568;
        }

        .form-select {
            color: #4A5568;
            cursor: pointer;
            padding-right: 40px; /* Space for icon */
        }

        .input-icon {
            position: absolute;
            right: 16px;
            pointer-events: none;
        }

        .form-textarea {
            width: 100%;
            height: 90px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 400;
            font-size: 14px;
            color: #5A6E7F;
            outline: none;
            resize: none;
            line-height: 140%;
        }

        .form-textarea::placeholder {
            color: #5A6E7F;
        }

        /* File Upload Area */
        .file-upload {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 114px;
            background: #FFFFFF;
            border: 1px dashed #E2E8F0;
            border-radius: 10px;
            gap: 8px;
            cursor: pointer;
        }

        .file-upload-title {
            font-weight: 600;
            font-size: 13px;
            line-height: 16px;
            color: #1B2A4A;
        }

        .file-upload-subtitle {
            font-weight: 400;
            font-size: 11px;
            line-height: 13px;
            color: #5A6E7F;
        }

        /* Footer & Button */
        .footer {
            padding: 0 24px;
            display: flex;
            flex-direction: column;
            background: #F4F6F9;
            flex-shrink: 0;
            margin-top: 8px;
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
            font-weight: 600;
            font-size: 16px;
            line-height: 19px;
            color: #FFFFFF;
            cursor: pointer;
        }

        /* Footer Indicator */
        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 21px 0 8px;
            background: #F4F6F9;
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
                <h1 class="header-title">Form Pengajuan Dispensasi</h1>
                <p class="header-subtitle">Ajukan dispensasi siswa</p>
            </div>
            <a href="{{ route('data_dispen') }}" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content Form -->
        <main class="content">
            
            <!-- Kelas -->
            <div class="form-group">
                <label class="form-label">Kelas</label>
                <div class="input-wrapper">
                    <select class="form-control form-select">
                        <option>Pilih Kelas</option>
                    </select>
                    <svg class="input-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>

            <!-- Nama Siswa -->
            <div class="form-group">
                <label class="form-label">Nama Siswa</label>
                <div class="input-wrapper">
                    <select class="form-control form-select">
                        <option>Pilih Siswa</option>
                    </select>
                    <svg class="input-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>

            <!-- No. Telepon -->
            <div class="form-group">
                <label class="form-label">No. Telepon</label>
                <input type="tel" class="form-control" placeholder="Masukkan nomor WhatsApp aktif">
            </div>

            <!-- Tanggal -->
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <div class="input-wrapper">
                    <input type="text" class="form-control" value="06/09/2026">
                    <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>

            <!-- Alasan Dispensasi -->
            <div class="form-group">
                <label class="form-label">Alasan Dispensasi / Lomba / Kegiatan</label>
                <textarea class="form-textarea" placeholder="Contoh: Mengikuti Lomba Informatika"></textarea>
            </div>

            <!-- Lampiran Foto -->
            <div class="form-group">
                <label class="form-label" style="font-size: 13px;">Lampiran Foto / File Pendukung</label>
                <div class="file-upload">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                    <div style="text-align: center;">
                        <div class="file-upload-title">Lampirkan Foto / File Bukti Pendukung</div>
                        <div class="file-upload-subtitle">Foto / file bukti pendukung dispens</div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer & Button -->
        <div class="footer">        
            <button class="btn-submit">
                Ajukan Dispensasi
            </button>
        </div>

        <!-- Home Indicator -->
        <div class="home-indicator-wrapper">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>