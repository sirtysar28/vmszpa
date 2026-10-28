@extends('layouts.kiosk')

@section('title', 'Check-out Tamu')

@section('content')
<div class="k-card">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Check-Out
    </h2>
    <p class="k-sub">Daftar tamu yang sedang Check-In. Pilih nama Anda untuk melakukan Check-Out.</p>

    <form method="GET" action="{{ route('kiosk.checkout') }}" style="margin-bottom:1.2rem">
        <div class="k-search-row">
            <div class="form-group" style="margin-bottom:0">
                <input type="text" id="q" name="q" value="{{ $search }}" class="form-control"
                       placeholder="Cari nama / perusahaan / kendaraan…">
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius:14px">Cari</button>
        </div>
    </form>

    @if ($visits->count())
        <div class="k-list">
            @foreach ($visits as $visit)
                <div class="k-item">
                    <form method="POST" action="{{ route('kiosk.checkout.store', $visit) }}"
                          data-confirm="Konfirmasi CHECK-OUT untuk {{ addslashes($visit->guest->name) }}?">
                        @csrf
                        <div class="avatar-k">{{ strtoupper(mb_substr($visit->guest->name, 0, 1)) }}</div>
                        <div class="info">
                            <b>{{ $visit->guest->name }}</b>
                            <small>
                                {{ $visit->guest->company ?: 'Tanpa perusahaan' }}
                                &middot; {{ $visit->purpose }}
                                &middot; {{ $visit->vehicle_number }}
                            </small>
                            <small>Check-in: {{ $visit->check_in_at->translatedFormat('d M Y H:i') }}</small>
                        </div>
                        <span class="go">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        </span>
                    </form>
                </div>
            @endforeach
        </div>

        {{ $visits->links() }}
    @else
        <div class="k-alert error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            Tidak ada tamu yang sedang Check-In{{ $search ? ' sesuai pencarian' : '' }}.
        </div>
    @endif

    <div class="k-actions-row">
        <a href="{{ route('kiosk.index') }}" class="btn btn-outline">Kembali ke Layar Check-In</a>
    </div>
</div>
@endsection
