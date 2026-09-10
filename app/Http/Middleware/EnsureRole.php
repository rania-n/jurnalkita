<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Batasi akses ke peran tertentu.
     * Pakai: ->middleware('role:admin')  atau  ->middleware('role:guru,waka')
     *
     * Catatan: "piket" & "sekretaris" bukan peran DB — dicek terpisah
     * (User::isPiket() / User::isSekretaris()).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($user->role, $roles, true)) {
            // Bukan haknya — lempar ke beranda peran sendiri, jangan 403 mentah.
            return redirect()->route($user->homeRoute())
                ->with('info', 'Halaman itu bukan untuk peran Anda.');
        }

        return $next($request);
    }
}
