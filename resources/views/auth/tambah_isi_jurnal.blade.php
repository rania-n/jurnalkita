<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Jurnal Mengajar</title>
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
            background: #F4F6F9;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            border-radius: 12px;
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            height: 44px;
            flex-shrink: 0;
        }

        .time {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .status-icons {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .status-icons svg {
            fill: #1B2A4A;
        }

        /* Header Area */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px;
            flex-shrink: 0;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }

        .header-title {
            font-weight: 700;
            font-size: 20px;
            color: #1B2A4A;
            line-height: 24px;
        }

        .header-subtitle {
            font-weight: 400;
            font-size: 13px;
            color: #5A6E7F;
            line-height: 16px;
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
            flex-shrink: 0;
            text-decoration: none;
        }

        /* Main Content */
        .content {
            display: flex;
            flex-direction: column;
            padding: 0 24px 20px;
            gap: 16px;
            flex: 1;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Info Badges (Teacher & Date) */
        .info-badges {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .badge {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            gap: 8px;
            background: #E2E8F0;
            border-radius: 10px;
        }

        .badge.teacher {
            flex: 1.5;
        }

        .badge.date {
            flex: 1;
        }

        .badge span {
            font-weight: 600;
            font-size: 13px;
            color: #1B2A4A;
        }

        /* Form Groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .form-row {
            display: flex;
            gap: 12px;
            width: 100%;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
            line-height: 17px;
        }

        /* Input Controls */
        .form-control {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #EBEFF4;
            border-radius: 10px;
            font-weight: 400;
            font-size: 14px;
            color: #5E6F8D;
            padding: 0 16px;
            height: 46px;
            outline: none;
        }
        
        .form-control::placeholder {
            color: #5A6E7F;
        }

        .form-control:focus {
            border-color: #1B2A4A;
        }

        /* Textarea specific */
        textarea.form-control {
            padding: 12px 16px;
            resize: none;
        }

        .textarea-lg {
            height: 65px;
        }

        /* Select Wrapper for custom chevron */
        .select-wrapper {
            position: relative;
            width: 100%;
        }

        .select-wrapper select {
            appearance: none;
            -webkit-appearance: none;
            width: 100%;
            cursor: pointer;
        }

        .select-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Segmented Control (Kehadiran) */
        .segmented-control {
            display: flex;
            background: #FFFFFF;
            box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
            border-radius: 8px;
            height: 45px;
            width: 100%;
            overflow: hidden;
        }

        .segment-btn {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: transparent;
            border: none;
            font-weight: 600;
            font-size: 13px;
            color: #5A6E7F;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .segment-btn.active {
            background: #1B2A4A;
            color: #FFFFFF;
        }

        /* Bottom Action Area */
        .bottom-action {
            display: flex;
            flex-direction: column;
            padding: 16px 24px 8px;
            background: #F4F6F9;
            width: 100%;
        }

        .btn-primary {
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

    <div class="app-container">

        <!-- Header -->
        <header class="header">
            <div class="header-text">
                <h1 class="header-title">Form Jurnal Mengajar</h1>
                <p class="header-subtitle">Isi jurnal mengajar dan kehadiran siswa</p>
            </div>
            <a href="#" class="btn-back">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content Form -->
        <main class="content">
            
            <!-- Badges -->
            <div class="info-badges">
                <div class="badge teacher">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Winartin, S.pd.</span>
                </div>
                <div class="badge date">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>06/09/2026</span>
                </div>
            </div>

            <!-- Kelas -->
            <div class="form-group">
                <label class="form-label">Kelas</label>
                <div class="select-wrapper">
                    <select class="form-control">
                        <option value="" disabled selected>Pilih Kelas (e.g. X RPL 1)</option>
                        <option value="xrpl1">X RPL 1</option>
                        <option value="xrpl2">X RPL 2</option>
                    </select>
                    <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>

            <!-- Jam Mulai & Selesai -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jam Mulai</label>
                    <div class="select-wrapper">
                        <select class="form-control">
                            <option value="jp1">JP-1</option>
                            <option value="jp2">JP-2</option>
                            <option value="jp3">JP-3</option>
                        </select>
                        <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Selesai</label>
                    <div class="select-wrapper">
                        <select class="form-control">
                            <option value="jp3">JP-3</option>
                            <option value="jp4">JP-4</option>
                            <option value="jp5">JP-5</option>
                        </select>
                        <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
            </div>

            <!-- Mata Pelajaran -->
            <div class="form-group">
                <label class="form-label">Mata Pelajaran</label>
                <div class="select-wrapper">
                    <select class="form-control">
                        <option value="" disabled selected>Pilih Mata Pelajaran</option>
                        <option value="pbo">Pemrograman Berorientasi Objek</option>
                        <option value="web">Pemrograman Web</option>
                    </select>
                    <svg class="select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>

            <!-- Status Kehadiran Pengajar -->
            <div class="form-group">
                <label class="form-label">Status Kehadiran Pengajar</label>
                <div class="segmented-control">
                    <!-- Jika menggunakan form backend PHP, ini bisa diubah menjadi radio buttons tersembunyi yang dibungkus label -->
                    <button type="button" class="segment-btn active">Hadir</button>
                    <button type="button" class="segment-btn">Tugas</button>
                    <button type="button" class="segment-btn">Tidak Hadir</button>
                </div>
            </div>

            <!-- Materi -->
            <div class="form-group">
                <label class="form-label">Materi</label>
                <textarea class="form-control textarea-lg" placeholder="Mempelajari pemrograman modular dan dekomposisi fungsi pada aplikasi mobile..."></textarea>
            </div>

            <!-- Metode Pembelajaran -->
            <div class="form-group">
                <label class="form-label">Metode Pembelajaran</label>
                <!-- Bisa input text atau textarea tergantung kebutuhan -->
                <textarea class="form-control" style="height: 45px; padding-top: 12px;" placeholder="Menerangkan, diskusi, ulangan, dll..."></textarea>
            </div>

            <!-- Tugas Tambahan -->
            <div class="form-group">
                <label class="form-label">Tugas Tambahan (Jika guru tidak hadir)</label>
                <textarea class="form-control" style="height: 45px; padding-top: 12px;" placeholder="Mengerjakan materi halaman..."></textarea>
            </div>

        </main>

        <!-- Bottom Actions -->
        <div class="bottom-action">
            <a href="{{ route('input_absensi_siswa') }}" class="btn-primary">Lanjut ke Presensi Siswa</a>
            <div class="home-indicator">
                <div class="indicator-bar"></div>
            </div>
        </div>

    </div>

    <!-- Script sederhana untuk efek klik pada Segmented Control (Hadir/Tugas/Tidak Hadir) -->
    <script>
        const segmentBtns = document.querySelectorAll('.segment-btn');
        segmentBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                segmentBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>