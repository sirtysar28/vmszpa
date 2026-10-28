<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Guest;
use App\Models\Visit;
use App\Support\SendVisitNotification;
use Illuminate\Http\Request;

/**
 * Controller KIOSK — layar tablet di pos penerima tamu.
 * Dioperasikan akun khusus "Penerima Tamu" (login wajib),
 * dibatasi rate-limit & tanpa upload file.
 */
class KioskController extends Controller
{
    /* ---------- Layar Check-in (home kiosk) ---------- */

    public function index()
    {
        $today = now()->startOfDay();

        return view('kiosk.index', [
            'checkinCount' => Visit::whereDate('check_in_at', $today)->count(),
            'checkoutCount' => Visit::whereDate('check_out_at', $today)->count(),
            'didalamCount' => Visit::whereNull('check_out_at')->count(),
        ]);
    }

    /* ---------- Layar Daftar Tamu ---------- */

    public function daftar()
    {
        return view('kiosk.daftar');
    }

    /* ---------- Registrasi tamu baru ---------- */

    public function registrasi()
    {
        return view('kiosk.registrasi');
    }

    public function storeRegistrasi(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'vehicle_type' => ['required', 'in:Motor,Mobil,Truck'],
            'company' => ['nullable', 'string', 'max:255'],
            'identity_type' => ['required', 'in:KTP,SIM,Passport,Lainnya'],
            'identity_number' => ['required', 'string', 'max:60'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid.',
            'vehicle_type.required' => 'Jenis kendaraan wajib dipilih.',
            'identity_type.required' => 'Jenis identitas wajib dipilih.',
            'identity_number.required' => 'Nomor identitas wajib diisi.',
        ]);

        $guest = Guest::create($data + ['guest_code' => Guest::generateCode()]);

        return redirect()
            ->route('kiosk.checkin.form', $guest)
            ->with('kiosk_success', 'Registrasi berhasil! Kode Tamu Anda: '.$guest->guest_code);
    }

    /* ---------- Sudah pernah daftar ---------- */

    public function cari(Request $request)
    {
        $guests = collect();

        if ($request->filled(['name', 'phone'])) {
            $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{4,20}$/'],
            ], ['phone.regex' => 'Format nomor HP tidak valid.']);

            $guests = Guest::query()
                ->where('name', 'like', '%'.trim((string) $request->query('name')).'%')
                ->where('phone', 'like', '%'.trim((string) $request->query('phone')).'%')
                ->withCount(['visits' => fn ($q) => $q->whereNotNull('check_out_at')])
                ->limit(10)
                ->orderBy('name')
                ->get();
        }

        return view('kiosk.cari', ['guests' => $guests]);
    }

    /* ---------- Form Check-In kunjungan ---------- */

    public function checkinForm(Guest $guest)
    {
        if ($guest->activeVisit()) {
            return redirect()
                ->route('kiosk.index')
                ->with('kiosk_error', 'Anda sedang tercatat Check-In. Silakan Check-Out terlebih dahulu.');
        }

        return view('kiosk.checkin', [
            'guest' => $guest,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function checkinStore(Request $request, Guest $guest)
    {
        if ($guest->activeVisit()) {
            return redirect()->route('kiosk.index')->with('kiosk_error', 'Anda sudah tercatat Check-In.');
        }

        $data = $request->validate([
            'purpose' => ['required', 'string', 'max:255', 'min:3'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'vehicle_number' => ['required', 'string', 'max:30', 'min:3'],
            'meet_with' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'purpose.required' => 'Tujuan wajib diisi.',
            'department_id.required' => 'Departemen yang dituju wajib dipilih.',
            'department_id.exists' => 'Departemen tidak valid.',
            'vehicle_number.required' => 'Nomor kendaraan wajib diisi.',
            'vehicle_number.min' => 'Nomor kendaraan minimal 3 karakter.',
        ]);

        $visit = Visit::create($data + [
            'guest_id' => $guest->id,
            'check_in_at' => now(),
            'checked_in_by' => $request->user()->id, // operator penerima tamu
        ]);

        // Notifikasi email ke penerima terdaftar (gagal kirim tidak mengganggu kiosk)
        SendVisitNotification::send($visit, 'checkin');

        return redirect()
            ->route('kiosk.index')
            ->with('kiosk_success', 'Check-In berhasil! Selamat datang, '.$guest->name.'.');
    }

    /* ---------- Check-Out ---------- */

    public function checkoutIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $visits = Visit::with('guest')
            ->whereNull('check_out_at')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('guest', fn ($g) => $g->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%"))
                        ->orWhere('vehicle_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('check_in_at')
            ->paginate(10)
            ->withQueryString();

        return view('kiosk.checkout', compact('visits', 'search'));
    }

    public function checkoutStore(Request $request, Visit $visit)
    {
        if ($visit->check_out_at !== null) {
            return redirect()->route('kiosk.index')->with('kiosk_error', 'Tamu tersebut sudah Check-Out.');
        }

        $visit->update([
            'check_out_at' => now(),
            'checked_out_by' => $request->user()->id,
        ]);

        // Notifikasi email ke penerima terdaftar (gagal kirim tidak mengganggu kiosk)
        SendVisitNotification::send($visit, 'checkout');

        return redirect()
            ->route('kiosk.index')
            ->with('kiosk_success', 'Check-Out berhasil. Terima kasih, '.$visit->guest->name.'!');
    }
}
