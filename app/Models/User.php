<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'department_id', 'notification_read_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /** Akun khusus operator kiosk tablet di pos penerima tamu. */
    public function isPenerima(): bool
    {
        return $this->role === 'penerima';
    }

    /** Boleh membuka layar Kiosk (check-in / check-out tamu). */
    public function canOperateKiosk(): bool
    {
        return $this->isAdmin() || $this->isPenerima();
    }

    /** True bila akun terikat departemen tertentu (data di-scope ke departemen itu). */
    public function isDepartmentScoped(): bool
    {
        return $this->department_id !== null;
    }

    /** Label role untuk tampilan. */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'penerima' => 'Penerima Tamu',
            default => 'Petugas',
        };
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'notification_read_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
