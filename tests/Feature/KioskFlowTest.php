<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $penerima;

    protected function setUp(): void
    {
        parent::setUp();

        // Kiosk wajib login akun "Penerima Tamu" (tamu tidak punya login)
        $this->penerima = User::factory()->create(['role' => 'penerima']);
    }

    public function test_kiosk_butuh_login(): void
    {
        $this->get('/kiosk')->assertRedirect(route('login'));
    }

    public function test_kiosk_ditolak_untuk_role_bukan_penerima(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)->get('/kiosk')->assertStatus(403);
    }

    public function test_admin_juga_bisa_membuka_kiosk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/kiosk')->assertOk();
    }

    public function test_layar_utama_kiosk(): void
    {
        $response = $this->actingAs($this->penerima)->get('/kiosk');

        $response->assertOk();
        $response->assertSee('Welcome to');
        $response->assertSee('PT. Zinkpower Austridno');
        $response->assertSee('CHECK-IN');
        $response->assertSee('CHECK-OUT');
        $response->assertSee('Trijaya Solution');
        $response->assertSee('Keluar'); // tombol keluar operator kiosk
    }

    public function test_alur_lengkap_registrasi_checkin_checkout(): void
    {
        // 1. Layar daftar tamu
        $this->actingAs($this->penerima)->get('/kiosk/daftar')->assertOk()->assertSee('Sudah pernah Daftar');

        // 2. Registrasi tamu baru
        $response = $this->actingAs($this->penerima)->post('/kiosk/registrasi', [
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
        $dept = Department::create(['name' => 'Umum Test', 'code' => 'UMT']);
        $this->actingAs($this->penerima)->get("/kiosk/checkin/{$guest->id}")->assertOk()->assertSee('Tujuan');

        $response = $this->actingAs($this->penerima)->post("/kiosk/checkin/{$guest->id}", [
            'purpose' => 'Meeting',
            'department_id' => $dept->id,
            'vehicle_number' => 'B 1234 AND',
            'meet_with' => 'Mr. X',
            'notes' => 'Diskusi kerjasama',
        ]);
        $response->assertRedirect(route('kiosk.index', absolute: false));

        $visit = Visit::where('guest_id', $guest->id)->first();
        $this->assertNotNull($visit);
        $this->assertNull($visit->check_out_at, 'Tamu masih di dalam');
        $this->assertSame($this->penerima->id, $visit->checked_in_by, 'Tercatat operator penerima tamu');

        // 4. Tamu tercatat di layar check-out
        $this->actingAs($this->penerima)->get('/kiosk/checkout')->assertOk()->assertSee('Andi Saputra');

        // 5. Check-out
        $response = $this->actingAs($this->penerima)->post("/kiosk/checkout/{$visit->id}");
        $response->assertRedirect(route('kiosk.index', absolute: false));
        $this->assertNotNull($visit->fresh()->check_out_at, 'Tamu sudah check-out');
        $this->assertSame($this->penerima->id, $visit->fresh()->checked_out_by);
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

        $this->actingAs($this->penerima)
            ->get("/kiosk/checkin/{$guest->id}")
            ->assertRedirect(route('kiosk.index', absolute: false));
    }

    public function test_upload_file_di_kiosk_ditolak(): void
    {
        $this->withHeaders(['Content-Type' => 'multipart/form-data'])
            ->actingAs($this->penerima)
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

        $this->actingAs($this->penerima)
            ->get('/kiosk/cari?name=chan&phone=0811')
            ->assertOk()
            ->assertSee('Chandra');
    }
}
