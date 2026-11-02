{{-- Layout khusus export PDF (Dompdf) — bukan extends layouts.app --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Tamu</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; margin: 0; }
        .head { width: 100%; border-bottom: 2px solid #0b5cab; padding-bottom: 6px; margin-bottom: 10px; }
        .head h1 { margin: 0; font-size: 14pt; color: #0b5cab; }
        .head p { margin: 2px 0 0; font-size: 8.5pt; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: top; }
        th { background: #eff6ff; color: #0b5cab; font-size: 8.5pt; text-align: left; }
        td { font-size: 8.5pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .foot { margin-top: 10px; font-size: 8pt; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ config('services.company.name') }} — Data Tamu</h1>
        <p>Rekapitulasi kunjungan tamu ({{ $visits->count() }} data) &middot; Dicetak: {{ $cetak }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Tamu</th>
                <th>Nama</th>
                <th>No HP</th>
                <th>Perusahaan</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Tujuan</th>
                <th>No Kendaraan</th>
                <th>Bertemu</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($visits as $visit)
            <tr>
                <td>{{ $visit->guest->guest_code }}</td>
                <td>{{ $visit->guest->name }}</td>
                <td>{{ $visit->guest->phone }}</td>
                <td>{{ $visit->guest->company ?: '-' }}</td>
                <td>{{ $visit->check_in_at?->translatedFormat('d M Y H:i') }}</td>
                <td>{{ $visit->check_out_at?->translatedFormat('d M Y H:i') ?: '-' }}</td>
                <td>{{ $visit->purpose }}</td>
                <td>{{ mb_strtoupper((string) $visit->vehicle_number) }}</td>
                <td>{{ $visit->meet_with ?: '-' }}</td>
                <td>{{ $visit->notes ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="10">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="foot">{{ config('services.company.domain') }} &mdash; VMSZPA</div>
</body>
</html>
