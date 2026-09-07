<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran - Jurnalkita</title>
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

        .register-container {
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

        /* Status Bar */
        .status-bar { display: flex; justify-content: space-between; align-items: center; padding: 14px 24px 0px; height: 44px; }
        .time-text { font-weight: 600; font-size: 14px; color: #1B2A4A; }
        .status-icons { display: flex; gap: 6px; align-items: center; }

        /* Header */
        .screen-header { display: flex; justify-content: space-between; align-items: center; padding: 60px 24px 16px;}
        .header-text h1 { font-weight: 700; font-size: 20px; color: #1B2A4A; line-height: 24px; }
        .header-text p { font-weight: 400; font-size: 12px; color: #5A6E7F; line-height: 15px; margin-top: 2px; }
        
        .btn-back { width: 34px; height: 34px; background: #EBEFF4; border-radius: 100px; display: flex; justify-content: center; align-items: center; cursor: pointer; border: none; text-decoration: none; }

        /* Role Cards */
        .role-container { display: flex; flex-direction: column; padding: 0px 24px; gap: 20px; flex-grow: 1; margin-top: 8px; }
        
        .role-card { background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 16px; cursor: pointer; transition: all 0.2s ease; text-decoration: none; }
        .role-card:hover { border-color: #1B2A4A; transform: translateY(-2px); }
        
        .role-top { display: flex; justify-content: space-between; align-items: center; }
        .icon-box { width: 48px; height: 48px; background: #EBEFF4; border-radius: 12px; display: flex; justify-content: center; align-items: center; }
        
        .role-info h2 { font-weight: 700; font-size: 18px; color: #1B2A4A; line-height: 22px; margin-bottom: 4px; }
        .role-info p { font-weight: 400; font-size: 13px; color: #5A6E7F; line-height: 140%; }
        
        /* Bottom Indicator */
        .bottom-indicator { padding: 21px 0px 8px; display: flex; justify-content: center; }
        .home-indicator { width: 139px; height: 5px; background: #1B2A4A; border-radius: 100px; }
    </style>
</head>
<body>

    <div class="register-container">

        <div class="screen-header">
            <div class="header-text">
                <h1>Pilih Peran Daftar</h1>
                <p>Silakan pilih jenis keanggotaan Anda</p>
            </div>
            <!-- Menggunakan fungsi route() dari Laravel untuk kembali ke login -->
            <a href="{{ route('login') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
            </a>
        </div>

        <div class="role-container">
            <a href="{{ route('register_guru') }}" class="role-card">
                <div class="role-top">
                    <div class="icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
                <div class="role-info">
                    <h2>Guru Mata Pelajaran</h2>
                    <p>Daftar sebagai tenaga pengajar untuk melakukan pengisian jurnal KBM dan absensi siswa di kelas.</p>
                </div>
            </a>

            <a href="{{ route('register_pengurus_kelas') }}" class="role-card">
                <div class="role-top">
                    <div class="icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
                <div class="role-info">
                    <h2>Pengurus Kelas / Siswa</h2>
                    <p>Mewakili ketua kelas atau sekretaris untuk melihat riwayat jurnal dan membantu administrasi KBM harian.</p>
                </div>
            </a>
        </div>

        <div class="bottom-indicator">
            <div class="home-indicator"></div>
        </div>

    </div>

</body>
</html>