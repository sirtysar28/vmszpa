<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'ip_address', 'successful', 'user_agent', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'successful' => 'boolean'];
    }

    public static function log(bool $successful, string $email, ?string $userAgent = null): void
    {
        static::create([
            'email' => mb_substr($email, 0, 255),
            'ip_address' => request()->ip() ?? '0.0.0.0',
            'successful' => $successful,
            'user_agent' => mb_substr((string) $userAgent, 0, 1000),
            'created_at' => now(),
        ]);
    }
}
