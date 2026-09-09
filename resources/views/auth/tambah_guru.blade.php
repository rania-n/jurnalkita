<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Guru</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="w-[390px] h-[844px] bg-[#F4F6F9] rounded-xl relative flex flex-col justify-between shadow-[0_10px_25px_rgba(0,0,0,0.1)] overflow-hidden">
        
        <div class="flex flex-col w-full h-full overflow-y-auto no-scrollbar">

            <!-- Screen Header -->
            <div class="flex justify-between items-center pt-[60px] px-6 pb-4 shrink-0">
                <div class="flex flex-col gap-0.5">
                    <h1 class="font-bold text-[20px] leading-[24px] text-[#1B2A4A]">Tambah Data Guru</h1>
                    <p class="font-normal text-[13px] leading-[16px] text-[#4A5568]">Tambah akun guru sebagai staff piket</p>
                </div>
                <a href="{{ route('data_guru') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full text-[#1B2A4A] text-[18px] cursor-pointer transition-colors hover:bg-[#CBD5E1]">
                    <i class="ph ph-arrow-left"></i>
                </a>
            </div>

            <!-- Form Content -->
            <form id="formTambahGuru" class="flex flex-col px-6 pb-6 gap-4 flex-1">
                
                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">Nama Lengkap</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="text" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568]" placeholder="Masukkan nama lengkap guru" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">NIK</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="number" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568]" placeholder="Masukkan Nomor Induk Kependudukan" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">Email</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="email" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568]" placeholder="Masukkan alamat email aktif" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">No. Telepon</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="tel" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568]" placeholder="Masukkan nomor WhatsApp aktif" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">Mata Pelajaran Utama</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A] relative">
                        <select class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#4A5568] appearance-none cursor-pointer" required>
                            <option value="" disabled selected>Pilih Mata Pelajaran</option>
                            <option value="rpl" class="text-[#1B2A4A]">Kejuruan Rekayasa Perangkat Lunak</option>
                            <option value="matematika" class="text-[#1B2A4A]">Matematika</option>
                            <option value="bahasa_indonesia" class="text-[#1B2A4A]">Bahasa Indonesia</option>
                            <option value="bahasa_inggris" class="text-[#1B2A4A]">Bahasa Inggris</option>
                        </select>
                        <i class="ph ph-caret-down text-[#4A5568] text-[18px] pointer-events-none absolute right-4"></i>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">Password</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="password" id="password" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568] pr-2" placeholder="Buat kata sandi baru" required>
                        <button type="button" class="bg-transparent border-none text-[#4A5568] text-[18px] flex items-center justify-center cursor-pointer" onclick="togglePassword('password', 'icon-pass')">
                            <i id="icon-pass" class="ph ph-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-full">
                    <label class="font-semibold text-[14px] leading-[17px] text-[#1B2A4A]">Konfirmasi Password</label>
                    <div class="flex justify-between items-center px-4 w-full h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] focus-within:border-[#1B2A4A]">
                        <input type="text" id="confirm-password" class="w-full h-full bg-transparent outline-none border-none font-normal text-[14px] text-[#1B2A4A] placeholder:text-[#4A5568] pr-2" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="bg-transparent border-none text-[#4A5568] text-[18px] flex items-center justify-center cursor-pointer" onclick="togglePassword('confirm-password', 'icon-confirm')">
                            <i id="icon-confirm" class="ph ph-eye"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Bottom Action -->
        <div class="flex flex-col px-6 pb-4 gap-3 bg-[#F4F6F9] shrink-0 w-full">
            <button type="submit" form="formTambahGuru" class="flex justify-center items-center w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none font-semibold text-[16px] text-white cursor-pointer transition-opacity hover:opacity-90">
                Simpan Data Guru
            </button>
            <div class="flex justify-center pt-3 pb-2 w-full">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            }
        }
    </script>
</body>
</html>