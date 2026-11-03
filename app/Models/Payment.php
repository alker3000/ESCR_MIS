<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'user_id',
        'payment_method',
        'payment_mode',
        'gcash_reference_number',
        'gcash_sender_name',
        'gcash_sender_number',
        'amount',
        'proof_file_path',
        'status',
        'verified_by',
        'verified_at',
        'cashier_remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'verified' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
    }

    public function methodLabel(): string
    {
        return $this->payment_method === 'cash'
            ? 'Cash (Walk-In at School Cashier)'
            : 'GCash Online Payment';
    }

    public function methodBadgeClass(): string
    {
        return $this->payment_method === 'cash'
            ? 'bg-success'
            : 'gcash-badge';
    }
}
