<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dispensasi</title>
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
            padding: 60px 24px 16px;
            flex-shrink: 0;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            max-width: 280px;
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
            line-height: 140%;
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
            padding-bottom: 20px;
        }
        
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Search Box */
        .search-box {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            gap: 8px;
            background: #E2E8F0;
            border-radius: 10px;
            width: 100%;
        }

        .search-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            font-weight: 400;
            font-size: 13px;
            color: #1B2A4A;
        }

        .search-input::placeholder {
            color: #4A5568;
        }

        /* Filters Row */
        .filters-row {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .filter-item {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            cursor: pointer;
        }

        .filter-item span {
            font-weight: 700;
            font-size: 11px;
            color: #4A5568;
        }

        /* Action Button */
        .btn-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #1B2A4A;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-action span {
            font-weight: 700;
            font-size: 12px;
            color: #FFFFFF;
        }

        /* Cards List */
        .card-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            background: #FFFFFF;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.04);
            border-radius: 12px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .card-name {
            font-weight: 700;
            font-size: 14px;
            color: #1B2A4A;
        }

        .card-class {
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
        }

        .card-date {
            font-weight: 600;
            font-size: 11px;
            color: #1B2A4A;
            margin-top: 2px;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .status-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
        }

        /* Status Colors */
        .status-pending { background: #FFF4B8; color: #C67A00; }
        .status-approved { background: #D1FFC2; color: #0A5C36; }
        .status-rejected { background: #FFE4E6; color: #B91C1C; }
        .status-neutral { background: #E2E8F0; color: #4A5568; }

        .btn-detail {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 34px;
            height: 34px;
            background: #E2E8F0;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin-left: 2px;
        }

        /* Footer Indicator */
        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 12px 0 8px;
            background: #F4F6F9;
            flex-shrink: 0;
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
                <h1 class="header-title">Daftar Dispensasi</h1>
                <p class="header-subtitle">Riwayat dan status persetujuan dispensasi oleh Staff Piket dan Waka Kesiswaan.</p>
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
            
            <!-- Search Box -->
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" class="search-input" placeholder="Cari nama atau alasan dispensasi...">
            </div>

            <!-- Filters -->
            <div class="filters-row">
                <div class="filter-item">
                    <span>Semua Kelas</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="filter-item">
                    <span>Tanggal</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('tambah_data_dispen') }}" class="btn-action">
                <span>Ajukan Dispensasi</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <circle cx="10" cy="13" r="2"></circle>
                    <path d="M7 18v-1a3 3 0 0 1 6 0v1"></path>
                    <line x1="16" y1="13" x2="18" y2="13"></line>
                    <line x1="16" y1="17" x2="18" y2="17"></line>
                </svg>
            </a>

            <!-- Cards List -->
            <div class="card-list">
                
                <!-- Card: Dude Fahrezi -->
                <div class="card">
                    <div class="card-info">
                        <div class="card-name">Dude Fahrezi</div>
                        <div class="card-class">XI RPL 2</div>
                        <div class="card-date">Tanggal: 06-09-2026</div>
                    </div>
                    <div class="card-actions">
                        <div class="status-icon status-pending">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="status-icon status-neutral">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <a href="{{ route('detail_dispen') }}" button class="btn-detail">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><circle cx="10" cy="13" r="2"></circle><path d="M7 18v-1a3 3 0 0 1 6 0v1"></path><line x1="16" y1="13" x2="18" y2="13"></line><line x1="16" y1="17" x2="18" y2="17"></line></svg>
                        </a>
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