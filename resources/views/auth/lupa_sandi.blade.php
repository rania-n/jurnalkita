<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
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
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .app-container::-webkit-scrollbar {
            display: none;
        }

        /* Header / Back Button */
        .header-section {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 60px 24px 8px; /* Padding atas disesuaikan untuk status bar */
            width: 100%;
        }

        .btn-back {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 34px;
            height: 34px;
            background: #EBEFF4;
            border-radius: 100px;
            text-decoration: none;
            color: #1B2A4A;
        }

        /* Main Content */
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 24px 24px;
            gap: 28px;
        }

        /* Icon Circle */
        .icon-circle {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80px;
            height: 80px;
            background: #FEF3C7;
            border-radius: 40px;
        }

        /* Text Section */
        .text-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            text-align: center;
        }

        .text-section h1 {
            font-weight: 800;
            font-size: 22px;
            color: #1B2A4A;
        }

        .text-section p {
            font-weight: 400;
            font-size: 14px;
            line-height: 150%;
            color: #5A6E7F;
        }

        /* Input Group */
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .input-group label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            padding: 0px 16px;
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
        }

        .input-wrapper input {
            border: none;
            outline: none;
            width: 100%;
            height: 100%;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            background: transparent;
        }

        .input-wrapper input::placeholder {
            color: #94A3B8;
        }

        /* Button */
        .btn-submit {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            padding: 12px 16px;
            width: 100%;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
        }

        /* Help Card */
        .help-card {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 16px;
            gap: 12px;
            width: 100%;
            height: 76px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
            border-radius: 12px;
            text-decoration: none;
        }

        .help-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 46px;
            height: 50px;
            background: #1B2A4A;
            border-radius: 20px;
        }

        .help-text {
            font-weight: 700;
            font-size: 14px;
            color: #1B2A4A;
        }

        /* Home Indicator */
        .bottom-area {
            margin-top: auto;
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 21px 0px 8px;
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
        
        <!-- Header (Tombol Kembali) -->
        <div class="header-section">
            <a href="{{ route('login')}}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Konten Utama -->
        <form class="main-content" action="" method="POST">
            
            <!-- Icon -->
            <div class="icon-circle">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="4" width="14" height="16" rx="2" ry="2"></rect>
                    <circle cx="12" cy="10" r="2"></circle>
                    <line x1="12" y1="12" x2="12" y2="15"></line>
                </svg>
            </div>

            <!-- Teks Judul & Subjudul -->
            <div class="text-section">
                <h1>Lupa Kata Sandi?</h1>
                <p>Masukkan email atau nomor WhatsApp Anda. Admin sekolah akan membantu mereset kata sandi Anda.</p>
            </div>

            <!-- Input Email / No. WA -->
            <div class="input-group">
                <label for="contact">Email / No. WhatsApp</label>
                <div class="input-wrapper">
                    <input type="text" id="contact" name="contact" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn-submit">Kirim Permintaan Reset</button>

            <!-- Card Pusat Bantuan -->
            <a href="#" class="help-card">
                <div class="help-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <span class="help-text">Pusat bantuan</span>
            </a>

        </form>

        <!-- Home Indicator (Sticky di bawah) -->
        <div class="bottom-area">
            <div class="home-indicator"></div>
        </div>

    </div>

</body> 
</html>