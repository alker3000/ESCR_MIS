<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromissoryNote extends Model
{
    protected $fillable = [
        'note_number',
        'user_id',
        'enrollment_id',
        'invoice_id',
        'special_exam_invoice_id',
        'installment_period',
        'outstanding_amount',
        'promised_settlement_date',
        'submission_deadline',
        'student_contact',
        'guardian_name',
        'guardian_contact',
        'signed_file_path',
        'original_filename',
        'status',
        'registrar_remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_amount' => 'decimal:2',
            'promised_settlement_date' => 'date',
            'submission_deadline' => 'date',
            'reviewed_at' => 'datetime',
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
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function specialExamInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'special_exam_invoice_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'Approved (Moved to Finals — Exam Cleared)',
            'rejected' => 'Rejected',
            default => 'Pending Registrar Review',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
    }
}
