<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Memanggil Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter',sans-serif] m-0 p-0 box-border">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col justify-between overflow-hidden shadow-[0_10px_25px_rgba(0,0,0,0.1)]">

        <div class="flex justify-between items-center pt-[60px] px-6 pb-4">
            <div class="flex flex-col">
                <h1 class="font-bold text-[20px] text-[#1B2A4A] leading-6">Pilih Peran Daftar</h1>
                <p class="font-normal text-[12px] text-[#5A6E7F] leading-[15px] mt-0.5">Silakan pilih jenis keanggotaan Anda</p>
            </div>
            <!-- Menggunakan fungsi route() dari Laravel untuk kembali ke login -->
            <a href="{{ route('login') }}" class="w-[34px] h-[34px] bg-[#EBEFF4] rounded-full flex justify-center items-center cursor-pointer border-none no-underline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
            </a>
        </div>

        <div class="flex flex-col px-6 gap-5 flex-grow mt-2">
            <a href="{{ route('register_guru') }}" class="bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-2xl p-6 flex flex-col gap-4 cursor-pointer transition-all duration-200 ease-in-out no-underline hover:border-[#1B2A4A] hover:-translate-y-0.5">
                <div class="flex justify-between items-center">
                    <div class="w-12 h-12 bg-[#EBEFF4] rounded-xl flex justify-center items-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
                <div class="flex flex-col">
                    <h2 class="font-bold text-[18px] text-[#1B2A4A] leading-[22px] mb-1">Guru Mata Pelajaran</h2>
                    <p class="font-normal text-[13px] text-[#5A6E7F] leading-[140%]">Daftar sebagai tenaga pengajar untuk melakukan pengisian jurnal KBM dan absensi siswa di kelas.</p>
                </div>
            </a>

            <a href="{{ route('register_pengurus_kelas') }}" class="bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-2xl p-6 flex flex-col gap-4 cursor-pointer transition-all duration-200 ease-in-out no-underline hover:border-[#1B2A4A] hover:-translate-y-0.5">
                <div class="flex justify-between items-center">
                    <div class="w-12 h-12 bg-[#EBEFF4] rounded-xl flex justify-center items-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
                <div class="flex flex-col">
                    <h2 class="font-bold text-[18px] text-[#1B2A4A] leading-[22px] mb-1">Pengurus Kelas / Siswa</h2>
                    <p class="font-normal text-[13px] text-[#5A6E7F] leading-[140%]">Mewakili ketua kelas atau sekretaris untuk melihat riwayat jurnal dan membantu administrasi KBM harian.</p>
                </div>
            </a>
        </div>

        <div class="pt-[21px] pb-2 flex justify-center">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>

    </div>

</body>
</html>