@extends('layouts.kiosk')

@section('title', 'Cari Tamu Terdaftar')

@section('content')
<div class="k-card">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Sudah pernah Daftar
    </h2>
    <p class="k-sub">Cari data Anda berdasarkan nama dan nomor HP yang pernah didaftarkan.</p>

    @if ($errors->any())
        <div class="k-alert error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="GET" action="{{ route('kiosk.cari') }}">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Nama <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="{{ request('name') }}"
                       class="form-control" placeholder="Nama Anda" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Nomor HP <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" value="{{ request('phone') }}"
                       class="form-control" placeholder="08xxxxxxxxxx" required>
            </div>
        </div>
        <div class="k-actions-row">
            <a href="{{ route('kiosk.daftar') }}" class="btn btn-outline">Kembali</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                Cari Tamu
            </button>
        </div>
    </form>

    @if (request()->filled(['name', 'phone']))
        <div class="mt-2" style="margin-top:1.6rem">
            <div class="form-label" style="margin-bottom:.6rem">Hasil pencarian ({{ $guests->count() }}):</div>
            @if ($guests->count())
                <div class="k-list">
                    @foreach ($guests as $guest)
                        <a class="k-item" href="{{ route('kiosk.checkin.form', $guest) }}">
                            <div class="avatar-k">{{ strtoupper(mb_substr($guest->name, 0, 1)) }}</div>
                            <div class="info">
                                <b>{{ $guest->name }}</b>
                                <small>{{ $guest->phone }} &middot; {{ $guest->company ?: 'Tanpa perusahaan' }}</small>
                            </div>
                            <span class="badge-code">{{ $guest->guest_code }}</span>
                            <span class="go">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="k-alert error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    Data tidak ditemukan. Silakan <a href="{{ route('kiosk.registrasi') }}" style="text-decoration:underline"><b>Daftar Sekarang</b></a>.
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
