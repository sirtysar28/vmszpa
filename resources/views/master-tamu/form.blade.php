@extends('layouts.app')

@section('title', $guest->exists ? 'Edit Master Tamu' : 'Tambah Master Tamu')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $guest->exists ? 'Edit Master Tamu' : 'Tambah Master Tamu' }}</h1>
        <p>{{ $guest->exists ? 'Perbarui data induk tamu '.$guest->name.'.' : 'Tambahkan data tamu baru ke master data.' }}</p>
    </div>
    <div class="actions">
        <a href="{{ route('master-tamu.index') }}" class="btn btn-soft">&larr; Kembali</a>
    </div>
</div>

<div class="card" style="max-width:680px">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $guest->exists ? route('master-tamu.update', $guest) : route('master-tamu.store') }}">
            @csrf
            @if ($guest->exists) @method('PUT') @endif

            @if ($guest->exists)
                <div class="form-group">
                    <label class="form-label">Kode Tamu</label>
                    <div><span class="badge badge-code" style="font-size:.9rem">{{ $guest->guest_code }}</span></div>
                </div>
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $guest->name) }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Nama sesuai identitas" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor HP <span class="req">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $guest->phone) }}"
                           class="form-control @error('phone') is-invalid @enderror"
                           placeholder="08xxxxxxxxxx" required>
                    @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="company">Dari Perusahaan</label>
                    <input type="text" id="company" name="company" value="{{ old('company', $guest->company) }}"
                           class="form-control" placeholder="Contoh: PT. Andi Perkasa">
                    <p class="form-hint">Kosongkan bila datang perorangan.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="vehicle_type">Jenis Kendaraan <span class="req">*</span></label>
                    <select id="vehicle_type" name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" required>
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach (['Motor', 'Mobil', 'Truck'] as $type)
                            <option value="{{ $type }}" @selected(old('vehicle_type', $guest->vehicle_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('vehicle_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="identity_type">Jenis Identitas <span class="req">*</span></label>
                    <select id="identity_type" name="identity_type" class="form-control @error('identity_type') is-invalid @enderror" required>
                        <option value="">-- Pilih Identitas --</option>
                        @foreach (['KTP', 'SIM', 'Passport', 'Lainnya'] as $type)
                            <option value="{{ $type }}" @selected(old('identity_type', $guest->identity_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('identity_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="identity_number">Nomor Identitas <span class="req">*</span></label>
                    <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number', $guest->identity_number) }}"
                           class="form-control @error('identity_number') is-invalid @enderror"
                           placeholder="Nomor KTP / SIM / Passport" required>
                    @error('identity_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                {{ $guest->exists ? 'Simpan Perubahan' : 'Tambah Tamu' }}
            </button>
        </form>
    </div>
</div>
@endsection
