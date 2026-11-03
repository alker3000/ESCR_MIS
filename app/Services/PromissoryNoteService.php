<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PromissoryNote;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromissoryNoteService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Student uploads a signed Promissory Note for an installment invoice.
     */
    public function submitPromissoryNote(
        User $student,
        Invoice $invoice,
        array $data,
        UploadedFile $signedFile
    ): PromissoryNote {
        return DB::transaction(function () use ($student, $invoice, $data, $signedFile) {
            $enrollment = $invoice->enrollment ?? $student->enrollment;

            $ext = $signedFile->getClientOriginalExtension() ?: 'pdf';
            $filePath = $signedFile->storeAs(
                'documents/promissory_notes/' . $student->id,
                Str::uuid()->toString() . '.' . $ext,
                'local'
            );

            $periodMap = [
                'prelim_installment' => 'Prelim Installment',
                'midterm_installment' => 'Midterm Installment',
                'semifinal_installment' => 'Semi-Finals Installment',
                'finals_installment' => 'Finals Installment',
            ];
            $installmentPeriod = $periodMap[$invoice->invoice_type] ?? ($invoice->exam_period ? "{$invoice->exam_period} Installment" : 'Prelim Installment');

            $deadlineMap = [
                'prelim_installment' => SystemSetting::getValue('prelim_pn_deadline', '2027-08-10'),
                'midterm_installment' => SystemSetting::getValue('midterm_pn_deadline', '2027-09-07'),
                'semifinal_installment' => SystemSetting::getValue('semifinal_pn_deadline', '2027-10-05'),
                'finals_installment' => SystemSetting::getValue('finals_pn_deadline', '2027-11-02'),
            ];

            $finalsDueDate = SystemSetting::getValue('finals_due_date', '2027-11-09');
            $promisedDate = !empty($data['promised_settlement_date'])
                ? $data['promised_settlement_date']
                : $finalsDueDate;

            $note = PromissoryNote::create([
                'note_number' => 'PN-2026-' . strtoupper(Str::random(3)) . rand(100, 999),
                'user_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'invoice_id' => $invoice->id,
                'installment_period' => $installmentPeriod,
                'outstanding_amount' => $invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->amount,
                'promised_settlement_date' => $promisedDate,
                'submission_deadline' => $deadlineMap[$invoice->invoice_type] ?? now()->toDateString(),
                'student_contact' => $data['student_contact'] ?? ($student->contact_number ?: $enrollment->contact_number),
                'guardian_name' => $data['guardian_name'] ?? $enrollment->guardian_name,
                'guardian_contact' => $data['guardian_contact'] ?? $enrollment->guardian_contact,
                'signed_file_path' => $filePath,
                'original_filename' => $signedFile->getClientOriginalName(),
                'status' => 'pending_review',
            ]);

            AuditService::log(
                'PROMISSORY_NOTE_UPLOADED',
                "Student {$student->name} uploaded signed Promissory Note {$note->note_number} for {$note->installment_period} (to be moved to Finals term).",
                'PromissoryNote',
                $note->id,
                null,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'outstanding_amount' => $note->outstanding_amount,
                    'promised_settlement_date' => $promisedDate,
                ],
                $student
            );

            NotificationService::send(
                $student,
                'promissory_approved',
                "Promissory Note Submitted ({$note->note_number})",
                "Dear {$student->name},\n\nYour signed Promissory Note ({$note->note_number}) notifying the Registrar regarding your {$note->installment_period} (PHP " . number_format((float) $note->outstanding_amount, 2) . ") has been uploaded and is now pending Registrar approval to move the balance to your Final Term installment.\n\nEast Systems Colleges of Rizal"
            );

            return $note;
        });
    }

    /**
     * Registrar approves the Promissory Note (ESCR Policy):
     * 1. Moves the unpaid balance of the current term installment (e.g. Prelim, Midterm, Semi-Finals) into the 4th (Finals) Installment invoice.
     * 2. Marks the current term installment invoice as 'promised' ('Moved to Finals').
     * 3. Immediately clears the student to take the current term examination (exam_restricted = false).
     */
    public function approvePromissoryNote(PromissoryNote $note, User $staff, ?string $remarks = null): PromissoryNote
    {
        return DB::transaction(function () use ($note, $staff, $remarks) {
            $invoice = $note->invoice;
            $enrollment = $note->enrollment;
            $student = $note->user;

            $transferAmount = (float) $note->outstanding_amount;

            $finalsInvoice = Invoice::where('user_id', $student->id)
                ->when($note->enrollment_id, fn ($q) => $q->where('enrollment_id', $note->enrollment_id))
                ->where('invoice_type', 'finals_installment')
                ->latest('id')
                ->first();

            if ($invoice && $invoice->invoice_type !== 'finals_installment' && $finalsInvoice) {
                $newFinalsAmount = (float) $finalsInvoice->amount + $transferAmount;
                $baseDesc = rtrim((string) $finalsInvoice->description, '.');

                $finalsInvoice->update([
                    'amount' => $newFinalsAmount,
                    'description' => "{$baseDesc}. Includes PHP " . number_format($transferAmount, 2) . " moved from {$note->installment_period} (Promissory Note {$note->note_number}).",
                ]);

                $targetDate = $finalsInvoice->due_date ?? $note->promised_settlement_date;

                $invoice->update([
                    'status' => 'promised',
                    'promised_date' => $targetDate,
                    'description' => rtrim((string) $invoice->description, '.') . " — Balance of PHP " . number_format($transferAmount, 2) . " moved to 4th Installment (Finals) via Promissory Note {$note->note_number}.",
                ]);

                $note->update([
                    'promised_settlement_date' => $targetDate,
                    'special_exam_invoice_id' => null,
                    'status' => 'approved',
                    'registrar_remarks' => $remarks ?? "Promissory Note approved. Tuition balance of PHP " . number_format($transferAmount, 2) . " moved to Final Term Installment ({$finalsInvoice->invoice_number}). Student is cleared for examination.",
                    'reviewed_by' => $staff->id,
                    'reviewed_at' => now(),
                ]);
            } else {
                if ($invoice) {
                    $invoice->update([
                        'status' => 'unpaid',
                        'due_date' => $note->promised_settlement_date,
                        'promised_date' => $note->promised_settlement_date,
                    ]);
                }

                $note->update([
                    'special_exam_invoice_id' => null,
                    'status' => 'approved',
                    'registrar_remarks' => $remarks ?? 'Promissory Note verified and approved. Installment deadline extended and student cleared for examination.',
                    'reviewed_by' => $staff->id,
                    'reviewed_at' => now(),
                ]);
            }

            // Refresh Exam Clearance -> immediately lifts exam restriction!
            if ($enrollment) {
                $this->paymentService->refreshExamClearanceStatus($enrollment);
            }

            AuditService::log(
                'PROMISSORY_NOTE_APPROVED',
                "{$staff->roleLabel()} {$staff->name} approved Promissory Note {$note->note_number} for {$student->name}. Balance of PHP " . number_format($transferAmount, 2) . " moved to Finals Installment and student cleared for exam.",
                'PromissoryNote',
                $note->id,
                ['status' => 'pending_review'],
                [
                    'status' => 'approved',
                    'moved_amount' => $transferAmount,
                    'finals_invoice' => $finalsInvoice?->invoice_number,
                    'new_finals_amount' => $finalsInvoice?->amount,
                ],
                $staff
            );

            NotificationService::send(
                $student,
                'promissory_approved',
                "Promissory Note Approved - Tuition Moved to Finals & Cleared for Exam ({$note->note_number})",
                "Dear {$student->name},\n\nYour Promissory Note ({$note->note_number}) for {$note->installment_period} has been APPROVED by the Office of the Registrar.\n\nIn accordance with East Systems Colleges of Rizal policy:\n1. Your {$note->installment_period} balance of PHP " . number_format($transferAmount, 2) . " has been moved and added to your Final Term Installment" . ($finalsInvoice ? " ({$finalsInvoice->invoice_number}, now totaling PHP " . number_format((float) $finalsInvoice->amount, 2) . " due on " . $finalsInvoice->due_date?->format('F d, Y') . ")" : "") . ".\n2. Your account is now OFFICIALLY CLEARED FOR EXAMINATION. You may view and print your Digital Examination Permit in the Student Portal.\n\nEast Systems Colleges of Rizal"
            );

            return $note;
        });
    }

    /**
     * Registrar rejects a Promissory Note.
     */
    public function rejectPromissoryNote(PromissoryNote $note, User $staff, string $remarks): PromissoryNote
    {
        $note->update([
            'status' => 'rejected',
            'registrar_remarks' => $remarks,
            'reviewed_by' => $staff->id,
            'reviewed_at' => now(),
        ]);

        AuditService::log(
            'PROMISSORY_NOTE_REJECTED',
            "{$staff->roleLabel()} {$staff->name} rejected Promissory Note {$note->note_number}: {$remarks}",
            'PromissoryNote',
            $note->id,
            ['status' => 'pending_review'],
            ['status' => 'rejected', 'remarks' => $remarks],
            $staff
        );

        NotificationService::send(
            $note->user,
            'document_rejected',
            "Promissory Note Rejected ({$note->note_number})",
            "Dear {$note->user->name},\n\nYour uploaded Promissory Note ({$note->note_number}) was not approved.\nReason: {$remarks}\n\nPlease ensure the form is properly signed by your Parent/Guardian and re-upload it in the portal.\n\nEast Systems Colleges of Rizal"
        );

        return $note;
    }
}
