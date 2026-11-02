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
        $tujuan = trim((string) $request->query('tujuan', ''));

        // Scope: akun departemen hanya melihat kunjungan departemennya
        $base = Visit::forUser($request->user())
            ->whereDate('check_in_at', '>=', $dari)
            ->whereDate('check_in_at', '<=', $sampai)
            ->when($tujuan !== '', fn ($q) => $q->where('purpose', $tujuan));

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

        // Daftar tujuan unik utk dropdown filter (di-scope per hak akses user)
        $tujuanList = Visit::forUser($request->user())->distinct()->orderBy('purpose')->pluck('purpose');

        // Detail kunjungan
        $kunjungan = (clone $base)->with(['guest', 'department'])->latest('check_in_at')->paginate(10)->withQueryString();

        return view('laporan.index', compact(
            'dari', 'sampai', 'tujuan', 'tujuanList', 'totalKunjungan', 'totalTamuUnik',
            'totalSedangDidalam', 'rekapTujuan', 'rekapPerusahaan', 'rekapHarian', 'kunjungan'
        ));
    }

    /** Export laporan CSV — kolom mengikuti urutan tabel Detail Kunjungan. */
    public function export(Request $request)
    {
        $dari = $request->query('dari', now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->query('sampai', now()->format('Y-m-d'));
        $tujuan = trim((string) $request->query('tujuan', ''));

        $visits = Visit::with(['guest', 'department'])->forUser($request->user())
            ->whereDate('check_in_at', '>=', $dari)
            ->whereDate('check_in_at', '<=', $sampai)
            ->when($tujuan !== '', fn ($q) => $q->where('purpose', $tujuan))
            ->orderBy('check_in_at')
            ->get();

        $filename = "laporan-{$dari}-sd-{$sampai}.csv";

        return response()->streamDownload(function () use ($visits) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Nama', 'No HP', 'Perusahaan', 'Check-In', 'Check-Out', 'Tujuan', 'Bertemu', 'No Kendaraan', 'Catatan', 'Kode Tamu']);

            foreach ($visits as $v) {
                fputcsv($out, [
                    $v->guest->name,
                    $v->guest->phone,
                    $v->guest->company,
                    $v->check_in_at?->format('d M Y H:i'),
                    $v->check_out_at?->format('d M Y H:i'),
                    $v->purpose,
                    $v->meet_with,
                    mb_strtoupper((string) $v->vehicle_number),
                    $v->notes,
                    $v->guest->guest_code,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
