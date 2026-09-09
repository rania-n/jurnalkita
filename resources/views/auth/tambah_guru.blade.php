<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Guru</title>
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

        /* Application Container */
        .app-card {
            position: relative;
            width: 390px;
            min-height: 844px;
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
            padding: 0 24px;
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

        /* Form Area */
        .form-content {
            width: 390px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0 24px 24px;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            width: 100%;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
        }

        .input-wrapper {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px;
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            position: relative;
        }

        .input-wrapper:focus-within {
            border-color: #1B2A4A;
        }

        .form-input {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-weight: 400;
            font-size: 14px;
            line-height: 17px;
            color: #1B2A4A;
        }

        .form-input::placeholder {
            color: #4A5568;
        }

        .form-select {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-weight: 400;
            font-size: 14px;
            line-height: 17px;
            color: #4A5568;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
        }

        .form-select option {
            color: #1B2A4A;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #4A5568;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Bottom Action / Footer */
        .bottom-action {
            width: 390px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0 24px 16px;
            gap: 12px;
            background: #F4F6F9;
        }

        .btn-submit {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            padding: 0 16px;
            width: 100%;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            line-height: 19px;
            color: #FFFFFF;
            transition: opacity 0.2s ease;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .home-indicator {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            padding: 12px 0 8px;
            width: 100%;
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

            <!-- Screen Header -->
            <div class="screen-header">
                <div class="header-title-group">
                    <h1 class="header-title">Tambah Data Guru</h1>
                    <p class="header-subtitle">Tambah akun guru sebagai staff piket</p>
                </div>
                <a href="{{ route('data_guru') }}" class="back-btn">
                    <i class="ph ph-arrow-left"></i>
                </a>
            </div>

            <!-- Form Content -->
            <form class="form-content">
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-input" placeholder="Masukkan nama lengkap guru" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">NIK</label>
                    <div class="input-wrapper">
                        <input type="number" class="form-input" placeholder="Masukkan Nomor Induk Kependudukan" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrapper">
                        <input type="email" class="form-input" placeholder="Masukkan alamat email aktif" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <div class="input-wrapper">
                        <input type="tel" class="form-input" placeholder="Masukkan nomor WhatsApp aktif" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mata Pelajaran Utama</label>
                    <div class="input-wrapper">
                        <select class="form-select" required>
                            <option value="" disabled selected>Pilih Mata Pelajaran</option>
                            <option value="rpl">Kejuruan Rekayasa Perangkat Lunak</option>
                            <option value="matematika">Matematika</option>
                            <option value="bahasa_indonesia">Bahasa Indonesia</option>
                            <option value="bahasa_inggris">Bahasa Inggris</option>
                        </select>
                        <i class="ph ph-caret-down" style="color: #4A5568; font-size: 18px; pointer-events: none;"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" class="form-input" placeholder="Buat kata sandi baru" required>
                        <button type="button" class="icon-btn" onclick="togglePassword('password', 'icon-pass')">
                            <i id="icon-pass" class="ph ph-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="text" id="confirm-password" class="form-input" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="icon-btn" onclick="togglePassword('confirm-password', 'icon-confirm')">
                            <i id="icon-confirm" class="ph ph-eye"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Bottom Action -->
        <div class="bottom-action">
            <button type="submit" class="btn-submit">
                Simpan Data Guru
            </button>
            <div class="home-indicator">
                <div class="indicator-bar"></div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            }
        }
    </script>
</body>
</html>