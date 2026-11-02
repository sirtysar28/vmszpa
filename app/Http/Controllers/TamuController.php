<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;

class TamuController extends Controller
{
    /** Data Tamu: daftar kunjungan (check-in/check-out) — dibatasi 10 baris/halaman. */
    public function index(Request $request)
    {
        $query = Visit::with('guest')->forUser($request->user());

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

        $visits = $query->latest('check_in_at')->paginate(10)->withQueryString();

        return view('tamu.index', compact('visits', 'search'));
    }

    /** Detail satu kunjungan (untuk modal / tautan kode tamu). */
    public function show(Visit $visit)
    {
        $visit->load('guest');

        return view('tamu.show', compact('visit'));
    }

    /** Hapus data kunjungan (hanya admin). */
    public function destroy(Visit $visit)
    {
        $name = $visit->guest->name;
        $visit->delete();

        return back()->with('success', "Data kunjungan {$name} telah dihapus.");
    }

    /** Export data kunjungan: Excel (xlsx), PDF, atau CSV — mengikuti filter aktif. */
    public function export(Request $request)
    {
        $query = Visit::with('guest')->forUser($request->user())->latest('check_in_at');

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
        if ($request->query('status') === 'didalam') {
            $query->whereNull('check_out_at');
        } elseif ($request->query('status') === 'selesai') {
            $query->whereNotNull('check_out_at');
        }
        if ($request->filled('dari')) {
            $query->whereDate('check_in_at', '>=', $request->query('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('check_in_at', '<=', $request->query('sampai'));
        }

        $visits = $query->get();
        $filename = 'data-tamu-'.now()->format('Ymd-His');

        return match ($request->query('format', 'csv')) {
            'xlsx' => $this->exportExcel($visits, $filename),
            'pdf' => $this->exportPdf($visits, $filename),
            default => $this->exportCsv($visits, $filename),
        };
    }

    /** Baris tabel export (kolom lengkap, urutan konsisten di semua format). */
    private function exportRows($visits): array
    {
        $rows = [[
            'Kode Tamu', 'Nama', 'No HP', 'Perusahaan',
            'Check-In', 'Check-Out', 'Tujuan', 'No Kendaraan', 'Bertemu', 'Catatan',
        ]];

        foreach ($visits as $v) {
            $rows[] = [
                $v->guest->guest_code,
                $v->guest->name,
                $v->guest->phone,
                $v->guest->company,
                $v->check_in_at?->format('d M Y H:i'),
                $v->check_out_at?->format('d M Y H:i'),
                $v->purpose,
                mb_strtoupper((string) $v->vehicle_number),
                $v->meet_with,
                $v->notes,
            ];
        }

        return $rows;
    }

    private function exportExcel($visits, string $filename)
    {
        $xlsx = SimpleXLSXGen::fromArray($this->exportRows($visits));

        $tmp = tempnam(sys_get_temp_dir(), 'vms-export-').'.xlsx';
        $xlsx->saveAs($tmp);

        return response()->download(
            $tmp,
            $filename.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        )->deleteFileAfterSend(true);
    }

    private function exportPdf($visits, string $filename)
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml(view('tamu.export-pdf', [
            'visits' => $visits,
            'cetak' => now()->translatedFormat('d M Y H:i'),
        ])->render());
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    private function exportCsv($visits, string $filename)
    {
        return response()->streamDownload(function () use ($visits) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 utk Excel

            foreach ($this->exportRows($visits) as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
