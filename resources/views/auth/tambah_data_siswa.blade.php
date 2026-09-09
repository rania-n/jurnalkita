<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa - Jurnalkita</title>
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

        /* Header Area */
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px; /* 60px top to simulate status bar area */
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
            text-decoration: none;
            color: #1B2A4A;
        }

        /* Form Area */
        .form-content {
            flex: 1;
            padding: 0 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .form-content::-webkit-scrollbar {
            display: none;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .form-input, .form-select {
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 400;
            font-size: 14px;
            color: #4A5568;
            outline: none;
            appearance: none; /* Removes default dropdown arrow */
        }

        .form-input::placeholder {
            color: #A0AEC0;
        }

        .form-input:focus, .form-select:focus {
            border-color: #1B2A4A;
        }

        /* Custom Dropdown Arrow */
        .select-icon {
            position: absolute;
            right: 16px;
            pointer-events: none;
        }

        /* Footer Area */
        .footer-area {
            padding: 0 24px 16px;
            background: #F4F6F9;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-submit {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            color: #FFFFFF;
            cursor: pointer;
            text-decoration: none;
        }

        /* Home Indicator */
        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            padding: 12px 0 8px;
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
                <h1 class="header-title">Tambah Data Siswa</h1>
                <p class="header-subtitle">Kelola data siswa yang tersedia</p>
            </div>
            <a href="{{ route('data_siswa') }}" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Form Content -->
        <form class="form-content" action="#" method="POST">
            
            <!-- Input Kelas -->
            <div class="form-group">
                <label class="form-label">Kelas</label>
                <div class="input-wrapper">
                    <select class="form-select" required>
                        <option value="" disabled selected>Pilih Kelas</option>
                        <option value="X RPL 1">X RPL 1</option>
                        <option value="X RPL 2">X RPL 2</option>
                        <option value="XI TKJ 1">XI TKJ 1</option>
                    </select>
                    <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Input NIS -->
            <div class="form-group">
                <label class="form-label">NIS</label>
                <div class="input-wrapper">
                    <input type="number" class="form-input" placeholder="12345678909" required>
                </div>
            </div>

            <!-- Input Nama Lengkap -->
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input type="text" class="form-input" placeholder="Rania Nurillah" required>
                </div>
            </div>

            <!-- Input Jenis Kelamin -->
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <div class="input-wrapper">
                    <select class="form-select" required>
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan" selected>Perempuan</option>
                    </select>
                    <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

        </form>

        <!-- Footer Area (Button & Home Indicator) -->
        <div class="footer-area">
            <button type="submit" class="btn-submit" onclick="document.querySelector('form').submit()">Tambah Siswa</button>
            <div class="home-indicator-wrapper">
                <div class="home-indicator"></div>
            </div>
        </div>

    </div>

</body>
</html>