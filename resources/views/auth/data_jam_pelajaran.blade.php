<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan Aplikasi - Jam Pelajaran</title>
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

  <!-- Mobile Container -->
  <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl shadow-[0_10px_25px_rgba(0,0,0,0.1)] relative flex flex-col justify-between overflow-hidden">
        
    <div class="flex flex-col items-start w-full">
      <!-- Screen Header -->
      <div class="flex justify-between items-center pt-[60px] px-6 pb-4 w-full shrink-0">
        <div class="flex flex-col gap-0.5 w-[308px]">
          <h1 class="font-bold text-[20px] leading-6 text-navy-800 m-0">Jam Pelajaran</h1>
          <p class="font-normal text-[13px] leading-4 text-[#4A5568] m-0">Konfigurasi rentang waktu jam pelajaran</p>
        </div>
        <a href="{{ route('login') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-slate-300 rounded-full border-none cursor-pointer no-underline shrink-0 transition-colors" aria-label="Kembali">
          <svg class="w-5 h-5 stroke-navy-800 fill-none stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col items-center px-6 pb-5 gap-4 w-full flex-1">
        
      <!-- Segmented Control -->
      <div class="flex flex-row items-center w-full h-[28px] bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-lg overflow-hidden shrink-0">
        <button class="flex-1 flex justify-center items-center h-full bg-navy-800 font-semibold text-[11px] text-white border-none cursor-pointer transition-all">Senin-Kamis</button>
        <button class="flex-1 flex justify-center items-center h-full bg-transparent font-semibold text-[11px] text-[#4A5568] border-none cursor-pointer transition-all">Jumat</button>
        <button class="flex-1 flex justify-center items-center h-full bg-transparent font-semibold text-[11px] text-[#4A5568] border-none cursor-pointer transition-all">Kustom</button>
      </div>

      <!-- Time Table Card -->
      <div class="flex flex-col items-start p-4 pt-2 gap-2 w-full bg-white shadow-[0_4px_12px_rgba(27,42,74,0.06)] rounded-xl shrink-0">
        
        <div class="flex items-center w-[310px] h-4">
          <div class="w-[60px] font-semibold text-[13px] text-[#4A5568]">JP</div>
          <div class="w-[125px] font-semibold text-[13px] text-[#4A5568]">JAM MULAI</div>
          <div class="flex-1 font-semibold text-[13px] text-[#4A5568]">JAM SELESAI</div>
        </div>
        
        <div class="w-[310px] h-0 border-b border-[#E2E8F0] my-1"></div>
        
        <div class="flex flex-col gap-2 w-[310px]">
          <!-- JP 1 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 1</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
          <!-- JP 2 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 2</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
          <!-- JP 3 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 3</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
          <!-- JP 4 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 4</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
          <!-- JP 5 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 5</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
          <!-- JP 6 -->
          <div class="flex items-center gap-2 w-full h-7">
            <div class="w-[44px] font-bold text-[13px] text-navy-800">JP 6</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
            <div class="flex items-center px-3 w-[125px] h-7 bg-[#F4F6F9] rounded-lg font-normal text-[11px] text-[#4A5568]">07:00</div>
          </div>
        </div>
      </div>

      <!-- Edit Button -->
      <a href="{{ route('edit_jam_pelajaran') }}" class="flex justify-center items-center w-full h-[48px] bg-navy-800 hover:bg-[#2a3f6c] rounded-xl border-none font-semibold text-[16px] text-white cursor-pointer no-underline transition-colors mt-1 shrink-0">
        Edit Jam Pelajaran
      </a>
        
    </div>

    <!-- Home Indicator -->
    <div class="flex justify-center items-start pt-[21px] pb-2 w-full h-[34px] shrink-0">
      <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
    </div>
        
  </div>

</body>
</html>