@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-shell">

    {{-- KIRI: FORM --}}
    <div class="auth-left">
        <div class="auth-form-card">
            <div class="auth-logo-mobile">
                <img src="{{ asset('images/logo-besar.jpeg') }}" alt="Logo ZPA">
            </div>

            <h2 class="auth-title">Password Baru</h2>
            <p class="auth-sub">Buat password baru untuk akun <b>{{ $email }}</b>.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimal 8 karakter" required autocomplete="new-password">
                        <button type="button" class="input-eye" id="togglePassword" data-target="password" aria-label="Tampilkan password" title="Intip password">
                            <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeSlash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <p class="form-hint">Minimal 8 karakter, mengandung huruf dan angka.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Ulangi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" placeholder="Ulangi password baru" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Simpan Password Baru
                </button>
            </form>

            <p class="auth-meta"><a href="{{ route('login') }}">Kembali ke Login</a></p>
        </div>
    </div>

    {{-- KANAN: INFO --}}
    <div class="auth-right">
        <div class="auth-brand">
            <img src="{{ asset('images/logo-besar.jpeg') }}" alt="{{ config('services.company.name') }}">
        </div>
        <h1>Password Aman =<br>Akun <span>Terlindungi</span></h1>
        <p class="lead">Gunakan kombinasi huruf besar, huruf kecil, dan angka. Jangan gunakan password yang sama dengan akun lain.</p>
        <div class="auth-footer">
            Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener">Trijaya Solution</a>
        </div>
    </div>
</div>
@endsection
