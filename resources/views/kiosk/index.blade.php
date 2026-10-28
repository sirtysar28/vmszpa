@extends('layouts.kiosk')

@section('title', 'Check-in Tamu')

@section('content')
{{-- Counter hari ini --}}
<div class="k-counters">
    <div class="k-counter in">
        <div class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        </div>
        <div>
            <b>{{ $checkinCount }}</b>
            <small>CHECK-IN</small>
        </div>
        <span class="didalam">{{ $didalamCount }} di dalam</span>
    </div>
    <div class="k-counter out">
        <div class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        </div>
        <div>
            <b>{{ $checkoutCount }}</b>
            <small>CHECK-OUT</small>
        </div>
    </div>
</div>

{{-- Aksi utama --}}
<div class="k-actions">
    <a href="{{ route('kiosk.daftar') }}" class="k-big-btn blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        DAFTAR &amp; CHECK-IN
        <small>Tamu baru maupun yang sudah pernah daftar</small>
    </a>
    <a href="{{ route('kiosk.checkout') }}" class="k-big-btn orange">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        CHECK-OUT
        <small>Tamu yang sudah selesai kunjungan</small>
    </a>
</div>
@endsection
