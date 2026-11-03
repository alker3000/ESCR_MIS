<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $fillable = [
        'enrollment_id',
        'user_id',
        'document_type',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function typeLabel(): string
    {
        return match ($this->document_type) {
            'psa_birth_certificate' => 'PSA Birth Certificate',
            'form_138_report_card' => 'Form 138 / Report Card',
            'good_moral_certificate' => 'Certificate of Good Moral Character',
            'id_picture' => '2x2 ID Picture',
            'transfer_credentials' => 'Honorable Dismissal / Transfer Credentials',
            default => ucwords(str_replace('_', ' ', $this->document_type)),
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
