@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-shell">

    {{-- ========== KIRI: FORM LOGIN ========== --}}
    <div class="auth-left">
        <div class="auth-form-card">

            <div class="auth-logo-mobile">
                <img src="{{ asset('images/logo-besar.jpeg') }}" alt="Logo ZPA">
            </div>

            <h2 class="auth-title">Selamat Datang 👋</h2>
            <p class="auth-sub">Masuk untuk mengelola data kunjungan tamu {{ config('services.company.short') }}.</p>

            @if (session('status'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" autocomplete="off">
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="nama@perusahaan.com" required autofocus autocomplete="username">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Password + intip --}}
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="input-eye" id="togglePassword" data-target="password"
                                aria-label="Tampilkan password" title="Intip password">
                            <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeSlash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Captcha 5 karakter --}}
                <div class="form-group">
                    <label class="form-label" for="captcha">Kode Keamanan <span class="req">(5 karakter)</span></label>
                    <div class="captcha-wrap">
                        <input type="text" id="captcha" name="captcha" maxlength="5"
                               class="form-control @error('captcha') is-invalid @enderror"
                               placeholder="•••••" required autocomplete="off">
                        <img src="{{ route('captcha') }}" alt="Kode captcha" class="captcha-img"
                             id="captchaImage" title="Klik untuk ganti kode">
                    </div>
                    @error('captcha')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <p class="form-hint">Klik gambar untuk mengganti kode jika kurang jelas.</p>
                </div>

                {{-- Remember + lupa password --}}
                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember" value="1"> Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                    Masuk
                </button>
            </form>

            <p class="auth-meta">
                Tamu/kunjungan? Buka <a href="{{ route('kiosk.index') }}">Layar Check-in Tamu</a>
            </p>
        </div>
    </div>

    {{-- ========== KANAN: INFORMASI APLIKASI ========== --}}
    <div class="auth-right">
        <div class="auth-brand">
            <img src="{{ asset('images/logo-besar.jpeg') }}" alt="{{ config('services.company.name') }}">
        </div>
        <h1>Visitor Management<br>System <span>ZPA</span></h1>
        <p class="lead">
            Sistem manajemen kunjungan tamu digital {{ config('services.company.name') }} —
            check-in &amp; check-out cepat, aman, dan terdata secara real-time.
        </p>

        <div class="auth-features">
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <div><b>Check-In Cepat</b><small>Tamu mendaftar &amp; check-in mandiri lewat tablet pos security.</small></div>
            </div>
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <div><b>Keamanan Terjaga</b><small>Validasi data, captcha, dan audit trail setiap aktivitas login.</small></div>
            </div>
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                <div><b>Laporan Lengkap</b><small>Rekap kunjungan harian, tujuan, dan perusahaan dalam satu dasbor.</small></div>
            </div>
        </div>

        <div class="auth-stats">
            <div><b>{{ \App\Models\Guest::count() }}</b><small>Tamu Terdaftar</small></div>
            <div><b>{{ \App\Models\Visit::whereNull('check_out_at')->count() }}</b><small>Sedang Di Dalam</small></div>
            <div><b>{{ \App\Models\Visit::whereDate('check_in_at', today())->count() }}</b><small>Check-in Hari Ini</small></div>
        </div>

        <div class="auth-footer">
            Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener">Trijaya Solution</a>
        </div>
    </div>
</div>
@endsection
