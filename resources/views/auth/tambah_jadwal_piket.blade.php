<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Piket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #e5e5e5; /* Latar belakang luar frame */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Container Frame Utama */
        .mobile-frame {
            position: relative;
            width: 390px;
            height: 844px;
            min-height: 844px;
            background: #F4F6F9;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1); /* Tambahan shadow agar frame terlihat */
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 0px 24px;
            height: 44px;
            width: 100%;
        }

        .status-bar .time {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        .status-icons {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 6px;
        }

        /* Header */
        .screen-header {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 60px 24px 16px;
            width: 100%;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .header-text h1 {
            font-weight: 700;
            font-size: 20px;
            color: #1B2A4A;
        }

        .header-text p {
            font-weight: 400;
            font-size: 13px;
            color: #4A5568;
        }

        .back-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 34px;
            height: 34px;
            background: #E2E8F0;
            border-radius: 100px;
            border: none;
            cursor: pointer;
        }

        /* Form Content */
        .form-content {
            display: flex;
            flex-direction: column;
            padding: 0px 24px 24px;
            gap: 16px;
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .form-row {
            display: flex;
            flex-direction: row;
            gap: 12px;
            width: 100%;
        }

        .form-row .form-group {
            flex: 1; /* Agar field Jam Mulai & Jam Selesai terbagi rata */
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
        }

        /* Input Styling */
        .input-wrapper {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 0px 16px;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            position: relative;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            font-weight: 400;
            font-size: 14px;
            color: #1B2A4A;
            outline: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .input-wrapper select,
        .input-wrapper input::placeholder {
            color: #4A5568;
        }

        .input-wrapper select {
            cursor: pointer;
        }

        /* Hilangkan icon jam default di input time */
        .input-wrapper input[type="time"]::-webkit-calendar-picker-indicator {
            display: none;
        }

        .dropdown-icon {
            position: absolute;
            right: 16px;
            pointer-events: none;
        }

        /* Bottom Section (Button + Indicator) */
        .bottom-section {
            position: absolute;
            bottom: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            padding: 0px 24px;
        }

        .submit-btn {
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

        .home-indicator-wrapper {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            padding: 12px 0 8px;
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

    <div class="mobile-frame">

        <!-- Header -->
        <div class="screen-header">
            <div class="header-text">
                <h1>Tambah Jadwal Piket</h1>
                <p>Atur jadwal piket harian staff</p>
            </div>
            <a href="{{ route('data_jadwal_piket') }}" class="back-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
        </div>

        <!-- Form Elements -->
        <div class="form-content">
            
            <!-- Hari -->
            <div class="form-group">
                <label class="form-label">Hari</label>
                <div class="input-wrapper">
                    <select>
                        <option value="" disabled selected>Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                    </select>
                    <svg class="dropdown-icon" width="12" height="8" viewBox="0 0 12 8" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1.5L6 6.5L11 1.5"/></svg>
                </div>
            </div>

            <!-- Guru Piket -->
            <div class="form-group">
                <label class="form-label">Guru Piket</label>
                <div class="input-wrapper">
                    <select>
                        <option value="" disabled selected>Pilih Guru</option>
                        <option value="1">Guru Pertama</option>
                        <option value="2">Guru Kedua</option>
                    </select>
                    <svg class="dropdown-icon" width="12" height="8" viewBox="0 0 12 8" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1.5L6 6.5L11 1.5"/></svg>
                </div>
            </div>

            <!-- Jam Mulai & Selesai (Row) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jam Mulai</label>
                    <div class="input-wrapper">
                        <input type="time" value="07:00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jam Selesai</label>
                    <div class="input-wrapper">
                        <input type="time" value="12:00">
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <div class="input-wrapper">
                    <input type="text" placeholder="Keterangan tambahan (opsional)">
                </div>
            </div>

        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <button class="submit-btn">Tambah Jadwal Piket</button>
            <div class="home-indicator-wrapper">
                <div class="home-indicator"></div>
            </div>
        </div>

    </div>

</body>
</html>