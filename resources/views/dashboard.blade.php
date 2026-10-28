@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Ringkasan kunjungan tamu {{ config('services.company.name') }} hari ini, {{ now()->translatedFormat('l, d F Y') }}.</p>
    </div>
    <div class="actions">
        <a href="{{ route('tamu.index') }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Data Tamu
        </a>
        <a href="{{ route('kiosk.index') }}" class="btn btn-primary" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
            Buka Kiosk Tablet
        </a>
    </div>
</div>

{{-- Statistik --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="ico ico-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div><b>{{ number_format($stats['tamu_terdaftar'], 0, ',', '.') }}</b><small>Total Tamu Terdaftar</small></div>
    </div>
    <div class="stat-card">
        <div class="ico ico-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        </div>
        <div><b>{{ $stats['sedang_didalam'] }}</b><small>Sedang Check-In (Di Dalam)</small></div>
    </div>
    <div class="stat-card">
        <div class="ico ico-amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
        </div>
        <div><b>{{ $stats['checkin_hari_ini'] }}</b><small>Check-in Hari Ini</small></div>
    </div>
    <div class="stat-card">
        <div class="ico ico-red">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        </div>
        <div><b>{{ $stats['checkout_hari_ini'] }}</b><small>Check-out Hari Ini</small></div>
    </div>
</div>

<div class="grid-2">
    {{-- Grafik 7 hari --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                Aktivitas 7 Hari Terakhir
            </div>
            <div class="chart-legend">
                <span class="li-in"><i></i>Check-in</span>
                <span class="li-out"><i></i>Check-out</span>
            </div>
            <div class="chart">
                @foreach ($chart as $row)
                    @php $maxVal = max(1, max(array_column($chart, 'checkin')), max(array_column($chart, 'checkout'))); @endphp
                    <div class="bar-col">
                        <div class="bars">
                            <div class="bar in" title="Check-in: {{ $row['checkin'] }}" style="height: {{ round($row['checkin'] / $maxVal * 100) }}%"></div>
                            <div class="bar out" title="Check-out: {{ $row['checkout'] }}" style="height: {{ round($row['checkout'] / $maxVal * 100) }}%"></div>
                        </div>
                        <small>{{ $row['label'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sedang di dalam --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Tamu yang Sedang Di Dalam ({{ $sedangDidalam->count() }})
            </div>
            @forelse ($sedangDidalam as $visit)
                <div class="user-row">
                    <div class="avatar">{{ strtoupper(mb_substr($visit->guest->name, 0, 1)) }}</div>
                    <div class="u-info">
                        <b>{{ $visit->guest->name }}</b>
                        <small>{{ $visit->guest->company ?: 'Tanpa perusahaan' }} &middot; {{ $visit->purpose }}</small>
                    </div>
                    <span class="badge badge-in">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        {{ $visit->check_in_at->diffForHumans() }}
                    </span>
                </div>
            @empty
                <p class="text-mute mt-1">Belum ada tamu yang sedang berada di dalam area.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Kunjungan terbaru --}}
<div class="card mt-2">
    <div class="card-body">
        <div class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Kunjungan Terbaru
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tamu</th>
                        <th>Tujuan</th>
                        <th>No. Kendaraan</th>
                        <th>Check-In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($terbaru as $visit)
                    <tr>
                        <td><span class="badge badge-code">{{ $visit->guest->guest_code }}</span></td>
                        <td>
                            <div class="cell-main">{{ $visit->guest->name }}</div>
                            <div class="cell-sub">{{ $visit->guest->company ?: '—' }}</div>
                        </td>
                        <td>{{ $visit->purpose }}</td>
                        <td>{{ $visit->vehicle_number }}</td>
                        <td>{{ $visit->check_in_at->translatedFormat('d M Y H:i') }}</td>
                        <td>
                            @if ($visit->check_out_at)
                                <span class="badge badge-out">Check-out {{ $visit->check_out_at->translatedFormat('H:i') }}</span>
                            @else
                                <span class="badge badge-in">Di Dalam</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="table-empty">Belum ada data kunjungan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
