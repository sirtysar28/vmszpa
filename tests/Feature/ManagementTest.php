<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'password' => 'Rahasia123']);
    }

    public function test_dashboard_butuh_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_bisa_melihat_dashboard_dan_data_tamu(): void
    {
        $guest = Guest::create([
            'guest_code' => 'ZPA-ABC123',
            'name' => 'Andi',
            'phone' => '08188888',
            'company' => 'PT. Andi Perkasa',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3201',
        ]);

        Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Meeting',
            'vehicle_number' => 'B 1234 AND',
            'meet_with' => 'Mr. X',
            'check_in_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');

        $this->actingAs($this->admin)
            ->get('/data-tamu')
            ->assertOk()
            ->assertSee('Andi')
            ->assertSee('ZPA-ABC123');
    }

    public function test_laporan_menampilkan_rekap(): void
    {
        $this->actingAs($this->admin)
            ->get('/laporan?dari='.now()->format('Y-m-d').'&sampai='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('Total Kunjungan');
    }

    public function test_petugas_tidak_bisa_akses_menu_pengguna(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'password' => 'Rahasia123']);

        $this->actingAs($petugas)->get('/pengguna')->assertStatus(403);
    }

    public function test_export_csv_data_tamu(): void
    {
        $this->actingAs($this->admin)
            ->get('/data-tamu-export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_security_header_aktif(): void
    {
        $response = $this->get('/login');

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString("default-src 'self'", (string) $response->headers->get('Content-Security-Policy'));
    }
}
