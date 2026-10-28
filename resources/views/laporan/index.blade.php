@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="page-head">
    <div>
        <h1>Laporan Kunjungan</h1>
        <p>Periode {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}.</p>
    </div>
    <div class="actions">
        <a href="{{ route('laporan.export', request()->query()) }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Export CSV
        </a>
        <button type="button" class="btn btn-soft js-print">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </button>
    </div>
</div>

{{-- Filter periode --}}
<form method="GET" action="{{ route('laporan.index') }}" class="filter-bar no-print">
    <div class="fg">
        <label class="form-label" for="dari">Dari Tanggal</label>
        <input type="date" id="dari" name="dari" value="{{ $dari }}" class="form-control" required>
    </div>
    <div class="fg">
        <label class="form-label" for="sampai">Sampai Tanggal</label>
        <input type="date" id="sampai" name="sampai" value="{{ $sampai }}" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Tampilkan
    </button>
</form>

{{-- Ringkasan --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="ico ico-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        </div>
        <div><b>{{ number_format($totalKunjungan) }}</b><small>Total Kunjungan</small></div>
    </div>
    <div class="stat-card">
        <div class="ico ico-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div><b>{{ number_format($totalTamuUnik) }}</b><small>Tamu Unik</small></div>
    </div>
    <div class="stat-card">
        <div class="ico ico-amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <div><b>{{ number_format($totalSedangDidalam) }}</b><small>Belum Check-out</small></div>
    </div>
</div>

<div class="grid-2">
    {{-- Rekap tujuan --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Tujuan Kunjungan Terbanyak
            </div>
            @forelse ($rekapTujuan as $row)
                <div class="user-row">
                    <div class="u-info"><b>{{ $row->purpose }}</b></div>
                    <span class="badge badge-in">{{ $row->jumlah }}x</span>
                </div>
            @empty
                <p class="text-mute">Tidak ada data pada periode ini.</p>
            @endforelse
        </div>
    </div>

    {{-- Rekap perusahaan --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="8" width="16" height="14" rx="2"/><path d="M2 20h20"/><path d="M12 4v4"/><path d="M8 4h8"/></svg>
                Perusahaan Pengunjung Terbanyak
            </div>
            @forelse ($rekapPerusahaan as $row)
                <div class="user-row">
                    <div class="u-info"><b>{{ $row->perusahaan }}</b></div>
                    <span class="badge badge-in">{{ $row->jumlah }}x</span>
                </div>
            @empty
                <p class="text-mute">Tidak ada data pada periode ini.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Rekap harian --}}
<div class="card mt-2">
    <div class="card-body">
        <div class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
            Rekap Harian
        </div>
        <div class="table-wrap">
            <table class="table" style="min-width:520px">
                <thead>
                    <tr><th>Tanggal</th><th>Total Kunjungan</th><th>Masih Di Dalam</th><th>Selesai</th></tr>
                </thead>
                <tbody>
                @forelse ($rekapHarian as $row)
                    <tr>
                        <td class="cell-main">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('l, d M Y') }}</td>
                        <td>{{ $row->jumlah }}</td>
                        <td>{{ $row->masih_didalam }}</td>
                        <td>{{ $row->jumlah - $row->masih_didalam }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-empty">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail kunjungan --}}
<div class="card mt-2">
    <div class="card-body">
        <div class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
            Detail Kunjungan
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th><th>Tamu</th><th>Perusahaan</th>
                        <th>Check-In</th><th>Check-Out</th><th>Tujuan</th><th>Kendaraan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($kunjungan as $visit)
                    <tr>
                        <td><span class="badge badge-code">{{ $visit->guest->guest_code }}</span></td>
                        <td class="cell-main">{{ $visit->guest->name }}</td>
                        <td>{{ $visit->guest->company ?: '—' }}</td>
                        <td>{{ $visit->check_in_at->translatedFormat('d M H:i') }}</td>
                        <td>{{ $visit->check_out_at?->translatedFormat('d M H:i') ?? '—' }}</td>
                        <td>{{ $visit->purpose }}</td>
                        <td>{{ $visit->vehicle_number }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="table-empty">Tidak ada kunjungan pada periode ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $kunjungan->links() }}
    </div>
</div>
@endsection
