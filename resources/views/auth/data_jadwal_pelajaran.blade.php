<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jadwal Pelajaran</title>
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
            border-radius: 12px;
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

        /* Main Content */
        .content {
            flex: 1;
            padding: 0 24px;
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

        /* Search Bar */
        .search-bar {
            display: flex;
            align-items: center;
            background: #E2E8F0;
            border-radius: 10px;
            padding: 10px 14px;
            gap: 8px;
            height: 36px;
        }

        .search-bar input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            font-weight: 400;
            font-size: 13px;
            color: #1B2A4A;
        }

        .search-bar input::placeholder {
            color: #4A5568;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .filter-select {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 8px 12px;
            height: 32px;
            font-weight: 700;
            font-size: 11px;
            color: #4A5568;
            cursor: pointer;
            flex: 1;
            appearance: none;
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 9L12 15L18 9' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            padding-right: 24px;
        }

        /* Add Button */
        .btn-add-schedule {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1B2A4A;
            border-radius: 8px;
            padding: 10px 12px;
            height: 36px;
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            width: 100%;
        }

        /* Schedule List & Cards */
        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 24px;
        }

        .schedule-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background: #FFFFFF;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.04);
            min-height: 88px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .subject-title {
            font-weight: 700;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
            margin-bottom: 2px;
        }

        .subject-details {
            font-weight: 400;
            font-size: 11px;
            line-height: 14px;
            color: #4A5568;
            white-space: pre-line;
        }

        .subject-class {
            font-weight: 600;
            font-size: 11px;
            line-height: 13px;
            color: #1B2A4A;
            margin-top: 4px;
        }

        .card-actions {
            display: flex;
            gap: 4px;
        }

        .action-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #E0F2FE;
        }

        .btn-delete {
            background: #FFE4E6;
        }

        /* Footer Indicator */
        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 8px 0 8px;
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
                <h1 class="header-title">Daftar Jadwal Pelajaran</h1>
                <p class="header-subtitle">Kelola jadwal pelajaran yang tersedia</p>
            </div>
            <a href="#" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="content">
            
            <!-- Search -->
            <div class="search-bar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari mata pelajaran...">
            </div>

            <!-- Filters -->
            <div class="filters">
                <select class="filter-select">
                    <option>Semua Kelas</option>
                </select>
                <select class="filter-select" style="flex: 0.7;">
                    <option>Hari</option>
                </select>
                <select class="filter-select">
                    <option>Nama Guru</option>
                </select>
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_jadwal_pelajaran') }}" class="btn-add-schedule">
                Tambah Jadwal Pelajaran
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                    <line x1="12" y1="12" x2="12" y2="18"></line>
                </svg>
            </a>

            <!-- Schedule List -->
            <div class="schedule-list">
                
                <!-- Card 1 -->
                <div class="schedule-card">
                    <div class="card-info">
                        <h3 class="subject-title">Matematika</h3>
                        <p class="subject-details">Senin | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="subject-class">Kelas: X RPL 1</p>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn btn-edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="action-btn btn-delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="schedule-card">
                    <div class="card-info">
                        <h3 class="subject-title">Matematika</h3>
                        <p class="subject-details">Selasa | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="subject-class">Kelas: X RPL 1</p>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn btn-edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="action-btn btn-delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="schedule-card">
                    <div class="card-info">
                        <h3 class="subject-title">Matematika</h3>
                        <p class="subject-details">Rabu | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="subject-class">Kelas: X RPL 1</p>
                    </div>
                    <div class="card-actions">
                        <button class="action-btn btn-edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="action-btn btn-delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

            </div>
        </main>

        <!-- Home Indicator -->
        <div class="home-indicator-wrapper">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>