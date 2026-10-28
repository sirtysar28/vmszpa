@extends('layouts.app')

@section('title', 'Detail Kunjungan')

@section('content')
<div class="page-head">
    <div>
        <h1>Detail Kunjungan</h1>
        <p>Kode Tamu <span class="badge badge-code">{{ $visit->guest->guest_code }}</span></p>
    </div>
    <div class="actions">
        <a href="{{ route('tamu.index') }}" class="btn btn-soft">&larr; Kembali</a>
    </div>
</div>

<div class="card" style="max-width:760px">
    <div class="card-body">
        <div class="table-wrap">
            <table class="table" style="min-width:auto">
                <tbody>
                    <tr><th style="width:220px">Nama Tamu</th><td>{{ $visit->guest->name }}</td></tr>
                    <tr><th>No HP</th><td>{{ $visit->guest->phone }}</td></tr>
                    <tr><th>Perusahaan</th><td>{{ $visit->guest->company ?: '—' }}</td></tr>
                    <tr><th>Identitas</th><td>{{ $visit->guest->identity_type }} · {{ $visit->guest->identity_number }}</td></tr>
                    <tr><th>Tujuan</th><td>{{ $visit->purpose }}</td></tr>
                    <tr><th>Kendaraan</th><td>{{ $visit->guest->vehicle_type }} · {{ $visit->vehicle_number }}</td></tr>
                    <tr><th>Bertemu Dengan</th><td>{{ $visit->meet_with ?: '—' }}</td></tr>
                    <tr><th>Catatan</th><td>{{ $visit->notes ?: '—' }}</td></tr>
                    <tr><th>Tgl &amp; Jam Check-In</th><td>{{ $visit->check_in_at->translatedFormat('l, d F Y H:i') }}</td></tr>
                    <tr><th>Tgl &amp; Jam Check-Out</th><td>{{ $visit->check_out_at?->translatedFormat('l, d F Y H:i') ?? 'Masih di dalam' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
