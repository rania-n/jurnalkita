<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jadwal Pelajaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden">
        
        <!-- Header -->
        <header class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-[20px] text-[#1B2A4A] leading-6">Daftar Jadwal Pelajaran</h1>
                <p class="font-normal text-[13px] text-[#4A5568] leading-4">Kelola jadwal pelajaran yang tersedia</p>
            </div>
            <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full border-none cursor-pointer shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-6 flex flex-col gap-4 overflow-y-auto no-scrollbar">
            
            <!-- Search -->
            <div class="flex items-center bg-[#E2E8F0] rounded-[10px] p-[10px_14px] gap-2 h-[36px]">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari mata pelajaran..." class="flex-1 bg-transparent border-none outline-none font-normal text-[13px] text-[#1B2A4A] placeholder:text-[#4A5568]">
            </div>

            <!-- Filters -->
            <div class="flex gap-2 w-full">
                <select class="flex-1 flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg p-[8px_12px] pr-6 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width=%2212%22%20height=%2212%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath%20d=%22M6%209L12%2015L18%209%22%20stroke=%22%234A5568%22%20stroke-width=%222%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22/%3E%3C/svg%3E')] bg-no-repeat bg-[right_8px_center]">
                    <option>Semua Kelas</option>
                </select>
                <select class="flex-[0.7] flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg p-[8px_12px] pr-6 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width=%2212%22%20height=%2212%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath%20d=%22M6%209L12%2015L18%209%22%20stroke=%22%234A5568%22%20stroke-width=%222%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22/%3E%3C/svg%3E')] bg-no-repeat bg-[right_8px_center]">
                    <option>Hari</option>
                </select>
                <select class="flex-1 flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg p-[8px_12px] pr-6 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width=%2212%22%20height=%2212%22%20viewBox=%220%200%2024%2024%22%20fill=%22none%22%20xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath%20d=%22M6%209L12%2015L18%209%22%20stroke=%22%234A5568%22%20stroke-width=%222%22%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22/%3E%3C/svg%3E')] bg-no-repeat bg-[right_8px_center]">
                    <option>Nama Guru</option>
                </select>
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_jadwal_pelajaran') }}" class="flex items-center justify-between bg-[#1B2A4A] rounded-lg p-[10px_12px] h-[36px] border-none text-white font-bold text-[11px] cursor-pointer w-full">
                Tambah Jadwal Pelajaran
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                    <line x1="12" y1="12" x2="12" y2="18"></line>
                </svg>
            </a>

            <!-- Schedule List -->
            <div class="flex flex-col gap-3 pb-6">
                
                <!-- Card 1 -->
                <div class="flex justify-between items-start bg-white rounded-xl p-[14px] shadow-[0_2px_8px_rgba(27,42,74,0.04)] min-h-[88px]">
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-[14px] leading-[17px] text-[#1B2A4A] mb-0.5">Matematika</h3>
                        <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Senin | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="font-semibold text-[11px] leading-[13px] text-[#1B2A4A] mt-1">Kelas: X RPL 1</p>
                    </div>
                    <div class="flex gap-1">
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="flex justify-between items-start bg-white rounded-xl p-[14px] shadow-[0_2px_8px_rgba(27,42,74,0.04)] min-h-[88px]">
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-[14px] leading-[17px] text-[#1B2A4A] mb-0.5">Matematika</h3>
                        <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Selasa | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="font-semibold text-[11px] leading-[13px] text-[#1B2A4A] mt-1">Kelas: X RPL 1</p>
                    </div>
                    <div class="flex gap-1">
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="flex justify-between items-start bg-white rounded-xl p-[14px] shadow-[0_2px_8px_rgba(27,42,74,0.04)] min-h-[88px]">
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-[14px] leading-[17px] text-[#1B2A4A] mb-0.5">Matematika</h3>
                        <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Rabu | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
                        <p class="font-semibold text-[11px] leading-[13px] text-[#1B2A4A] mt-1">Kelas: X RPL 1</p>
                    </div>
                    <div class="flex gap-1">
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#E0F2FE]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="flex justify-center items-center w-[28px] h-[28px] rounded-lg border-none cursor-pointer bg-[#FFE4E6]">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>

            </div>
        </main>

        <!-- Home Indicator -->
        <div class="flex justify-center w-full py-2 bg-[#F4F6F9]">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>

    </div>

</body>
</html>