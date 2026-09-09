<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Data Guru</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
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

  <!-- App Main Frame -->
  <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl shadow-[0_10px_25px_rgba(0,0,0,0.1)] relative flex flex-col justify-between items-start overflow-hidden">
    
    <div class="w-full flex flex-col items-start">
      <!-- Screen Header -->
      <div class="flex justify-between items-center pt-[60px] px-6 pb-4 w-full shrink-0">
        <div class="flex flex-col gap-0.5 w-[308px]">
          <h1 class="font-bold text-[20px] leading-6 text-navy-800">Daftar Data Guru</h1>
          <p class="font-normal text-[13px] leading-4 text-[#4A5568]">Kelola data guru staff piket</p>
        </div>
        <button type="button" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] hover:bg-[#CBD5E1] rounded-full border-none cursor-pointer text-navy-800 text-[18px] transition-colors" aria-label="Kembali">
          <i class="ph ph-arrow-left"></i>
        </button>
      </div>

      <!-- Main Content Area -->
      <div class="w-full flex flex-col items-start px-6 pb-5 gap-4">
        
        <!-- Search Box -->
        <div class="flex items-center px-3.5 py-2.5 gap-2 w-full h-[36px] bg-[#E2E8F0] rounded-lg">
          <i class="ph ph-magnifying-glass text-[16px] text-[#4A5568]"></i>
          <input type="text" class="w-full bg-transparent border-none outline-none font-normal text-[13px] leading-4 text-navy-800 placeholder:text-[#4A5568]" placeholder="Cari nama staff...">
        </div>

        <!-- Filter Row -->
        <div class="flex items-start gap-2 w-full h-[29px]">
          <div class="relative flex items-center justify-between px-3 py-[8px] w-[167px] h-[29px] bg-white border border-[#E2E8F0] rounded-lg">
            <select class="w-full h-full border-none outline-none bg-transparent font-bold text-[11px] leading-[13px] text-[#4A5568] appearance-none cursor-pointer">
              <option value="">Semua Kelas</option>
              <option value="X">Kelas X</option>
              <option value="XI">Kelas XI</option>
              <option value="XII">Kelas XII</option>
            </select>
            <i class="ph ph-caret-down absolute right-3 text-[14px] text-[#4A5568] pointer-events-none"></i>
          </div>

          <div class="relative flex items-center justify-between px-3 py-[8px] w-[167px] h-[29px] bg-white border border-[#E2E8F0] rounded-lg">
            <select class="w-full h-full border-none outline-none bg-transparent font-bold text-[11px] leading-[13px] text-[#4A5568] appearance-none cursor-pointer">
              <option value="">Semua Mapel</option>
              <option value="matematika">Matematika</option>
              <option value="inggris">Bahasa Inggris</option>
              <option value="fisika">Fisika</option>
              <option value="biologi">Biologi</option>
            </select>
            <i class="ph ph-caret-down absolute right-3 text-[14px] text-[#4A5568] pointer-events-none"></i>
          </div>
        </div>

        <!-- Add Button -->
        <a href="{{ route('tambah_guru') }}" class="flex items-center justify-between px-3 py-[8px] w-full h-[29px] bg-navy-800 hover:opacity-90 rounded-lg border-none cursor-pointer no-underline transition-opacity">
          <span class="font-bold text-[11px] leading-[13px] text-white">Tambah Data Guru</span>
          <i class="ph ph-user-plus text-[16px] text-white"></i>
        </a>

        <!-- Teacher List Container -->
        <div class="flex flex-col items-start gap-3 w-full max-h-[440px] overflow-y-auto [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
          
          <!-- Card 1 -->
          <div class="flex items-center justify-between p-[14px] gap-3 w-full h-[85px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
            <div class="flex flex-col items-start gap-1 w-[246px]">
              <span class="font-bold text-[14px] leading-[17px] text-navy-800 truncate w-full">Budi Santoso, S.Pd</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">NIK. 198501012010011001</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Mapel: Matematika</span>
            </div>
            <div class="flex flex-col items-start gap-[3px] w-[56px]">
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E2E8F0] text-navy-800 rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer pt-[7px] pb-[7px] pr-[7px] pl-[4px]">
                <span>Detail</span>
                <i class="ph ph-article text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E0F2FE] text-[#0369A1] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Edit</span>
                <i class="ph ph-pencil-simple text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#FFE4E6] text-[#B91C1C] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Hapus</span>
                <i class="ph ph-trash text-[11px]"></i>
              </a>
            </div>
          </div>

          <!-- Card 2 -->
          <div class="flex items-center justify-between p-[14px] gap-3 w-full h-[85px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
            <div class="flex flex-col items-start gap-1 w-[246px]">
              <span class="font-bold text-[14px] leading-[17px] text-navy-800 truncate w-full">Winartin, S.Pd</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">NIK. 198802242013022002</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Mapel: Bahasa Inggris</span>
            </div>
            <div class="flex flex-col items-start gap-[3px] w-[56px]">
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E2E8F0] text-navy-800 rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer pt-[7px] pb-[7px] pr-[7px] pl-[4px]">
                <span>Detail</span>
                <i class="ph ph-article text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E0F2FE] text-[#0369A1] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Edit</span>
                <i class="ph ph-pencil-simple text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#FFE4E6] text-[#B91C1C] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Hapus</span>
                <i class="ph ph-trash text-[11px]"></i>
              </a>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="flex items-center justify-between p-[14px] gap-3 w-full h-[85px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
            <div class="flex flex-col items-start gap-1 w-[246px]">
              <span class="font-bold text-[14px] leading-[17px] text-navy-800 truncate w-full">Drs. M. Yusuf</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">NIK. 197210151998031003</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Mapel: Fisika</span>
            </div>
            <div class="flex flex-col items-start gap-[3px] w-[56px]">
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E2E8F0] text-navy-800 rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer pt-[7px] pb-[7px] pr-[7px] pl-[4px]">
                <span>Detail</span>
                <i class="ph ph-article text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E0F2FE] text-[#0369A1] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Edit</span>
                <i class="ph ph-pencil-simple text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#FFE4E6] text-[#B91C1C] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Hapus</span>
                <i class="ph ph-trash text-[11px]"></i>
              </a>
            </div>
          </div>

          <!-- Card 4 -->
          <div class="flex items-center justify-between p-[14px] gap-3 w-full h-[85px] bg-white shadow-[0_2px_8px_rgba(27,42,74,0.039)] rounded-xl shrink-0">
            <div class="flex flex-col items-start gap-1 w-[246px]">
              <span class="font-bold text-[14px] leading-[17px] text-navy-800 truncate w-full">Sarah Amelia, M.Pd</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">NIK. 199208082018012004</span>
              <span class="font-normal text-[11px] leading-[13px] text-[#4A5568]">Mapel: Biologi</span>
            </div>
            <div class="flex flex-col items-start gap-[3px] w-[56px]">
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E2E8F0] text-navy-800 rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer pt-[7px] pb-[7px] pr-[7px] pl-[4px]">
                <span>Detail</span>
                <i class="ph ph-article text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#E0F2FE] text-[#0369A1] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Edit</span>
                <i class="ph ph-pencil-simple text-[11px]"></i>
              </a>
              <a href="#" class="flex items-center justify-between w-[56px] h-[17px] bg-[#FFE4E6] text-[#B91C1C] rounded font-semibold text-[10px] leading-[12px] no-underline cursor-pointer p-[6px] pl-[4px]">
                <span>Hapus</span>
                <i class="ph ph-trash text-[11px]"></i>
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Home Indicator -->
    <div class="flex justify-center items-start pt-[21px] pb-[8px] w-full h-[34px] shrink-0">
      <div class="w-[139px] h-[5px] bg-navy-800 rounded-full"></div>
    </div>
  </div>

</body>
</html>