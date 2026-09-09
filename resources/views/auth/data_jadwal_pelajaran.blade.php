<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Jadwal Pelajaran</title>
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

  <!-- Mobile App Container -->
  <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl shadow-[0_10px_25px_rgba(0,0,0,0.1)] relative flex flex-col overflow-hidden">
        
    <!-- Header -->
    <header class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
      <div class="flex flex-col gap-[2px]">
        <h1 class="font-bold text-[20px] leading-6 text-navy-800">Daftar Jadwal Pelajaran</h1>
        <p class="font-normal text-[13px] leading-4 text-[#4A5568]">Kelola jadwal pelajaran yang tersedia</p>
      </div>
      <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-slate-300 rounded-full border-none cursor-pointer shrink-0 transition-colors">
        <svg class="w-5 h-5 stroke-navy-800 fill-none stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 px-6 flex flex-col gap-4 overflow-y-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
      
      <!-- Search -->
      <div class="flex items-center bg-[#E2E8F0] rounded-lg px-3.5 py-2.5 gap-2 h-[36px]">
        <svg class="w-4 h-4 stroke-[#4A5568] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" class="flex-1 bg-transparent border-none outline-none font-normal text-[13px] text-navy-800 placeholder:text-[#4A5568]" placeholder="Cari mata pelajaran...">
      </div>

      <!-- Filters -->
      <div class="flex gap-2 w-full">
        <select class="flex-1 flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M6%209L12%2015L18%209%22%20stroke%3D%22%234A5568%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[right_8px_center] pr-6">
          <option>Semua Kelas</option>
        </select>
        <select class="flex-[0.7] flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M6%209L12%2015L18%209%22%20stroke%3D%22%234A5568%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[right_8px_center] pr-6">
          <option>Hari</option>
        </select>
        <select class="flex-1 flex items-center justify-between bg-white border border-[#E2E8F0] rounded-lg px-3 py-2 h-[32px] font-bold text-[11px] text-[#4A5568] cursor-pointer appearance-none outline-none bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M6%209L12%2015L18%209%22%20stroke%3D%22%234A5568%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[right_8px_center] pr-6">
          <option>Nama Guru</option>
        </select>
      </div>

      <!-- Add Button -->
      <a href="{{ route('tambah_data_jadwal_pelajaran') }}" class="flex items-center justify-between bg-navy-800 hover:bg-[#2a3f6c] rounded-lg px-3 py-2.5 h-[36px] border-none text-white font-bold text-[11px] cursor-pointer w-full no-underline transition-colors">
        <span>Tambah Jadwal Pelajaran</span>
        <svg class="w-4 h-4 stroke-white fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
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
            <h3 class="font-bold text-[14px] leading-[17px] text-navy-800 mb-0.5">Matematika</h3>
            <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Senin | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
            <p class="font-semibold text-[11px] leading-[13px] text-navy-800 mt-1">Kelas: X RPL 1</p>
          </div>
          <div class="flex gap-1">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#0369A1] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#B91C1C] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="flex justify-between items-start bg-white rounded-xl p-[14px] shadow-[0_2px_8px_rgba(27,42,74,0.04)] min-h-[88px]">
          <div class="flex flex-col gap-1">
            <h3 class="font-bold text-[14px] leading-[17px] text-navy-800 mb-0.5">Matematika</h3>
            <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Selasa | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
            <p class="font-semibold text-[11px] leading-[13px] text-navy-800 mt-1">Kelas: X RPL 1</p>
          </div>
          <div class="flex gap-1">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#0369A1] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#B91C1C] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="flex justify-between items-start bg-white rounded-xl p-[14px] shadow-[0_2px_8px_rgba(27,42,74,0.04)] min-h-[88px]">
          <div class="flex flex-col gap-1">
            <h3 class="font-bold text-[14px] leading-[17px] text-navy-800 mb-0.5">Matematika</h3>
            <p class="font-normal text-[11px] leading-[14px] text-[#4A5568] whitespace-pre-line">Rabu | JP 1 - JP 2 | R58<br>Winartin, S.pd</p>
            <p class="font-semibold text-[11px] leading-[13px] text-navy-800 mt-1">Kelas: X RPL 1</p>
          </div>
          <div class="flex gap-1">
            <button class="flex justify-center items-center w-7 h-7 bg-[#E0F2FE] hover:bg-blue-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#0369A1] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
            <button class="flex justify-center items-center w-7 h-7 bg-[#FFE4E6] hover:bg-red-200 rounded-lg border-none cursor-pointer transition-colors">
              <svg class="w-3.5 h-3.5 stroke-[#B91C1C] fill-none stroke-2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
          </div>
        </div>

      </div>
    </main>

    <!-- Footer Indicator -->
    <div class="flex justify-center items-start pt-2 pb-2 w-full bg-[#F4F6F9] shrink-0">
      <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
    </div>

  </div>

</body>
</html>