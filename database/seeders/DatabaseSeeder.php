<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed awal: akun administrator.
     * GANTI PASSWORD INI SEGERA setelah instalasi di produksi!
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vmszpa.com'],
            [
                'name' => 'Administrator ZPA',
                'password' => 'Admin@Zpa2026',
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        // Akun khusus operator KIOSK tablet di pos penerima tamu.
        // Tamu tidak punya login — kiosk dioperasikan akun ini.
        User::updateOrCreate(
            ['email' => 'penerima@vmszpa.com'],
            [
                'name' => 'Penerima Tamu',
                'password' => 'Penerima@2026',
                'role' => 'penerima',
                'is_active' => true,
            ],
        );

        $this->command?->info('Akun admin dibuat: admin@vmszpa.com / Admin@Zpa2026');
        $this->command?->info('Akun kiosk dibuat: penerima@vmszpa.com / Penerima@2026');
        $this->command?->warn('PENTING: Segera ganti password default ini di lingkungan produksi!');

        // Master departemen + akun manager/petugas contoh
        $this->call(DepartmentSeeder::class);

        // Data dummy untuk demo dashboard (tamu + kunjungan 7 hari terakhir)
        $this->call(DummyDataSeeder::class);
    }
}
