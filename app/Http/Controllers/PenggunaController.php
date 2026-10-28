<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    /** Kelola akun petugas/admin (khusus admin). */
    public function index()
    {
        $users = User::with('department')->latest()->paginate(10);

        return view('pengguna.index', compact('users'));
    }

    public function create()
    {
        return view('pengguna.form', [
            'user' => new User,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'role' => ['required', Rule::in(['admin', 'manager', 'petugas', 'penerima'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password' => 'Password harus mengandung huruf dan angka.',
        ]);

        User::create($data);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('pengguna.form', [
            'user' => $user,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'role' => ['required', Rule::in(['admin', 'manager', 'petugas', 'penerima'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password' => 'Password harus mengandung huruf dan angka.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleActive(Request $request, User $user)
    {
        // Cegah menonaktifkan diri sendiri
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['Tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status pengguna '.($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['Tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'Pengguna telah dihapus.');
    }
}
