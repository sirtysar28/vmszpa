<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers untuk memitigasi XSS, clickjacking, MIME-sniffing,
 * dan injection script eksternal (termasuk payload php2shell/webshell).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // CSP: hanya resource dari aplikasi sendiri; style inline diizinkan
        // terbatas (utility kecil). Semua JavaScript WAJIB file eksternal —
        // inline script diblokir untuk memitigasi injeksi XSS/php2shell.
        $scriptSrc = app()->environment('local')
            ? "script-src 'self' ws: http://localhost:* http://127.0.0.1:*"
            : "script-src 'self'";

        $csp = "default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; "
             . $scriptSrc
             . "; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; "
             . "base-uri 'self'; form-action 'self'; object-src 'none'";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
