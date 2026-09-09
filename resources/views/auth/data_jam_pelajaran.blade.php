<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Aplikasi - Jam Pelajaran</title>
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

        /* Mobile Container Layout */
        .app-container {
            width: 390px;
            min-height: 844px;
            height: 844px;
            border-radius: 12px;
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Top Section Frame (Status Bar + Header) */
        .top-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            width: 100%;
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

        /* Header Area */
        .screen-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px;
            width: 100%;
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
            line-height: 24px;
            color: #1B2A4A;
        }

        .header-subtitle {
            font-weight: 400;
            font-size: 13px;
            line-height: 16px;
            color: #4A5568;
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
            transition: background 0.2s ease;
        }

        .btn-back:hover {
            background: #cbd5e1;
        }

        /* Main Content Area */
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0px 24px 20px;
            gap: 16px;
            width: 100%;
            flex: 1;
        }

        /* Segmented Control */
        .segmented-control {
            display: flex;
            flex-direction: row;
            align-items: center;
            width: 342px;
            height: 28px;
            background: #FFFFFF;
            box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
        }

        .segment-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            height: 100%;
            font-weight: 600;
            font-size: 11px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .segment-btn.active {
            background: #1B2A4A;
            color: #FFFFFF;
        }

        .segment-btn:not(.active) {
            background: transparent;
            color: #4A5568;
        }

        /* Time Table Card */
        .time-table-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 8px 16px 16px;
            gap: 8px;
            width: 342px;
            background: #FFFFFF;
            box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
            border-radius: 12px;
        }

        .table-header {
            display: flex;
            align-items: center;
            width: 310px;
            height: 16px;
        }

        .col-jp-head {
            width: 60px;
            font-weight: 600;
            font-size: 13px;
            color: #4A5568;
        }

        .col-time-head {
            flex: 1;
            width: 125px;
            font-weight: 600;
            font-size: 13px;
            color: #4A5568;
        }

        .divider {
            width: 310px;
            height: 0px;
            border: 1px solid #E2E8F0;
            margin: 4px 0;
        }

        .table-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 310px;
        }

        .table-row {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 310px;
            height: 28px;
        }

        .col-jp-label {
            width: 44px;
            font-weight: 700;
            font-size: 13px;
            color: #1B2A4A;
        }

        .time-input-box {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            width: 125px;
            height: 28px;
            background: #F4F6F9;
            border-radius: 8px;
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
        }

        /* Primary Button */
        .btn-primary {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 0px;
            width: 342px;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            color: #FFFFFF;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }

        .btn-primary:hover {
            background: #2a3f6c;
        }

        /* Home Indicator Bar */
        .home-indicator {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 21px 0px 8px;
            width: 100%;
            height: 34px;
            flex-shrink: 0;
        }

        .indicator-bar {
            width: 139px;
            height: 5px;
            background: #1B2A4A;
            border-radius: 100px;
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <div class="top-section">
            <!-- Screen Header -->
            <div class="screen-header">
                <div class="header-text">
                    <h1 class="header-title">Jam Pelajaran</h1>
                    <p class="header-subtitle">Konfigurasi rentang waktu jam pelajaran</p>
                </div>
                <a href="{{ route('login') }}" class="back-btn" aria-label="Kembali">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            
            <!-- Segmented Control -->
            <div class="segmented-control">
                <button class="segment-btn active">Senin-Kamis</button>
                <button class="segment-btn">Jumat</button>
                <button class="segment-btn">Kustom</button>
            </div>

            <!-- Time Table Card -->
            <div class="time-table-card">
                <div class="table-header">
                    <div class="col-jp-head">JP</div>
                    <div class="col-time-head">JAM MULAI</div>
                    <div class="col-time-head">JAM SELESAI</div>
                </div>
                
                <div class="divider"></div>
                
                <div class="table-body">
                    <!-- JP 1 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 1</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                    <!-- JP 2 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 2</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                    <!-- JP 3 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 3</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                    <!-- JP 4 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 4</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                    <!-- JP 5 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 5</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                    <!-- JP 6 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 6</div>
                        <div class="time-input-box">07:00</div>
                        <div class="time-input-box">07:00</div>
                    </div>
                </div>
            </div>

            <!-- Edit Button -->
            <a href="{{ route('edit_jam_pelajaran') }}" class="btn-primary">
                Edit Jam Pelajaran
            </a>
            
        </div>

        <!-- Home Indicator -->
        <div class="home-indicator">
            <div class="indicator-bar"></div>
        </div>
        
    </div>

</body>
</html>