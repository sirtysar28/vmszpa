<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Captcha sederhana & aman berbasis GD (tanpa dependensi eksternal).
 * - 5 karakter alfanumerik (huruf ambigu dihilangkan: 0/O, 1/I/l)
 * - Kode disimpan di session (session terenkripsi, http-only)
 * - Kode hanya berlaku 1x pemakaian (sekali pakai = sekali gagal pun harus refresh)
 */
class Captcha
{
    private const CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public static function length(): int
    {
        return (int) (config('services.captcha.length') ?: 5);
    }

    /** Buat kode baru & simpan ke session. */
    public static function generate(): string
    {
        $code = '';
        $max = strlen(self::CHARS) - 1;
        for ($i = 0; $i < self::length(); $i++) {
            $code .= self::CHARS[random_int(0, $max)];
        }

        Session::put('captcha', [
            'code' => $code,
            'expires' => now()->addMinutes(10)->getTimestamp(),
        ]);

        return $code;
    }

    /** Verifikasi jawaban user (case-insensitive, sekali pakai). */
    public static function verify(?string $answer): bool
    {
        $data = Session::pull('captcha'); // hapus setelah diverifikasi

        if (! is_array($data) || ! isset($data['code'], $data['expires'])) {
            return false;
        }
        if (now()->getTimestamp() > (int) $data['expires']) {
            return false;
        }

        return hash_equals(strtoupper($data['code']), strtoupper(trim((string) $answer)));
    }

    /** Cari font TTF yang tersedia (font dibundle agar konsisten di semua server). */
    private static function fontFile(): ?string
    {
        $candidates = [
            resource_path('fonts/DejaVuSans-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        ];

        foreach ($candidates as $file) {
            if (@is_readable($file)) {
                return $file;
            }
        }

        return null;
    }

    /** Render gambar PNG captcha sebagai string binary. */
    public static function image(): string
    {
        $code = self::generate();
        $width = 220;
        $height = 70;
        $img = imagecreatetruecolor($width, $height);

        // Latar gradien
        $bg1 = imagecolorallocate($img, 240, 244, 250);
        $bg2 = imagecolorallocate($img, 210, 220, 236);
        for ($x = 0; $x < $width; $x++) {
            $t = $x / $width;
            $r = (int) ((1 - $t) * 240 + $t * 210);
            $g = (int) ((1 - $t) * 244 + $t * 220);
            $b = (int) ((1 - $t) * 250 + $t * 236);
            imageline($img, $x, 0, $x, $height, imagecolorallocate($img, $r, $g, $b));
        }

        // Titik noise
        for ($i = 0; $i < 120; $i++) {
            $c = imagecolorallocate($img, random_int(150, 210), random_int(150, 210), random_int(150, 210));
            imagesetpixel($img, random_int(0, $width - 1), random_int(0, $height - 1), $c);
        }

        // Garis noise
        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($img, random_int(90, 170), random_int(110, 180), random_int(160, 220));
            imageline($img, random_int(0, 40), random_int(5, $height - 5), random_int($width - 60, $width), random_int(5, $height - 5), $c);
        }

        // Karakter — font dibundle di aplikasi agar ukurannya konsisten di server mana pun
        $fontFile = self::fontFile();
        $useTtf = function_exists('imagettftext') && $fontFile !== null;
        $chars = str_split($code);
        $slot = ($width - 30) / count($chars);

        foreach ($chars as $i => $char) {
            $x = 15 + $i * $slot + random_int(-3, 3);

            if ($useTtf) {
                $y = random_int(44, 54);
                $color = imagecolorallocate($img, random_int(15, 70), random_int(45, 100), random_int(120, 190));
                imagettftext($img, random_int(26, 30), random_int(-16, 16), (int) $x, $y, $color, $fontFile, $char);
            } else {
                // Fallback tanpa TTF: gambar dengan font GD lalu perbesar 2x biar tetap terbaca
                $tmp = imagecreatetruecolor(16, 18);
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                imagefill($tmp, 0, 0, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
                imagestring($tmp, 5, 1, 1, $char, imagecolorallocate($tmp, random_int(15, 70), random_int(45, 100), random_int(120, 190)));
                imagecopyresized($img, $tmp, (int) ($x - 2), random_int(14, 24), 0, 0, 32, 36, 16, 18);
                imagedestroy($tmp);
            }
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);

        return (string) ob_get_clean();
    }
}
