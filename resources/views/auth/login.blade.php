<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jurnalkita</title>
    <!-- Memanggil font Inter persis seperti di Figma -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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

        .login-container {
            width: 402px;
            height: 874px;
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 100px 24px 40px;
            gap: 8px;
            text-align: center;
        }

        .logo-box {
            width: 72px;
            height: 72px;
            background: #1B2A4A;
            border-radius: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
        }

        .book-icon {
            width: 24px;
            height: 28px;
            border: 2px solid #FFFFFF;
            border-radius: 4px;
            position: relative;
        }
        
        .book-icon::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 4px;
            right: 4px;
            height: 2px;
            background: #FFFFFF;
        }

        .logo-title {
            font-weight: 800;
            font-size: 28px;
            color: #1B2A4A;
            line-height: 34px;
        }

        .logo-subtitle {
            font-weight: 500;
            font-size: 13px;
            color: #5A6E7F;
            line-height: 16px;
        }

        .form-section {
            display: flex;
            flex-direction: column;
            padding: 0px 24px;
            gap: 16px;
            flex-grow: 1;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .input-group label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 0px 16px;
            height: 46px;
        }

        .input-wrapper input {
            border: none;
            outline: none;
            width: 100%;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            background: transparent;
        }

        .input-wrapper input::placeholder {
            color: #94A3B8;
        }

        /* Styling baru untuk tombol mata agar tidak berborder default */
        .eye-icon {
            cursor: pointer;
            background: none;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            outline: none;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-group input {
            width: 20px;
            height: 20px;
            accent-color: #1B2A4A;
            cursor: pointer;
        }

        .checkbox-group label {
            font-weight: 400;
            font-size: 13px;
            color: #5A6E7F;
            cursor: pointer;
        }

        .forgot-password {
            font-weight: 600;
            font-size: 13px;
            color: #1B2A4A;
            text-decoration: none;
        }

        .btn-submit {
            margin-top: 12px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            width: 100px;
            height: 48px;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
        }

        .footer-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 34px;
        }

        .register-link {
            font-weight: 400;
            font-size: 14px;
            color: #5A6E7F;
        }

        .register-link a {
            font-weight: 700;
            color: #1B2A4A;
            text-decoration: none;
            margin-left: 4px;
        }

        .home-indicator {
            width: 139px;
            height: 5px;
            background: #1B2A4A;
            border-radius: 100px;
            margin-top: 24px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <!-- Bagian Logo -->
        <div class="logo-section">
            <div class="logo-box">
                <div class="book-icon"></div>
            </div>
            <h1 class="logo-title">jurnalkita</h1>
            <p class="logo-subtitle">Sistem Jurnal & Absensi Guru SMKN 1 Boyolangu</p>
        </div>

        <!-- Bagian Form -->
        <form class="form-section" action="" method="POST">
            
            <div class="input-group">
                <label for="username">Email / Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Masukkan email atau username">
                </div>
            </div>

            <div class="input-group">
                <label for="password">Kata Sandi</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi">
                    
                    <!-- SVG Figma dipasang di dalam button ber-ID togglePassword -->
                    <button type="button" id="togglePassword" class="eye-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_3_34)">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </g>
                            <defs>
                            <clipPath id="clip0_3_34">
                            <rect width="18" height="18" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-group">
                    <input type="checkbox" name="remember">
                    <span>Keep Logged In (30 hari)</span>
                </label>
                <a href="{{ route('lupa_sandi') }}" class="forgot-password">Lupa Sandi?</a>
            </div>

            <div style="display: flex; justify-content: flex-start;">
                <button type="submit" class="btn-submit">Masuk</button>
            </div>
            
        </form>

        <!-- Bagian Bawah -->
        <div class="footer-section">
            <p class="register-link">Belum punya akun? <a href="{{ route('pilih_peran') }}">Daftar Akun Baru</a></p>
            <div class="home-indicator"></div>
        </div>

    </div>

    <!-- Script JavaScript -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // Ubah tipe input dari password ke text
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>
</body> 
</html>