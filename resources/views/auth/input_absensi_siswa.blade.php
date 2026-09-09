<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Presensi Siswa</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons CDN (Untuk Search & Upload) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* =======================================
           BASE STYLES (SAMA DENGAN FORM JURNAL)
           ======================================= */
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

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1B2A4A;
            line-height: 17px;
            margin-bottom: 6px;
            display: block;
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
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 48px;
            background: #1B2A4A;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            color: #FFFFFF;
            border: none;
            cursor: pointer;
        }

        .home-indicator {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 34px;
            padding-bottom: 8px;
        }

        .indicator-bar {
            width: 139px;
            height: 5px;
            background: #1B2A4A;
            border-radius: 100px;
        }

        /* =======================================
           SPECIFIC STYLES (HALAMAN PRESENSI)
           ======================================= */
        
        /* Search Input */
        .search-wrapper {
            position: relative;
            width: 100%;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #5A6E7F;
        }

        .search-input {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #EBEFF4;
            border-radius: 10px;
            padding: 0 16px 0 42px;
            height: 46px;
            font-size: 14px;
            color: #5E6F8D;
            outline: none;
        }

        .search-input::placeholder {
            color: #5A6E7F;
        }

        .search-input:focus {
            border-color: #1B2A4A;
        }

        /* Student Card List */
        .student-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .student-card {
            background: #FFFFFF;
            border: 1px solid #EBEFF4;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .avatar-male { background-color: #E0F2FE; color: #0369A1; }
        .avatar-female { background-color: #FCE7F3; color: #BE185D; }

        .student-info { display: flex; flex-direction: column; gap: 2px; }
        .student-name { font-size: 14px; font-weight: 600; color: #1B2A4A; }
        .student-nis { font-size: 11px; font-weight: 600; color: #5A6E7F; }

        /* Status Buttons Bar */
        .status-bar-container {
            width: 100%;
            height: 32px;
            background: #F4F6F9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 2px;
        }

        .status-btn {
            flex: 1;
            height: 100%;
            border-radius: 6px;
            border: none;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            background: transparent;
            color: #5E6F8D;
            transition: all 0.2s;
        }

        .status-btn.active {
            background: #1B2A4A;
            color: #FFFFFF;
            box-shadow: 0 2px 4px rgba(27, 42, 74, 0.15);
        }

        /* Attendance Summary Section */
        .summary-card {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #EBEFF4;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-item {
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 6px;
            flex: 1;
            margin: 0 2px;
        }

        .bg-hadir { background: #D1FFC2; }
        .bg-sakit { background: #FFF4B8; }
        .bg-izin { background: #E0F2FE; }
        .bg-alpha { background: #FFE4E6; }
        .bg-dispen { background: #F3E8FF; }

        .summary-label {
            font-size: 9px;
            font-weight: 600;
            color: #4A5568;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 700;
        }

        .txt-hadir { color: #0A5C36; }
        .txt-sakit { color: #C67A00; }
        .txt-izin { color: #0369A1; }
        .txt-alpha { color: #B91C1C; }
        .txt-dispen { color: #6D28D9; }

        /* Photo Upload Container */
        .upload-box {
            width: 100%;
            background: #FFFFFF;
            border: 1px dashed #B8C4D9;
            border-radius: 12px;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .upload-box:hover { border-color: #1B2A4A; }
        .upload-title { font-size: 13px; font-weight: 600; color: #1B2A4A; }
        .upload-desc { font-size: 11px; font-weight: 400; color: #5A6E7F; }
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
            <!-- Gunakan URL route kamu di atribut href ini -->
            <a href="{{ route('tambah_isi_jurnal') }}" class="btn-back">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="content">

            <!-- Search -->
            <div>
                <label class="form-label">Input Presensi Siswa</label>
                <div class="search-wrapper">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" id="searchInput" oninput="filterStudents()" 
                        placeholder="Cari nama atau NIS siswa..." class="search-input">
                </div>
            </div>

            <!-- List Siswa -->
            <div id="studentList" class="student-list">
                <!-- Di-render oleh Javascript -->
            </div>

            <!-- Ringkasan Presensi -->
            <div class="summary-card">
                <div class="summary-item bg-hadir">
                    <span class="summary-label">Hadir</span>
                    <span id="countHadir" class="summary-value txt-hadir">0</span>
                </div>
                <div class="summary-item bg-sakit">
                    <span class="summary-label">Sakit</span>
                    <span id="countSakit" class="summary-value txt-sakit">0</span>
                </div>
                <div class="summary-item bg-izin">
                    <span class="summary-label">Izin</span>
                    <span id="countIzin" class="summary-value txt-izin">0</span>
                </div>
                <div class="summary-item bg-alpha">
                    <span class="summary-label">Alpha</span>
                    <span id="countAlpha" class="summary-value txt-alpha">0</span>
                </div>
                <div class="summary-item bg-dispen">
                    <span class="summary-label">Dispen</span>
                    <span id="countDispen" class="summary-value txt-dispen">0</span>
                </div>
            </div>

            <!-- Upload Foto -->
            <div>
                <label class="form-label">Lampiran Foto Suasana Kelas</label>
                <input type="file" id="photoInput" accept="image/*" style="display: none;" onchange="handlePhotoUpload(event)">
                
                <div onclick="document.getElementById('photoInput').click()" class="upload-box">
                    <div id="photoPreviewContainer" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i data-lucide="image-plus" style="color: #1B2A4A;"></i>
                        <span class="upload-title">Lampirkan Foto Suasana Kelas</span>
                        <span class="upload-desc">Foto bukti pembelajaran sedang berlangsung</span>
                    </div>
                </div>
            </div>

        </main>

        <!-- Bottom Actions -->
        <div class="bottom-action">
            <button onclick="submitForm()" class="btn-primary">Simpan Jurnal & Absensi</button>
            <div class="home-indicator">
                <div class="indicator-bar"></div>
            </div>
        </div>

    </div>

    <script>
        // Data Siswa
        const students = [
            { id: "01", name: "Ahmad Fauzi", nis: "12345", gender: "male", status: "Hadir" },
            { id: "02", name: "Akana Fizyla", nis: "12345", gender: "female", status: "Hadir" },
            { id: "03", name: "Anjana Fauzia", nis: "12345", gender: "female", status: "Hadir" },
            { id: "04", name: "Bina Fazaya", nis: "12345", gender: "female", status: "Hadir" },
            { id: "05", name: "Celo Cassano", nis: "12345", gender: "male", status: "Hadir" },
            { id: "06", name: "Anjana Fauzia", nis: "12345", gender: "female", status: "Hadir" },
            { id: "07", name: "Bina Fazaya", nis: "12345", gender: "female", status: "Dispen" },
            { id: "08", name: "Celo Cassano", nis: "12345", gender: "male", status: "Hadir" }
        ];

        const statusOptions = ["Hadir", "Sakit", "Izin", "Alpha", "Dispen"];

        // Render List Siswa
        function renderStudents(filterText = "") {
            const listContainer = document.getElementById('studentList');
            listContainer.innerHTML = "";

            const filtered = students.filter(s => 
                s.name.toLowerCase().includes(filterText.toLowerCase()) || 
                s.nis.includes(filterText)
            );

            if(filtered.length === 0) {
                listContainer.innerHTML = `<div style="text-align: center; padding: 20px 0; font-size: 13px; color: #94a3b8;">Siswa tidak ditemukan</div>`;
                return;
            }

            filtered.forEach((student) => {
                const avatarClass = student.gender === "male" ? "avatar-male" : "avatar-female";
                const card = document.createElement('div');
                card.className = "student-card";
                
                let buttonsHTML = statusOptions.map(opt => {
                    const activeClass = student.status === opt ? "active" : "";
                    return `
                        <button onclick="setStatus('${student.id}', '${opt}')" class="status-btn ${activeClass}">
                            ${opt}
                        </button>
                    `;
                }).join('');

                card.innerHTML = `
                    <div class="student-header">
                        <div class="avatar ${avatarClass}">${student.id}</div>
                        <div class="student-info">
                            <span class="student-name">${student.name}</span>
                            <span class="student-nis">NIS: ${student.nis}</span>
                        </div>
                    </div>
                    <div class="status-bar-container">
                        ${buttonsHTML}
                    </div>
                `;

                listContainer.appendChild(card);
            });

            updateSummary();
        }

        function setStatus(studentId, newStatus) {
            const student = students.find(s => s.id === studentId);
            if(student) {
                student.status = newStatus;
                renderStudents(document.getElementById('searchInput').value);
            }
        }

        function updateSummary() {
            const counts = { Hadir: 0, Sakit: 0, Izin: 0, Alpha: 0, Dispen: 0 };
            students.forEach(s => counts[s.status] = (counts[s.status] || 0) + 1);

            document.getElementById('countHadir').innerText = counts.Hadir;
            document.getElementById('countSakit').innerText = counts.Sakit;
            document.getElementById('countIzin').innerText = counts.Izin;
            document.getElementById('countAlpha').innerText = counts.Alpha;
            document.getElementById('countDispen').innerText = counts.Dispen;
        }

        function filterStudents() {
            const query = document.getElementById('searchInput').value;
            renderStudents(query);
        }

        function handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('photoPreviewContainer');
                    container.innerHTML = `
                        <img src="${e.target.result}" style="width: 100%; height: 90px; object-fit: cover; border-radius: 8px;">
                        <span class="upload-desc" style="color: #16a34a; font-weight: 600; margin-top: 4px;">✓ Foto berhasil dipilih</span>
                    `;
                }
                reader.readAsDataURL(file);
            }
        }

        function submitForm() {
            alert("Jurnal dan presensi berhasil disimpan!");
        }

        // Initialize Icons & Render
        lucide.createIcons();
        renderStudents();
    </script>
</body>
</html>