@extends('layouts.app')

@section('title', 'Data Tamu')

@section('content')
<div class="page-head">
    <div>
        <h1>Data Tamu</h1>
        <p>Rekapitulasi seluruh kunjungan tamu. Check-in &amp; check-out dilakukan dari <a href="{{ route('kiosk.index') }}" target="_blank" rel="noopener">Kiosk</a>.</p>
    </div>
    <div class="actions">
        <a href="{{ route('master-tamu.index') }}" class="btn btn-soft">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            Master Tamu
        </a>
        <a href="{{ route('tamu.export', request()->query()) }}" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Export CSV
        </a>
        <button type="button" class="btn btn-soft js-print">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </button>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('tamu.index') }}" class="filter-bar no-print">
    <div class="fg" style="flex:2">
        <label class="form-label" for="q">Cari</label>
        <input type="text" id="q" name="q" value="{{ $search }}" class="form-control"
               placeholder="Nama, no HP, perusahaan, kode tamu, kendaraan…">
    </div>
    <div class="fg">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">Semua</option>
            <option value="didalam" @selected(request('status') === 'didalam')">Di Dalam</option>
            <option value="selesai" @selected(request('status') === 'selesai')">Sudah Check-out</option>
        </select>
    </div>
    <div class="fg">
        <label class="form-label" for="dari">Dari Tanggal</label>
        <input type="date" id="dari" name="dari" value="{{ request('dari') }}" class="form-control">
    </div>
    <div class="fg">
        <label class="form-label" for="sampai">Sampai</label>
        <input type="date" id="sampai" name="sampai" value="{{ request('sampai') }}" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Filter
    </button>
</form>

{{-- Tabel --}}
<div class="card">
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Tamu</th>
                        <th>Tamu</th>
                        <th>No HP</th>
                        <th>Perusahaan</th>
                        <th>Departemen</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Tujuan</th>
                        <th>No. Kendaraan</th>
                        <th>Bertemu</th>
                        <th>Catatan</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($visits as $visit)
                    <tr>
                        <td><span class="badge badge-code">{{ $visit->guest->guest_code }}</span></td>
                        <td>
                            <div class="cell-main">{{ $visit->guest->name }}</div>
                            <div class="cell-sub">{{ $visit->guest->identity_type }} {{ $visit->guest->identity_number ? '· '.mb_substr($visit->guest->identity_number, 0, 2).'****'.mb_substr($visit->guest->identity_number, -2) : '' }}</div>
                        </td>
                        <td>{{ $visit->guest->phone }}</td>
                        <td>{{ $visit->guest->company ?: '—' }}</td>
                        <td>{{ $visit->department?->name ?: '—' }}</td>
                        <td>{{ $visit->check_in_at->translatedFormat('d M Y H:i') }}</td>
                        <td>{{ $visit->check_out_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                        <td>{{ $visit->purpose }}</td>
                        <td>
                            <div class="cell-main">{{ $visit->vehicle_number }}</div>
                            <div class="cell-sub">{{ $visit->guest->vehicle_type }}</div>
                        </td>
                        <td>{{ $visit->meet_with ?: '—' }}</td>
                        <td>{{ $visit->notes ?: '—' }}</td>
                        <td class="no-print">
                            <div class="flex wrap gap-2">
                                @if (auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('tamu.destroy', $visit) }}"
                                          data-confirm="Hapus data kunjungan {{ addslashes($visit->guest->name) }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus data">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/></svg>
                            Tidak ada data yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $visits->links() }}
    </div>
</div>
@endsection
