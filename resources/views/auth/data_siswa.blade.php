<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Jurnalkita</title>
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

        /* --- Header Area --- */
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px; /* 60px top padding for status bar */
            background: #F4F6F9;
            z-index: 10;
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
        }

        .header-subtitle {
            font-weight: 400;
            font-size: 13px;
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
            text-decoration: none;
            color: #1B2A4A;
        }

        /* --- Controls Area (Search, Filters, Button) --- */
        .controls-area {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0 24px 16px;
            background: #F4F6F9;
            z-index: 10;
        }

        .search-wrapper {
            display: flex;
            align-items: center;
            background: #E2E8F0;
            border-radius: 10px;
            height: 36px;
            padding: 0 14px;
            gap: 8px;
        }

        .search-wrapper input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-weight: 400;
            font-size: 13px;
            color: #1B2A4A;
        }

        .search-wrapper input::placeholder {
            color: #4A5568;
        }

        .filters-row {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 32px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 0 12px;
            font-weight: 700;
            font-size: 11px;
            color: #4A5568;
            cursor: pointer;
        }

        .btn-add {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 32px;
            background: #1B2A4A;
            border-radius: 8px;
            padding: 0 12px;
            text-decoration: none;
        }

        .btn-add span {
            font-weight: 700;
            font-size: 11px;
            color: #FFFFFF;
        }

        /* --- Content Scroll (Student List) --- */
        .content-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 0 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .content-scroll::-webkit-scrollbar {
            display: none;
        }

        /* --- Student Card --- */
        .student-card {
            display: flex;
            align-items: center;
            background: #FFFFFF;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.04);
            border-radius: 12px;
            padding: 14px;
            gap: 12px;
        }

        /* Avatars */
        .avatar {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .avatar-male {
            background: #E0F2FE;
            color: #0369A1;
        }

        .avatar-female {
            background: #FCE7F3;
            color: #BE185D;
        }

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex-grow: 1;
        }

        .student-name {
            font-weight: 700;
            font-size: 14px;
            color: #1B2A4A;
        }

        .student-nis {
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
        }

        .student-kelas {
            font-weight: 600;
            font-size: 11px;
            color: #1B2A4A;
        }

        /* Card Actions */
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
            text-decoration: none;
        }

        .btn-edit { background: #E0F2FE; }
        .btn-delete { background: #FFE4E6; }

        /* --- Footer Indicator --- */
        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            padding: 12px 0 8px;
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
        <div class="header-area">
            <div class="header-text">
                <h1 class="header-title">Data Siswa</h1>
                <p class="header-subtitle">Kelola Data Siswa</p>
            </div>
            <a href="#" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Sticky Controls -->
        <div class="controls-area">
            <!-- Search -->
            <div class="search-wrapper">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari nama atau NIS siswa...">
            </div>

            <!-- Filters -->
            <div class="filters-row">
                <div class="filter-btn">
                    <span>Semua Kelas</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="filter-btn">
                    <span>Jenis Kelamin</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_siswa') }}" class="btn-add">
                <span>Tambah Data Siswa</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </a>
        </div>

        <!-- Scrollable Student List -->
        <div class="content-scroll">
            
            <!-- Card 1 (Male) -->
            <div class="student-card">
                <div class="avatar avatar-male">
                    <!-- Male Icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 9h8"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="student-info">
                    <h3 class="student-name">Ahmad Fauzi</h3>
                    <p class="student-nis">NIS: 12345</p>
                    <p class="student-kelas">Kelas: X RPL 1</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="action-btn btn-delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card 2 (Female) -->
            <div class="student-card">
                <div class="avatar avatar-female">
                    <!-- Female Icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z"></path>
                        <path d="M6 10c0-4 2-7 6-7s6 3 6 7v4s-2 2-6 2-6-2-6-2v-4z"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="student-info">
                    <h3 class="student-name">Dewi Lestari</h3>
                    <p class="student-nis">NIS: 12346</p>
                    <p class="student-kelas">Kelas: X RPL 1</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

            <!-- Card 3 (Male) -->
            <div class="student-card">
                <div class="avatar avatar-male">
                    <!-- Male Icon -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 9h8"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="student-info">
                    <h3 class="student-name">Fajar Nugraha</h3>
                    <p class="student-nis">NIS: 12347</p>
                    <p class="student-kelas">Kelas: X RPL 1</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

        </div>

        <!-- Footer / Home Indicator -->
        <div class="home-indicator-wrapper">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>