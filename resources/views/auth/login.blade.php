<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Memanggil Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter',sans-serif] m-0 p-0 box-border">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col justify-between overflow-hidden shadow-[0_10px_25px_rgba(0,0,0,0.1)]">
        
        <!-- Bagian Logo -->
        <div class="flex flex-col items-center pt-[100px] px-6 pb-10 gap-2 text-center">
            <div class="w-[72px] h-[72px] bg-[#1B2A4A] rounded-[20px] flex justify-center items-center mb-2">
                <div class="w-6 h-7 border-2 border-white rounded relative after:content-[''] after:absolute after:bottom-1 after:left-1 after:right-1 after:h-0.5 after:bg-white"></div>
            </div>
            <h1 class="font-extrabold text-[28px] text-[#1B2A4A] leading-[34px]">jurnalkita</h1>
            <p class="font-medium text-[13px] text-[#5A6E7F] leading-[16px]">Sistem Jurnal & Absensi Guru SMKN 1 Boyolangu</p>
        </div>

        <!-- Bagian Form -->
        <form class="flex flex-col px-6 gap-4 flex-grow" action="" method="POST">
            
            <div class="flex flex-col gap-1.5">
                <label for="username" class="font-semibold text-[14px] text-[#1B2A4A]">Email / Username</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] px-4 h-[46px]">
                    <input type="text" id="username" name="username" placeholder="Masukkan email atau username" class="border-none outline-none w-full font-normal text-[14px] text-[#1B2A4A] bg-transparent placeholder:text-[#94A3B8]">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="font-semibold text-[14px] text-[#1B2A4A]">Kata Sandi</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] px-4 h-[46px]">
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" class="border-none outline-none w-full font-normal text-[14px] text-[#1B2A4A] bg-transparent placeholder:text-[#94A3B8]">
                    
                    <button type="button" id="togglePassword" class="cursor-pointer bg-transparent border-none flex items-center justify-center p-0 outline-none">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_3_34)">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </g>
                            <defs>
                            <clipPath id="clip0_3_34">
                            <rect width="18" height="18" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center mt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-5 h-5 accent-[#1B2A4A] cursor-pointer">
                    <span class="font-normal text-[13px] text-[#5A6E7F] cursor-pointer">Keep Logged In (30 hari)</span>
                </label>
                <a href="{{ route('lupa_sandi') }}" class="font-semibold text-[13px] text-[#1B2A4A] no-underline">Lupa Sandi?</a>
            </div>

            <div class="flex justify-start">
                <button type="submit" class="mt-3 bg-[#1B2A4A] rounded-xl border-none w-[100px] h-[48px] text-white font-semibold text-[16px] cursor-pointer">Masuk</button>
            </div>
            
        </form>

        <!-- Bagian Bawah -->
        <div class="flex flex-col items-center pb-[34px]">
            <p class="font-normal text-[14px] text-[#5A6E7F]">Belum punya akun? <a href="{{ route('pilih_peran') }}" class="font-bold text-[#1B2A4A] no-underline ml-1">Daftar Akun Baru</a></p>
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full mt-6"></div>
        </div>

    </div>

    <!-- Script JavaScript -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>
</body> 
</html>