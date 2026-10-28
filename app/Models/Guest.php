<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = [
        'guest_code', 'name', 'phone', 'company',
        'vehicle_type', 'identity_type', 'identity_number',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function activeVisit(): ?Visit
    {
        return $this->visits()->whereNull('check_out_at')->latest('check_in_at')->first();
    }

    public static function generateCode(): string
    {
        $prefix = config('services.guest_code.prefix', 'ZPA');

        do {
            $code = $prefix.'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (static::where('guest_code', $code)->exists());

        return $code;
    }
}
