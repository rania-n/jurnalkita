<?php

$file = 'resources/views/satpam/hasil-scan.blade.php';
$content = file_get_contents($file);

$search = '            </div>
        @else';
$replace = "            </div>
            @if(auth()->check() && auth()->user()->role === 'satpam' && \$dispensasi->jenis === 'izin_keluar' && !\$dispensasi->waktu_kembali)
                <form method=\"POST\" action=\"{{ route('satpam.konfirmasi-kembali', \$dispensasi) }}\" class=\"w-full mt-4\">
                    @csrf
                    <x-ui.button type=\"submit\" block icon=\"how_to_reg\">Konfirmasi Kembali ke Sekolah</x-ui.button>
                </form>
            @endif
        @else";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
