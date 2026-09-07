<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kelas - Jurnalkita</title>
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
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .app-container::-webkit-scrollbar {
            display: none;
        }

        /* Top Content Wrapper */
        .top-content {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 24px; /* Padding atas 60px menutupi ruang status bar */
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

        /* Form */
        .form-section {
            display: flex;
            flex-direction: column;
            padding: 0px 24px;
            gap: 16px;
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
        
        .custom-select:invalid {
            color: #4A5568;
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

        /* Bottom Area */
        .bottom-area {
            display: flex;
            flex-direction: column;
            padding: 0px 24px 16px;
            gap: 12px;
            width: 100%;
        }

        .btn-submit {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1B2A4A;
            border-radius: 12px;
            border: none;
            width: 100%;
            height: 48px;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
        }

        .home-indicator-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 21px 0px 8px;
            width: 100%;
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
        
        <!-- Bagian Atas (Header & Form) -->
        <div class="top-content">
            <!-- Header -->
            <div class="header-section">
                <div class="header-text">
                    <h1 class="header-title">Tambah Data Kelas</h1>
                    <p class="header-subtitle">Kelola data kelas yang tersedia</p>
                </div>
                <!-- Tombol Back (Otomatis kembali ke halaman sebelumnya) -->
                <a href="{{ route('data_kelas') }}" class="btn-back">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Form -->
            <form class="form-section" action="" method="POST" id="formTambahKelas">
                
                <!-- Dropdown Tingkat -->
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
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Jurusan -->
                <div class="input-group">
                    <label for="jurusan">Jurusan</label>
                    <div class="input-wrapper">
                        <select id="jurusan" name="jurusan" class="custom-select" required>
                            <option value="" disabled selected hidden>Pilih Jurusan (e.g. Rekayasa Perangkat Lunak)</option>
                            <option value="RPL">Rekayasa Perangkat Lunak</option>
                            <option value="TKJ">Teknik Komputer dan Jaringan</option>
                            <option value="MM">Multimedia</option>
                        </select>
                        <div class="select-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Guru Wali Kelas -->
                <div class="input-group">
                    <label for="wali_kelas">Guru Wali Kelas</label>
                    <div class="input-wrapper">
                        <select id="wali_kelas" name="wali_kelas" class="custom-select" required>
                            <option value="" disabled selected hidden>Pilih Guru Wali Kelas</option>
                            <option value="1">Bpk. Budi Santoso</option>
                            <option value="2">Ibu Siti Aminah</option>
                            <option value="3">Bpk. Ahmad Fauzi</option>
                        </select>
                        <div class="select-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bagian Bawah (Tombol & Indikator) -->
        <div class="bottom-area">
            <!-- Gunakan atribut form agar terhubung dengan form di atas -->
            <button type="submit" form="formTambahKelas" class="btn-submit">Tambah Kelas</button>
            
            <div class="home-indicator-wrapper">
                <div class="home-indicator"></div>
            </div>
        </div>

    </div>

</body> 
</html>