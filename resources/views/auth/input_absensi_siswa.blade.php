<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Presensi Siswa</title>
    <!-- Google Fonts & Tailwind CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            800: '#1B2A4A',
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-sans">

    <!-- Mobile App Container -->
    <div class="w-[390px] h-[844px] bg-[#F4F6F9] relative flex flex-col shadow-2xl overflow-hidden rounded-xl">

        <!-- Header -->
        <header class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
            <div class="flex flex-col gap-0.5 flex-1">
                <h1 class="font-bold text-xl text-navy-800 leading-6">Form Jurnal Mengajar</h1>
                <p class="font-normal text-xs text-[#5A6E7F] leading-4">Isi jurnal mengajar dan kehadiran siswa</p>
            </div>
            <a href="{{ route('tambah_isi_jurnal') }}" class="w-[34px] h-[34px] bg-[#E2E8F0] rounded-full flex justify-center items-center shrink-0 no-underline">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex flex-col px-6 pb-[20px] gap-4 flex-1 overflow-y-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">

            <!-- Search -->
            <div>
                <label class="block font-semibold text-sm text-navy-800 leading-[17px] mb-1.5">Input Presensi Siswa</label>
                <div class="relative w-full">
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-[#5A6E7F]"></i>
                    <input type="text" id="searchInput" oninput="filterStudents()" 
                        placeholder="Cari nama atau NIS siswa..." 
                        class="w-full bg-white border border-[#EBEFF4] focus:border-navy-800 rounded-lg pl-10 pr-4 h-[46px] text-sm text-[#5E6F8D] outline-none">
                </div>
            </div>

            <!-- List Siswa -->
            <div id="studentList" class="flex flex-col gap-3">
                <!-- Di-render oleh Javascript -->
            </div>

            <!-- Ringkasan Presensi -->
            <div class="w-full bg-white border border-[#EBEFF4] rounded-xl p-3 flex justify-between items-center">
                <div class="flex-1 m-0.5 p-1.5 rounded-md flex flex-col justify-center items-center bg-[#D1FFC2]">
                    <span class="text-[9px] font-semibold text-[#4A5568] mb-0.5">Hadir</span>
                    <span id="countHadir" class="text-base font-bold text-[#0A5C36]">0</span>
                </div>
                <div class="flex-1 m-0.5 p-1.5 rounded-md flex flex-col justify-center items-center bg-[#FFF4B8]">
                    <span class="text-[9px] font-semibold text-[#4A5568] mb-0.5">Sakit</span>
                    <span id="countSakit" class="text-base font-bold text-[#C67A00]">0</span>
                </div>
                <div class="flex-1 m-0.5 p-1.5 rounded-md flex flex-col justify-center items-center bg-[#E0F2FE]">
                    <span class="text-[9px] font-semibold text-[#4A5568] mb-0.5">Izin</span>
                    <span id="countIzin" class="text-base font-bold text-[#0369A1]">0</span>
                </div>
                <div class="flex-1 m-0.5 p-1.5 rounded-md flex flex-col justify-center items-center bg-[#FFE4E6]">
                    <span class="text-[9px] font-semibold text-[#4A5568] mb-0.5">Alpha</span>
                    <span id="countAlpha" class="text-base font-bold text-[#B91C1C]">0</span>
                </div>
                <div class="flex-1 m-0.5 p-1.5 rounded-md flex flex-col justify-center items-center bg-[#F3E8FF]">
                    <span class="text-[9px] font-semibold text-[#4A5568] mb-0.5">Dispen</span>
                    <span id="countDispen" class="text-base font-bold text-[#6D28D9]">0</span>
                </div>
            </div>

            <!-- Upload Foto -->
            <div>
                <label class="block font-semibold text-sm text-navy-800 leading-[17px] mb-1.5">Lampiran Foto Suasana Kelas</label>
                <input type="file" id="photoInput" accept="image/*" class="hidden" onchange="handlePhotoUpload(event)">
                
                <div onclick="document.getElementById('photoInput').click()" 
                    class="w-full bg-white border border-dashed border-[#B8C4D9] hover:border-navy-800 rounded-xl py-6 px-5 flex flex-col justify-center items-center gap-2 cursor-pointer transition-colors">
                    <div id="photoPreviewContainer" class="flex flex-col items-center gap-2">
                        <i data-lucide="image-plus" class="text-navy-800"></i>
                        <span class="text-xs font-semibold text-navy-800">Lampirkan Foto Suasana Kelas</span>
                        <span class="text-[11px] font-normal text-[#5A6E7F]">Foto bukti pembelajaran sedang berlangsung</span>
                    </div>
                </div>
            </div>

        </main>

        <!-- Bottom Actions -->
        <div class="flex flex-col pt-4 px-6 pb-2 bg-[#F4F6F9] w-full">
            <button onclick="submitForm()" class="flex justify-center items-center w-full h-[48px] bg-navy-800 rounded-xl font-semibold text-base text-white border-none cursor-pointer">Simpan Jurnal & Absensi</button>
            <div class="flex justify-center items-end h-[34px] pb-2">
                <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
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
                listContainer.innerHTML = `<div class="text-center py-5 text-xs text-slate-400">Siswa tidak ditemukan</div>`;
                return;
            }

            filtered.forEach((student) => {
                const avatarClass = student.gender === "male" 
                    ? "bg-[#E0F2FE] text-[#0369A1]" 
                    : "bg-[#FCE7F3] text-[#BE185D]";

                const card = document.createElement('div');
                card.className = "bg-white border border-[#EBEFF4] rounded-xl p-3 flex flex-col gap-3 shadow-[0_2px_8px_rgba(0,0,0,0.02)]";
                
                let buttonsHTML = statusOptions.map(opt => {
                    const activeClass = student.status === opt 
                        ? "bg-navy-800 text-white shadow-[0_2px_4px_rgba(27,42,74,0.15)]" 
                        : "bg-transparent text-[#5E6F8D]";

                    return `
                        <button onclick="setStatus('${student.id}', '${opt}')" class="flex-1 h-full rounded-md text-[11px] font-semibold border-none cursor-pointer transition-all ${activeClass}">
                            ${opt}
                        </button>
                    `;
                }).join('');

                card.innerHTML = `
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-base font-bold shrink-0 ${avatarClass}">${student.id}</div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-semibold text-navy-800">${student.name}</span>
                            <span class="text-[11px] font-semibold text-[#5A6E7F]">NIS: ${student.nis}</span>
                        </div>
                    </div>
                    <div class="w-full h-8 bg-[#F4F6F9] rounded-lg flex items-center gap-1 p-0.5">
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
                        <img src="${e.target.result}" class="w-full h-[90px] object-cover rounded-lg">
                        <span class="text-[11px] font-semibold text-green-600 mt-1">✓ Foto berhasil dipilih</span>
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