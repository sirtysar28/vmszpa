@extends('layouts.kiosk')

@section('title', 'Registrasi Tamu')

@section('content')
<div class="k-card">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M12 12v6"/></svg>
        Registrasi Tamu Baru
    </h2>
    <p class="k-sub">Lengkapi data diri Anda untuk mendapatkan Kode Tamu.</p>

    @if ($errors->any())
        <div class="k-alert error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('kiosk.registrasi.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="Nama sesuai identitas" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Nomor HP <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       class="form-control @error('phone') is-invalid @enderror"
                       placeholder="08xxxxxxxxxx" required>
                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="vehicle_type">Jenis Kendaraan <span class="req">*</span></label>
                <select id="vehicle_type" name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" required>
                    <option value="">-- Pilih Kendaraan --</option>
                    @foreach (['Motor', 'Mobil', 'Truck'] as $type)
                        <option value="{{ $type }}" @selected(old('vehicle_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('vehicle_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="company">Dari Perusahaan</label>
                <input type="text" id="company" name="company" value="{{ old('company') }}"
                       class="form-control" placeholder="Contoh: PT. Andi Perkasa">
                <p class="form-hint">Kosongkan bila datang perorangan.</p>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="identity_type">Jenis Identitas <span class="req">*</span></label>
                <select id="identity_type" name="identity_type" class="form-control @error('identity_type') is-invalid @enderror" required>
                    <option value="">-- Pilih Identitas --</option>
                    @foreach (['KTP', 'SIM', 'Passport', 'Lainnya'] as $type)
                        <option value="{{ $type }}" @selected(old('identity_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('identity_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="identity_number">Nomor Identitas <span class="req">*</span></label>
                <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}"
                       class="form-control @error('identity_number') is-invalid @enderror"
                       placeholder="Nomor KTP / SIM / Passport" required>
                @error('identity_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="k-actions-row">
            <a href="{{ route('kiosk.daftar') }}" class="btn btn-outline" style="border-radius:14px;padding:.95rem 1.4rem;font-size:1rem">Kembali</a>
            <button type="submit" class="btn btn-primary" style="border-radius:14px;padding:.95rem 1.6rem;font-size:1.05rem">
                Selanjutnya &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
