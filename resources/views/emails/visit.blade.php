{{-- Email notifikasi check-in / check-out tamu — HTML rapi, email-client friendly (table + inline style) --}}
@php
    $isCheckin = $type === 'checkin';
    $label = $isCheckin ? 'CHECK-IN' : 'CHECK-OUT';
    $color = $isCheckin ? '#047857' : '#b91c1c';
    $time  = $isCheckin ? $visit->check_in_at : ($visit->check_out_at ?? now());
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $label }} Tamu</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(7,61,117,.12);">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#0b5cab;padding:22px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color:#ffffff;font-size:19px;font-weight:700;letter-spacing:.5px;">
                                    VMS {{ config('services.company.short') }}
                                </td>
                                <td align="right" style="color:#bcdcf8;font-size:12px;line-height:1.4;">
                                    {{ config('services.company.name') }}<br>{{ config('services.company.domain') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Judul --}}
                <tr>
                    <td style="padding:26px 28px 6px 28px;">
                        <span style="display:inline-block;background-color:{{ $color }};color:#ffffff;font-size:12px;font-weight:700;letter-spacing:1.5px;padding:6px 14px;border-radius:999px;">{{ $label }}</span>
                        <h1 style="margin:14px 0 4px 0;font-size:21px;color:#0f172a;">
                            {{ $isCheckin ? 'Tamu baru saja masuk' : 'Tamu telah keluar' }}
                        </h1>
                        <p style="margin:0;color:#64748b;font-size:14px;">
                            {{ now()->translatedFormat('l, d F Y') }} &middot; pukul {{ $time->translatedFormat('H:i') }} WIB
                        </p>
                    </td>
                </tr>

                {{-- Data tamu --}}
                <tr>
                    <td style="padding:16px 28px 4px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:12px;">
                            @php
                                $rows = [
                                    'Nama Tamu'        => $visit->guest->name,
                                    'Kode Tamu'        => $visit->guest->guest_code,
                                    'Nomor HP'         => $visit->guest->phone,
                                    'Perusahaan'       => $visit->guest->company ?: 'Perorangan',
                                    'Identitas'        => $visit->guest->identity_type.' · '.$visit->guest->identity_number,
                                    'Departemen Tujuan'=> $visit->department?->name ?: '—',
                                    'Tujuan'           => $visit->purpose,
                                    'Bertemu Dengan'   => $visit->meet_with ?: '—',
                                    'Kendaraan'        => trim(($visit->guest->vehicle_type ?: '').' · '.($visit->vehicle_number ?: '')) ?: '—',
                                    'Waktu Check-In'   => $visit->check_in_at->translatedFormat('d M Y H:i'),
                                    'Waktu Check-Out'  => $visit->check_out_at?->translatedFormat('d M Y H:i') ?: 'Masih di dalam area',
                                ];
                            @endphp
                            @foreach ($rows as $title => $value)
                                <tr>
                                    <td width="170" style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #eef2f7;vertical-align:top;">{{ $title }}</td>
                                    <td style="padding:10px 16px;font-size:13px;color:#0f172a;font-weight:600;border-bottom:1px solid #eef2f7;vertical-align:top;">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                {{-- Catatan --}}
                @if ($visit->notes)
                    <tr>
                        <td style="padding:14px 28px 0 28px;">
                            <div style="background-color:#f8fafc;border-left:4px solid #0b5cab;border-radius:8px;padding:12px 16px;">
                                <small style="color:#64748b;font-size:12px;font-weight:700;letter-spacing:.5px;">CATATAN</small>
                                <p style="margin:4px 0 0 0;color:#0f172a;font-size:13px;">{{ $visit->notes }}</p>
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- CTA --}}
                <tr>
                    <td style="padding:24px 28px 6px 28px;">
                        <a href="{{ route('dashboard') }}"
                           style="display:inline-block;background-color:#0b5cab;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 26px;border-radius:10px;">
                            Lihat Dashboard VMS
                        </a>
                        <p style="margin:14px 0 0 0;color:#94a3b8;font-size:12px;">
                            Email ini dikirim otomatis oleh sistem setiap tamu melakukan {{ strtolower($label) }} di kiosk.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 28px 24px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e2e8f0;padding-top:16px;">
                            <tr>
                                <td style="color:#94a3b8;font-size:11px;line-height:1.6;">
                                    &copy; {{ date('Y') }} {{ config('services.company.name') }} &middot; {{ config('services.company.app') }}<br>
                                    Developed by <a href="https://trijayasolution.com" style="color:#0b5cab;text-decoration:none;">Trijaya Solution</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
