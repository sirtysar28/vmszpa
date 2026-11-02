<!DOCTYPE html>
<html lang="id">
@php
    // Retry-After dikirim oleh middleware throttle (jumlah detik tunggu).
    // Setelah itu operator kiosk kembali ke layar utama; tamu lain ke login.
    $retryAfter = max(1, (int) ($exception->getHeaders()['Retry-After'] ?? 60));
    $target = auth()->check() && in_array(auth()->user()->role, ['penerima', 'admin'], true)
        ? route('kiosk.index')
        : route('login');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Terlalu Banyak Permintaan | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo-kecil.png') }}">
    <meta http-equiv="refresh" content="{{ $retryAfter }};url={{ $target }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #0b5cab 0%, #063d73 100%);
            color: #fff;
            padding: 24px;
            text-align: center;
        }
        .card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            backdrop-filter: blur(8px);
        }
        .icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon svg { width: 44px; height: 44px; stroke: #ffd166; }
        h1 { font-size: 1.6rem; margin-bottom: 12px; }
        p { color: rgba(255, 255, 255, 0.85); line-height: 1.6; margin-bottom: 8px; }
        .countdown {
            font-size: 3.5rem;
            font-weight: 700;
            margin: 20px 0 8px;
            font-variant-numeric: tabular-nums;
        }
        .countdown small { font-size: 1rem; font-weight: 400; display: block; margin-top: 4px; }
        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 14px 36px;
            background: #fff;
            color: #0b5cab;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <h1>Sedikit Jeda Dulu, Ya</h1>
        <p>Sistem sedang menangkap napas karena terlalu banyak permintaan berurutan.</p>
        <p>Halaman ini akan kembali otomatis &mdash; tidak perlu melakukan apa pun.</p>

        <div class="countdown">
            <span id="detik">{{ $retryAfter }}</span>
            <small>detik lagi</small>
        </div>

        <a class="btn" href="{{ $target }}">Kembali Sekarang</a>
    </main>

    <script>
        let sisa = {{ $retryAfter }};
        const el = document.getElementById('detik');
        const hitung = setInterval(() => {
            sisa--;
            if (sisa <= 0) {
                clearInterval(hitung);
                window.location.replace('{{ $target }}');
                return;
            }
            el.textContent = sisa;
        }, 1000);
    </script>
</body>
</html>
