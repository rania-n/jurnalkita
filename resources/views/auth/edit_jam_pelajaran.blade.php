<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jam Pelajaran</title>
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
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen p-5 font-sans">

    <!-- Mobile Container -->
    <div class="w-[390px] h-[844px] min-h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col justify-between overflow-hidden shadow-2xl">
        
        <!-- Top Section -->
        <div class="flex flex-col items-start w-full">
            <!-- Screen Header -->
            <div class="flex justify-between items-center pt-[60px] px-6 pb-4 w-full shrink-0">
                <div class="flex flex-col gap-0.5 w-[308px]">
                    <h1 class="font-bold text-xl leading-6 text-navy-800">Edit Jam Pelajaran</h1>
                    <p class="font-normal text-xs leading-4 text-[#4A5568]">Konfigurasi rentang waktu jam pelajaran</p>
                </div>
                <a href="{{ route('data_jam_pelajaran') }}" class="w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-[#cbd5e1] rounded-full flex justify-center items-center shrink-0 transition-colors no-underline">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col items-center px-6 gap-4 w-full flex-1 overflow-y-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
            
            <!-- Segmented Control -->
            <div class="flex items-center w-[342px] h-[28px] bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-lg p-0 overflow-hidden shrink-0">
                <button class="flex-1 h-full font-semibold text-[11px] bg-navy-800 text-white rounded-lg border-none cursor-pointer transition-all">Senin-Kamis</button>
                <button class="flex-1 h-full font-semibold text-[11px] bg-transparent text-[#4A5568] rounded-lg border-none cursor-pointer transition-all">Jumat</button>
                <button class="flex-1 h-full font-semibold text-[11px] bg-transparent text-[#4A5568] rounded-lg border-none cursor-pointer transition-all">Kustom</button>
            </div>

            <!-- Time Table Card -->
            <div class="flex flex-col items-center p-2 px-4 pb-4 gap-2 w-[342px] bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-xl shrink-0">
                <div class="flex items-center w-[310px] h-4 text-xs font-semibold text-[#4A5568]">
                    <div class="w-[52px]">JP</div>
                    <div class="w-[115px]">JAM MULAI</div>
                    <div class="flex-1">JAM SELESAI</div>
                </div>
                
                <div class="w-[310px] h-0 border-b border-[#E2E8F0] my-1"></div>
                
                <div class="flex flex-col gap-2 w-[310px]">
                    <!-- JP 1 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 1</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 2 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 2</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 3 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 3</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 4 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 4</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 5 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 5</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- JP 6 -->
                    <div class="flex items-center gap-2 w-[310px] h-7">
                        <div class="w-[44px] font-bold text-xs text-navy-800">JP 6</div>
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <input type="text" class="w-[107px] h-7 bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 font-normal text-[11px] text-[#4A5568] outline-none" value="07:00">
                        <button class="w-7 h-7 bg-[#FFE4E6] hover:bg-[#fecdd3] rounded-lg flex justify-center items-center border-none cursor-pointer transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Add Button -->
                <button class="flex justify-between items-center px-4 py-3 w-[310px] h-[33px] bg-[#E0F2FE] hover:bg-[#bae6fd] border border-[#0369A1] rounded-lg cursor-pointer mt-1 transition-colors">
                    <span class="font-semibold text-sm text-[#0369A1]">Tambah Jam Pelajaran</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0369A1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 1 10-10"></path>
                        <polyline points="12 6 12 12 16 14"></polyline>
                        <line x1="19" y1="16" x2="19" y2="22"></line>
                        <line x1="16" y1="19" x2="22" y2="19"></line>
                    </svg>
                </button>
            </div>

            <!-- Save Button -->
            <button class="flex justify-center items-center py-3 w-[342px] h-[48px] bg-navy-800 hover:bg-[#2a3f6c] rounded-xl border-none font-semibold text-base text-white cursor-pointer transition-colors shrink-0">
                Simpan Perubahan
            </button>
            
        </div>

        <!-- Home Indicator -->
        <div class="flex justify-center items-start pb-2 w-full h-[13px] shrink-0">
            <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
        </div>
        
    </div>

</body>
</html>