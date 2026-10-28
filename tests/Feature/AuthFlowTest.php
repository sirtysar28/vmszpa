<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_menampilkan_form_dan_info(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Selamat Datang');
        $response->assertSee('Visitor Management');
        $response->assertSee('Trijaya Solution');
        $response->assertSee('Lupa password?');
    }

    public function test_endpoint_captcha_menghasilkan_gambar(): void
    {
        $response = $this->get('/captcha');

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame(5, strlen((string) session('captcha.code')));
    }

    public function test_login_gagal_dengan_captcha_salah(): void
    {
        User::factory()->create([
            'email' => 'petugas@vmszpa.com',
            'password' => 'Rahasia123',
        ]);

        $this->get('/login'); // generate captcha di session

        $response = $this->post('/login', [
            'email' => 'petugas@vmszpa.com',
            'password' => 'Rahasia123',
            'captcha' => 'XXXXX', // pasti salah (huruf ambigu tak ada di charset)
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_login_berhasil_dengan_captcha_benar(): void
    {
        $user = User::factory()->create([
            'email' => 'petugas@vmszpa.com',
            'password' => 'Rahasia123',
        ]);

        $this->get('/login');
        $code = session('captcha.code');
        $this->assertNotNull($code);

        $response = $this->post('/login', [
            'email' => 'petugas@vmszpa.com',
            'password' => 'Rahasia123',
            'captcha' => $code,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->create([
            'email' => 'off@vmszpa.com',
            'password' => 'Rahasia123',
            'is_active' => false,
        ]);

        $this->get('/login');
        $code = session('captcha.code');

        $response = $this->post('/login', [
            'email' => 'off@vmszpa.com',
            'password' => 'Rahasia123',
            'captcha' => $code,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_lupa_password_menghasilkan_reset_link(): void
    {
        $user = User::factory()->create(['email' => 'lupa@vmszpa.com']);

        $response = $this->post('/lupa-password', ['email' => 'lupa@vmszpa.com']);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'lupa@vmszpa.com']);
    }
}
