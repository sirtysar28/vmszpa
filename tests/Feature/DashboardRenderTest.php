<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_merender_dashboard_dengan_grafik_dan_data_dummy(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'admin@vmszpa.com')->first();

        $res = $this->actingAs($user)->get(route('dashboard'));
        $res->assertOk();

        // Grafik: bar check-in & check-out ter-render dengan nilai di atas bar
        $res->assertSee('class="bar in"', false);
        $res->assertSee('class="bar out"', false);
        $res->assertSee('class="vals"', false);
        $res->assertSee('bar-col today', false);
        $res->assertSee('Aktivitas 7 Hari Terakhir');

        // Dummy data tampil
        $res->assertSee('Budi Santoso');
        $res->assertSee('Kunjungan Terbaru');
        $res->assertSee('Tamu yang Sedang Di Dalam');
    }
}
