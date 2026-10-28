<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder departemen + akun contoh per departemen:
 * - manager  : atasan/manajer departemen (lihat data kunjungan departemen sendiri)
 * - petugas  : petugas/sekretariat departemen
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
            $dept = Department::updateOrCreate(['name' => $d['name']], ['code' => $d['code']]);

            // Manager tiap departemen
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $d['name']));
            User::updateOrCreate(
                ['email' => "manager.{$slug}@vmszpa.com"],
                [
                    'name' => 'Manager '.$d['name'],
                    'password' => 'Manager@2026',
                    'role' => 'manager',
                    'department_id' => $dept->id,
                    'is_active' => true,
                ],
            );
        }

        // Petugas contoh (departemen Umum & Security)
        foreach ([['umum', 'Umum'], ['security', 'Security']] as [$slug, $deptName]) {
            $dept = Department::where('name', $deptName)->first();
            User::updateOrCreate(
                ['email' => "petugas.{$slug}@vmszpa.com"],
                [
                    'name' => 'Petugas '.$deptName,
                    'password' => 'Petugas@2026',
                    'role' => 'petugas',
                    'department_id' => $dept->id,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('Departemen + akun manager/petugas contoh dibuat.');
        $this->command?->table(
            ['Email', 'Password', 'Role'],
            [
                ['manager.umum@vmszpa.com', 'Manager@2026', 'Manager Umum'],
                ['manager.keuangan@vmszpa.com', 'Manager@2026', 'Manager Keuangan'],
                ['petugas.umum@vmszpa.com', 'Petugas@2026', 'Petugas Umum'],
                ['petugas.security@vmszpa.com', 'Petugas@2026', 'Petugas Security'],
            ],
        );
    }
}
