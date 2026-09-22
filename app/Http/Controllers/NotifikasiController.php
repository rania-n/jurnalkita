<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        $notifikasis = auth()->user()->notifications()->paginate(20);

        return view('notifikasi.index', compact('notifikasis'));
    }

    /**
     * Endpoint ringan buat di-poll berkala lewat JS (initNotifikasiPoll() di
     * app.js) -- biar titik merah di lonceng update sendiri kalau ada
     * notifikasi baru masuk dari aksi orang lain, tanpa guru/sekre harus
     * reload manual. Cuma angka doang (bukan render ulang HTML), jadi ringan.
     */
    public function jumlah(): JsonResponse
    {
        return response()->json(['jumlah' => auth()->user()->unreadNotifications()->count()]);
    }

    /** Isi popup lonceng -- di-fetch AJAX tiap dibuka (lihat _daftar-fragment.blade.php kenapa). */
    public function fragment(): View
    {
        return view('notifikasi._daftar-fragment');
    }

    /**
     * Tandai satu notifikasi dibaca, lalu lempar ke tautannya.
     *
     * Tautan yang tersimpan itu URL ABSOLUT (dibuat pakai route() saat
     * notifikasi dikirim, ikut APP_URL waktu itu). Kalau APP_URL pernah salah
     * setel (atau situsnya dibuka dari domain lain dari yang di APP_URL),
     * redirect ke URL absolut itu bisa lompat ke domain BEDA -> sesi login
     * ketinggalan di domain asal, jadinya dianggap tamu / balik ke halaman
     * masuk. Makanya cuma path+query-nya yang dipakai (redirect relatif),
     * biar selalu ikut domain yang lagi beneran dipakai -- aman juga buat
     * notifikasi lama yang domainnya sudah kadung salah tersimpan.
     */
    public function buka(string $id): RedirectResponse
    {
        $notifikasi = auth()->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        $url = $notifikasi->data['url'] ?? null;
        if (! $url) {
            return redirect()->route('notifikasi.index');
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return redirect($path.($query ? "?{$query}" : ''));
    }

    public function tandaiSemuaDibaca(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
