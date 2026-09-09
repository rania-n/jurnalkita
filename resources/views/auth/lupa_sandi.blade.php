<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Memanggil Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter',sans-serif] m-0 p-0 box-border">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        
        <!-- Header (Tombol Kembali) -->
        <div class="flex flex-row items-start pt-[60px] px-6 pb-2 w-full">
            <a href="{{ route('login')}}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#EBEFF4] rounded-full no-underline text-[#1B2A4A]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Konten Utama -->
        <form class="flex flex-col items-center pt-5 px-6 pb-6 gap-7" action="" method="POST">
            
            <!-- Icon -->
            <div class="flex justify-center items-center w-20 h-20 bg-[#FEF3C7] rounded-[40px]">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="4" width="14" height="16" rx="2" ry="2"></rect>
                    <circle cx="12" cy="10" r="2"></circle>
                    <line x1="12" y1="12" x2="12" y2="15"></line>
                </svg>
            </div>

            <!-- Teks Judul & Subjudul -->
            <div class="flex flex-col items-center gap-2 text-center">
                <h1 class="font-extrabold text-[22px] text-[#1B2A4A]">Lupa Kata Sandi?</h1>
                <p class="font-normal text-[14px] leading-[150%] text-[#5A6E7F]">Masukkan email atau nomor WhatsApp Anda. Admin sekolah akan membantu mereset kata sandi Anda.</p>
            </div>

            <!-- Input Email / No. WA -->
            <div class="flex flex-col gap-1.5 w-full">
                <label for="contact" class="font-semibold text-[14px] text-[#1B2A4A]">Email / No. WhatsApp</label>
                <div class="flex items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px]">
                    <input type="text" id="contact" name="contact" placeholder="Contoh: 081234567890" required class="border-none outline-none w-full h-full font-normal text-[14px] text-[#1B2A4A] bg-transparent placeholder:text-[#94A3B8]">
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="flex flex-row justify-center items-center py-3 px-4 w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none text-white font-semibold text-[16px] cursor-pointer">Kirim Permintaan Reset</button>

            <!-- Card Pusat Bantuan -->
            <a href="#" class="flex flex-row items-center p-4 gap-3 w-full h-[76px] bg-white border border-[#E2E8F0] shadow-[0px_4px_12px_rgba(27,42,74,0.06)] rounded-xl no-underline">
                <div class="flex justify-center items-center w-[46px] h-[50px] bg-[#1B2A4A] rounded-[20px]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <span class="font-bold text-[14px] text-[#1B2A4A]">Pusat bantuan</span>
            </a>

        </form>

        <!-- Home Indicator (Sticky di bawah) -->
        <div class="mt-auto w-full flex justify-center pt-[21px] pb-2">
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
        </div>

    </div>

</body> 
</html>