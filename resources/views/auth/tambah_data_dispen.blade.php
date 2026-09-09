<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dispensasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden">

        <!-- Header -->
        <header class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
            <div class="flex flex-col gap-0.5 max-w-[280px]">
                <h1 class="font-bold text-[20px] text-[#1B2A4A] leading-6">Daftar Dispensasi</h1>
                <p class="font-normal text-[13px] text-[#4A5568] leading-[140%]">Riwayat dan status persetujuan dispensasi oleh Staff Piket dan Waka Kesiswaan.</p>
            </div>
            <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-6 flex flex-col gap-4 overflow-y-auto no-scrollbar pb-5">
            
            <!-- Search Box -->
            <div class="flex items-center p-[10px_14px] gap-2 bg-[#E2E8F0] rounded-[10px] w-full">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" class="flex-1 bg-transparent border-none outline-none font-normal text-[13px] text-[#1B2A4A] placeholder:text-[#4A5568]" placeholder="Cari nama atau alasan dispensasi...">
            </div>

            <!-- Filters -->
            <div class="flex gap-2 w-full">
                <div class="flex-1 flex justify-between items-center p-[8px_12px] bg-white border border-[#E2E8F0] rounded-lg cursor-pointer">
                    <span class="font-bold text-[11px] text-[#4A5568]">Semua Kelas</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="flex-1 flex justify-between items-center p-[8px_12px] bg-white border border-[#E2E8F0] rounded-lg cursor-pointer">
                    <span class="font-bold text-[11px] text-[#4A5568]">Tanggal</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('tambah_data_dispen') }}" class="flex justify-between items-center p-[10px_12px] bg-[#1B2A4A] rounded-lg border-none w-full cursor-pointer">
                <span class="font-bold text-[12px] text-white">Ajukan Dispensasi</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <circle cx="10" cy="13" r="2"></circle>
                    <path d="M7 18v-1a3 3 0 0 1 6 0v1"></path>
                    <line x1="16" y1="13" x2="18" y2="13"></line>
                    <line x1="16" y1="17" x2="18" y2="17"></line>
                </svg>
            </a>

            <!-- Cards List -->
            <div class="flex flex-col gap-4">
                
                <!-- Card: Dude Fahrezi -->
                <div class="flex justify-between items-center p-[14px] bg-white shadow-[0px_2px_8px_rgba(27,42,74,0.04)] rounded-xl">
                    <div class="flex flex-col gap-0.5">
                        <div class="font-bold text-[14px] text-[#1B2A4A]">Dude Fahrezi</div>
                        <div class="font-normal text-[11px] text-[#4A5568]">XI RPL 2</div>
                        <div class="font-semibold text-[11px] text-[#1B2A4A] mt-0.5">Tanggal: 06-09-2026</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="flex justify-center items-center w-[28px] h-[28px] rounded-lg bg-[#FFF4B8] text-[#C67A00]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="flex justify-center items-center w-[28px] h-[28px] rounded-lg bg-[#E2E8F0] text-[#4A5568]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <a href="{{ route('detail_dispen') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-lg border-none cursor-pointer ml-0.5">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><circle cx="10" cy="13" r="2"></circle><path d="M7 18v-1a3 3 0 0 1 6 0v1"></path><line x1="16" y1="13" x2="18" y2="13"></line><line x1="16" y1="17" x2="18" y2="17"></line></svg>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Home Indicator -->
        <div class="flex justify-center w-full pt-3 pb-2 bg-[#F4F6F9] shrink-0">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>

    </div>

</body>
</html>