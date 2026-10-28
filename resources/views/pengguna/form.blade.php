@extends('layouts.app')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>
        <p>{{ $user->exists ? 'Perbarui data akun '.$user->name.'.' : 'Buat akun petugas/administrator baru.' }}</p>
    </div>
    <div class="actions">
        <a href="{{ route('pengguna.index') }}" class="btn btn-soft">&larr; Kembali</a>
    </div>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $user->exists ? route('pengguna.update', $user) : route('pengguna.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                       class="form-control @error('name') is-invalid @enderror" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">
                        Password {{ $user->exists ? '(kosongkan bila tidak diubah)' : '' }}
                    </label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               {{ $user->exists ? '' : 'required' }} autocomplete="new-password">
                        <button type="button" class="input-eye pw-toggle" data-target="password" title="Intip password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <p class="form-hint">Minimal 8 karakter, huruf &amp; angka.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Ulangi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="petugas" @selected(old('role', $user->role) === 'petugas')>Petugas</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                {{ $user->exists ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
            </button>
        </form>
    </div>
</div>
@endsection
