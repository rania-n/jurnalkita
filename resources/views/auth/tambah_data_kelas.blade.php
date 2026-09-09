<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kelas - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col justify-between shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-y-auto no-scrollbar">
        
        <!-- Bagian Atas (Header & Form) -->
        <div class="flex flex-col w-full">
            <!-- Header -->
            <div class="flex justify-between items-center pt-[60px] px-6 pb-6">
                <div class="flex flex-col gap-0.5">
                    <h1 class="font-bold text-[20px] text-[#1B2A4A]">Tambah Data Kelas</h1>
                    <p class="font-normal text-[13px] text-[#4A5568]">Kelola data kelas yang tersedia</p>
                </div>
                <!-- Tombol Back -->
                <a href="{{ route('data_kelas') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full text-[#1B2A4A] cursor-pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Form -->
            <form class="flex flex-col px-6 gap-4" action="" method="POST" id="formTambahKelas">
                
                <!-- Dropdown Tingkat -->
                <div class="flex flex-col gap-1.5">
                    <label for="tingkat" class="font-semibold text-[14px] text-[#1B2A4A]">Tingkat</label>
                    <div class="relative flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px]">
                        <select id="tingkat" name="tingkat" class="appearance-none w-full h-full px-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none cursor-pointer invalid:text-[#4A5568]" required>
                            <option value="" disabled selected hidden>Pilih Tingkat (X, XI, XII)</option>
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                        <div class="absolute right-4 pointer-events-none flex">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Jurusan -->
                <div class="flex flex-col gap-1.5">
                    <label for="jurusan" class="font-semibold text-[14px] text-[#1B2A4A]">Jurusan</label>
                    <div class="relative flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px]">
                        <select id="jurusan" name="jurusan" class="appearance-none w-full h-full px-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none cursor-pointer invalid:text-[#4A5568]" required>
                            <option value="" disabled selected hidden>Pilih Jurusan (e.g. Rekayasa Perangkat Lunak)</option>
                            <option value="RPL">Rekayasa Perangkat Lunak</option>
                            <option value="TKJ">Teknik Komputer dan Jaringan</option>
                            <option value="MM">Multimedia</option>
                        </select>
                        <div class="absolute right-4 pointer-events-none flex">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Guru Wali Kelas -->
                <div class="flex flex-col gap-1.5">
                    <label for="wali_kelas" class="font-semibold text-[14px] text-[#1B2A4A]">Guru Wali Kelas</label>
                    <div class="relative flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px]">
                        <select id="wali_kelas" name="wali_kelas" class="appearance-none w-full h-full px-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none cursor-pointer invalid:text-[#4A5568]" required>
                            <option value="" disabled selected hidden>Pilih Guru Wali Kelas</option>
                            <option value="1">Bpk. Budi Santoso</option>
                            <option value="2">Ibu Siti Aminah</option>
                            <option value="3">Bpk. Ahmad Fauzi</option>
                        </select>
                        <div class="absolute right-4 pointer-events-none flex">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bagian Bawah (Tombol & Indikator) -->
        <div class="flex flex-col px-6 pb-4 gap-3 w-full">
            <button type="submit" form="formTambahKelas" class="flex justify-center items-center bg-[#1B2A4A] rounded-xl border-none w-full h-[48px] text-white font-semibold text-[16px] cursor-pointer transition-opacity hover:opacity-90">Tambah Kelas</button>
            
            <div class="flex justify-center items-start pt-3 pb-2 w-full">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>

    </div>

</body> 
</html>