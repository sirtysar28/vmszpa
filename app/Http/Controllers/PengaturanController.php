<?php

namespace App\Http\Controllers;

use App\Mail\SmtpTestMail;
use App\Models\Setting;
use App\Support\SendVisitNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PENGATURAN SMTP (khusus admin) — konfigurasi mail server untuk
 * notifikasi email check-in / check-out tamu. Disimpan di database
 * dan diterapkan ke config runtime (lihat AppServiceProvider).
 */
class PengaturanController extends Controller
{
    public function edit()
    {
        $settings = Setting::allSettings();

        return view('pengaturan.smtp', [
            'mailer' => $settings->get('mail_mailer', 'log'),
            'host' => $settings->get('mail_host', ''),
            'port' => $settings->get('mail_port', '587'),
            'encryption' => $settings->get('mail_encryption', 'tls'),
            'username' => $settings->get('mail_username', ''),
            'hasPassword' => filled($settings->get('mail_password')),
            'fromAddress' => $settings->get('mail_from_address', ''),
            'fromName' => $settings->get('mail_from_name', config('app.name')),
            'recipients' => $settings->get('mail_recipients', ''),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log'],
            'mail_host' => ['nullable', 'string', 'max:255', 'required_if:mail_mailer,smtp'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535', 'required_if:mail_mailer,smtp'],
            'mail_encryption' => ['required', 'in:tls,ssl,none'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'], // kosong = pertahankan
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_recipients' => ['nullable', 'string', 'max:1000'],
        ], [
            'mail_host.required_if' => 'Host SMTP wajib diisi.',
            'mail_port.required_if' => 'Port SMTP wajib diisi.',
            'mail_port.between' => 'Port harus 1–65535.',
            'mail_from_address.required' => 'Alamat pengirim wajib diisi.',
            'mail_from_address.email' => 'Format alamat pengirim tidak valid.',
            'mail_from_name.required' => 'Nama pengirim wajib diisi.',
        ]);

        // Validasi format email penerima satu per satu
        $recipients = SendVisitNotification::recipientsFromString((string) ($data['mail_recipients'] ?? ''));
        $rawRecipients = (string) ($data['mail_recipients'] ?? '');
        if (trim($rawRecipients) !== '' && count($recipients) === 0) {
            return back()->withErrors(['mail_recipients' => 'Semua alamat email penerima tidak valid.'])->withInput();
        }

        $pairs = [
            'mail_mailer' => $data['mail_mailer'],
            'mail_host' => $data['mail_host'] ?? '',
            'mail_port' => (string) ($data['mail_port'] ?? ''),
            'mail_encryption' => $data['mail_encryption'],
            'mail_username' => $data['mail_username'] ?? '',
            'mail_from_address' => $data['mail_from_address'],
            'mail_from_name' => $data['mail_from_name'],
            'mail_recipients' => $rawRecipients,
        ];

        // Password dikosongkan → pertahankan yang lama
        if (filled($data['mail_password'] ?? null)) {
            $pairs['mail_password'] = Setting::encrypt($data['mail_password']);
        }

        Setting::setMany($pairs);

        return back()->with('success', 'Pengaturan SMTP berhasil disimpan.');
    }

    /** Kirim email uji coba ke alamat penerima notifikasi / alamat uji. */
    public function test(Request $request)
    {
        $request->validate(['test_email' => ['nullable', 'email', 'max:255']]);

        $recipient = $request->input('test_email') ?: Setting::get('mail_from_address');

        if (! filter_var((string) $recipient, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['test_email' => 'Isi alamat email penerima uji coba terlebih dahulu.']);
        }

        try {
            Mail::to($recipient)->send(new SmtpTestMail((string) $recipient));

            return back()->with('success', "Email uji coba berhasil dikirim ke {$recipient}.");
        } catch (\Throwable $e) {
            Log::warning('Uji coba SMTP gagal: '.$e->getMessage());

            return back()->withErrors(['test_email' => 'Gagal mengirim email: '.$e->getMessage()]);
        }
    }
}
