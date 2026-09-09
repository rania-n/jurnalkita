<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data - Manajemen Database Sekolah</title>
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

        /* Header Area */
        .screen-header {
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
            flex: 1;
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

        /* Content List Container */
        .menu-content {
            display: flex;
            flex-direction: column;
            padding: 0 24px 20px;
            gap: 12px;
            flex: 1;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .menu-content::-webkit-scrollbar {
            display: none;
        }

        /* Card Menu Item */
        .menu-card {
            display: flex;
            align-items: center;
            padding: 16px;
            gap: 12px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
            border-radius: 12px;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .menu-card:active {
            transform: scale(0.98);
        }

        .icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }

        .card-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }

        .card-title {
            font-weight: 600;
            font-size: 15px;
            line-height: 18px;
            color: #1B2A4A;
        }

        .card-subtitle {
            font-weight: 400;
            font-size: 12px;
            line-height: 15px;
            color: #4A5568;
        }

        /* Badge Count */
        .badge {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px 8px;
            background: #E2E8F0;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            line-height: 13px;
            color: #1B2A4A;
            flex-shrink: 0;
        }

        .chevron-icon {
            flex-shrink: 0;
        }

        /* Home Indicator Bar */
        .home-indicator {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 21px 0px 8px;
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
        <div>
            <!-- Screen Header -->
            <div class="screen-header">
                <div class="header-text">
                    <h1 class="header-title">Master Data</h1>
                    <p class="header-subtitle">Manajemen Database Sekolah</p>
                </div>
                <a href="javascript:history.back()" class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Menu Card Content -->
            <div class="menu-content">
                
                <!-- 1. Data Guru -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Data Guru</span>
                        <span class="card-subtitle">45 guru terdaftar aktif</span>
                    </div>
                    <div class="badge">45</div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 2. Data Kelas -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 12h6"></path>
                            <path d="M9 16h6"></path>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Data Kelas</span>
                        <span class="card-subtitle">36 kelas aktif semester ini</span>
                    </div>
                    <div class="badge">36</div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 3. Data Siswa -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Data Siswa</span>
                        <span class="card-subtitle">1.260 siswa terdaftar</span>
                    </div>
                    <div class="badge">1.2k</div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 4. Jadwal Pelajaran -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M8 14h.01"></path>
                            <path d="M12 14h.01"></path>
                            <path d="M16 14h.01"></path>
                            <path d="M8 18h.01"></path>
                            <path d="M12 18h.01"></path>
                            <path d="M16 18h.01"></path>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Jadwal Pelajaran</span>
                        <span class="card-subtitle">Konfigurasi jadwal pelajaran</span>
                    </div>
                    <div class="badge">24</div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 5. Jadwal Piket -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <polyline points="9 16 11 18 15 14"></polyline>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Jadwal Piket</span>
                        <span class="card-subtitle">Konfigurasi penugasan harian</span>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 6. Jam Pelajaran (13 JP) -->
                <a href="#" class="menu-card">
                    <div class="icon-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                            <line x1="19" y1="5" x2="21" y2="3"></line>
                        </svg>
                    </div>
                    <div class="card-details">
                        <span class="card-title">Jam Pelajaran (13 JP)</span>
                        <span class="card-subtitle">3 konfigurasi jam pelajaran aktif</span>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

            </div>
        </div>

        <!-- Home Indicator -->
        <div class="home-indicator">
            <div class="indicator-bar"></div>
        </div>
    </div>

</body>
</html>