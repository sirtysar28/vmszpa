@extends('layouts.app')

@section('title', 'Pengaturan Profil')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengaturan Profil</h1>
        <p>Kelola data akun Anda: nama, email, dan password login.</p>
    </div>
    <div class="actions">
        <a href="{{ route('dashboard') }}" class="btn btn-soft">&larr; Kembali ke Dashboard</a>
    </div>
</div>

<div class="grid-2">
    {{-- Data profil --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Data Profil
            </div>

            <div class="profil-current">
                <div class="avatar" style="width:52px;height:52px;font-size:1.3rem">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                <div>
                    <b>{{ $user->name }}</b>
                    <small>{{ $user->email }}</small>
                    <small class="text-mute">{{ $user->roleLabel() }}@if($user->department) &middot; {{ $user->department->name }}@endif</small>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('profil.update') }}">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Simpan Profil
                </button>
            </form>
        </div>
    </div>

    {{-- Ganti password --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ganti Password
            </div>
            <p class="text-soft" style="font-size:.87rem;margin-bottom:1.2rem">
                Password minimal 8 karakter, mengandung huruf dan angka.
            </p>

            <form method="POST" action="{{ route('profil.password') }}" autocomplete="off">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="current_password">Password Saat Ini</label>
                    <div class="input-group">
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        <button type="button" class="input-eye pw-toggle" data-target="current_password" title="Intip password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('current_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        <button type="button" class="input-eye pw-toggle" data-target="password" title="Intip password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Ulangi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ganti Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
