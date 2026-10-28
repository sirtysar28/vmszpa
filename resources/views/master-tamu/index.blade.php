@extends('layouts.app')

@section('title', 'Master Data Tamu')

@section('content')
<div class="page-head">
    <div>
        <h1>Master Data Tamu</h1>
        <p>Data induk tamu terdaftar. Tambah/ubah data tamu di sini; riwayat kunjungan ada di menu Data Tamu.</p>
    </div>
    <div class="actions">
        <a href="{{ route('master-tamu.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            Tambah Tamu
        </a>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('master-tamu.index') }}" class="filter-bar no-print">
    <div class="fg" style="flex:2">
        <label class="form-label" for="q">Cari</label>
        <input type="text" id="q" name="q" value="{{ $search }}" class="form-control"
               placeholder="Nama, no HP, perusahaan, kode tamu, identitas…">
    </div>
    <div class="fg">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">Semua</option>
            <option value="didalam" @selected(request('status') === 'didalam')">Sedang Di Dalam</option>
            <option value="luar" @selected(request('status') === 'luar')">Tidak Di Dalam</option>
        </select>
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
            <table class="table" style="min-width:820px">
                <thead>
                    <tr>
                        <th>Kode Tamu</th>
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>Perusahaan</th>
                        <th>Identitas</th>
                        <th>Kendaraan</th>
                        <th>Total Kunjungan</th>
                        <th>Terakhir Kunjungan</th>
                        <th>Status</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($guests as $guest)
                    <tr>
                        <td><span class="badge badge-code">{{ $guest->guest_code }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar" style="width:34px;height:34px;font-size:.85rem;border-radius:9px">{{ strtoupper(mb_substr($guest->name, 0, 1)) }}</div>
                                <span class="cell-main">{{ $guest->name }}</span>
                            </div>
                        </td>
                        <td>{{ $guest->phone }}</td>
                        <td>{{ $guest->company ?: '—' }}</td>
                        <td>
                            <div class="cell-main">{{ $guest->identity_type }}</div>
                            <div class="cell-sub">{{ $guest->identity_number }}</div>
                        </td>
                        <td>{{ $guest->vehicle_type ?: '—' }}</td>
                        <td>{{ $guest->visits_count }}x</td>
                        <td>{{ $guest->last_visit_at ? \Carbon\Carbon::parse($guest->last_visit_at)->translatedFormat('d M Y') : '—' }}</td>
                        <td>
                            @if ($guest->active_visits_count > 0)
                                <span class="badge badge-in">Di Dalam</span>
                            @else
                                <span class="badge badge-out">Di Luar</span>
                            @endif
                        </td>
                        <td class="no-print">
                            <div class="flex wrap gap-2">
                                <a href="{{ route('tamu.index', ['q' => $guest->name]) }}" class="btn btn-sm btn-soft" title="Lihat riwayat kunjungan">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                    Riwayat
                                </a>
                                <a href="{{ route('master-tamu.edit', $guest) }}" class="btn btn-sm btn-soft" title="Edit data tamu">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    Edit
                                </a>
                                @if (auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('master-tamu.destroy', $guest) }}"
                                          data-confirm="Hapus master data {{ addslashes($guest->name) }}? Seluruh riwayat kunjungannya ({{ $guest->visits_count }}) juga akan terhapus.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus data tamu">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2\"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="table-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/></svg>
                            Belum ada master data tamu yang cocok.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $guests->links() }}
    </div>
</div>
@endsection
