<?php

namespace App\Support;

use App\Mail\VisitNotification;
use App\Models\Setting;
use App\Models\Visit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pengirim notifikasi email check-in / check-out tamu.
 *
 * Penerima diambil dari Pengaturan SMTP (kolom "penerima notifikasi",
 * dipisah koma). Bila SMTP belum dikonfigurasi / daftar penerima kosong,
 * email dilewati dan aktivitas kiosk tetap lancar.
 */
class SendVisitNotification
{
    public static function send(Visit $visit, string $type): void
    {
        try {
            $recipients = self::recipients();

            if (empty($recipients)) {
                return; // belum ada penerima terdaftar → lewati
            }

            Mail::to($recipients)->send(new VisitNotification($visit, $type));
        } catch (\Throwable $e) {
            // Jangan pernah ganggu alur check-in / check-out di kiosk
            Log::warning('Gagal kirim notifikasi email kunjungan: '.$e->getMessage());
        }
    }

    /** @return string[] daftar email penerima dari setting mail_recipients */
    public static function recipients(): array
    {
        return self::recipientsFromString((string) Setting::get('mail_recipients', ''));
    }

    /** Parse string daftar email (dipisah koma / titik-koma / baris baru). */
    public static function recipientsFromString(string $raw): array
    {
        return collect(preg_split('/[,;\n]/', $raw))
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
