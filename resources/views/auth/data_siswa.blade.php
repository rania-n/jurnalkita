<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Jurnalkita</title>
    <!-- Google Font & Tailwind CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- App Container -->
    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-2xl overflow-hidden">
        
        <!-- Header -->
        <div class="flex justify-between items-center pt-15 px-6 pb-4 bg-[#F4F6F9] z-10">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-xl text-navy-800">Data Siswa</h1>
                <p class="font-normal text-xs text-[#4A5568]">Kelola Data Siswa</p>
            </div>
            <a href="#" class="w-[34px] h-[34px] bg-[#E2E8F0] rounded-full flex justify-center items-center text-navy-800">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Sticky Controls -->
        <div class="flex flex-col gap-3 px-6 pb-4 bg-[#F4F6F9] z-10">
            <!-- Search -->
            <div class="flex items-center bg-[#E2E8F0] rounded-lg h-9 px-3.5 gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari nama atau NIS siswa..." class="bg-transparent border-none outline-none w-full text-xs text-navy-800 placeholder-[#4A5568]">
            </div>

            <!-- Filters -->
            <div class="flex gap-2">
                <div class="flex-1 flex justify-between items-center h-8 bg-white border border-[#E2E8F0] rounded-lg px-3 text-[11px] font-bold text-[#4A5568] cursor-pointer">
                    <span>Semua Kelas</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="flex-1 flex justify-between items-center h-8 bg-white border border-[#E2E8F0] rounded-lg px-3 text-[11px] font-bold text-[#4A5568] cursor-pointer">
                    <span>Jenis Kelamin</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Add Button -->
            <a href="{{ route('tambah_data_siswa') }}" class="flex justify-between items-center h-8 bg-navy-800 rounded-lg px-3 no-underline">
                <span class="font-bold text-[11px] text-white">Tambah Data Siswa</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </a>
        </div>

        <!-- Scrollable Student List -->
        <div class="flex-1 overflow-y-auto px-6 pb-6 flex flex-col gap-3 [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
            
            <!-- Card 1 (Male) -->
            <div class="flex items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl p-3.5 gap-3">
                <div class="w-10 h-10 rounded-full flex justify-center items-center shrink-0 bg-[#E0F2FE] text-[#0369A1]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 9h8"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="flex flex-col gap-0.5 flex-grow">
                    <h3 class="font-bold text-sm text-navy-800">Ahmad Fauzi</h3>
                    <p class="font-normal text-[11px] text-[#4A5568]">NIS: 12345</p>
                    <p class="font-semibold text-[11px] text-navy-800">Kelas: X RPL 1</p>
                </div>
                <div class="flex gap-1">
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#E0F2FE]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#FFE4E6]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Card 2 (Female) -->
            <div class="flex items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl p-3.5 gap-3">
                <div class="w-10 h-10 rounded-full flex justify-center items-center shrink-0 bg-[#FCE7F3] text-[#BE185D]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z"></path>
                        <path d="M6 10c0-4 2-7 6-7s6 3 6 7v4s-2 2-6 2-6-2-6-2v-4z"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="flex flex-col gap-0.5 flex-grow">
                    <h3 class="font-bold text-sm text-navy-800">Dewi Lestari</h3>
                    <p class="font-normal text-[11px] text-[#4A5568]">NIS: 12346</p>
                    <p class="font-semibold text-[11px] text-navy-800">Kelas: X RPL 1</p>
                </div>
                <div class="flex gap-1">
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#E0F2FE]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#FFE4E6]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Card 3 (Male) -->
            <div class="flex items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-xl p-3.5 gap-3">
                <div class="w-10 h-10 rounded-full flex justify-center items-center shrink-0 bg-[#E0F2FE] text-[#0369A1]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 9h8"></path>
                        <circle cx="9" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="13" r="1" fill="currentColor" stroke="none"></circle>
                        <path d="M10 17c1.1.5 2.9.5 4 0"></path>
                    </svg>
                </div>
                <div class="flex flex-col gap-0.5 flex-grow">
                    <h3 class="font-bold text-sm text-navy-800">Fajar Nugraha</h3>
                    <p class="font-normal text-[11px] text-[#4A5568]">NIS: 12347</p>
                    <p class="font-semibold text-[11px] text-navy-800">Kelas: X RPL 1</p>
                </div>
                <div class="flex gap-1">
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#E0F2FE]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="w-7 h-7 rounded-lg flex justify-center items-center border-none cursor-pointer bg-[#FFE4E6]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer / Home Indicator -->
        <div class="flex justify-center pt-3 pb-2 bg-[#F4F6F9]">
            <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
        </div>

    </div>

</body>
</html>