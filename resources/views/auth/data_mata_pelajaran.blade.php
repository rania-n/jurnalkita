<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mata Pelajaran - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <!-- Mobile Container -->
    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-2xl overflow-hidden">
        
        <!-- Header -->
        <header class="flex justify-between items-center pt-[60px] px-6 pb-4 bg-[#F4F6F9]">
            <div class="flex flex-col gap-[2px]">
                <h1 class="font-bold text-[20px] text-[#1B2A4A]">Daftar Mata Pelajaran</h1>
                <p class="font-normal text-[13px] text-[#4A5568]">Kelola mata pelajaran yang tersedia</p>
            </div>
            <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full border-none cursor-pointer text-[#1B2A4A] transition-colors active:bg-[#cbd5e1]">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-6 flex flex-col gap-4 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <!-- Search -->
            <div class="flex flex-row items-center px-[14px] py-[10px] gap-2 w-full h-[36px] bg-[#E2E8F0] rounded-[10px]">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" class="border-none bg-transparent w-full font-normal text-[13px] text-[#1B2A4A] outline-none placeholder-[#4A5568]" placeholder="Cari kelas...">
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_mata_pelajaran') }}" class="flex flex-row justify-between items-center px-3 py-2 w-full h-[36px] bg-[#1B2A4A] rounded-lg border-none text-white cursor-pointer">
                <span class="font-bold text-[11px]">Tambah Mata Pelajaran</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </a>
                
            <!-- Course List -->
            <!-- Card 1 -->
            <div class="flex flex-row items-center justify-between p-[14px] w-full bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl">
                <div class="flex flex-col gap-[2px] max-w-[200px]">
                    <span class="font-bold text-[14px] text-[#1B2A4A] leading-[1.2]">Matematika</span>
                    <span class="font-normal text-[11px] text-[#4A5568]">MAT-001</span>
                </div>
                <div class="flex flex-row gap-1">
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE] text-[#0369A1]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6] text-[#B91C1C]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="flex flex-row items-center justify-between p-[14px] w-full bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl">
                <div class="flex flex-col gap-[2px] max-w-[200px]">
                    <span class="font-bold text-[14px] text-[#1B2A4A] leading-[1.2]">Kreativitas, Inovasi, dan Kewirausahaan</span>
                    <span class="font-normal text-[11px] text-[#4A5568]">KIK-001</span>
                </div>
                <div class="flex flex-row gap-1">
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE] text-[#0369A1]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6] text-[#B91C1C]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="flex flex-row items-center justify-between p-[14px] w-full bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl">
                <div class="flex flex-col gap-[2px] max-w-[200px]">
                    <span class="font-bold text-[14px] text-[#1B2A4A] leading-[1.2]">Mapel Pilihan RPL</span>
                    <span class="font-normal text-[11px] text-[#4A5568]">MPR-001</span>
                </div>
                <div class="flex flex-row gap-1">
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE] text-[#0369A1]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6] text-[#B91C1C]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="flex flex-row items-center justify-between p-[14px] w-full bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl">
                <div class="flex flex-col gap-[2px] max-w-[200px]">
                    <span class="font-bold text-[14px] text-[#1B2A4A] leading-[1.2]">Bahasa Jepang</span>
                    <span class="font-normal text-[11px] text-[#4A5568]">BJE-001</span>
                </div>
                <div class="flex flex-row gap-1">
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE] text-[#0369A1]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6] text-[#B91C1C]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- Footer Area (Home Indicator) -->
        <div class="flex justify-center pt-3 pb-2 bg-[#F4F6F9]">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>

    </div>

</body>
</html>