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
            height: 874px;
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
            padding: 16px 24px;
            height: 74px;
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
            <!-- Status Bar -->
            <div class="status-bar">
                <span class="time">9:41</span>
                <div class="status-icons">
                    <svg width="18" height="12" viewBox="0 0 18 12" fill="#1B2A4A"><path d="M1 9.5C1 10.3284 1.67157 11 2.5 11C3.32843 11 4 10.3284 4 9.5V8.5C4 7.67157 3.32843 7 2.5 7C1.67157 7 1 7.67157 1 8.5V9.5ZM5 9.5C5 10.3284 5.67157 11 6.5 11C7.32843 11 8 10.3284 8 9.5V6.5C8 5.67157 7.32843 5 6.5 5C5.67157 5 5 5.67157 5 6.5V9.5ZM9 9.5C9 10.3284 9.67157 11 10.5 11C11.3284 11 12 10.3284 12 9.5V4.5C12 3.67157 11.3284 3 10.5 3C9.67157 3 9 3.67157 9 4.5V9.5ZM13 9.5C13 10.3284 13.6716 11 14.5 11C15.3284 11 16 10.3284 16 9.5V2.5C16 1.67157 15.3284 1 14.5 1C13.6716 1 13 1.67157 13 2.5V9.5Z"/></svg>
                    <svg width="16" height="12" viewBox="0 0 16 12" fill="#1B2A4A"><path d="M8 11.5C8.82843 11.5 9.5 10.8284 9.5 10C9.5 9.17157 8.82843 8.5 8 8.5C7.17157 8.5 6.5 9.17157 6.5 10C6.5 10.8284 7.17157 11.5 8 11.5Z"/><path d="M11.5355 5.46447C9.58291 3.51184 6.41709 3.51184 4.46447 5.46447C4.17157 5.75736 3.69696 5.75736 3.40406 5.46447C3.11117 5.17157 3.11117 4.69696 3.40406 4.40406C5.94271 1.86541 10.0573 1.86541 12.5959 4.40406C12.8888 4.69696 12.8888 5.17157 12.5959 5.46447C12.303 5.75736 11.8284 5.75736 11.5355 5.46447Z"/><path d="M14.364 2.63604C10.8492 -0.87868 5.15076 -0.87868 1.63604 2.63604C1.34315 2.92893 0.868528 2.92893 0.575635 2.63604C0.282742 2.34315 0.282742 1.86853 0.575635 1.57563C4.6804 -2.52912 11.3196 -2.52912 15.4244 1.57563C15.7173 1.86853 15.7173 2.34315 15.4244 2.63604C15.1315 2.92893 14.6569 2.92893 14.364 2.63604Z"/></svg>
                    <svg width="25" height="12" viewBox="0 0 25 12"><rect x="1" y="1" width="20" height="10" rx="3" stroke="#1B2A4A" stroke-width="1" fill="none"/><rect x="3" y="3" width="16" height="6" rx="1.5" fill="#1B2A4A"/><path d="M22 4V8C23.1046 8 24 7.10457 24 6C24 4.89543 23.1046 4 22 4Z" fill="#1B2A4A"/></svg>
                </div>
            </div>

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