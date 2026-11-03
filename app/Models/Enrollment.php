<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'application_reference',
        'academic_year',
        'semester',
        'course',
        'year_level',
        'student_type',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'birth_date',
        'address',
        'contact_number',
        'guardian_name',
        'guardian_relationship',
        'guardian_contact',
        'previous_school',
        'previous_school_year',
        'payment_scheme',
        'status',
        'exam_restricted',
        'exam_restriction_reason',
        'registrar_remarks',
        'reviewed_by',
        'reviewed_at',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'exam_restricted' => 'boolean',
            'reviewed_at' => 'datetime',
            'enrolled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function promissoryNotes(): HasMany
    {
        return $this->hasMany(PromissoryNote::class);
    }

    public function telaOrders(): HasMany
    {
        return $this->hasMany(TelaOrder::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Registrar Review',
            'returned' => 'Returned for Revision',
            'approved_awaiting_payment' => 'Approved: Awaiting Payment',
            'enrolled' => 'Officially Enrolled',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'under_review' => 'bg-info text-dark',
            'returned' => 'bg-danger',
            'approved_awaiting_payment' => 'bg-primary',
            'enrolled' => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
