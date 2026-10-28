<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Pengaturan aplikasi (key-value) — dipakai untuk konfigurasi SMTP
 * notifikasi email yang dapat diubah admin dari halaman Pengaturan.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'vms.settings';

    /** Ambil nilai setting (null bila tidak ada). */
    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allSettings()->get($key, $default);
    }

    /** Simpan / perbarui satu setting lalu flush cache. */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(static::CACHE_KEY);
    }

    /** Simpan banyak setting sekaligus. */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(static::CACHE_KEY);
    }

    /** Semua setting sebagai koleksi key => value (di-cache selamanya). */
    /**
     * Semua setting sebagai koleksi key => value (di-cache selamanya).
     * Nilai cache disimpan sebagai array murni — config cache
     * `serializable_classes: false` melarang objek di-unserialize.
     */
    public static function allSettings(): Collection
    {
        return collect(Cache::rememberForever(static::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->all();
        }));
    }

    /** Enkripsi nilai sensitif (password SMTP). */
    public static function encrypt(?string $plain): ?string
    {
        return $plain === null || $plain === '' ? null : Crypt::encryptString($plain);
    }

    /** Dekripsi nilai sensitif. */
    public static function decrypt(?string $cipher): ?string
    {
        if ($cipher === null || $cipher === '') {
            return null;
        }

        try {
            return Crypt::decryptString($cipher);
        } catch (\Throwable) {
            return null; // APP_KEY berganti → password harus diisi ulang
        }
    }
}
