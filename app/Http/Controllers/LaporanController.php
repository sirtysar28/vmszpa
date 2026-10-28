<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $dari = $request->query('dari', now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->query('sampai', now()->format('Y-m-d'));

        // Scope: akun departemen hanya melihat kunjungan departemennya
        $base = Visit::forUser($request->user())
            ->whereDate('check_in_at', '>=', $dari)
            ->whereDate('check_in_at', '<=', $sampai);

        $totalKunjungan = (clone $base)->count();
        $totalTamuUnik = (clone $base)->distinct('guest_id')->count('guest_id');
        $totalSedangDidalam = (clone $base)->whereNull('check_out_at')->count();

        // Rekap tujuan
        $rekapTujuan = (clone $base)
            ->selectRaw('purpose, COUNT(*) AS jumlah')
            ->groupBy('purpose')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        // Rekap perusahaan
        $rekapPerusahaan = Visit::forUser($request->user())
            ->join('guests', 'guests.id', '=', 'visits.guest_id')
            ->whereDate('check_in_at', '>=', $dari)
            ->whereDate('check_in_at', '<=', $sampai)
            ->selectRaw('COALESCE(guests.company, "-") AS perusahaan, COUNT(*) AS jumlah')
            ->groupBy('perusahaan')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        // Rekap harian
        $rekapHarian = (clone $base)
            ->selectRaw('DATE(check_in_at) AS tanggal, COUNT(*) AS jumlah,
                SUM(CASE WHEN check_out_at IS NULL THEN 1 ELSE 0 END) AS masih_didalam')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Detail kunjungan
        $kunjungan = (clone $base)->with(['guest', 'department'])->latest('check_in_at')->paginate(10)->withQueryString();

        return view('laporan.index', compact(
            'dari', 'sampai', 'totalKunjungan', 'totalTamuUnik',
            'totalSedangDidalam', 'rekapTujuan', 'rekapPerusahaan', 'rekapHarian', 'kunjungan'
        ));
    }

    /** Export laporan CSV. */
    public function export(Request $request)
    {
        $dari = $request->query('dari', now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->query('sampai', now()->format('Y-m-d'));

        $visits = Visit::with(['guest', 'department'])->forUser($request->user())
            ->whereDate('check_in_at', '>=', $dari)
            ->whereDate('check_in_at', '<=', $sampai)
            ->orderBy('check_in_at')
            ->get();

        $filename = "laporan-{$dari}-sd-{$sampai}.csv";

        return response()->streamDownload(function () use ($visits) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Kode Tamu', 'Nama', 'No HP', 'Perusahaan', 'Departemen', 'Check-In', 'Check-Out', 'Tujuan', 'No Kendaraan', 'Bertemu', 'Catatan']);

            foreach ($visits as $v) {
                fputcsv($out, [
                    $v->guest->guest_code,
                    $v->guest->name,
                    $v->guest->phone,
                    $v->guest->company,
                    $v->department?->name,
                    $v->check_in_at?->format('d M Y H:i'),
                    $v->check_out_at?->format('d M Y H:i'),
                    $v->purpose,
                    $v->vehicle_number,
                    $v->meet_with,
                    $v->notes,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
