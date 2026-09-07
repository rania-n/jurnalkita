<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jam Pelajaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #e5e5e5; /* Dark backdrop for preview */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Mobile Container Layout */
        .app-container {
            width: 390px;
            min-height: 874px;
            height: 874px;
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        /* Top Section */
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
            padding: 0px 24px;
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
            align-items: center;
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
            width: 52px;
            font-weight: 600;
            font-size: 13px;
            color: #4A5568;
        }

        .col-time-head-start {
            width: 115px;
            font-weight: 600;
            font-size: 13px;
            color: #4A5568;
        }

        .col-time-head-end {
            flex: 1;
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
            width: 107px;
            height: 28px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
            outline: none;
        }

        .btn-delete {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 28px;
            height: 28px;
            background: #FFE4E6;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-delete:hover {
            background: #fecdd3;
        }

        .btn-add-jam {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            width: 310px;
            height: 33px;
            background: #E0F2FE;
            border: 1px solid #0369A1;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 4px;
            transition: background 0.2s;
        }
        
        .btn-add-jam:hover {
            background: #bae6fd;
        }

        .btn-add-jam span {
            font-weight: 600;
            font-size: 14px;
            color: #0369A1;
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
        }

        .btn-primary:hover {
            background: #2a3f6c;
        }

        /* Home Indicator Bar */
        .home-indicator {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 0px 0px 8px;
            width: 100%;
            height: 13px;
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
        
        <!-- Top Section -->
        <div class="top-section">
            <!-- Screen Header -->
            <div class="screen-header">
                <div class="header-text">
                    <h1 class="header-title">Edit Jam Pelajaran</h1>
                    <p class="header-subtitle">Konfigurasi rentang waktu jam pelajaran</p>
                </div>
                <button class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
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
                    <div class="col-time-head-start">JAM MULAI</div>
                    <div class="col-time-head-end">JAM SELESAI</div>
                </div>
                
                <div class="divider"></div>
                
                <div class="table-body">
                    <!-- JP 1 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 1</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 2 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 2</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 3 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 3</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 4 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 4</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 5 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 5</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 6 -->
                    <div class="table-row">
                        <div class="col-jp-label">JP 6</div>
                        <input type="text" class="time-input-box" value="07:00">
                        <input type="text" class="time-input-box" value="07:00">
                        <button class="btn-delete">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Add Button -->
                <button class="btn-add-jam">
                    <span>Tambah Jam Pelajaran</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 1 10-10"></path>
                        <polyline points="12 6 12 12 16 14"></polyline>
                        <line x1="19" y1="16" x2="19" y2="22"></line>
                        <line x1="16" y1="19" x2="22" y2="19"></line>
                    </svg>
                </button>
            </div>

            <!-- Save Button -->
            <button class="btn-primary">
                Simpan Perubahan
            </button>
            
        </div>

        <!-- Home Indicator -->
        <div class="home-indicator">
            <div class="indicator-bar"></div>
        </div>
        
    </div>

</body>
</html>