@extends('layouts.kiosk')

@section('title', 'Daftar Tamu')

@section('content')
<div class="k-card">
    <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Daftar Tamu
    </h2>
    <p class="k-sub">Apakah Anda sudah pernah mendaftar sebelumnya?</p>

    <div class="k-choice">
        <a href="{{ route('kiosk.cari') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <b>Sudah pernah Daftar</b>
            <small>Cari data Anda dengan nama &amp; nomor HP</small>
        </a>
        <a href="{{ route('kiosk.registrasi') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            <b>Daftar Sekarang</b>
            <small>Isi formulir registrasi tamu baru</small>
        </a>
    </div>

    <div class="k-actions-row">
        <a href="{{ route('kiosk.index') }}" class="btn btn-outline" style="border-radius:14px;padding:.95rem 1.4rem;font-size:1rem">
            &larr; Kembali ke Layar Check-In
        </a>
    </div>
</div>
@endsection
