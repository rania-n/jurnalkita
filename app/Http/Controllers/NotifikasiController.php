<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        $notifikasis = auth()->user()->notifications()->paginate(20);

        return view('notifikasi.index', compact('notifikasis'));
    }

    /** Tandai satu notifikasi dibaca, lalu lempar ke tautannya. */
    public function buka(string $id): RedirectResponse
    {
        $notifikasi = auth()->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        return redirect($notifikasi->data['url'] ?? route('notifikasi.index'));
    }

    public function tandaiSemuaDibaca(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
