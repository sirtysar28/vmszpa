<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revisi "update 2 Nov 2026":
 * - Master Tamu: kolom Kode Tamu dihide, total & terakhir kunjungan jadi 2 baris rata tengah.
 * - Seeder hanya membuat Admin + Penerima (akun contoh manager/petugas dihapus).
 * - Dashboard: kartu Kunjungan Terbaru pindah ke atas.
 * - Data Tamu: Kode Tamu & Departemen dihide, export Excel + PDF.
 * - Kiosk: nomor kendaraan otomatis huruf besar.
 * - Laporan: Detail Kunjungan pindah ke atas, filter tujuan, kolom baru (kode paling kanan,
 *   nama tamu diklik → detail, departemen dihide).
 */
class Revisi2Nov2026Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function buatTamuDenganKunjungan(string $nama, array $attrKunjungan = []): Visit
    {
        $guest = Guest::create([
            'guest_code' => 'ZPA-'.strtoupper(substr(md5($nama.uniqid()), 0, 6)),
            'name' => $nama,
            'phone' => '0812'.random_int(1000000, 9999999),
            'company' => 'PT. Contoh',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '32'.random_int(100000000000, 999999999999),
        ]);

        return Visit::create([
            'guest_id' => $guest->id,
            'purpose' => $attrKunjungan['purpose'] ?? 'Meeting',
            'vehicle_number' => $attrKunjungan['vehicle_number'] ?? 'b 1234 ab',
            'check_in_at' => $attrKunjungan['check_in_at'] ?? now(),
        ]);
    }

    /* ===================== Master Tamu ===================== */

    public function test_master_tamu_menyembunyikan_kolom_kode_tamu(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Master');

        $res = $this->actingAs($this->admin)->get('/master-tamu');

        $res->assertOk()->assertDontSee('<th>Kode Tamu</th>', false);
    }

    public function test_master_tamu_kolom_kunjungan_dua_baris_rata_tengah(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Master');

        $res = $this->actingAs($this->admin)->get('/master-tamu');

        $res->assertOk()
            ->assertSee('<th style="text-align:center">Total Kunjungan<br>Terakhir Kunjungan</th>', false)
            ->assertSee('1x', false); // total kunjungan tamu
    }

    /* ===================== Seeder pengguna ===================== */

    public function test_seeder_departemen_tidak_membuat_akun_manager_atau_petugas(): void
    {
        $this->seed(DepartmentSeeder::class);

        $this->assertSame(
            0,
            User::whereIn('role', ['manager', 'petugas'])->count(),
            'Seeder hanya boleh membuat departemen, bukan akun contoh'
        );
    }

    /* ===================== Dashboard ===================== */

    public function test_dashboard_kartu_kunjungan_terbaru_berada_di_atas(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Baru');

        $content = $this->actingAs($this->admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($content, 'Aktivitas 7 Hari Terakhir'),
            strpos($content, 'Kunjungan Terbaru'),
            'Kartu Kunjungan Terbaru harus di atas grafik aktivitas'
        );
    }

    public function test_dashboard_menampilkan_nomor_kendaraan_huruf_besar(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Kendaraan', ['vehicle_number' => 'b 9988 xy']);

        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('B 9988 XY', false)
            ->assertDontSee('b 9988 xy', false);
    }

    /* ===================== Data Tamu ===================== */

    public function test_data_tamu_menyembunyikan_kolom_kode_tamu_dan_departemen(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Data');

        $res = $this->actingAs($this->admin)->get('/data-tamu');

        $res->assertOk()
            ->assertDontSee('<th>Kode Tamu</th>', false)
            ->assertDontSee('<th>Departemen</th>', false);
    }

    public function test_data_tamu_menyediakan_export_excel_dan_pdf(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Export');

        $this->actingAs($this->admin)->get('/data-tamu')->assertOk()
            ->assertSee('Export Excel')
            ->assertSee('Export PDF');

        $xlsx = $this->actingAs($this->admin)->get('/data-tamu-export?format=xlsx');
        $xlsx->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $xlsx->headers->get('Content-Type')
        );

        $pdf = $this->actingAs($this->admin)->get('/data-tamu-export?format=pdf');
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertSame('%PDF', substr($pdf->getContent(), 0, 4));
    }

    public function test_export_data_tamu_tidak_memuat_kolom_departemen(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Export Dept');

        // CSV dibaca langsung — xlsx & pdf memakai builder baris yang sama
        $csv = $this->actingAs($this->admin)->get('/data-tamu-export?format=csv');
        $csv->assertOk();

        $barisPertama = explode("\n", trim($csv->streamedContent()))[0];
        $this->assertStringContainsString('Kode Tamu', $barisPertama);
        $this->assertStringNotContainsString('Departemen', $barisPertama);
    }

    /* ===================== Kiosk: nomor kendaraan kapital ===================== */

    public function test_checkin_kiosk_nomor_kendaraan_tersimpan_huruf_besar(): void
    {
        $penerima = User::factory()->create(['role' => 'penerima']);
        $guest = Guest::create([
            'guest_code' => 'ZPA-KIOSK',
            'name' => 'Tamu Kiosk',
            'phone' => '08117777',
            'vehicle_type' => 'Mobil',
            'identity_type' => 'KTP',
            'identity_number' => '3201',
        ]);

        $this->actingAs($penerima)->post("/kiosk/checkin/{$guest->id}", [
            'purpose' => 'Meeting',
            'vehicle_number' => 'b 4321 cd',
        ])->assertRedirect(route('kiosk.index', absolute: false));

        $this->assertDatabaseHas('visits', [
            'guest_id' => $guest->id,
            'vehicle_number' => 'B 4321 CD',
        ]);
    }

    /* ===================== Laporan ===================== */

    public function test_laporan_detail_kunjungan_berada_di_atas_dengan_kolom_baru(): void
    {
        $visit = $this->buatTamuDenganKunjungan('Tamu Laporan', ['purpose' => 'Delivery']);

        $res = $this->actingAs($this->admin)->get('/laporan');
        $res->assertOk();

        $content = $res->getContent();
        $this->assertLessThan(
            strpos($content, 'Tujuan Kunjungan Terbanyak'),
            strpos($content, 'Detail Kunjungan'),
            'Detail Kunjungan harus di atas kartu rekap'
        );

        // Departemen dihide; kode tamu paling kanan setelah kendaraan & catatan
        $res->assertDontSee('<th>Departemen</th>', false)
            ->assertSee('<th>Kendaraan</th><th>Catatan</th><th>Kode</th>', false);

        // Nama tamu bisa diklik menuju halaman detail tamu
        $res->assertSee(route('tamu.show', $visit), false);
    }

    public function test_laporan_filter_tujuan_hanya_menampilkan_kunjungan_terkait(): void
    {
        $this->buatTamuDenganKunjungan('Tamu Meeting', ['purpose' => 'Meeting']);
        $this->buatTamuDenganKunjungan('Tamu Delivery', ['purpose' => 'Delivery']);

        $res = $this->actingAs($this->admin)
            ->get('/laporan?dari='.now()->format('Y-m-d').'&sampai='.now()->format('Y-m-d').'&tujuan=Delivery');

        $res->assertOk()
            ->assertSee('Tamu Delivery')
            ->assertDontSee('Tamu Meeting')
            ->assertSee('Semua Tujuan'); // dropdown filter tersedia
    }
}
