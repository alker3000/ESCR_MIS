<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'enrollment_id',
        'invoice_type',
        'exam_period',
        'title',
        'description',
        'amount',
        'amount_paid',
        'due_date',
        'promised_date',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date' => 'date',
            'promised_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function promissoryNote(): HasOne
    {
        return $this->hasOne(PromissoryNote::class)->latestOfMany();
    }

    public function telaOrder(): HasOne
    {
        return $this->hasOne(TelaOrder::class);
    }

    public function balance(): float
    {
        if ($this->status === 'promised' || $this->status === 'paid') {
            return 0.0;
        }
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }

    public function isInstallment(): bool
    {
        return in_array($this->invoice_type, [
            'prelim_installment',
            'midterm_installment',
            'semifinal_installment',
            'finals_installment',
        ], true);
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'paid' || $this->status === 'promised') {
            return false;
        }
        if ($this->status === 'overdue') {
            return true;
        }
        return $this->due_date && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Paid & Verified',
            'pending_verification' => 'Awaiting Cashier Verification',
            'overdue' => 'Overdue (Exam Restricted)',
            'promised' => 'Moved to Finals (Promised)',
            default => 'Unpaid',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success',
            'pending_verification' => 'bg-info text-dark',
            'overdue' => 'bg-danger',
            'promised' => 'bg-primary',
            default => 'bg-warning text-dark',
        };
    }
}
