<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Kiosk Tamu') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo-kecil.png') }}">
    <link rel="stylesheet" href="{{ asset('css/kiosk.css') }}?v={{ filemtime(public_path('css/kiosk.css')) }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/kiosk.js') }}?v={{ filemtime(public_path('js/kiosk.js')) }}" defer></script>
</head>
<body>

<header class="k-header">
    <div class="k-header-inner">
        <img src="{{ asset('images/logo-kecil.png') }}" alt="Logo {{ config('services.company.name') }}">
        <div class="k-welcome">
            <small>Welcome to</small>
            <h1>{{ config('services.company.name') }}</h1>
        </div>
        <div class="k-clock">
            <b id="kiosk-clock">--:--:--</b>
            <small id="kiosk-date">&nbsp;</small>
        </div>
    </div>
</header>

<main class="k-main">
    @if (session('kiosk_success'))
        <div class="k-alert success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
            <span>{{ session('kiosk_success') }}</span>
        </div>
    @endif
    @if (session('kiosk_error'))
        <div class="k-alert error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            <span>{{ session('kiosk_error') }}</span>
        </div>
    @endif

    @yield('content')
</main>

<footer class="k-footer">
    &copy; {{ date('Y') }} {{ config('services.company.name') }} &middot; {{ config('services.company.domain') }}
    &mdash; Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener">Trijaya Solution</a>
</footer>

</body>
</html>
