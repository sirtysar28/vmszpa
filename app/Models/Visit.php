<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'guest_id', 'purpose', 'vehicle_number', 'meet_with',
        'notes', 'check_in_at', 'check_out_at',
        'checked_in_by', 'checked_out_by', 'department_id',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isInside(): bool
    {
        return $this->check_out_at === null;
    }

    /**
     * Scope data kunjungan sesuai hak akses user:
     * - Akun tanpa departemen (admin global)  → semua data.
     * - Akun terikat departemen (admin dept / manager / petugas) → hanya kunjungan departemen itu.
     */
    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if ($user?->department_id) {
            $query->where('visits.department_id', $user->department_id);
        }

        return $query;
    }
}
