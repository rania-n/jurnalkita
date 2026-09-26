# Konvensi Proyek jurnalkita

Wajib diikuti di setiap perubahan kode pada proyek ini:

- **Minimalkan JavaScript baru.** Utamakan solusi server-side/Blade (form submit biasa, link, redirect) dibanding menambah JS baru. Kalau memang perlu interaktivitas di sisi klien, pakai vanilla JS sesingkat mungkin — proyek ini tidak memakai Alpine/Livewire/framework JS apa pun.
- **Selaraskan dengan tombol & komponen yang sudah ada.** Sebelum membuat pola tampilan baru (tombol, modal, tab, badge, dsb.), cek dulu apakah komponen serupa sudah ada di `resources/views/components/` dan pakai itu. Jangan bikin pola baru kalau yang sudah ada bisa dipakai ulang.
- **Bahasa Indonesia baku, efektif, tidak rancu maupun ambigu** di semua teks yang tampil ke pengguna (label, pesan error, tombol, notifikasi). Hindari kata tidak baku (mis. "cuma" → "hanya"). Halaman Admin dipakai staf/guru dewasa (bukan siswa), jadi bahasanya harus sopan dan formal.
- **Perubahan pada data atau fitur bersama harus disinkronkan ke semua role yang terdampak.** Kalau 1 halaman/fitur diubah, cek role lain yang punya fungsi serupa atau memakai data yang sama, dan selaraskan juga.
- **Efektif, bebas bug, dan ramah digunakan.** Boleh mengerjakan agak lama asal hasilnya jelas dan teruji — dahulukan ketelitian dari kecepatan.
- **Jangan pernah menjalankan `git commit` sendiri.** Setelah pekerjaan selesai, selalu berikan langkah `git add` dan `git commit` yang siap disalin-tempel supaya pengguna yang menjalankan commit-nya sendiri.
- **Jangan `git push` kecuali diminta eksplisit.**

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
