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

        abort_unless($user && in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
