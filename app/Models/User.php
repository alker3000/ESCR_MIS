<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'student_number',
        'contact_number',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class)->latestOfMany();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function promissoryNotes(): HasMany
    {
        return $this->hasMany(PromissoryNote::class);
    }

    public function telaOrders(): HasMany
    {
        return $this->hasMany(TelaOrder::class);
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isRegistrar(): bool
    {
        return $this->role === 'registrar';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'registrar' => 'registrar.dashboard',
            'cashier' => 'cashier.dashboard',
            'super_admin' => 'admin.dashboard',
            default => 'student.dashboard',
        };
    }

    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            'super_admin' => 'bg-danger',
            'registrar' => 'bg-primary',
            'cashier' => 'bg-success',
            default => 'bg-info text-dark',
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'registrar' => 'Registrar',
            'cashier' => 'Cashier',
            default => 'Student',
        };
    }
}
