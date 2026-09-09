<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - Jurnalkita</title>
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
            overflow: hidden; /* Prevent body scroll, handle inside */
        }

        /* Top Header Area (Fixed) */
        .header-area {
            background: #F4F6F9;
            padding: 60px 24px 16px; /* 60px atas untuk simulasi status bar */
            display: flex;
            flex-direction: column;
            gap: 16px;
            z-index: 10;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Search Bar */
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

        /* Scrollable Content Area */
        .content-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 0 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .content-scroll::-webkit-scrollbar {
            display: none;
        }

        /* Add Button */
        .btn-add {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1B2A4A;
            border-radius: 8px;
            padding: 8px 12px;
            height: 36px;
            text-decoration: none;
        }

        .btn-add span {
            font-weight: 700;
            font-size: 11px;
            color: #FFFFFF;
        }

        /* Class Card */
        .class-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFFFFF;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.04);
            border-radius: 12px;
            padding: 14px;
            gap: 12px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex-grow: 1;
        }

        .card-title {
            font-weight: 700;
            font-size: 14px;
            color: #1B2A4A;
        }

        .card-student-count {
            font-weight: 400;
            font-size: 11px;
            color: #4A5568;
        }

        .card-teacher {
            font-weight: 600;
            font-size: 11px;
            color: #1B2A4A;
        }

        /* Action Buttons Wrapper */
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

        .btn-view { background: #E2E8F0; }
        .btn-edit { background: #E0F2FE; }
        .btn-delete { background: #FFE4E6; }

        /* Home Indicator */
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
        
        <!-- Header & Search (Fixed at top) -->
        <div class="header-area">
            <div class="header-top">
                <div class="header-text">
                    <h1 class="header-title">Data Kelas</h1>
                    <p class="header-subtitle">Kelola Data Kelas</p>
                </div>
                <!-- Tombol Back -->
                <a href="#" class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-wrapper">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari kelas...">
            </div>
        </div>

        <!-- Scrollable List Area -->
        <div class="content-scroll">
            
            <!-- Tombol Tambah Data -->
            <a href="{{ route('tambah_data_kelas') }}" class="btn-add">
                <span>Tambah Data Kelas</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </a>

            <!-- LIST KELAS DIMULAI DARI SINI -->
            
            <!-- Card 1 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">X RPL 1</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view" title="Lihat">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </button>
                    <button class="action-btn btn-edit" title="Edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="action-btn btn-delete" title="Hapus">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">X RPL 2</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></button>
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">XI TKJ 1</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></button>
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">XI TKJ 2</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></button>
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

            <!-- Card 5 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">XII RPL 1</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></button>
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>

            <!-- Card 6 -->
            <div class="class-card">
                <div class="card-info">
                    <h3 class="card-title">XI RPL 2</h3>
                    <p class="card-student-count">36 Siswa</p>
                    <p class="card-teacher">Wali Kelas: Winartin, S.pd</p>
                </div>
                <div class="card-actions">
                    <button class="action-btn btn-view"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></button>
                    <button class="action-btn btn-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>
                    <button class="action-btn btn-delete"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                </div>
            </div>
            
        </div>

        <!-- Bottom Home Indicator -->
        <div class="home-indicator-wrapper">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>