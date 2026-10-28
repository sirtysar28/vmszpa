<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Visit;
use Illuminate\Http\Request;

class TamuController extends Controller
{
    /** Data Tamu: daftar kunjungan (check-in/check-out). */
    public function index(Request $request)
    {
        $query = Visit::with('guest');

        // Pencarian
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('guest', function ($g) use ($search) {
                    $g->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('guest_code', 'like', "%{$search}%");
                })->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->query('status') === 'didalam') {
            $query->whereNull('check_out_at');
        } elseif ($request->query('status') === 'selesai') {
            $query->whereNotNull('check_out_at');
        }

        // Filter tanggal
        if ($request->filled('dari')) {
            $query->whereDate('check_in_at', '>=', $request->query('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('check_in_at', '<=', $request->query('sampai'));
        }

        $visits = $query->latest('check_in_at')->paginate(15)->withQueryString();

        return view('tamu.index', compact('visits', 'search'));
    }

    /** Detail satu kunjungan (untuk modal / tautan kode tamu). */
    public function show(Visit $visit)
    {
        $visit->load('guest');

        return view('tamu.show', compact('visit'));
    }

    /** Force check-out dari sisi management. */
    public function forceCheckout(Request $request, Visit $visit)
    {
        if ($visit->check_out_at === null) {
            $visit->update([
                'check_out_at' => now(),
                'checked_out_by' => $request->user()->id,
            ]);

            return back()->with('success', 'Tamu '.$visit->guest->name.' berhasil di-check-out.');
        }

        return back()->withErrors(['Tamu tersebut sudah check-out.']);
    }

    /** Hapus data kunjungan (hanya admin). */
    public function destroy(Visit $visit)
    {
        $name = $visit->guest->name;
        $visit->delete();

        return back()->with('success', "Data kunjungan {$name} telah dihapus.");
    }

    /** Export CSV. */
    public function export(Request $request)
    {
        $query = Visit::with('guest')->latest('check_in_at');

        if ($request->filled('q')) {
            $s = trim((string) $request->query('q'));
            $query->where(function ($q) use ($s) {
                $q->whereHas('guest', function ($g) use ($s) {
                    $g->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('company', 'like', "%{$s}%")
                        ->orWhere('guest_code', 'like', "%{$s}%");
                })->orWhere('vehicle_number', 'like', "%{$s}%");
            });
        }
        if ($request->filled('dari')) {
            $query->whereDate('check_in_at', '>=', $request->query('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('check_in_at', '<=', $request->query('sampai'));
        }

        $filename = 'data-tamu-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 utk Excel
            fputcsv($out, ['Kode Tamu', 'Nama', 'No HP', 'Perusahaan', 'Check-In', 'Check-Out', 'Tujuan', 'No Kendaraan', 'Bertemu', 'Catatan']);

            $query->chunk(500, function ($visits) use ($out) {
                foreach ($visits as $v) {
                    fputcsv($out, [
                        $v->guest->guest_code,
                        $v->guest->name,
                        $v->guest->phone,
                        $v->guest->company,
                        $v->check_in_at?->format('d M Y H:i'),
                        $v->check_out_at?->format('d M Y H:i'),
                        $v->purpose,
                        $v->vehicle_number,
                        $v->meet_with,
                        $v->notes,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
