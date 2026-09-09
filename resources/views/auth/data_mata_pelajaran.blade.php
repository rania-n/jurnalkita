<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mata Pelajaran - Jurnalkita</title>
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

        /* Mobile Container */
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px; /* 60px padding atas untuk notch/status bar */
            background: #F4F6F9;
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
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .btn-back:active {
            background: #cbd5e1;
        }

        /* --- Content Area --- */
        .content {
            flex: 1;
            padding: 0 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            /* Hide scrollbar for cleaner look */
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 10px 14px;
            gap: 8px;
            width: 100%;
            height: 36px;
            background: #E2E8F0;
            border-radius: 10px;
        }

        .search-input {
            border: none;
            background: transparent;
            width: 100%;
            font-weight: 400;
            font-size: 13px;
            color: #1B2A4A;
            outline: none;
        }

        .search-input::placeholder {
            color: #4A5568;
        }

        /* Add Button */
        .btn-add {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            width: 100%;
            height: 36px;
            background: #1B2A4A;
            border-radius: 8px;
            border: none;
            color: #FFFFFF;
            cursor: pointer;
        }

        .btn-add span {
            font-weight: 700;
            font-size: 11px;
        }

        /* Course Cards */
        .course-card {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 14px;
            width: 100%;
            background: #FFFFFF;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.04);
            border-radius: 12px;
        }

        .course-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            max-width: 200px;
        }

        .course-title {
            font-weight: 700;
            font-size: 14px;
            color: #1B2A4A;
            line-height: 1.2;
        }

        .course-code {
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
        }

        .course-actions {
            display: flex;
            flex-direction: row;
            gap: 4px;
        }

        .btn-action {
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
            color: #0369A1;
        }

        .btn-delete {
            background: #FFE4E6;
            color: #B91C1C;
        }

        /* --- Home Indicator --- */
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
        <header class="header">
            <div class="header-text">
                <h1 class="header-title">Daftar Mata Pelajaran</h1>
                <p class="header-subtitle">Kelola mata pelajaran yang tersedia</p>
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
                <input type="text" class="search-input" placeholder="Cari kelas...">
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_mata_pelajaran') }}" class="btn-add">
                <span>Tambah Mata Pelajaran</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </a>
                
            <!-- Course List -->
            <!-- Card 1 -->
            <div class="course-card">
                <div class="course-info">
                    <span class="course-title">Matematika</span>
                    <span class="course-code">MAT-001</span>
                </div>
                <div class="course-actions">
                    <button class="btn-action btn-edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="btn-action btn-delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="course-card">
                <div class="course-info">
                    <span class="course-title">Kreativitas, Inovasi, dan Kewirausahaan</span>
                    <span class="course-code">KIK-001</span>
                </div>
                <div class="course-actions">
                    <button class="btn-action btn-edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="btn-action btn-delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="course-card">
                <div class="course-info">
                    <span class="course-title">Mapel Pilihan RPL</span>
                    <span class="course-code">MPR-001</span>
                </div>
                <div class="course-actions">
                    <button class="btn-action btn-edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="btn-action btn-delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="course-card">
                <div class="course-info">
                    <span class="course-title">Bahasa Jepang</span>
                    <span class="course-code">BJE-001</span>
                </div>
                <div class="course-actions">
                    <button class="btn-action btn-edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="btn-action btn-delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- Footer Area (Home Indicator) -->
        <div class="home-indicator-wrapper">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>