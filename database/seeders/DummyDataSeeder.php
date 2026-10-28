<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

/**
 * Seeder data dummy untuk demo dashboard:
 * - Tamu terdaftar
 * - Kunjungan 7 hari terakhir (grafik check-in / check-out terisi)
 * - Tamu yang sedang berada di dalam (check-out belum dilakukan)
 *
 * Jalankan: php artisan db:seed --class=DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Visit::count() > 0) {
            $this->command?->info('Data kunjungan sudah ada — seeder dummy dilewati. (Hapus data kunjungan dulu bila ingin ulang.)');

            return;
        }

        $admin = User::where('role', 'admin')->first();
        $deptIds = Department::pluck('id')->all();

        // ====== 1. Data tamu ======
        $guests = [
            ['name' => 'Budi Santoso',    'phone' => '0812-3456-7801', 'company' => 'PT Wijaya Karya',      'vehicle_type' => 'Mobil',  'identity_type' => 'KTP', 'vehicle_number' => 'B 1234 XYZ'],
            ['name' => 'Siti Rahmawati',  'phone' => '0812-3456-7802', 'company' => 'PT Adhi Karya',        'vehicle_type' => 'Motor',  'identity_type' => 'SIM', 'vehicle_number' => 'F 5678 KL'],
            ['name' => 'Ahmad Fauzi',     'phone' => '0812-3456-7803', 'company' => 'CV Sumber Makmur',     'vehicle_type' => 'Motor',  'identity_type' => 'KTP', 'vehicle_number' => 'F 2233 MN'],
            ['name' => 'Dewi Lestari',    'phone' => '0812-3456-7804', 'company' => 'PT Berau Coal',        'vehicle_type' => 'Mobil',  'identity_type' => 'KTP', 'vehicle_number' => 'B 8765 OP'],
            ['name' => 'Rizky Pratama',   'phone' => '0812-3456-7805', 'company' => 'Dinas Pekerjaan Umum', 'vehicle_type' => 'Mobil',  'identity_type' => 'KTP', 'vehicle_number' => 'H 4455 QR'],
            ['name' => 'Joko Susilo',     'phone' => '0812-3456-7806', 'company' => 'PT Trijaya Solution',  'vehicle_type' => 'Motor',  'identity_type' => 'SIM', 'vehicle_number' => 'F 9911 ST'],
            ['name' => 'Indah Permata',   'phone' => '0812-3456-7807', 'company' => 'Bank Permata',         'vehicle_type' => 'Mobil',  'identity_type' => 'KTP', 'vehicle_number' => 'B 7788 UV'],
            ['name' => 'Hendra Gunawan',  'phone' => '0812-3456-7808', 'company' => 'PT PLN Persero',       'vehicle_type' => 'Motor',  'identity_type' => 'KTP', 'vehicle_number' => 'F 3344 WX'],
            ['name' => 'Maya Anggraini',  'phone' => '0812-3456-7809', 'company' => 'PT Telkom Indonesia',  'vehicle_type' => null,     'identity_type' => 'KTP', 'vehicle_number' => null],
            ['name' => 'Agus Salim',      'phone' => '0812-3456-7810', 'company' => 'CV Mitra Abadi',       'vehicle_type' => 'Truck',  'identity_type' => 'KTP', 'vehicle_number' => 'B 9090 YZ'],
            ['name' => 'Ratna Sari',      'phone' => '0812-3456-7811', 'company' => 'PT Semen Indonesia',   'vehicle_type' => 'Mobil',  'identity_type' => 'SIM', 'vehicle_number' => 'G 1122 AB'],
            ['name' => 'Doni Kurniawan',  'phone' => '0812-3456-7812', 'company' => 'PT Krakatau Steel',    'vehicle_type' => 'Motor',  'identity_type' => 'KTP', 'vehicle_number' => 'F 6677 CD'],
            ['name' => 'Fitri Handayani', 'phone' => '0812-3456-7813', 'company' => 'RSUD Zainal Abidin',   'vehicle_type' => null,     'identity_type' => 'KTP', 'vehicle_number' => null],
            ['name' => 'Bambang Wijaya',  'phone' => '0812-3456-7814', 'company' => 'PT Waskita Karya',     'vehicle_type' => 'Mobil',  'identity_type' => 'KTP', 'vehicle_number' => 'B 5566 EF'],
            ['name' => 'Lina Marlina',    'phone' => '0812-3456-7815', 'company' => 'PT PP Persero',        'vehicle_type' => 'Motor',  'identity_type' => 'SIM', 'vehicle_number' => 'F 8899 GH'],
            ['name' => 'Yusuf Hamdani',   'phone' => '0812-3456-7816', 'company' => 'CV Berkah Jaya',       'vehicle_type' => 'Motor',  'identity_type' => 'KTP', 'vehicle_number' => 'F 1020 IJ'],
            ['name' => 'Nina Kartika',    'phone' => '0812-3456-7817', 'company' => 'Kantor Notaris Rajab', 'vehicle_type' => null,     'identity_type' => 'Passport', 'vehicle_number' => null],
            ['name' => 'Eko Prasetyo',    'phone' => '0812-3456-7818', 'company' => 'PT Astra International', 'vehicle_type' => 'Mobil', 'identity_type' => 'KTP', 'vehicle_number' => 'B 3040 KL'],
        ];

        $guestIds = [];
        foreach ($guests as $g) {
            $guestIds[] = Guest::create([
                'guest_code' => Guest::generateCode(),
                'name' => $g['name'],
                'phone' => $g['phone'],
                'company' => $g['company'],
                'vehicle_type' => $g['vehicle_type'],
                'identity_type' => $g['identity_type'],
                'identity_number' => '32'.random_int(100000000000, 999999999999),
            ])->id;
        }

        // ====== 2. Rencana kunjungan 7 hari (agar grafik terlihat jelas) ======
        // 'in'  = jumlah check-in hari itu
        // 'out' = jumlah yang sudah check-out hari itu (sisanya masih di dalam)
        $plan = [
            6 => ['in' => 5, 'out' => 5],
            5 => ['in' => 3, 'out' => 3],
            4 => ['in' => 7, 'out' => 6],
            3 => ['in' => 4, 'out' => 4],
            2 => ['in' => 6, 'out' => 6],
            1 => ['in' => 8, 'out' => 7],
            0 => ['in' => 6, 'out' => 2], // hari ini: 2 sudah pulang, 4 masih di dalam
        ];

        $purposes = [
            'Rapat Koordinasi', 'Pengiriman Dokumen', 'Survey Lokasi', 'Pemeliharaan Alat',
            'Kunjungan Bisnis', 'Audit Internal', 'Pengambilan Sampel', 'Instalasi Perangkat',
            'Verifikasi Data', 'Serah Terima Barang',
        ];
        $meetWith = [
            'Mgr. HSE', 'Kabag Umum', 'Div. Keuangan', 'Mgr. Logistik',
            'Direktur Operasional', 'Staff IT', 'Kabag SDM', 'Mgr. Teknik',
        ];

        $guestIndex = 0;
        $visitIndex = 0;

        foreach ($plan as $daysAgo => $dayPlan) {
            $dayStart = now()->subDays($daysAgo)->startOfDay();

            for ($i = 0; $i < $dayPlan['in']; $i++, $visitIndex++) {
                $guestId = $guestIds[$guestIndex % count($guestIds)];
                $guestIndex++;

                $isStillInside = ($i >= $dayPlan['out']);
                $isToday = ($daysAgo === 0);

                // Check-in antara 07.30 - 15.30
                $checkIn = (clone $dayStart)->addHours(7)->addMinutes(random_int(30, 480));

                // Hari ini: jangan sampai check-in di masa depan
                if ($isToday && $checkIn->isFuture()) {
                    $checkIn = now()->subMinutes(random_int(40, 300));
                }

                $checkOut = null;
                if (! $isStillInside) {
                    $checkOut = (clone $checkIn)->addMinutes(random_int(45, 240));

                    // Hari ini: pastikan check-out tidak melewati waktu sekarang
                    if ($isToday && $checkOut->isFuture()) {
                        $checkOut = now()->subMinutes(random_int(5, 30));
                    }
                }

                Visit::create([
                    'guest_id' => $guestId,
                    'purpose' => $purposes[$visitIndex % count($purposes)],
                    'vehicle_number' => $guests[$guestIndex % count($guests)]['vehicle_number'],
                    'meet_with' => $meetWith[$visitIndex % count($meetWith)],
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'checked_in_by' => $admin?->id,
                    'checked_out_by' => $checkOut ? $admin?->id : null,
                    'department_id' => $deptIds[$visitIndex % max(1, count($deptIds))] ?? null,
                ]);
            }
        }

        $this->command?->info('Data dummy berhasil dibuat: '.count($guests).' tamu, '.$visitIndex.' kunjungan (7 hari terakhir).');
    }
}
