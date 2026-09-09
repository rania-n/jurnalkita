<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data - Manajemen Database Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Memanggil Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter',sans-serif] m-0 p-0 box-border">

    <div class="w-[390px] h-[844px] min-h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col justify-between shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden">
        <div class="flex flex-col h-full">
            <!-- Screen Header -->
            <div class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
                <div class="flex flex-col gap-0.5 flex-1">
                    <h1 class="font-bold text-[20px] leading-6 text-[#1B2A4A]">Master Data</h1>
                    <p class="font-normal text-[13px] leading-4 text-[#4A5568]">Manajemen Database Sekolah</p>
                </div>
                <a href="javascript:history.back()" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-[#cbd5e1] rounded-full border-none cursor-pointer no-underline shrink-0 transition-colors duration-200 ease-in-out">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Menu Card Content -->
            <div class="flex flex-col px-6 pb-5 gap-3 flex-1 overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                
                <!-- 1. Data Guru -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Data Guru</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">45 guru terdaftar aktif</span>
                    </div>
                    <div class="flex items-center justify-center py-1 px-2 bg-[#E2E8F0] rounded-md font-bold text-[11px] leading-[13px] text-[#1B2A4A] shrink-0">45</div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 2. Data Kelas -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 12h6"></path>
                            <path d="M9 16h6"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Data Kelas</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">36 kelas aktif semester ini</span>
                    </div>
                    <div class="flex items-center justify-center py-1 px-2 bg-[#E2E8F0] rounded-md font-bold text-[11px] leading-[13px] text-[#1B2A4A] shrink-0">36</div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 3. Data Siswa -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Data Siswa</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">1.260 siswa terdaftar</span>
                    </div>
                    <div class="flex items-center justify-center py-1 px-2 bg-[#E2E8F0] rounded-md font-bold text-[11px] leading-[13px] text-[#1B2A4A] shrink-0">1.2k</div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 4. Jadwal Pelajaran -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M8 14h.01"></path>
                            <path d="M12 14h.01"></path>
                            <path d="M16 14h.01"></path>
                            <path d="M8 18h.01"></path>
                            <path d="M12 18h.01"></path>
                            <path d="M16 18h.01"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Jadwal Pelajaran</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">Konfigurasi jadwal pelajaran</span>
                    </div>
                    <div class="flex items-center justify-center py-1 px-2 bg-[#E2E8F0] rounded-md font-bold text-[11px] leading-[13px] text-[#1B2A4A] shrink-0">24</div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 5. Jadwal Piket -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <polyline points="9 16 11 18 15 14"></polyline>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Jadwal Piket</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">Konfigurasi penugasan harian</span>
                    </div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- 6. Jam Pelajaran (13 JP) -->
                <a href="#" class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline transition-all duration-150 ease-in-out active:scale-[0.98]">
                    <div class="flex items-center justify-center w-7 h-7 shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                            <line x1="19" y1="5" x2="21" y2="3"></line>
                        </svg>
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        <span class="font-semibold text-[15px] leading-[18px] text-[#1B2A4A]">Jam Pelajaran (13 JP)</span>
                        <span class="font-normal text-[12px] leading-[15px] text-[#4A5568]">3 konfigurasi jam pelajaran aktif</span>
                    </div>
                    <svg class="shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

            </div>
        </div>

        <!-- Home Indicator -->
        <div class="flex justify-center items-start pt-[21px] pb-2 h-[34px] shrink-0">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>
    </div>

</body>
</html>