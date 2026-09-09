<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Pelajaran - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden">
        
        <!-- Header -->
        <div class="flex justify-between items-center pt-[60px] px-6 pb-4 bg-[#F4F6F9]">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-[20px] text-[#1B2A4A]">Tambah Mata Pelajaran</h1>
                <p class="font-normal text-[13px] text-[#4A5568]">Kelola mata pelajaran yang tersedia</p>
            </div>
            <a href="{{ route('data_mata_pelajaran') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full text-[#1B2A4A] transition-colors active:bg-[#cbd5e1] cursor-pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Form Content -->
        <form id="formTambahMapel" class="flex-1 pt-2 px-6 pb-6 flex flex-col gap-4" action="#" method="POST">
            <!-- Input Mata Pelajaran -->
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Mata Pelajaran</label>
                <input type="text" class="w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] px-4 font-normal text-[14px] text-[#1B2A4A] outline-none transition-colors focus:border-[#1B2A4A] placeholder:text-[#718096]" placeholder="Bahasa Indonesia" required>
            </div>
        </form>

        <!-- Footer Area -->
        <div class="px-6 pb-4 bg-[#F4F6F9] flex flex-col gap-3">
            <button type="submit" form="formTambahMapel" class="flex justify-center items-center w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none font-semibold text-[16px] text-white cursor-pointer transition-opacity active:opacity-80">Tambah Mata Pelajaran</button>
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>

    </div>

</body>
</html>