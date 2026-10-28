<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

/**
 * MASTER DATA TAMU — kelola data induk tamu (bukan riwayat kunjungan):
 * tambah, ubah, hapus, dan cari tamu terdaftar. Riwayat kunjungan
 * tetap di menu "Data Tamu"; check-in/out hanya lewat Kiosk.
 */
class MasterTamuController extends Controller
{
    /** Daftar master tamu — 10 baris/halaman + pencarian & filter. */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $query = Guest::query()
            ->withCount('visits')
            ->withCount(['visits as active_visits_count' => fn ($q) => $q->whereNull('check_out_at')])
            ->withMax('visits AS last_visit_at', 'check_in_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('guest_code', 'like', "%{$search}%")
                    ->orWhere('identity_number', 'like', "%{$search}%");
            });
        }

        // Filter status: sedang di dalam / tidak
        if ($request->query('status') === 'didalam') {
            $query->whereHas('visits', fn ($v) => $v->whereNull('check_out_at'));
        } elseif ($request->query('status') === 'luar') {
            $query->whereDoesntHave('visits', fn ($v) => $v->whereNull('check_out_at'));
        }

        $guests = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('master-tamu.index', compact('guests', 'search'));
    }

    public function create()
    {
        return view('master-tamu.form', ['guest' => new Guest]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        Guest::create($data + ['guest_code' => Guest::generateCode()]);

        return redirect()->route('master-tamu.index')->with('success', 'Master data tamu berhasil ditambahkan.');
    }

    public function edit(Guest $guest)
    {
        return view('master-tamu.form', ['guest' => $guest]);
    }

    public function update(Request $request, Guest $guest)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $guest->update($data);

        return redirect()->route('master-tamu.index')->with('success', 'Master data tamu berhasil diperbarui.');
    }

    /** Hapus master tamu (hanya admin) — riwayat kunjungannya ikut terhapus. */
    public function destroy(Guest $guest)
    {
        $name = $guest->name;
        $visits = $guest->visits()->count();
        $guest->delete();

        return back()->with('success', "Master data tamu {$name} ({$visits} kunjungan) telah dihapus.");
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'company' => ['nullable', 'string', 'max:255'],
            'vehicle_type' => ['required', 'in:Motor,Mobil,Truck'],
            'identity_type' => ['required', 'in:KTP,SIM,Passport,Lainnya'],
            'identity_number' => ['required', 'string', 'max:60'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid.',
            'vehicle_type.required' => 'Jenis kendaraan wajib dipilih.',
            'identity_type.required' => 'Jenis identitas wajib dipilih.',
            'identity_number.required' => 'Nomor identitas wajib diisi.',
        ];
    }
}
