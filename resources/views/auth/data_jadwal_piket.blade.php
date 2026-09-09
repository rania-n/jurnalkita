<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Jadwal Piket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: { navy: { 800: '#1B2A4A' } }
        }
      }
    }
  </script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-sans">

  <!-- Main Container -->
  <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl shadow-[0_10px_25px_rgba(0,0,0,0.1)] relative flex flex-col overflow-hidden">
    
    <!-- Screen Header -->
    <div class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0 w-full">
      <div class="flex flex-col gap-0.5 w-[308px]">
        <h1 class="font-bold text-[20px] leading-6 text-navy-800 m-0">Daftar Jadwal Piket</h1>
        <p class="font-normal text-[13px] leading-4 text-[#4A5568] m-0">Kelola jadwal piket yang tersedia</p>
      </div>
      <a href="{{ route('login') }}" class="flex justify-center items-center p-2 w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-slate-300 rounded-full border-none cursor-pointer transition-colors" aria-label="Kembali">
        <svg class="w-[18px] h-[18px] fill-navy-800" viewBox="0 0 24 24">
          <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
        </svg>
      </a>
    </div>

    <!-- Content Frame -->
    <div class="flex flex-col px-6 pb-5 gap-4 w-full h-[690px] overflow-y-auto flex-1 [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
      
      <!-- Search Frame -->
      <div class="flex items-center px-3.5 py-2.5 gap-2 w-full h-[36px] bg-[#E2E8F0] rounded-lg shrink-0">
        <div class="flex justify-center items-center">
          <svg class="w-4 h-4 stroke-[#4A5568] fill-none stroke-2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <input type="text" class="w-full bg-transparent border-none outline-none font-normal text-[13px] text-[#4A5568] placeholder:text-[#4A5568]" placeholder="Cari nama staff...">
      </div>

      <!-- Day Filter Tabs -->
      <div class="flex items-start gap-2 w-full h-[28px] bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-lg shrink-0">
        <button class="flex-1 flex justify-center items-center h-full bg-white rounded-lg border-none font-semibold text-[11px] text-[#4A5568] cursor-pointer">Senin</button>
        <button class="flex-1 flex justify-center items-center h-full bg-white rounded-lg border-none font-semibold text-[11px] text-[#4A5568] cursor-pointer">Selasa</button>
        <button class="flex-1 flex justify-center items-center h-full bg-navy-800 rounded-lg border-none font-semibold text-[11px] text-white cursor-pointer">Rabu</button>
        <button class="flex-1 flex justify-center items-center h-full bg-white rounded-lg border-none font-semibold text-[11px] text-[#4A5568] cursor-pointer">Kamis</button>
        <button class="flex-1 flex justify-center items-center h-full bg-white rounded-lg border-none font-semibold text-[11px] text-[#4A5568] cursor-pointer">Jumat</button>
      </div>

      <!-- Tambah Jadwal Piket Button -->
      <a href="{{ route('tambah_jadwal_piket')}}" class="flex items-center justify-between px-3 py-2 gap-1.5 w-full h-[29px] bg-navy-800 hover:bg-[#2a3f6c] rounded-lg border-none cursor-pointer shrink-0 no-underline transition-colors">
        <span class="font-bold text-[11px] text-white">Tambah Jadwal Piket</span>
        <div class="flex justify-center items-center">
          <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
        </div>
      </a>

      <!-- Cards List -->
      <div class="flex flex-col items-start gap-3 w-full shrink-0">
        
        <!-- Card 1 -->
        <div class="flex items-center p-[14px] gap-3 w-full h-[62px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
          <div class="flex flex-col gap-1 flex-1 h-[34px]">
            <div class="font-bold text-[14px] leading-[17px] text-navy-800">Budi Santoso, S.Pd</div>
            <div class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Rabu | 07:00 - 12:00</div>
          </div>
          <div class="flex items-start gap-1 h-[28px] shrink-0">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E2E8F0] hover:bg-slate-300 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-[14px] h-[14px] fill-navy-800" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#0369A1] fill-none stroke-2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#B91C1C] fill-none stroke-2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="flex items-center p-[14px] gap-3 w-full h-[62px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
          <div class="flex flex-col gap-1 flex-1 h-[34px]">
            <div class="font-bold text-[14px] leading-[17px] text-navy-800">Winartin, S.Pd</div>
            <div class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Rabu | 07:00 - 12:00</div>
          </div>
          <div class="flex items-start gap-1 h-[28px] shrink-0">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E2E8F0] hover:bg-slate-300 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-[14px] h-[14px] fill-navy-800" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#0369A1] fill-none stroke-2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#B91C1C] fill-none stroke-2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="flex items-center p-[14px] gap-3 w-full h-[62px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
          <div class="flex flex-col gap-1 flex-1 h-[34px]">
            <div class="font-bold text-[14px] leading-[17px] text-navy-800">Drs. M. Yusuf</div>
            <div class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Rabu | 12:00 - 16:00</div>
          </div>
          <div class="flex items-start gap-1 h-[28px] shrink-0">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E2E8F0] hover:bg-slate-300 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-[14px] h-[14px] fill-navy-800" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#0369A1] fill-none stroke-2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#B91C1C] fill-none stroke-2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="flex items-center p-[14px] gap-3 w-full h-[62px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
          <div class="flex flex-col gap-1 flex-1 h-[34px]">
            <div class="font-bold text-[14px] leading-[17px] text-navy-800">Sarah Amelia, M.Pd</div>
            <div class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Rabu | 07:00 - 12:00</div>
          </div>
          <div class="flex items-start gap-1 h-[28px] shrink-0">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E2E8F0] hover:bg-slate-300 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-[14px] h-[14px] fill-navy-800" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#0369A1] fill-none stroke-2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-4 h-4 stroke-[#B91C1C] fill-none stroke-2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Home Indicator -->
    <div class="flex justify-center items-start pt-[21px] pb-2 w-full h-[34px] shrink-0">
      <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
    </div>

  </div>

</body>
</html>