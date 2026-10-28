@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengguna</h1>
        <p>Kelola akun petugas &amp; administrator VMS.</p>
    </div>
    <div class="actions">
        <a href="{{ route('pengguna.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
            Tambah Pengguna
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                {{ $errors->first() }}
            </div>
        @endif
        <div class="table-wrap">
            <table class="table" style="min-width:720px">
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>Role</th><th>Departemen</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar" style="width:34px;height:34px;font-size:.85rem;border-radius:9px">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                                <span class="cell-main">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->isAdmin())
                                <span class="badge badge-code">Administrator</span>
                            @elseif ($user->isManager())
                                <span class="badge" style="background:#fff7ed;color:#c2410c">Manager</span>
                            @elseif ($user->isPenerima())
                                <span class="badge badge-in">Penerima Tamu</span>
                            @else
                                <span class="badge badge-out">Petugas</span>
                            @endif
                        </td>
                        <td>@if ($user->department) {{ $user->department->name }} @else <span class="text-mute">Semua</span> @endif</td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge badge-in">Aktif</span>
                            @else
                                <span class="badge badge-out">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->translatedFormat('d M Y') }}</td>
                        <td>
                            <div class="flex wrap gap-2">
                                <a href="{{ route('pengguna.edit', $user) }}" class="btn btn-sm btn-soft">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    Edit
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('pengguna.toggle', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('pengguna.destroy', $user) }}"
                                          data-confirm="Hapus akun {{ addslashes($user->name) }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="table-empty">Belum ada pengguna.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection
