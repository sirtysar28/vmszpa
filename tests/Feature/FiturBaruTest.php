<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FiturBaruTest extends TestCase
{
    use RefreshDatabase;

    private function buatKunjungan(string $nama, ?int $departmentId, array $attrs = []): Visit
    {
        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => $nama,
            'phone' => '0812'.random_int(1000000, 9999999),
            'vehicle_type' => 'Motor',
            'identity_type' => 'KTP',
            'identity_number' => '32'.random_int(100000000000, 999999999999),
            ...($attrs['guest'] ?? []),
        ]);

        return Visit::create([
            'guest_id' => $guest->id,
            'purpose' => $attrs['purpose'] ?? 'Meeting',
            'vehicle_number' => 'F 1234 AB',
            'check_in_at' => $attrs['check_in_at'] ?? now(),
            'check_out_at' => $attrs['check_out_at'] ?? null,
            'department_id' => $departmentId,
        ]);
    }

    /* ===================== 1. Limit 10 list tamu ===================== */

    public function test_daftar_tamu_dibatasi_10_per_halaman(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($i = 0; $i < 18; $i++) {
            $this->buatKunjungan('Tamu Ke-'.$i, null, ['check_in_at' => now()->subMinutes($i)]);
        }

        $res = $this->actingAs($admin)->get('/data-tamu');
        $res->assertOk();

        $this->assertSame(
            10,
            substr_count($res->content(), 'badge-code'),
            'Halaman 1 harus menampilkan tepat 10 baris'
        );
        $res->assertSee('>2</a>', false); // ada tautan ke halaman 2
    }

    /* ===================== 2. Pagination pakai SVG kecil ===================== */

    public function test_pagination_menggunakan_svg_chevron(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        for ($i = 0; $i < 16; $i++) {
            $this->buatKunjungan('Tamu Hal-'.$i, null, ['check_in_at' => now()->subMinutes($i)]);
        }

        $html = $this->actingAs($admin)->get('/data-tamu')->content();

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('&lsaquo;', $html);
        $this->assertStringNotContainsString('&rsaquo;', $html);
    }

    /* ===================== 3. Notifikasi aktivitas ===================== */

    public function test_endpoint_notifikasi_dan_tandai_dibaca(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'notification_read_at' => now()->subDay()]);

        $dept = Department::create(['name' => 'Produksi', 'code' => 'PRD']);
        $this->buatKunjungan('Tamu Notif', $dept->id);

        $res = $this->actingAs($admin)->getJson('/notifikasi/data');
        $res->assertOk()
            ->assertJsonPath('unread', 1)
            ->assertJsonStructure(['unread', 'items' => [['type', 'title', 'dept', 'code', 'time', 'ago']]]);

        $items = $res->json('items');
        $this->assertSame('Check-in — Tamu Notif', $items[0]['title']);

        // Tandai dibaca
        $this->actingAs($admin)->post('/notifikasi/baca')->assertOk();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->assertSame(0, $this->actingAs($admin)->getJson('/notifikasi/data')->json('unread'));
    }

    public function test_notifikasi_admin_departemen_hanya_departemennya(): void
    {
        $deptA = Department::create(['name' => 'Keuangan', 'code' => 'FIN']);
        $deptB = Department::create(['name' => 'IT', 'code' => 'IT']);
        $manager = User::factory()->create(['role' => 'manager', 'department_id' => $deptA->id]);

        $this->buatKunjungan('Tamu Keuangan', $deptA->id);
        $this->buatKunjungan('Tamu IT', $deptB->id);

        $items = $this->actingAs($manager)->getJson('/notifikasi/data')->json('items');

        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertSame('Keuangan', $item['dept']);
        }
    }

    /* ===================== 4. Pengaturan profil ===================== */

    public function test_update_nama_dan_email_profil(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($user)->put('/profil', [
            'name' => 'Nama Baru',
            'email' => 'baru@vmszpa.com',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@vmszpa.com', $user->email);
    }

    public function test_ganti_password_salah_password_saat_ini_ditolak(): void
    {
        $user = User::factory()->create(['role' => 'petugas', 'password' => 'Rahasia123']);

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'PasswordSalah1',
            'password' => 'PasswordBaru1',
            'password_confirmation' => 'PasswordBaru1',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_ganti_password_berhasil(): void
    {
        $user = User::factory()->create(['role' => 'petugas', 'password' => 'Rahasia123']);

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'Rahasia123',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue(
            Hash::check('PasswordBaru123', $user->fresh()->password)
        );
    }

    /* ===================== 5. Scoping departemen ===================== */

    public function test_manager_departemen_hanya_melihat_data_departemennya(): void
    {
        $deptA = Department::create(['name' => 'Logistik', 'code' => 'LOG']);
        $deptB = Department::create(['name' => 'HSE', 'code' => 'HSE']);
        $manager = User::factory()->create(['role' => 'manager', 'department_id' => $deptA->id]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->buatKunjungan('Tamu Logistik 1', $deptA->id);
        $this->buatKunjungan('Tamu Logistik 2', $deptA->id);
        $this->buatKunjungan('Tamu HSE', $deptB->id);

        // Data Tamu: manager hanya lihat 2 kunjungan Logistik
        $html = $this->actingAs($manager)->get('/data-tamu')->content();
        $this->assertStringContainsString('Tamu Logistik 1', $html);
        $this->assertStringContainsString('Tamu Logistik 2', $html);
        $this->assertStringNotContainsString('Tamu HSE', $html);

        // Admin global lihat semua
        $html = $this->actingAs($admin)->get('/data-tamu')->content();
        $this->assertStringContainsString('Tamu Logistik 1', $html);
        $this->assertStringContainsString('Tamu HSE', $html);

        // Dashboard & laporan juga ter-scope
        $dash = $this->actingAs($manager)->get('/dashboard')->content();
        $this->assertStringNotContainsString('Tamu HSE', $dash);

        $lap = $this->actingAs($manager)->get('/laporan')->content();
        $this->assertStringNotContainsString('Tamu HSE', $lap);
    }

    public function test_pengguna_baru_dengan_role_manager_dan_departemen(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'SDM', 'code' => 'HRD']);

        $this->actingAs($admin)->post('/pengguna', [
            'name' => 'Manager SDM',
            'email' => 'manager.sdm@vmszpa.com',
            'password' => 'Manager@2026',
            'password_confirmation' => 'Manager@2026',
            'role' => 'manager',
            'department_id' => $dept->id,
        ])->assertRedirect()->assertSessionHas('success');

        $user = User::where('email', 'manager.sdm@vmszpa.com')->first();
        $this->assertTrue($user->isManager());
        $this->assertSame($dept->id, $user->department_id);
    }

    /* ===================== 6. PWA ===================== */

    public function test_manifest_dan_service_worker_terlayani(): void
    {
        // File statis PWA tersedia di public/ (disajikan langsung oleh web server)
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('images/icons/icon-192.png'));
        $this->assertFileExists(public_path('images/icons/icon-512.png'));
        $this->assertFileExists(public_path('images/icons/icon-maskable-512.png'));

        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('VMS ZPA', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(3, $manifest['icons']);

        $sw = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('install', $sw);
        $this->assertStringContainsString('fetch', $sw);
    }

    public function test_layout_memuat_meta_pwa_dan_notifikasi(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($user)->get('/dashboard')->content();

        $this->assertStringContainsString('manifest.webmanifest', $html);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $html);
        $this->assertStringContainsString('id="notifBtn"', $html);
        $this->assertStringContainsString('id="userMenuBtn"', $html);
        $this->assertStringContainsString('csrf-token', $html);
    }
}
