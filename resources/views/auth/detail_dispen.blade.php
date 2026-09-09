<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dispensasi Waka</title>
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

    <!-- Mobile App Container -->
    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-2xl overflow-hidden">

        <!-- Header -->
        <header class="flex justify-between items-center pt-15 px-6 pb-4 shrink-0">
            <div class="flex flex-col gap-0.5 flex-1">
                <h1 class="font-bold text-xl text-navy-800 leading-6">Detail Dispensasi</h1>
                <p class="font-normal text-xs text-[#4A5568] leading-4">Detail dispensasi siswa.</p>
            </div>
            <a href="{{ route('data_dispen') }}" class="w-[34px] h-[34px] bg-[#E2E8F0] rounded-full flex justify-center items-center shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex flex-col px-6 pb-5 gap-4 flex-1 overflow-y-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
            
            <!-- Student Card -->
            <div class="flex items-center p-4 gap-3 bg-white border border-[#E2E8F0] rounded-xl w-full">
                <div class="flex flex-col gap-1 flex-1">
                    <div class="font-bold text-base text-navy-800 leading-[19px]">Dude Fahrezi</div>
                    <div class="font-normal text-xs text-[#4A5568] leading-[15px]">XI RPL 2</div>
                    <div class="font-semibold text-xs text-navy-800 leading-[15px]">Tanggal: 06-09-2026</div>
                </div>
                <div class="w-[34px] h-[34px] bg-[#E2E8F0] rounded-lg flex justify-center items-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <circle cx="10" cy="13" r="2"></circle>
                        <path d="M7 18v-1a3 3 0 0 1 6 0v1"></path>
                        <line x1="16" y1="13" x2="18" y2="13"></line>
                        <line x1="16" y1="17" x2="18" y2="17"></line>
                    </svg>
                </div>
            </div>

            <!-- Form Groups -->
            <div class="flex flex-col gap-1.5 w-full">
                <span class="font-semibold text-sm text-navy-800 leading-[17px]">Alasan Dispensasi</span>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-lg font-normal text-sm text-navy-800 p-3.5 h-[45px]">Lomba Futsal Tingkat Nasional</div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
                <span class="font-semibold text-sm text-navy-800 leading-[17px]">No. Telepon</span>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] font-normal text-sm text-navy-800 px-4 h-[46px]">085648830046</div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
                <span class="font-semibold text-sm text-navy-800 leading-[17px]">Surat Dispensasi/Izin</span>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-lg font-normal text-sm text-navy-800 p-3.5 gap-[10px] h-[46px]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="flex-1 whitespace-nowrap overflow-hidden text-ellipsis">Surat_Undangan_Lomba_Futsal.pdf</span>
                    <span class="font-bold text-[11px] text-navy-800 cursor-pointer uppercase">LIHAT</span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
                <span class="font-semibold text-sm text-navy-800 leading-[17px]">Status Persetujuan</span>
                <div class="flex flex-col p-3.5 gap-3 bg-white border border-[#E2E8F0] rounded-lg">
                    
                    <!-- Approved Status -->
                    <div class="flex items-center gap-3">
                        <div class="w-[28px] h-[28px] rounded-lg flex justify-center items-center shrink-0 bg-[#D1FFC2] text-[#0A5C36]">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="font-bold text-xs text-navy-800 leading-[16px]">Staff Piket: Disetujui</span>
                            <span class="font-normal text-[11px] text-[#4A5568] leading-[13px]">Oleh: Bpk. Hariyadi | 09:12</span>
                        </div>
                    </div>
                    
                    <div class="w-full border-t border-[#E2E8F0]"></div>
                    
                    <!-- Pending Status -->
                    <div class="flex items-center gap-3">
                        <div class="w-[28px] h-[28px] rounded-lg flex justify-center items-center shrink-0 bg-[#FFF4B8] text-[#C67A00]">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="font-bold text-xs text-navy-800 leading-[16px]">Waka Kesiswaan: Menunggu</span>
                            <span class="font-normal text-[11px] text-[#4A5568] leading-[13px]">Proses peninjauan dokumen</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 w-full mt-auto">
                <button class="flex-1 h-[43px] flex justify-center items-center gap-1.5 rounded-[10px] font-semibold text-base leading-[19px] cursor-pointer bg-[#D1FFC2] border border-[#0A5C36] text-[#0A5C36]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Setujui
                </button>
                <button class="flex-1 h-[43px] flex justify-center items-center gap-1.5 rounded-[10px] font-semibold text-base leading-[19px] cursor-pointer bg-[#FFE4E6] border border-[#B91C1C] text-[#B91C1C]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Tolak
                </button>
            </div>

        </main>
    </div>

</body>
</html>