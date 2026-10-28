@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<div class="auth-shell">

    {{-- KIRI: FORM --}}
    <div class="auth-left">
        <div class="auth-form-card">
            <div class="auth-logo-mobile">
                <img src="{{ asset('images/logo-besar.jpeg') }}" alt="Logo ZPA">
            </div>

            <h2 class="auth-title">Lupa Password? 🔑</h2>
            <p class="auth-sub">
                Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang password.
            </p>

            @if (session('status'))
                <div class="alert alert-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Terdaftar</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="nama@perusahaan.com" required autofocus>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Kirim Tautan Reset
                </button>
            </form>

            <p class="auth-meta">
                Sudah ingat password? <a href="{{ route('login') }}">Kembali ke Login</a>
            </p>
        </div>
    </div>

    {{-- KANAN: INFO --}}
    <div class="auth-right">
        <div class="auth-brand">
            <img src="{{ asset('images/logo-besar.jpeg') }}" alt="{{ config('services.company.name') }}">
        </div>
        <h1>Atur Ulang<br>Password <span>Akun</span></h1>
        <p class="lead">
            Tautan reset password hanya berlaku <b>60 menit</b> dan dapat digunakan sekali.
            Pastikan Anda mengakses email resmi perusahaan.
        </p>
        <div class="auth-features">
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <div><b>Tautan Sekali Pakai</b><small>Setiap tautan reset hanya bisa digunakan satu kali.</small></div>
            </div>
            <div class="feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <div><b>Kedaluwarsa 60 Menit</b><small>Tautan hangus otomatis setelah 1 jam.</small></div>
            </div>
        </div>
        <div class="auth-footer">
            Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener">Trijaya Solution</a>
        </div>
    </div>
</div>
@endsection
