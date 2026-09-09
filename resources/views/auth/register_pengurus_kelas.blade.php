<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengurus Kelas - Jurnalkita</title>
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

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px; 
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
            font-size: 12px;
            color: #5A6E7F;
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

        /* Alert Box */
        .alert-box {
            display: flex;
            align-items: center;
            padding: 12px;
            gap: 10px;
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            margin-bottom: 24px;
        }

        .alert-text {
            font-weight: 500;
            font-size: 12px;
            line-height: 140%;
            color: #1B2A4A;
        }

        /* Form */
        .form-section {
            display: flex;
            flex-direction: column;
            padding: 0px 24px 34px;
            gap: 14px;
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
            height: 46px;
            position: relative;
            overflow: hidden;
        }

        .input-wrapper input {
            border: none;
            outline: none;
            width: 100%;
            height: 100%;
            padding: 0px 16px;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            background: transparent;
        }

        .input-wrapper input::placeholder, 
        .input-wrapper select:invalid {
            color: #94A3B8;
        }

        .helper-text {
            font-weight: 400;
            font-size: 11px;
            line-height: 13px;
            color: #5A6E7F;
            margin-top: 2px;
        }

        /* Custom Select */
        .custom-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            outline: none;
            width: 100%;
            height: 100%;
            padding: 0px 40px 0px 16px;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            background: transparent;
            cursor: pointer;
        }
        
        .custom-select option {
            color: #1B2A4A;
        }

        .select-icon {
            position: absolute;
            right: 16px;
            pointer-events: none;
            display: flex;
        }

        .eye-icon {
            cursor: pointer;
            background: none;
            border: none;
            position: absolute;
            right: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            outline: none;
        }

        /* Submit Button */
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

        .home-indicator {
            width: 139px;
            height: 5px;
            background: #1B2A4A;
            border-radius: 100px;
            margin: 24px auto 8px;
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- Header -->
        <div class="header-section">
            <div class="header-text">
                <h1 class="header-title">Registrasi Pengurus Kelas</h1>
                <p class="header-subtitle">Akses jurnal mandiri perwakilan kelas</p>
            </div>
            <!-- Tombol Back mengarah ke halaman pilih peran -->
            <a href="{{ route('pilih_peran') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Alert Box -->
        <div class="alert-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span class="alert-text">Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.</span>
        </div>

        <!-- Form Registrasi -->
        <form class="form-section" action="" method="POST">
            
            <!-- Tingkat Dropdown -->
            <div class="input-group">
                <label for="tingkat">Tingkat</label>
                <div class="input-wrapper">
                    <select id="tingkat" name="tingkat" class="custom-select" required>
                        <option value="" disabled selected hidden>Pilih Tingkat (X, XI, XII)</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
                    </select>
                    <div class="select-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Jurusan Dropdown -->
            <div class="input-group">
                <label for="jurusan">Jurusan</label>
                <div class="input-wrapper">
                    <select id="jurusan" name="jurusan" class="custom-select" required>
                        <option value="" disabled selected hidden>Pilih Jurusan (e.g. Rekayasa Perangkat Lunak)</option>
                        <option value="RPL">Rekayasa Perangkat Lunak</option>
                        <option value="TKJ">Teknik Komputer dan Jaringan</option>
                        <option value="MM">Multimedia</option>
                        <!-- Tambahkan jurusan lain sesuai kebutuhan -->
                    </select>
                    <div class="select-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Username -->
            <div class="input-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="E.g: x-rpl-1-2026">
                </div>
                <p class="helper-text">Username dibuat otomatis berdasarkan Tingkat, Jurusan, dan Tahun angkatan</p>
            </div>

            <!-- No. WhatsApp -->
            <div class="input-group">
                <label for="phone">No. WhatsApp</label>
                <div class="input-wrapper">
                    <input type="number" id="phone" name="phone" placeholder="082334567890">
                </div>
            </div>

            <!-- Password Baru -->
            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Buat kata sandi baru">
                    <button type="button" id="togglePassword" class="eye-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="input-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi">
                    <button type="button" id="togglePasswordConfirm" class="eye-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-start;">
                <button type="submit" class="btn-submit">Daftar</button>
            </div>
            
            <div class="home-indicator"></div>
        </form>

    </div>

    <!-- Script JavaScript untuk 2 tombol mata -->
    <script>
        // Fungsi Toggle untuk Password Baru
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });

        // Fungsi Toggle untuk Konfirmasi Password
        const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
        const passwordConfirmInput = document.querySelector('#password_confirmation');

        togglePasswordConfirm.addEventListener('click', function () {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
        });
    </script>
</body> 
</html>