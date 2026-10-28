<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware area management (dashboard, data tamu, laporan, dll).
 * Akun "Penerima Tamu" (khusus operator kiosk) dialihkan ke layar Kiosk —
 * area management hanya untuk admin, manager, dan petugas.
 */
class EnsureManagement
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPenerima()) {
            return redirect()->route('kiosk.index')
                ->with('kiosk_error', 'Akun Penerima Tamu hanya untuk layar Kiosk.');
        }

        return $next($request);
    }
}
