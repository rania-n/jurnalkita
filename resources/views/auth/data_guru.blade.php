<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Data Guru</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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

        /* App Main Frame (readguru) */
        .app-card {
            position: relative;
            width: 390px;
            height: 844px;
            background: #F4F6F9;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            overflow: hidden;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Top Layout Wrapper */
        .top-frame {
            width: 390px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        /* Status Bar */
        .status-bar {
            width: 390px;
            height: 44px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px 0px;
        }

        .status-time {
            font-weight: 600;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
        }

        .status-icons {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 6px;
            color: #1B2A4A;
            font-size: 16px;
        }

        /* Screen Header */
        .screen-header {
            width: 390px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px;
        }

        .header-title-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
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

        .back-btn {
            width: 34px;
            height: 34px;
            background: #E2E8F0;
            border-radius: 100px;
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            color: #1B2A4A;
            font-size: 18px;
            transition: background 0.2s ease;
        }

        .back-btn:hover {
            background: #CBD5E1;
        }

        /* Main Content Area */
        .content-frame {
            width: 390px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0px 24px 20px;
            gap: 16px;
        }

        /* Search Bar */
        .search-box {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 10px 14px;
            gap: 8px;
            width: 342px;
            height: 36px;
            background: #E2E8F0;
            border-radius: 10px;
        }

        .search-icon {
            font-size: 16px;
            color: #4A5568;
        }

        .search-input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-weight: 400;
            font-size: 13px;
            line-height: 16px;
            color: #1B2A4A;
        }

        .search-input::placeholder {
            color: #4A5568;
        }

        /* Filter Row */
        .filter-row {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 8px;
            width: 342px;
            height: 29px;
        }

        .filter-dropdown {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            width: 167px;
            height: 29px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            position: relative;
        }

        .filter-select {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-weight: 700;
            font-size: 11px;
            line-height: 13px;
            color: #4A5568;
            appearance: none;
            cursor: pointer;
        }

        .filter-icon {
            font-size: 14px;
            color: #4A5568;
            pointer-events: none;
            position: absolute;
            right: 12px;
        }

        /* Add Button */
        .btn-add-guru {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            width: 342px;
            height: 29px;
            background: #1B2A4A;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s ease;
        }

        .btn-add-guru:hover {
            opacity: 0.9;
        }

        .btn-add-text {
            font-weight: 700;
            font-size: 11px;
            line-height: 13px;
            color: #FFFFFF;
        }

        .btn-add-icon {
            font-size: 16px;
            color: #FFFFFF;
        }

        /* Teacher List Container */
        .teacher-list {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            width: 342px;
            max-height: 440px;
            overflow-y: auto;
        }

        .teacher-list::-webkit-scrollbar {
            display: none;
        }

        /* Teacher Card */
        .teacher-card {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 14px;
            gap: 12px;
            width: 342px;
            height: 85px;
            background: #FFFFFF;
            box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.0392157);
            border-radius: 12px;
        }

        .teacher-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            width: 246px;
        }

        .teacher-name {
            font-weight: 700;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .teacher-nik,
        .teacher-mapel {
            font-weight: 400;
            font-size: 11px;
            line-height: 13px;
            color: #4A5568;
        }

        /* Action Badges Stack */
        .action-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
            width: 56px;
        }

        .action-badge {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 56px;
            height: 17px;
            border-radius: 4px;
            border: none;
            font-weight: 600;
            font-size: 10px;
            line-height: 12px;
            text-decoration: none;
            cursor: pointer;
        }

        .badge-detail {
            background: #E2E8F0;
            color: #1B2A4A;
            padding: 7px 7px 7px 4px;
        }

        .badge-edit {
            background: #E0F2FE;
            color: #0369A1;
            padding: 6px 6px 6px 4px;
        }

        .badge-delete {
            background: #FFE4E6;
            color: #B91C1C;
            padding: 6px 6px 6px 4px;
        }

        .action-badge i {
            font-size: 11px;
        }

        /* Home Indicator */
        .home-indicator {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            padding: 21px 0px 8px;
            width: 390px;
            height: 34px;
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

    <div class="app-card">
        <div class="top-frame">
            <!-- Status Bar -->
            <!-- Screen Header -->
            <div class="screen-header">
                <div class="header-title-group">
                    <h1 class="header-title">Daftar Data Guru</h1>
                    <p class="header-subtitle">Kelola data guru staff piket</p>
                </div>
                <button type="button" class="back-btn" aria-label="Kembali">
                    <i class="ph ph-arrow-left"></i>
                </button>
            </div>

            <!-- Main Content -->
            <div class="content-frame">
                <!-- Search Box -->
                <div class="search-box">
                    <i class="ph ph-magnifying-glass search-icon"></i>
                    <input type="text" class="search-input" placeholder="Cari nama staff...">
                </div>

                <!-- Filters -->
                <div class="filter-row">
                    <div class="filter-dropdown">
                        <select class="filter-select">
                            <option value="">Semua Kelas</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                        <i class="ph ph-caret-down filter-icon"></i>
                    </div>

                    <div class="filter-dropdown">
                        <select class="filter-select">
                            <option value="">Semua Mapel</option>
                            <option value="matematika">Matematika</option>
                            <option value="inggris">Bahasa Inggris</option>
                            <option value="fisika">Fisika</option>
                            <option value="biologi">Biologi</option>
                        </select>
                        <i class="ph ph-caret-down filter-icon"></i>
                    </div>
                </div>

                <!-- Add Button -->
                <a href="{{ route('tambah_guru') }}" class="btn-add-guru">
                    <span class="btn-add-text">Tambah Data Guru</span>
                    <i class="ph ph-user-plus btn-add-icon"></i>
                </a>

                <!-- Teacher List -->
                <div class="teacher-list">
                    <!-- Card 1 -->
                    <div class="teacher-card">
                        <div class="teacher-info">
                            <span class="teacher-name">Budi Santoso, S.Pd</span>
                            <span class="teacher-nik">NIK. 198501012010011001</span>
                            <span class="teacher-mapel">Mapel: Matematika</span>
                        </div>
                        <div class="action-group">
                            <a href="#" class="action-badge badge-detail">
                                <span>Detail</span>
                                <i class="ph ph-article"></i>
                            </a>
                            <a href="#" class="action-badge badge-edit">
                                <span>Edit</span>
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <a href="#" class="action-badge badge-delete">
                                <span>Hapus</span>
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="teacher-card">
                        <div class="teacher-info">
                            <span class="teacher-name">Winartin, S.Pd</span>
                            <span class="teacher-nik">NIK. 198802242013022002</span>
                            <span class="teacher-mapel">Mapel: Bahasa Inggris</span>
                        </div>
                        <div class="action-group">
                            <a href="#" class="action-badge badge-detail">
                                <span>Detail</span>
                                <i class="ph ph-article"></i>
                            </a>
                            <a href="#" class="action-badge badge-edit">
                                <span>Edit</span>
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <a href="#" class="action-badge badge-delete">
                                <span>Hapus</span>
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="teacher-card">
                        <div class="teacher-info">
                            <span class="teacher-name">Drs. M. Yusuf</span>
                            <span class="teacher-nik">NIK. 197210151998031003</span>
                            <span class="teacher-mapel">Mapel: Fisika</span>
                        </div>
                        <div class="action-group">
                            <a href="#" class="action-badge badge-detail">
                                <span>Detail</span>
                                <i class="ph ph-article"></i>
                            </a>
                            <a href="#" class="action-badge badge-edit">
                                <span>Edit</span>
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <a href="#" class="action-badge badge-delete">
                                <span>Hapus</span>
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="teacher-card">
                        <div class="teacher-info">
                            <span class="teacher-name">Sarah Amelia, M.Pd</span>
                            <span class="teacher-nik">NIK. 199208082018012004</span>
                            <span class="teacher-mapel">Mapel: Biologi</span>
                        </div>
                        <div class="action-group">
                            <a href="#" class="action-badge badge-detail">
                                <span>Detail</span>
                                <i class="ph ph-article"></i>
                            </a>
                            <a href="#" class="action-badge badge-edit">
                                <span>Edit</span>
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <a href="#" class="action-badge badge-delete">
                                <span>Hapus</span>
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Home Indicator -->
        <div class="home-indicator">
            <div class="indicator-bar"></div>
        </div>
    </div>

</body>
</html>