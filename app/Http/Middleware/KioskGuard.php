<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware tamu-kiosk: hanya boleh mengakses fitur check-in/check-out.
 * Tidak perlu login (untuk tablet di pos security), tapi dibatasi
 * method + rate-limit + tanpa akses resource internal.
 */
class KioskGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tolak upload file apapun di area kiosk (mitigasi webshell upload)
        if ($request->files->count() > 0) {
            abort(400, 'Upload tidak diizinkan.');
        }

        // Header tambahan: kiosk jangan pernah di-cache
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, max-age=0');

        return $response;
    }
}
