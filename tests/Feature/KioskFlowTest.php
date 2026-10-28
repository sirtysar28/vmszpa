<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_layar_utama_kiosk(): void
    {
        $response = $this->get('/kiosk');

        $response->assertOk();
        $response->assertSee('Welcome to');
        $response->assertSee('PT. Zinkpower Austridno');
        $response->assertSee('CHECK-IN');
        $response->assertSee('CHECK-OUT');
        $response->assertSee('Trijaya Solution');
    }

    public function test_alur_lengkap_registrasi_checkin_checkout(): void
    {
        // 1. Layar daftar tamu
        $this->get('/kiosk/daftar')->assertOk()->assertSee('Sudah pernah Daftar');

        // 2. Registrasi tamu baru
        $response = $this->post('/kiosk/registrasi', [
            'name' => 'Andi Saputra',
            'phone' => '08188888',
            'vehicle_type' => 'Mobil',
            'company' => 'PT. Andi Perkasa',
            'identity_type' => 'KTP',
            'identity_number' => '3201234567890001',
        ]);

        $guest = Guest::where('name', 'Andi Saputra')->first();
        $this->assertNotNull($guest, 'Tamu harus tersimpan');
        $this->assertMatchesRegularExpression('/^ZPA-[A-Z0-9]{6}$/', $guest->guest_code);
        $response->assertRedirect(route('kiosk.checkin.form', $guest, absolute: false));

        // 3. Form check-in kunjungan
        $this->get("/kiosk/checkin/{$guest->id}")->assertOk()->assertSee('Tujuan');

        $response = $this->post("/kiosk/checkin/{$guest->id}", [
            'purpose' => 'Meeting',
            'vehicle_number' => 'B 1234 AND',
            'meet_with' => 'Mr. X',
            'notes' => 'Diskusi kerjasama',
        ]);
        $response->assertRedirect(route('kiosk.index', absolute: false));

        $visit = Visit::where('guest_id', $guest->id)->first();
        $this->assertNotNull($visit);
        $this->assertNull($visit->check_out_at, 'Tamu masih di dalam');

        // 4. Tamu tercatat di layar check-out
        $this->get('/kiosk/checkout')->assertOk()->assertSee('Andi Saputra');

        // 5. Check-out
        $response = $this->post("/kiosk/checkout/{$visit->id}");
        $response->assertRedirect(route('kiosk.index', absolute: false));
        $this->assertNotNull($visit->fresh()->check_out_at, 'Tamu sudah check-out');
    }

    public function test_tamu_aktif_tidak_bisa_double_checkin(): void
    {
        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Budi',
            'phone' => '08177771',
            'vehicle_type' => 'Truck',
            'company' => 'PT. Budi Jaya',
            'identity_type' => 'SIM',
            'identity_number' => 'SIM12345',
        ]);

        Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Antar Barang',
            'vehicle_number' => 'B 9123 JUA',
            'check_in_at' => now(),
        ]);

        $this->get("/kiosk/checkin/{$guest->id}")
            ->assertRedirect(route('kiosk.index', absolute: false));
    }

    public function test_upload_file_di_kiosk_ditolak(): void
    {
        $this->withHeaders(['Content-Type' => 'multipart/form-data'])
            ->post('/kiosk/registrasi', ['name' => 'X'])
            ->assertStatus(302); // validasi gagal redirect, tidak 500
    }

    public function test_cari_tamu_sudah_pernah_daftar(): void
    {
        Guest::create([
            'guest_code' => 'ZPA-TEST01',
            'name' => 'Chandra',
            'phone' => '08111111',
            'company' => 'PT. Chandra Makmur',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3209',
        ]);

        $this->get('/kiosk/cari?name=chan&phone=0811')
            ->assertOk()
            ->assertSee('Chandra');
    }
}
