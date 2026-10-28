<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
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

        $this->applySmtpSettings();
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
