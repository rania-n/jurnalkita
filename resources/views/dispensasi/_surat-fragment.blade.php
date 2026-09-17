{{--
    Isi popup "Lihat Surat + QR" buat guru piket/waka/admin yang buka dari
    DALAM app (udah login). Beda dari surat.blade.php (link WA ke siswa,
    wajib halaman biasa) -- nggak ada meta-refresh di sini (nggak jalan
    kalau disisipkan lewat AJAX/innerHTML), tutup-buka lagi popup ini buat
    QR yang baru.

    Variabel yang wajib ada di scope pemanggil: $dispensasi, $qrUrl
--}}
@include('dispensasi._surat-konten')
