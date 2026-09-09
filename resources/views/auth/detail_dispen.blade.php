<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dispensasi Waka</title>
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
            padding: 60px 25px 16px;
            flex-shrink: 0;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
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

        /* Main Content */
        .content {
            display: flex;
            flex-direction: column;
            padding: 0 24px 20px;
            gap: 16px;
            flex: 1;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Student Card */
        .student-card {
            display: flex;
            align-items: center;
            padding: 16px;
            gap: 12px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            width: 100%;
        }

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }

        .student-name {
            font-weight: 700;
            font-size: 16px;
            color: #1B2A4A;
            line-height: 19px;
        }

        .student-class {
            font-weight: 400;
            font-size: 12px;
            color: #4A5568;
            line-height: 15px;
        }

        .student-date {
            font-weight: 600;
            font-size: 12px;
            color: #1B2A4A;
            line-height: 15px;
        }

        .icon-box {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 34px;
            height: 34px;
            background: #E2E8F0;
            border-radius: 8px;
            flex-shrink: 0;
        }

        /* Form Groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
            line-height: 17px;
        }

        .form-box {
            display: flex;
            align-items: center;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
        }
        
        /* Specific Box Overrides based on Figma data */
        .box-alasan {
            padding: 14px;
            height: 45px;
        }

        .box-telepon {
            padding: 0 16px;
            height: 46px;
            border-radius: 10px;
        }

        .box-surat {
            padding: 14px;
            gap: 10px;
            height: 46px;
        }

        .file-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-lihat {
            font-weight: 700;
            font-size: 11px;
            color: #1B2A4A;
            cursor: pointer;
            text-transform: uppercase;
        }

        /* Status Timeline */
        .status-container {
            display: flex;
            flex-direction: column;
            padding: 14px;
            gap: 12px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .status-icon.approved {
            background: #D1FFC2;
            color: #0A5C36;
        }

        .status-icon.pending {
            background: #FFF4B8;
            color: #C67A00;
        }

        .status-text {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .status-title {
            font-weight: 700;
            font-size: 13px;
            color: #1B2A4A;
            line-height: 16px;
        }

        .status-subtitle {
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
            line-height: 13px;
        }

        .status-divider {
            width: 100%;
            border-top: 1px solid #E2E8F0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-top: auto;
        }

        .btn {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            height: 43px;
            gap: 6px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            line-height: 19px;
        }

        .btn-setuju {
            background: #D1FFC2;
            border: 1px solid #0A5C36;
            color: #0A5C36;
        }

        .btn-tolak {
            background: #FFE4E6;
            border: 1px solid #B91C1C;
            color: #B91C1C;
        }
    </style>
</head>
<body>

    <div class="app-container">

        <!-- Header -->
        <header class="header">
            <div class="header-text">
                <h1 class="header-title">Detail Dispensasi</h1>
                <p class="header-subtitle">Detail dispensasi siswa.</p>
            </div>
            <a href="{{ route('data_dispen') }}" class="btn-back">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="content">
            
            <!-- Student Card -->
            <div class="student-card">
                <div class="student-info">
                    <div class="student-name">Dude Fahrezi</div>
                    <div class="student-class">XI RPL 2</div>
                    <div class="student-date">Tanggal: 06-09-2026</div>
                </div>
                <div class="icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <circle cx="10" cy="13" r="2"></circle>
                        <path d="M7 18v-1a3 3 0 0 1 6 0v1"></path>
                        <line x1="16" y1="13" x2="18" y2="13"></line>
                        <line x1="16" y1="17" x2="18" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Form Groups -->
            <div class="form-group">
                <span class="form-label">Alasan Dispensasi</span>
                <div class="form-box box-alasan">Lomba Futsal Tingkat Nasional</div>
            </div>

            <div class="form-group">
                <span class="form-label">No. Telepon</span>
                <div class="form-box box-telepon">085648830046</div>
            </div>

            <div class="form-group">
                <span class="form-label">Surat Dispensasi/Izin</span>
                <div class="form-box box-surat">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="file-name">Surat_Undangan_Lomba_Futsal.pdf</span>
                    <span class="btn-lihat">LIHAT</span>
                </div>
            </div>

            <div class="form-group">
                <span class="form-label">Status Persetujuan</span>
                <div class="status-container">
                    
                    <!-- Approved Status -->
                    <div class="status-item">
                        <div class="status-icon approved">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="status-text">
                            <span class="status-title">Staff Piket: Disetujui</span>
                            <span class="status-subtitle">Oleh: Bpk. Hariyadi | 09:12</span>
                        </div>
                    </div>
                    
                    <div class="status-divider"></div>
                    
                    <!-- Pending Status -->
                    <div class="status-item">
                        <div class="status-icon pending">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="status-text">
                            <span class="status-title">Waka Kesiswaan: Menunggu</span>
                            <span class="status-subtitle">Proses peninjauan dokumen</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-setuju">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Setujui
                </button>
                <button class="btn btn-tolak">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Tolak
                </button>
            </div>

        </main>
    </div>

</body>
</html>