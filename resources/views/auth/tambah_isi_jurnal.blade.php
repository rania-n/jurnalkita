<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Jurnal Mengajar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] bg-[#F4F6F9] relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden rounded-xl">

        <!-- Header -->
        <header class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
            <div class="flex flex-col gap-0.5 flex-1">
                <h1 class="font-bold text-[20px] text-[#1B2A4A] leading-[24px]">Form Jurnal Mengajar</h1>
                <p class="font-normal text-[13px] text-[#5A6E7F] leading-[16px]">Isi jurnal mengajar dan kehadiran siswa</p>
            </div>
            <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full border-none cursor-pointer shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content Form -->
        <main class="flex flex-col px-6 pb-5 gap-4 flex-1 overflow-y-auto no-scrollbar">
            
            <!-- Badges -->
            <div class="flex gap-2.5 w-full">
                <div class="flex-[1.5] flex items-center py-2.5 px-3.5 gap-2 bg-[#E2E8F0] rounded-[10px]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span class="font-semibold text-[13px] text-[#1B2A4A]">Winartin, S.pd.</span>
                </div>
                <div class="flex-1 flex items-center py-2.5 px-3.5 gap-2 bg-[#E2E8F0] rounded-[10px]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="font-semibold text-[13px] text-[#1B2A4A]">06/09/2026</span>
                </div>
            </div>

            <!-- Kelas -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Kelas</label>
                <div class="relative w-full">
                    <select class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 h-[46px] outline-none appearance-none cursor-pointer focus:border-[#1B2A4A]">
                        <option value="" disabled selected>Pilih Kelas (e.g. X RPL 1)</option>
                        <option value="xrpl1">X RPL 1</option>
                        <option value="xrpl2">X RPL 2</option>
                    </select>
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>

            <!-- Jam Mulai & Selesai -->
            <div class="flex gap-3 w-full">
                <div class="flex flex-col gap-1.5 flex-1">
                    <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Jam Mulai</label>
                    <div class="relative w-full">
                        <select class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 h-[46px] outline-none appearance-none cursor-pointer focus:border-[#1B2A4A]">
                            <option value="jp1">JP-1</option>
                            <option value="jp2">JP-2</option>
                            <option value="jp3">JP-3</option>
                        </select>
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5 flex-1">
                    <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Jam Selesai</label>
                    <div class="relative w-full">
                        <select class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 h-[46px] outline-none appearance-none cursor-pointer focus:border-[#1B2A4A]">
                            <option value="jp3">JP-3</option>
                            <option value="jp4">JP-4</option>
                            <option value="jp5">JP-5</option>
                        </select>
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
            </div>

            <!-- Mata Pelajaran -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Mata Pelajaran</label>
                <div class="relative w-full">
                    <select class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 h-[46px] outline-none appearance-none cursor-pointer focus:border-[#1B2A4A]">
                        <option value="" disabled selected>Pilih Mata Pelajaran</option>
                        <option value="pbo">Pemrograman Berorientasi Objek</option>
                        <option value="web">Pemrograman Web</option>
                    </select>
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
            </div>

            <!-- Status Kehadiran Pengajar -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Status Kehadiran Pengajar</label>
                <div class="flex bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-lg h-[45px] w-full overflow-hidden">
                    <button type="button" class="segment-btn flex-1 flex justify-center items-center bg-[#1B2A4A] text-white border-none font-semibold text-[13px] cursor-pointer rounded-lg transition-all">Hadir</button>
                    <button type="button" class="segment-btn flex-1 flex justify-center items-center bg-transparent text-[#5A6E7F] border-none font-semibold text-[13px] cursor-pointer rounded-lg transition-all">Tugas</button>
                    <button type="button" class="segment-btn flex-1 flex justify-center items-center bg-transparent text-[#5A6E7F] border-none font-semibold text-[13px] cursor-pointer rounded-lg transition-all">Tidak Hadir</button>
                </div>
            </div>

            <!-- Materi -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Materi</label>
                <textarea class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] py-3 px-4 h-[65px] outline-none resize-none focus:border-[#1B2A4A] placeholder:text-[#5A6E7F]" placeholder="Mempelajari pemrograman modular dan dekomposisi fungsi pada aplikasi mobile..."></textarea>
            </div>

            <!-- Metode Pembelajaran -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Metode Pembelajaran</label>
                <textarea class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 pt-3 h-[45px] outline-none resize-none focus:border-[#1B2A4A] placeholder:text-[#5A6E7F]" placeholder="Menerangkan, diskusi, ulangan, dll..."></textarea>
            </div>

            <!-- Tugas Tambahan -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A] leading-[17px]">Tugas Tambahan (Jika guru tidak hadir)</label>
                <textarea class="w-full bg-white border border-[#EBEFF4] rounded-[10px] font-normal text-[14px] text-[#5E6F8D] px-4 pt-3 h-[45px] outline-none resize-none focus:border-[#1B2A4A] placeholder:text-[#5A6E7F]" placeholder="Mengerjakan materi halaman..."></textarea>
            </div>

        </main>

        <!-- Bottom Actions -->
        <div class="flex flex-col pt-4 px-6 pb-2 bg-[#F4F6F9] w-full shrink-0">
            <a href="{{ route('input_absensi_siswa') }}" class="flex flex-row justify-center items-center px-4 w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none cursor-pointer font-semibold text-[16px] leading-[19px] text-white transition-opacity hover:opacity-90">Lanjut ke Presensi Siswa</a>
            <div class="flex flex-row justify-center items-start pt-3 pb-2 w-full">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>

    </div>

    <script>
        const segmentBtns = document.querySelectorAll('.segment-btn');
        segmentBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                segmentBtns.forEach(b => {
                    b.classList.remove('bg-[#1B2A4A]', 'text-white');
                    b.classList.add('bg-transparent', 'text-[#5A6E7F]');
                });
                this.classList.remove('bg-transparent', 'text-[#5A6E7F]');
                this.classList.add('bg-[#1B2A4A]', 'text-white');
            });
        });
    </script>
</body>
</html>