<?php

namespace Tests\Feature;

use App\Mail\VisitNotification;
use App\Models\Department;
use App\Models\Guest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use App\Support\SendVisitNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MasterTamuSmtpTest extends TestCase
{
    use RefreshDatabase;

    /* ===================== MASTER DATA TAMU ===================== */

    public function test_master_tamu_dibatasi_10_per_halaman(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($i = 0; $i < 14; $i++) {
            Guest::create([
                'guest_code' => Guest::generateCode(),
                'name' => 'Master Tamu '.$i,
                'phone' => '0812'.random_int(1000000, 9999999),
                'vehicle_type' => 'Motor',
                'identity_type' => 'KTP',
                'identity_number' => '32'.random_int(100000000000, 999999999999),
            ]);
        }

        $res = $this->actingAs($admin)->get('/master-tamu');
        $res->assertOk()->assertSee('Master Data Tamu');

        $this->assertSame(10, substr_count($res->content(), 'badge-code'), 'Master tamu 10 baris/halaman');
    }

    public function test_admin_bisa_menambah_dan_mengubah_master_tamu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/master-tamu', [
            'name' => 'Eka Wijaya',
            'phone' => '081399998888',
            'company' => 'PT. Eka Sentosa',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3201234500987001',
        ])->assertRedirect(route('master-tamu.index', absolute: false));

        $guest = Guest::where('name', 'Eka Wijaya')->first();
        $this->assertNotNull($guest);

        $this->actingAs($admin)->put("/master-tamu/{$guest->id}", [
            'name' => 'Eka Wijaya S.',
            'phone' => '081399997777',
            'company' => 'PT. Eka Jaya',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3201234500987001',
        ])->assertRedirect(route('master-tamu.index', absolute: false));

        $this->assertSame('Eka Wijaya S.', $guest->fresh()->name);
    }

    public function test_hapus_master_tamu_hanya_admin(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Hapus Saya',
            'phone' => '0811000011',
            'vehicle_type' => 'Motor',
            'identity_type' => 'KTP',
            'identity_number' => '3201',
        ]);

        $this->actingAs($petugas)->delete("/master-tamu/{$guest->id}")->assertStatus(403);
        $this->actingAs($admin)->delete("/master-tamu/{$guest->id}")->assertRedirect();
        $this->assertNull($guest->fresh());
    }

    /* ===================== CHECK-IN/OUT HANYA DARI KIOSK ===================== */

    public function test_force_checkout_dari_dashboard_sudah_dihapus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Tamu Dalam',
            'phone' => '0811000022',
            'vehicle_type' => 'Motor',
            'identity_type' => 'KTP',
            'identity_number' => '3202',
        ]);
        $visit = Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Meeting',
            'vehicle_number' => 'F 1 A',
            'check_in_at' => now(),
        ]);

        // Route force-checkout sudah tidak ada → 404
        $this->actingAs($admin)->post("/data-tamu/{$visit->id}/force-checkout")->assertStatus(404);
        $this->assertNull($visit->fresh()->check_out_at);
    }

    /* ===================== PENGATURAN SMTP ===================== */

    public function test_halaman_pengaturan_smtp_hanya_admin(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($petugas)->get('/pengaturan/smtp')->assertStatus(403);
        $this->actingAs($admin)->get('/pengaturan/smtp')->assertOk()->assertSee('Konfigurasi Mail Server');
    }

    public function test_simpan_pengaturan_smtp_dan_terapkan_ke_config(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/pengaturan/smtp', [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.vmszpa.com',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_username' => 'no-reply@vmszpa.com',
            'mail_password' => 'Rahasia123',
            'mail_from_address' => 'no-reply@vmszpa.com',
            'mail_from_name' => 'VMS ZPA',
            'mail_recipients' => 'security@vmszpa.com, manager.umum@vmszpa.com',
        ])->assertRedirect();

        $this->assertSame('smtp', Setting::get('mail_mailer'));
        $this->assertSame('smtp.vmszpa.com', Setting::get('mail_host'));
        $this->assertSame('Rahasia123', Setting::decrypt(Setting::get('mail_password')));
        $this->assertSame(['security@vmszpa.com', 'manager.umum@vmszpa.com'], SendVisitNotification::recipients());
    }

    public function test_validasi_email_penerima_smtp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/pengaturan/smtp', [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.vmszpa.com',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@vmszpa.com',
            'mail_from_name' => 'VMS ZPA',
            'mail_recipients' => 'bukan-email-valid',
        ])->assertSessionHasErrors('mail_recipients');
    }

    /* ===================== NOTIFIKASI EMAIL CHECK-IN/OUT ===================== */

    public function test_checkin_mengirim_email_notifikasi_html(): void
    {
        Mail::fake();

        $penerima = User::factory()->create(['role' => 'penerima']);
        Setting::setMany([
            'mail_mailer' => 'smtp',
            'mail_recipients' => 'security@vmszpa.com',
        ]);

        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Email Tamu',
            'phone' => '0811000033',
            'company' => 'PT. Email Kirim',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3203',
        ]);
        $dept = Department::create(['name' => 'IT', 'code' => 'ITX']);

        $this->actingAs($penerima)->post("/kiosk/checkin/{$guest->id}", [
            'purpose' => 'Instalasi Perangkat',
            'department_id' => $dept->id,
            'vehicle_number' => 'B 9999 ZZ',
        ])->assertRedirect();

        Mail::assertSent(VisitNotification::class, function ($mail) {
            return $mail->type === 'checkin'
                && $mail->hasTo('security@vmszpa.com');
        });
    }

    public function test_checkout_juga_mengirim_email_notifikasi(): void
    {
        Mail::fake();

        $penerima = User::factory()->create(['role' => 'penerima']);
        Setting::setMany([
            'mail_mailer' => 'smtp',
            'mail_recipients' => 'security@vmszpa.com',
        ]);

        $guest = Guest::create([
            'guest_code' => Guest::generateCode(),
            'name' => 'Email Checkout',
            'phone' => '0811000044',
            'vehicle_type' => 'Motor',
            'identity_type' => 'SIM',
            'identity_number' => '3204',
        ]);
        $visit = Visit::create([
            'guest_id' => $guest->id,
            'purpose' => 'Ambil Barang',
            'vehicle_number' => 'F 2222 QQ',
            'check_in_at' => now()->subHour(),
        ]);

        $this->actingAs($penerima)->post("/kiosk/checkout/{$visit->id}")->assertRedirect();

        Mail::assertSent(VisitNotification::class, function ($mail) {
            return $mail->type === 'checkout';
        });
    }
}
