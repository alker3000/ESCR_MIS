<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelaOrder extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'enrollment_id',
        'invoice_id',
        'blouse_polo_yards',
        'blouse_polo_unit_price',
        'skirt_yards',
        'skirt_unit_price',
        'logo_patch_qty',
        'logo_patch_unit_price',
        'total_amount',
        'student_notes',
        'status',
        'ready_at',
        'claimed_at',
        'processed_by',
        'cashier_remarks',
    ];

    protected function casts(): array
    {
        return [
            'blouse_polo_yards' => 'decimal:2',
            'blouse_polo_unit_price' => 'decimal:2',
            'skirt_yards' => 'decimal:2',
            'skirt_unit_price' => 'decimal:2',
            'logo_patch_unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'ready_at' => 'datetime',
            'claimed_at' => 'datetime',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function itemsSummary(): string
    {
        $parts = [];
        if ((float) $this->blouse_polo_yards > 0) {
            $parts[] = rtrim(rtrim(number_format((float) $this->blouse_polo_yards, 2), '0'), '.') . ' yd Blouse/Polo';
        }
        if ((float) $this->skirt_yards > 0) {
            $parts[] = rtrim(rtrim(number_format((float) $this->skirt_yards, 2), '0'), '.') . ' yd Skirt';
        }
        if ((int) $this->logo_patch_qty > 0) {
            $parts[] = $this->logo_patch_qty . ' pc Logo Patch';
        }
        return implode(', ', $parts) ?: 'No items';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'awaiting_payment' => 'Awaiting GCash Payment',
            'paid_processing' => 'Paid: Cashier Preparing Fabric',
            'ready_for_pickup' => 'Ready for Pickup at School',
            'claimed' => 'Claimed by Student',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'awaiting_payment' => 'bg-warning text-dark',
            'paid_processing' => 'bg-info text-dark',
            'ready_for_pickup' => 'bg-primary',
            'claimed' => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
