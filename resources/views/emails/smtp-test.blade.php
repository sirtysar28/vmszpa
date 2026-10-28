{{-- Email uji coba konfigurasi SMTP — HTML rapi --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Coba SMTP</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(7,61,117,.12);">
                <tr>
                    <td style="background-color:#0b5cab;padding:22px 28px;color:#ffffff;font-size:19px;font-weight:700;">
                        VMS {{ config('services.company.short') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:26px 28px;">
                        <span style="display:inline-block;background-color:#ecfdf5;color:#047857;font-size:12px;font-weight:700;letter-spacing:1px;padding:6px 14px;border-radius:999px;border:1px solid #a7f3d0;">BERHASIL</span>
                        <h1 style="margin:14px 0 8px 0;font-size:21px;color:#0f172a;">Pengaturan SMTP Berhasil!</h1>
                        <p style="margin:0 0 18px 0;color:#64748b;font-size:14px;">
                            Email uji coba ini terkirim ke <b>{{ $recipient }}</b> pada
                            {{ now()->translatedFormat('l, d F Y H:i') }}.
                            Notifikasi check-in / check-out tamu akan otomatis dikirim ke daftar penerima yang telah dikonfigurasi.
                        </p>
                        <a href="{{ route('pengaturan.smtp') }}"
                           style="display:inline-block;background-color:#0b5cab;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 26px;border-radius:10px;">
                            Buka Pengaturan SMTP
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 28px 24px 28px;color:#94a3b8;font-size:11px;border-top:1px solid #e2e8f0;padding-top:16px;">
                        &copy; {{ date('Y') }} {{ config('services.company.name') }} &middot; Developed by Trijaya Solution
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
