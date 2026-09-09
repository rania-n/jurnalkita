<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa - Jurnalkita</title>
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
        <div class="flex justify-between items-center pt-[60px] px-6 pb-4 bg-[#F4F6F9] shrink-0">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-[20px] text-[#1B2A4A]">Tambah Data Siswa</h1>
                <p class="font-normal text-[13px] text-[#4A5568]">Kelola data siswa yang tersedia</p>
            </div>
            <a href="{{ route('data_siswa') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full text-[#1B2A4A] cursor-pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Form Content -->
        <form class="flex-1 px-6 pb-6 flex flex-col gap-4 overflow-y-auto no-scrollbar" action="#" method="POST" id="formTambahSiswa">
            
            <!-- Input Kelas -->
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Kelas</label>
                <div class="relative flex items-center w-full">
                    <select class="w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] px-4 font-normal text-[14px] text-[#4A5568] outline-none appearance-none focus:border-[#1B2A4A] cursor-pointer" required>
                        <option value="" disabled selected>Pilih Kelas</option>
                        <option value="X RPL 1">X RPL 1</option>
                        <option value="X RPL 2">X RPL 2</option>
                        <option value="XI TKJ 1">XI TKJ 1</option>
                    </select>
                    <svg class="absolute right-4 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Input NIS -->
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">NIS</label>
                <div class="relative flex items-center w-full">
                    <input type="number" class="w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] px-4 font-normal text-[14px] text-[#4A5568] outline-none focus:border-[#1B2A4A] placeholder:text-[#A0AEC0]" placeholder="12345678909" required>
                </div>
            </div>

            <!-- Input Nama Lengkap -->
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Nama Lengkap</label>
                <div class="relative flex items-center w-full">
                    <input type="text" class="w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] px-4 font-normal text-[14px] text-[#4A5568] outline-none focus:border-[#1B2A4A] placeholder:text-[#A0AEC0]" placeholder="Rania Nurillah" required>
                </div>
            </div>

            <!-- Input Jenis Kelamin -->
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Jenis Kelamin</label>
                <div class="relative flex items-center w-full">
                    <select class="w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] px-4 font-normal text-[14px] text-[#4A5568] outline-none appearance-none focus:border-[#1B2A4A] cursor-pointer" required>
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan" selected>Perempuan</option>
                    </select>
                    <svg class="absolute right-4 pointer-events-none" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>

        </form>

        <!-- Footer Area -->
        <div class="px-6 pb-4 bg-[#F4F6F9] flex flex-col gap-3 shrink-0">
            <button type="submit" form="formTambahSiswa" class="flex justify-center items-center w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none font-semibold text-[16px] text-white cursor-pointer transition-opacity hover:opacity-90">Tambah Siswa</button>
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>

    </div>

</body>
</html>