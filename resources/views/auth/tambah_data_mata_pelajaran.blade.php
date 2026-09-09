<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Pelajaran - Jurnalkita</title>
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

        /* --- Header Area --- */
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px; /* 60px padding atas untuk area status bar */
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
            transition: background 0.2s;
        }
        
        .btn-back:active {
            background: #cbd5e1;
        }

        /* --- Form Area --- */
        .form-content {
            flex: 1; /* Mendorong footer ke bawah */
            padding: 8px 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
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

        .form-input {
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input::placeholder {
            color: #718096; /* Warna placeholder agar mirip Bahasa Indonesia di gambar */
        }

        .form-input:focus {
            border-color: #1B2A4A;
        }

        /* --- Footer Area --- */
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
            transition: opacity 0.2s;
        }

        .btn-submit:active {
            opacity: 0.8;
        }

        /* --- Home Indicator --- */
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
                <h1 class="header-title">Tambah Mata Pelajaran</h1>
                <p class="header-subtitle">Kelola mata pelajaran yang tersedia</p>
            </div>
            <a href="{{ route('data_mata_pelajaran') }}" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Form Content -->
        <form class="form-content" action="#" method="POST">
            <!-- Input Mata Pelajaran -->
            <div class="form-group">
                <label class="form-label">Mata Pelajaran</label>
                <input type="text" class="form-input" placeholder="Bahasa Indonesia" required>
            </div>
        </form>

        <!-- Footer Area (Button & Home Indicator) -->
        <div class="footer-area">
            <button type="submit" class="btn-submit" onclick="document.querySelector('form').submit()">Tambah Mata Pelajaran</button>
            <div class="home-indicator-wrapper">
                <div class="home-indicator"></div>
            </div>
        </div>

    </div>

</body>
</html>