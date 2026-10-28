<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware KIOSK — layar tablet untuk tamu di pos penerima tamu.
 *
 * Tamu TIDAK login. Yang login adalah akun "Penerima Tamu" khusus
 * (role: penerima) yang mengoperasikan tablet; admin juga diizinkan.
 * Route kiosk dilindungi middleware auth, middleware ini membatasi role.
 */
class KioskGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tolak upload file apapun di area kiosk (mitigasi webshell upload)
        if ($request->files->count() > 0) {
            abort(400, 'Upload tidak diizinkan.');
        }

        // Hanya akun Penerima Tamu / Administrator yang boleh mengoperasikan kiosk
        if (! $request->user()?->canOperateKiosk()) {
            abort(403, 'Kiosk hanya untuk akun Penerima Tamu / Administrator.');
        }

        // Header tambahan: kiosk jangan pernah di-cache
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, max-age=0');

        return $response;
    }
}
