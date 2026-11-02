<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pakai view pagination custom (resources/views/vendor/pagination)
        // — ikon panah SVG kecil, bukan bawaan Tailwind yang besar.
        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::simple-default');

        $this->configureRateLimiting();
        $this->applySmtpSettings();
    }

    /**
     * Rate limiter bernama untuk kiosk.
     *
     * Setiap limiter bernama mendapat bucket counter TERPISAH. Sebelumnya
     * throttle grup (60/menit) dan throttle route aksi (10/menit) tanpa nama
     * memakai bucket yang sama, sehingga tiap POST checkout terhitung 2x dan
     * checkout tamu ke-3 sudah terkena 429. Limit dipatok per user kiosk
     * (perangkat resmi yang sudah login), bukan per IP.
     */
    private function configureRateLimiting(): void
    {
        // Browsing layar kiosk (halaman daftar, cari, form) — longgar karena
        // hanya operator resmi yang bisa mengaksesnya.
        RateLimiter::for('kiosk', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Aksi tulis kiosk (registrasi / check-in / check-out) — cukup untuk
        // lobi ramai (rata-rata 1 aksi tiap 2 detik tanpa henti), tetap
        // memblokir spam dan submit berulang tak terkendali.
        RateLimiter::for('kiosk-action', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Terapkan pengaturan SMTP dari database (halaman Pengaturan admin)
     * ke config runtime — meng-override nilai .env bila sudah diisi.
     */
    private function applySmtpSettings(): void
    {
        try {
            $settings = Setting::allSettings();
        } catch (\Throwable) {
            return; // tabel settings belum termigrasi
        }

        if ($settings->isEmpty()) {
            return;
        }

        if ($mailer = $settings->get('mail_mailer')) {
            Config::set('mail.default', $mailer);
        }

        if ($host = $settings->get('mail_host')) {
            Config::set('mail.mailers.smtp.host', $host);
        }

        if ($port = $settings->get('mail_port')) {
            Config::set('mail.mailers.smtp.port', (int) $port);
        }

        if ($encryption = $settings->get('mail_encryption')) {
            Config::set([
                'mail.mailers.smtp.scheme' => $encryption === 'none' ? null : $encryption,
                'mail.mailers.smtp.verify_peer' => true,
            ]);
        }

        Config::set('mail.mailers.smtp.username', $settings->get('mail_username'));
        Config::set('mail.mailers.smtp.password', Setting::decrypt($settings->get('mail_password')));

        if ($from = $settings->get('mail_from_address')) {
            Config::set('mail.from.address', $from);
        }

        if ($fromName = $settings->get('mail_from_name')) {
            Config::set('mail.from.name', $fromName);
        }
    }
}
