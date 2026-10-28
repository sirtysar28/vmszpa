@extends('layouts.kiosk')

@section('title', 'Check-in')

@section('content')
<div class="k-card">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        Check-In Kunjungan
    </h2>
    <p class="k-sub">
        <b>{{ $guest->name }}</b>
        &middot; {{ $guest->phone }}
        &middot; {{ $guest->company ?: 'Tanpa perusahaan' }}
        &middot; <span class="badge-code">{{ $guest->guest_code }}</span>
    </p>

    @if ($errors->any())
        <div class="k-alert error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('kiosk.checkin.store', $guest) }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="purpose">Tujuan <span class="req">*</span></label>
            <input type="text" id="purpose" name="purpose" value="{{ old('purpose') }}"
                   class="form-control @error('purpose') is-invalid @enderror"
                   placeholder="Contoh: Meeting / Ambil Barang / Antar Barang" required>
            @error('purpose')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <div class="chips">
                <button type="button" class="chip" data-purpose="Meeting">Meeting</button>
                <button type="button" class="chip" data-purpose="Ambil Barang">Ambil Barang</button>
                <button type="button" class="chip" data-purpose="Antar Barang">Antar Barang</button>
                <button type="button" class="chip" data-purpose="Delivery">Delivery</button>
                <button type="button" class="chip" data-purpose="Kunjungan Pribadi">Kunjungan Pribadi</button>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="vehicle_number">Nomor Kendaraan <span class="req">*</span></label>
                <input type="text" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number') }}"
                       class="form-control @error('vehicle_number') is-invalid @enderror"
                       placeholder="Contoh: B 1234 AND" style="text-transform:uppercase" required>
                @error('vehicle_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <p class="form-hint">Kendaraan: {{ $guest->vehicle_type ?: '-' }}</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="meet_with">Bertemu Dengan Siapa</label>
                <input type="text" id="meet_with" name="meet_with" value="{{ old('meet_with') }}"
                       class="form-control" placeholder="Contoh: Mr. X / Bpk. Andi">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes">Catatan</label>
            <textarea id="notes" name="notes" class="form-control"
                      placeholder="Contoh: Antar hitam siku 1250pcs">{{ old('notes') }}</textarea>
        </div>

        <div class="k-actions-row">
            <a href="{{ route('kiosk.index') }}" class="btn btn-outline">Kembali</a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#047857,#10b981);box-shadow:0 10px 24px rgba(4,120,87,.35)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                CHECK-IN
            </button>
        </div>
    </form>
</div>
@endsection
