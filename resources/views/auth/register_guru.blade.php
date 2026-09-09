<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Guru - Jurnalkita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Utility untuk menyembunyikan scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] rounded-xl bg-[#F4F6F9] relative flex flex-col shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-y-auto no-scrollbar">
        
        <!-- Header -->
        <div class="flex justify-between items-center pt-[60px] px-6 pb-4">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-[20px] text-[#1B2A4A]">Registrasi Guru</h1>
                <p class="font-normal text-[12px] text-[#5A6E7F]">Lengkapi biodata pengajar Anda</p>
            </div>
            <!-- Tombol Back -->
            <a href="{{ route('pilih_peran') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#EBEFF4] rounded-full text-[#1B2A4A]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
        </div>

        <!-- Alert Box -->
        <div class="flex items-center p-3 mx-6 mb-6 gap-2.5 bg-[#FEF3C7] border-l-4 border-[#F59E0B]">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span class="font-medium text-[12px] leading-[1.4] text-[#1B2A4A]">Akun Anda akan diverifikasi oleh Admin sebelum dapat digunakan.</span>
        </div>

        <!-- Form Registrasi -->
        <form class="flex flex-col px-6 pb-[34px] gap-[14px]" action="" method="POST">
            
            <div class="flex flex-col gap-1.5">
                <label for="name" class="font-semibold text-[14px] text-[#1B2A4A]">Nama Lengkap</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="text" id="name" name="name" placeholder="Ahmad Suryadi, S.Pd" class="w-full h-full px-4 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="nik" class="font-semibold text-[14px] text-[#1B2A4A]">NIK</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="number" id="nik" name="nik" placeholder="12345678901" class="w-full h-full px-4 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email" class="font-semibold text-[14px] text-[#1B2A4A]">Email</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="email" id="email" name="email" placeholder="ahmad@smkn1boyolangu.sch.id" class="w-full h-full px-4 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="phone" class="font-semibold text-[14px] text-[#1B2A4A]">No. WhatsApp</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="number" id="phone" name="phone" placeholder="081234567890" class="w-full h-full px-4 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="subject" class="font-semibold text-[14px] text-[#1B2A4A]">Mata Pelajaran Utama</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <select id="subject" name="subject" class="appearance-none w-full h-full pl-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none cursor-pointer invalid:text-[#94A3B8]" required>
                        <option value="" disabled selected hidden>Pilih Mata Pelajaran</option>
                        <option value="matematika" class="text-[#1B2A4A]">Matematika</option>
                        <option value="bahasa_indonesia" class="text-[#1B2A4A]">Bahasa Indonesia</option>
                        <option value="rpl" class="text-[#1B2A4A]">Kejuruan RPL</option>
                    </select>
                    <!-- Ikon Chevron Down -->
                    <div class="absolute right-4 pointer-events-none flex">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="username" class="font-semibold text-[14px] text-[#1B2A4A]">Username</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="text" id="username" name="username" placeholder="ahmadsuryadi" class="w-full h-full px-4 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                </div>
            </div>

            <!-- Password Baru -->
            <div class="flex flex-col gap-1.5">
                <label for="password" class="font-semibold text-[14px] text-[#1B2A4A]">Password</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="password" id="password" name="password" placeholder="Buat kata sandi baru" class="w-full h-full pl-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                    <button type="button" id="togglePassword" class="absolute right-4 flex items-center justify-center p-0 bg-transparent outline-none cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="flex flex-col gap-1.5">
                <label for="password_confirmation" class="font-semibold text-[14px] text-[#1B2A4A]">Konfirmasi Password</label>
                <div class="flex items-center bg-white border border-[#E2E8F0] rounded-[10px] h-[46px] relative overflow-hidden">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" class="w-full h-full pl-4 pr-10 font-normal text-[14px] text-[#1B2A4A] bg-transparent outline-none placeholder:text-[#94A3B8]">
                    <button type="button" id="togglePasswordConfirm" class="absolute right-4 flex items-center justify-center p-0 bg-transparent outline-none cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.54628 8.73976C1.48378 8.90815 1.48378 9.09337 1.54628 9.26176C2.15506 10.7379 3.18842 12 4.51536 12.8881C5.8423 13.7762 7.40307 14.2503 8.99978 14.2503C10.5965 14.2503 12.1573 13.7762 13.4842 12.8881C14.8111 12 15.8445 10.7379 16.4533 9.26176C16.5158 9.09337 16.5158 8.90815 16.4533 8.73976C15.8445 7.26365 14.8111 6.00154 13.4842 5.11343C12.1573 4.22533 10.5965 3.75122 8.99978 3.75122C7.40307 3.75122 5.8423 4.22533 4.51536 5.11343C3.18842 6.00154 2.15506 7.26365 1.54628 8.73976Z" stroke="#5A6E7F" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="9" cy="9" r="2.5" stroke="#5A6E7F" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-start">
                <button type="submit" class="mt-3 bg-[#1B2A4A] rounded-xl w-[100px] h-[48px] text-white font-semibold text-[16px] cursor-pointer">Daftar</button>
            </div>
            
            <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full mx-auto mt-6 mb-2"></div>
        </form>
    </div>

    <!-- Script JavaScript untuk 2 tombol mata -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });

        const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
        const passwordConfirmInput = document.querySelector('#password_confirmation');
        togglePasswordConfirm.addEventListener('click', function () {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
        });
    </script>
</body> 
</html>