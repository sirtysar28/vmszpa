<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Support\Captcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /* ===================== LOGIN ===================== */

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended(
                Auth::user()->isPenerima() ? route('kiosk.index') : route('dashboard')
            );
        }

        Captcha::generate();

        return view('auth.login');
    }

    public function captcha()
    {
        return response(Captcha::image(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'captcha' => ['required', 'string', 'max:10'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'captcha.required' => 'Kode captcha wajib diisi.',
        ]);

        $throttleKey = mb_strtolower($data['email']).'|'.$request->ip();

        // Rate limit: 5 percobaan / 2 menit (mitigasi brute force)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        // Verifikasi captcha dulu (sekali pakai)
        if (! Captcha::verify($data['captcha'])) {
            RateLimiter::hit($throttleKey, 120);
            LoginAttempt::log(false, $data['email'], $request->userAgent());

            return back()->withErrors(['captcha' => 'Kode captcha salah.'])->withInput($request->only('email'));
        }

        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                Captcha::generate();

                return back()->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi Administrator.'])
                    ->withInput($request->only('email'));
            }

            LoginAttempt::log(true, $data['email'], $request->userAgent());
            $request->session()->regenerate();

            // Akun Penerima Tamu langsung dibawa ke layar Kiosk
            if ($user->isPenerima()) {
                return redirect()->intended(route('kiosk.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 120);
        LoginAttempt::log(false, $data['email'], $request->userAgent());
        Captcha::generate();

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* ===================== LUPA PASSWORD ===================== */

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], ['email.required' => 'Email wajib diisi.', 'email.email' => 'Format email tidak valid.']);

        $throttleKey = 'forgot|'.mb_strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors(['email' => "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik."]);
        }
        RateLimiter::hit($throttleKey, 300);

        $user = User::where('email', $request->input('email'))->first();

        // Selalu tampilkan pesan generik (jangan bocorkan keberadaan akun)
        $status = 'Tautan reset password telah dikirim ke email Anda jika akun tersebut terdaftar.';

        if ($user) {
            $token = Password::createToken($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            $user->notify(new ResetPasswordNotification($resetUrl));

            // Karena email di lingkungan lokal/dev masih mailer=log,
            // tampilkan tautan langsung HANYA di environment lokal.
            if (app()->environment('local')) {
                $status .= ' [DEV MODE] Buka tautan: '.$resetUrl;
            }
        }

        return back()->with('status', $status);
    }

    public function showReset(Request $request, string $token)
    {
        $request->validate(['email' => ['required', 'email']]);

        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed',
                Rule::password()->letters()->numbers(),
            ],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password' => 'Password harus mengandung huruf dan angka.',
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login dengan password baru.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
