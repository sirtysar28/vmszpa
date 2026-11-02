<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Seeder master departemen.
 * (Update 2 Nov 2026: akun contoh manager/petugas tidak lagi dibuat —
 * pengguna tinggal Admin + Penerima Tamu; akun lain dibuat manual lewat menu Pengguna.)
 *
 * Jalankan: php artisan db:seed --class=DepartmentSeeder
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Umum',          'code' => 'UM'],
            ['name' => 'Keuangan',      'code' => 'FIN'],
            ['name' => 'SDM / HRD',     'code' => 'HRD'],
            ['name' => 'Produksi',      'code' => 'PRD'],
            ['name' => 'Logistik',      'code' => 'LOG'],
            ['name' => 'IT',            'code' => 'IT'],
            ['name' => 'HSE',           'code' => 'HSE'],
            ['name' => 'Security',      'code' => 'SEC'],
        ];

        foreach ($departments as $d) {
            Department::updateOrCreate(['name' => $d['name']], ['code' => $d['code']]);
        }

        $this->command?->info('Master departemen dibuat/diperbarui ('.count($departments).' departemen).');
    }
}
