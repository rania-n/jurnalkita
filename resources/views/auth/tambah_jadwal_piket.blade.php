<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Piket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Hilangkan icon jam default di input time */
        .time-input::-webkit-calendar-picker-indicator {
            display: none;
        }
    </style>
</head>
<body class="bg-[#e5e5e5] flex justify-center items-center min-h-screen font-['Inter']">

    <div class="relative w-[390px] h-[844px] min-h-[844px] bg-[#F4F6F9] rounded-xl flex flex-col overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.1)]">

        <!-- Header -->
        <div class="flex flex-row justify-between items-center pt-[60px] px-6 pb-4 w-full">
            <div class="flex flex-col gap-0.5">
                <h1 class="font-bold text-[20px] text-[#1B2A4A]">Tambah Jadwal Piket</h1>
                <p class="font-normal text-[13px] text-[#4A5568]">Atur jadwal piket harian staff</p>
            </div>
            <a href="{{ route('data_jadwal_piket') }}" class="flex justify-center items-center w-[34px] h-[34px] bg-[#E2E8F0] rounded-full border-none cursor-pointer">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1B2A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
        </div>

        <!-- Form Elements -->
        <div class="flex flex-col px-6 pb-6 gap-4 w-full flex-1">
            
            <!-- Hari -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Hari</label>
                <div class="flex flex-row justify-between items-center px-4 h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] relative focus-within:border-[#1B2A4A]">
                    <select class="w-full h-full border-none bg-transparent font-normal text-[14px] text-[#1B2A4A] outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected class="text-[#4A5568]">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                    </select>
                    <svg class="absolute right-4 pointer-events-none" width="12" height="8" viewBox="0 0 12 8" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1.5L6 6.5L11 1.5"/></svg>
                </div>
            </div>

            <!-- Guru Piket -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Guru Piket</label>
                <div class="flex flex-row justify-between items-center px-4 h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] relative focus-within:border-[#1B2A4A]">
                    <select class="w-full h-full border-none bg-transparent font-normal text-[14px] text-[#1B2A4A] outline-none appearance-none cursor-pointer">
                        <option value="" disabled selected class="text-[#4A5568]">Pilih Guru</option>
                        <option value="1">Guru Pertama</option>
                        <option value="2">Guru Kedua</option>
                    </select>
                    <svg class="absolute right-4 pointer-events-none" width="12" height="8" viewBox="0 0 12 8" fill="none" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1.5L6 6.5L11 1.5"/></svg>
                </div>
            </div>

            <!-- Jam Mulai & Selesai (Row) -->
            <div class="flex flex-row gap-3 w-full">
                <div class="flex flex-col gap-1.5 flex-1">
                    <label class="font-semibold text-[14px] text-[#1B2A4A]">Jam Mulai</label>
                    <div class="flex flex-row justify-between items-center px-4 h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] relative focus-within:border-[#1B2A4A]">
                        <input type="time" value="07:00" class="time-input w-full h-full border-none bg-transparent font-normal text-[14px] text-[#1B2A4A] outline-none appearance-none">
                    </div>
                </div>
                
                <div class="flex flex-col gap-1.5 flex-1">
                    <label class="font-semibold text-[14px] text-[#1B2A4A]">Jam Selesai</label>
                    <div class="flex flex-row justify-between items-center px-4 h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] relative focus-within:border-[#1B2A4A]">
                        <input type="time" value="12:00" class="time-input w-full h-full border-none bg-transparent font-normal text-[14px] text-[#1B2A4A] outline-none appearance-none">
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="flex flex-col gap-1.5 w-full">
                <label class="font-semibold text-[14px] text-[#1B2A4A]">Keterangan</label>
                <div class="flex flex-row justify-between items-center px-4 h-[46px] bg-white border border-[#E2E8F0] rounded-[10px] relative focus-within:border-[#1B2A4A]">
                    <input type="text" placeholder="Keterangan tambahan (opsional)" class="w-full h-full border-none bg-transparent font-normal text-[14px] text-[#1B2A4A] outline-none appearance-none placeholder:text-[#4A5568]">
                </div>
            </div>

        </div>

        <!-- Bottom Section -->
        <div class="absolute bottom-0 w-full flex flex-col px-6">
            <button class="flex flex-row justify-center items-center px-4 w-full h-[48px] bg-[#1B2A4A] rounded-xl border-none cursor-pointer font-semibold text-[16px] leading-[19px] text-white transition-opacity hover:opacity-90">Tambah Jadwal Piket</button>
            <div class="flex flex-row justify-center items-start pt-3 pb-2 w-full">
                <div class="w-[139px] h-[5px] bg-[#1B2A4A] rounded-full"></div>
            </div>
        </div>

    </div>

</body>
</html>