@extends('layouts.app')

@section('title', 'Pengaturan SMTP')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengaturan SMTP</h1>
        <p>Konfigurasi mail server untuk notifikasi email check-in / check-out tamu (body HTML otomatis).</p>
    </div>
</div>

<div class="grid-2">
    {{-- Form SMTP --}}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Konfigurasi Mail Server
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('pengaturan.smtp.update') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mail_mailer">Mode Pengiriman <span class="req">*</span></label>
                        <select id="mail_mailer" name="mail_mailer" class="form-control">
                            <option value="smtp" @selected($mailer === 'smtp')>SMTP (kirim email sungguhan)</option>
                            <option value="log" @selected($mailer === 'log')>Log (simulasi, tidak terkirim)</option>
                        </select>
                        <p class="form-hint">Gunakan <b>Log</b> untuk uji coba aplikasi tanpa mail server.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mail_encryption">Enkripsi <span class="req">*</span></label>
                        <select id="mail_encryption" name="mail_encryption" class="form-control">
                            <option value="tls" @selected($encryption === 'tls')>TLS (port 587)</option>
                            <option value="ssl" @selected($encryption === 'ssl')>SSL (port 465)</option>
                            <option value="none" @selected($encryption === 'none')>Tanpa Enkripsi (port 25)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mail_host">SMTP Host <span class="req">*</span></label>
                        <input type="text" id="mail_host" name="mail_host" value="{{ old('mail_host', $host) }}"
                               class="form-control @error('mail_host') is-invalid @enderror"
                               placeholder="smtp.gmail.com / mail.vmszpa.com">
                        @error('mail_host')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mail_port">SMTP Port <span class="req">*</span></label>
                        <input type="number" id="mail_port" name="mail_port" value="{{ old('mail_port', $port) }}"
                               class="form-control @error('mail_port') is-invalid @enderror" placeholder="587">
                        @error('mail_port')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mail_username">SMTP Username</label>
                        <input type="text" id="mail_username" name="mail_username" value="{{ old('mail_username', $username) }}"
                               class="form-control" placeholder="no-reply@vmszpa.com" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mail_password">SMTP Password</label>
                        <div class="input-group">
                            <input type="password" id="mail_password" name="mail_password"
                                   class="form-control" autocomplete="new-password"
                                   placeholder="{{ $hasPassword ? '•••••••• (tersimpan, kosongkan bila tidak diubah)' : 'Isi password SMTP' }}">
                            <button type="button" class="input-eye pw-toggle" data-target="mail_password" title="Intip password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <p class="form-hint">Disimpan terenkripsi di database.</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="mail_from_address">Alamat Pengirim <span class="req">*</span></label>
                        <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $fromAddress) }}"
                               class="form-control @error('mail_from_address') is-invalid @enderror"
                               placeholder="no-reply@vmszpa.com">
                        @error('mail_from_address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="mail_from_name">Nama Pengirim <span class="req">*</span></label>
                        <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $fromName) }}"
                               class="form-control @error('mail_from_name') is-invalid @enderror"
                               placeholder="VMS ZPA">
                        @error('mail_from_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="mail_recipients">Penerima Notifikasi <span class="req">*</span></label>
                    <textarea id="mail_recipients" name="mail_recipients" class="form-control" rows="3"
                              placeholder="security@vmszpa.com, manager.umum@vmszpa.com">{{ old('mail_recipients', $recipients) }}</textarea>
                    <p class="form-hint">Pisahkan dengan koma. Email dikirim setiap tamu check-in / check-out di kiosk.</p>
                </div>

                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    {{-- Uji coba + info --}}
    <div>
        <div class="card mb-2">
            <div class="card-body">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                    Uji Coba Kirim Email
                </div>
                <form method="POST" action="{{ route('pengaturan.smtp.test') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="test_email">Email Tujuan Uji</label>
                        <input type="email" id="test_email" name="test_email" value="{{ old('test_email') }}"
                               class="form-control @error('test_email') is-invalid @enderror"
                               placeholder="{{ $fromAddress ?: 'email@contoh.com' }}">
                        @error('test_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <p class="form-hint">Kosongkan untuk mengirim ke alamat pengirim.</p>
                    </div>
                    <button type="submit" class="btn btn-soft btn-block" {{ $mailer === 'smtp' ? '' : 'disabled' }}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
                        Kirim Email Uji
                    </button>
                    @if ($mailer !== 'smtp')
                        <p class="form-hint">Aktifkan mode SMTP lalu simpan untuk mengaktifkan tombol ini.</p>
                    @endif
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    Info
                </div>
                <ul style="padding-left:1.1rem;font-size:.85rem;color:var(--ink-soft);line-height:1.9">
                    <li>Notifikasi otomatis terkirim setiap tamu <b>check-in</b> dan <b>check-out</b> di kiosk.</li>
                    <li>Body email berformat HTML rapi berisi identitas tamu, tujuan, departemen, dan waktu.</li>
                    <li>Gmail: gunakan <b>App Password</b>, bukan password akun biasa.</li>
                    <li>Gagal kirim tidak mengganggu alur check-in / check-out tamu.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
