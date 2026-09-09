<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Dispensasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-[#F4F6F9] flex justify-center">

  <!-- Mobile Container -->
  <div class="w-full max-w-[390px] min-h-screen bg-[#F4F6F9] flex flex-col relative pb-8 shadow-sm">
    
    <!-- Top Header & Back Button -->
    <div class="flex justify-between items-center px-[24px] pt-[40px] pb-[16px]">
      <div class="flex flex-col gap-[2px]">
        <h1 class="text-[20px] font-bold text-[#1B2A4A] leading-[24px]">Daftar Dispensasi</h1>
        <p class="text-[13px] text-[#4A5568] leading-[16px] max-w-[270px]">
          Riwayat dan status persetujuan dispensasi oleh Staff Piket dan Waka Kesiswaan.
        </p>
      </div>
      <a href="#" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-slate-300 rounded-full shrink-0 transition-colors">
        <svg class="w-[18px] h-[18px] text-[#1B2A4A]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
      </a>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col px-[24px] gap-[16px]">
      
      <!-- Search Bar -->
      <div class="flex items-center bg-[#E2E8F0] rounded-[10px] px-[14px] py-[10px] gap-[8px]">
        <svg class="w-[16px] h-[16px] text-[#4A5568]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" placeholder="Cari nama atau alasan dispensasi..." class="bg-transparent border-none outline-none text-[13px] w-full text-[#4A5568] placeholder-[#4A5568]">
      </div>

      <!-- Filter Row -->
      <div class="flex gap-[8px] w-full">
        <button class="flex-1 flex justify-between items-center bg-white border border-[#E2E8F0] rounded-[8px] px-[12px] py-[8px]">
          <span class="text-[11px] font-bold text-[#4A5568]">Semua Kelas</span>
          <svg class="w-[16px] h-[16px] text-[#4A5568]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        <button class="flex-1 flex justify-between items-center bg-white border border-[#E2E8F0] rounded-[8px] px-[12px] py-[8px]">
          <span class="text-[11px] font-bold text-[#4A5568]">Tanggal</span>
          <svg class="w-[16px] h-[16px] text-[#4A5568]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </button>
      </div>

      <!-- Tabs Navigation -->
      <div class="flex w-full bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-[8px] p-[2px]">
        <button class="flex-1 flex justify-center items-center h-[28px] bg-[#1B2A4A] rounded-[6px]">
          <span class="text-[11px] font-semibold text-white">Semua</span>
        </button>
        <button class="flex-1 flex justify-center items-center h-[28px] bg-transparent rounded-[6px]">
          <span class="text-[11px] font-semibold text-[#4A5568]">Menunggu</span>
        </button>
        <button class="flex-1 flex justify-center items-center h-[28px] bg-transparent rounded-[6px]">
          <span class="text-[11px] font-semibold text-[#4A5568]">Disetujui</span>
        </button>
        <button class="flex-1 flex justify-center items-center h-[28px] bg-transparent rounded-[6px]">
          <span class="text-[11px] font-semibold text-[#4A5568]">Ditolak</span>
        </button>
      </div>

      <!-- Action Button -->
      <a href="#" class="flex justify-between items-center bg-[#1B2A4A] hover:bg-slate-800 rounded-[8px] px-[12px] py-[8px] transition-colors">
        <span class="text-[11px] font-bold text-white">Ajukan Dispensasi</span>
        <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
          <line x1="12" y1="14" x2="12" y2="18"></line>
          <line x1="10" y1="16" x2="14" y2="16"></line>
        </svg>
      </a>

      <!-- Card List Container -->
      <div class="flex flex-col gap-[16px] pb-4">
        
        <!-- Card 1 (Menunggu) -->
        <div class="flex justify-between items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-[12px] p-[14px]">
          <div class="flex flex-col gap-[2px]">
            <div class="flex items-center bg-[#FEF3C7] shadow-[0_4px_12px_rgba(0,0,0,0.06)] rounded-[4px] px-[8px] py-[2px] w-fit mb-1">
              <span class="text-[11px] font-semibold text-[#F59E0B]">Menunggu</span>
            </div>
            <h3 class="text-[14px] font-bold text-[#1B2A4A] leading-[17px]">Dude Fahrezi</h3>
            <p class="text-[11px] text-[#4A5568] leading-[13px]">XI RPL 2</p>
            <p class="text-[11px] font-semibold text-[#1B2A4A] leading-[13px] mt-1">Tanggal: 06-09-2026</p>
          </div>
          <a href="#" class="flex items-center gap-[4px] h-[30px] bg-[#E2E8F0] hover:bg-slate-300 rounded-[4px] pl-[6px] pr-[8px] transition-colors">
            <span class="text-[12px] font-semibold text-[#1B2A4A]">Detail</span>
            <svg class="w-[16px] h-[16px] text-[#1B2A4A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </a>
        </div>

        <!-- Card 2 (Disetujui) -->
        <div class="flex justify-between items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-[12px] p-[14px]">
          <div class="flex flex-col gap-[2px]">
            <div class="flex items-center bg-[#D1FFC2] shadow-[0_4px_12px_rgba(0,0,0,0.06)] rounded-[4px] px-[8px] py-[2px] w-fit mb-1">
              <span class="text-[11px] font-semibold text-[#0A5C36]">Disetujui</span>
            </div>
            <h3 class="text-[14px] font-bold text-[#1B2A4A] leading-[17px]">Putri Zahwa</h3>
            <p class="text-[11px] text-[#4A5568] leading-[13px]">XI RPL 2</p>
            <p class="text-[11px] font-semibold text-[#1B2A4A] leading-[13px] mt-1">Tanggal: 06-09-2026</p>
          </div>
          <a href="#" class="flex items-center gap-[4px] h-[30px] bg-[#E2E8F0] hover:bg-slate-300 rounded-[4px] pl-[6px] pr-[8px] transition-colors">
            <span class="text-[12px] font-semibold text-[#1B2A4A]">Detail</span>
            <svg class="w-[16px] h-[16px] text-[#1B2A4A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </a>
        </div>

        <!-- Card 3 (Ditolak) -->
        <div class="flex justify-between items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-[12px] p-[14px]">
          <div class="flex flex-col gap-[2px]">
            <div class="flex items-center bg-[#FFE4E6] shadow-[0_4px_12px_rgba(0,0,0,0.06)] rounded-[4px] px-[8px] py-[2px] w-fit mb-1">
              <span class="text-[11px] font-semibold text-[#B91C1C]">Ditolak</span>
            </div>
            <h3 class="text-[14px] font-bold text-[#1B2A4A] leading-[17px]">Varadita April</h3>
            <p class="text-[11px] text-[#4A5568] leading-[13px]">XI RPL 2</p>
            <p class="text-[11px] font-semibold text-[#1B2A4A] leading-[13px] mt-1">Tanggal: 06-09-2026</p>
          </div>
          <a href="#" class="flex items-center gap-[4px] h-[30px] bg-[#E2E8F0] hover:bg-slate-300 rounded-[4px] pl-[6px] pr-[8px] transition-colors">
            <span class="text-[12px] font-semibold text-[#1B2A4A]">Detail</span>
            <svg class="w-[16px] h-[16px] text-[#1B2A4A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </a>
        </div>

        <!-- Card 4 (Ditolak) -->
        <div class="flex justify-between items-center bg-white shadow-[0_2px_8px_rgba(27,42,74,0.04)] rounded-[12px] p-[14px]">
          <div class="flex flex-col gap-[2px]">
            <div class="flex items-center bg-[#FFE4E6] shadow-[0_4px_12px_rgba(0,0,0,0.06)] rounded-[4px] px-[8px] py-[2px] w-fit mb-1">
              <span class="text-[11px] font-semibold text-[#B91C1C]">Ditolak</span>
            </div>
            <h3 class="text-[14px] font-bold text-[#1B2A4A] leading-[17px]">Fitra Fahrezi</h3>
            <p class="text-[11px] text-[#4A5568] leading-[13px]">XI RPL 2</p>
            <p class="text-[11px] font-semibold text-[#1B2A4A] leading-[13px] mt-1">Tanggal: 06-09-2026</p>
          </div>
          <a href="{{ route('detail_dispen') }}" class="flex items-center gap-[4px] h-[30px] bg-[#E2E8F0] hover:bg-slate-300 rounded-[4px] pl-[6px] pr-[8px] transition-colors">
            <span class="text-[12px] font-semibold text-[#1B2A4A]">Detail</span>
            <svg class="w-[16px] h-[16px] text-[#1B2A4A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </a>
        </div>

      </div>
    </div>
  </div>
</body>
</html>