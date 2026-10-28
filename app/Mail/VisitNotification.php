<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notifikasi aktivitas tamu (check-in / check-out)
 * dengan template HTML rapi (resources/views/emails/visit.blade.php).
 *
 * Sengaja TIDAK di-queue: server produksi tidak menjalankan worker,
 * jadi dikirim sinkron & dibungkus try-catch oleh pengirimnya
 * agar kegagalan SMTP tidak mengganggu alur kiosk.
 */
class VisitNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public Visit $visit, public string $type) // 'checkin' | 'checkout'
    {}

    public function envelope(): Envelope
    {
        $label = $this->type === 'checkin' ? 'Check-In' : 'Check-Out';

        return new Envelope(
            subject: '['.config('services.company.short').'] '.$label.' Tamu: '.$this->visit->guest->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.visit');
    }
}
