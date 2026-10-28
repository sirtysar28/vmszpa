<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Mail\VisitNotification;
use App\Models\Department;
use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Pastikan semua halaman ter-render tanpa error (admin, petugas, penerima kiosk). */
class RenderSemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_halaman_management_terrender(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petugas = User::factory()->create(['role' => 'petugas']);

        $dept = Department::create(['name' => 'Umum', 'code' => 'UM']);
        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Tamu Render',
            'phone' => '0811000099',
            'company' => 'PT. Render',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3209',
        ]);
        $visit = Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Meeting',
            'vehicle_number' => 'B 1 RE',
            'check_in_at' => now(),
            'department_id' => $dept->id,
        ]);

        $halamanAdmin = [
            '/dashboard',
            '/data-tamu',
            '/data-tamu/'.$visit->id,
            '/master-tamu',
            '/master-tamu/baru',
            '/master-tamu/'.$guest->id.'/edit',
            '/laporan',
            '/pengguna',
            '/pengguna/baru',
            '/pengguna/'.$petugas->id.'/edit',
            '/pengaturan/smtp',
            '/profil',
        ];

        foreach ($halamanAdmin as $url) {
            $this->actingAs($admin)->get($url)->assertOk("Halaman {$url} gagal dirender");
        }

        // Petugas: tanpa menu admin
        foreach (['/dashboard', '/data-tamu', '/master-tamu', '/laporan', '/profil'] as $url) {
            $this->actingAs($petugas)->get($url)->assertOk("Halaman {$url} gagal dirender");
        }
    }

    public function test_semua_layar_kiosk_terrender(): void
    {
        $penerima = User::factory()->create(['role' => 'penerima']);

        foreach (['/kiosk', '/kiosk/daftar', '/kiosk/registrasi', '/kiosk/cari', '/kiosk/checkout'] as $url) {
            $this->actingAs($penerima)->get($url)->assertOk("Layar kiosk {$url} gagal dirender");
        }
    }

    public function test_penerima_dialihkan_dari_management_ke_kiosk(): void
    {
        $penerima = User::factory()->create(['role' => 'penerima']);

        $this->actingAs($penerima)->get('/dashboard')->assertRedirect(route('kiosk.index', absolute: false));
        $this->actingAs($penerima)->get('/data-tamu')->assertRedirect(route('kiosk.index', absolute: false));
        $this->actingAs($penerima)->get('/laporan')->assertRedirect(route('kiosk.index', absolute: false));
    }

    public function test_email_notifikasi_terender_html_rapi(): void
    {
        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Tamu Email',
            'phone' => '0811000077',
            'company' => 'PT. Email Rapi',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3207',
        ]);
        $visit = Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Meeting',
            'vehicle_number' => 'B 7777 EE',
            'meet_with' => 'Mgr. Teknik',
            'notes' => 'Bawa sampel',
            'check_in_at' => now(),
            'department_id' => Department::create(['name' => 'Teknik', 'code' => 'TK'])->id,
        ]);

        $html = (new VisitNotification($visit, 'checkin'))->render();
        $this->assertStringContainsString('CHECK-IN', $html);
        $this->assertStringContainsString($guest->name, $html);
        $this->assertStringContainsString('<table', $html); // format HTML table rapi

        $htmlOut = (new VisitNotification($visit->fresh(), 'checkout'))->render();
        $this->assertStringContainsString('CHECK-OUT', $htmlOut);

        $testMail = (new SmtpTestMail('admin@vmszpa.com'))->render();
        $this->assertStringContainsString('Pengaturan SMTP Berhasil', $testMail);
    }
}
