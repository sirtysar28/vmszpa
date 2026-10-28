<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
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

        $this->command?->info('Akun admin dibuat: admin@vmszpa.com / Admin@Zpa2026');
        $this->command?->warn('PENTING: Segera ganti password default ini di lingkungan produksi!');
    }
}
