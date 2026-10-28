<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email uji coba konfigurasi SMTP dari halaman Pengaturan. */
class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.config('app.name').'] Uji Coba SMTP Berhasil',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.smtp-test');
    }
}
