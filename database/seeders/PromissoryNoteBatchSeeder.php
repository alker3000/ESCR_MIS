<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PromissoryNote;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PromissoryNoteBatchSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = User::where('role', 'registrar')->first() ?? User::find(2);

        // Fetch exactly 20 enrolled students who have term installment and finals installment invoices (excluding student 5)
        $students = User::where('role', 'student')
            ->where('id', '!=', 5)
            ->whereHas('invoices', function ($q) {
                $q->whereIn('invoice_type', ['prelim_installment', 'midterm_installment', 'semifinal_installment']);
            })
            ->whereHas('invoices', function ($q) {
                $q->where('invoice_type', 'finals_installment');
            })
            ->with(['enrollment', 'invoices'])
            ->take(20)
            ->get();

        $now = Carbon::now();
        $finalsDueDate = SystemSetting::getValue('finals_due_date', '2027-11-09');

        $counter = 2; // PN-2026-DEMO01 already exists as note 1

        // Installment periods to distribute
        $periods = [
            ['type' => 'midterm_installment', 'label' => 'Midterm Installment', 'deadline' => '2027-09-07'],
            ['type' => 'prelim_installment', 'label' => 'Prelim Installment', 'deadline' => '2027-08-10'],
            ['type' => 'semifinal_installment', 'label' => 'Semi-Finals Installment', 'deadline' => '2027-10-05'],
        ];

        foreach ($students as $index => $student) {
            $periodConfig = $periods[$index % count($periods)];
            $noteNumber = sprintf('PN-2026-%04d', $counter);
            $enrollment = $student->enrollment;

            // Find target installment invoice
            $targetInvoice = $student->invoices
                ->where('invoice_type', $periodConfig['type'])
                ->first();

            if (!$targetInvoice) {
                // If not found, use any unpaid installment invoice
                $targetInvoice = $student->invoices
                    ->whereIn('invoice_type', ['prelim_installment', 'midterm_installment', 'semifinal_installment'])
                    ->first();
            }

            if (!$targetInvoice) {
                continue;
            }

            $finalsInvoice = $student->invoices
                ->where('invoice_type', 'finals_installment')
                ->first();

            $guardianName = $enrollment?->guardian_name ?? 'Guardian of ' . $student->name;
            $guardianContact = $enrollment?->guardian_contact ?? '0918' . rand(1000000, 9999999);
            $studentContact = $student->contact_number ?? $enrollment?->contact_number ?? '0917' . rand(1000000, 9999999);
            $amount = (float) $targetInvoice->amount;

            // Status Distribution:
            // 0-9 (10 students): pending_review
            // 10-17 (8 students): approved
            // 18-19 (2 students): rejected
            if ($index < 10) {
                $status = 'pending_review';
                $reviewedBy = null;
                $reviewedAt = null;
                $remarks = null;
                $submittedAt = $now->copy()->subHours(rand(1, 48));
            } elseif ($index < 18) {
                $status = 'approved';
                $reviewedBy = $registrar?->id ?? 2;
                $reviewedAt = $now->copy()->subDays(rand(1, 5));
                $remarks = "Promissory Note approved. Tuition balance of PHP " . number_format($amount, 2) . " moved to Final Term Installment ({$finalsInvoice?->invoice_number}). Student is cleared for examination.";
                $submittedAt = $reviewedAt->copy()->subHours(rand(6, 24));

                // Apply policy rollover to invoices:
                // 1. Mark target invoice as promised
                $targetInvoice->update([
                    'status' => 'promised',
                    'promised_date' => $finalsDueDate,
                ]);

                // 2. Add amount to finals invoice if not already added
                if ($finalsInvoice && $finalsInvoice->amount == 5000.00) {
                    $finalsInvoice->update([
                        'amount' => (float) $finalsInvoice->amount + $amount,
                    ]);
                }

                // 3. Ensure student is cleared
                if ($enrollment) {
                    $enrollment->update(['exam_restricted' => false]);
                }
            } else {
                $status = 'rejected';
                $reviewedBy = $registrar?->id ?? 2;
                $reviewedAt = $now->copy()->subDays(rand(1, 3));
                $remarks = "Missing parent/guardian signature on Conforme section. Please re-upload signed document with attached government-issued ID.";
                $submittedAt = $reviewedAt->copy()->subHours(rand(12, 36));
            }

            $originalFilename = Str::slug($student->name, '_') . '_Signed_Promissory_Note.pdf';
            $signedFilePath = "documents/promissory_notes/{$student->id}/" . Str::uuid()->toString() . '.pdf';

            PromissoryNote::updateOrCreate(
                ['note_number' => $noteNumber],
                [
                    'user_id' => $student->id,
                    'enrollment_id' => $enrollment?->id,
                    'invoice_id' => $targetInvoice->id,
                    'special_exam_invoice_id' => null,
                    'installment_period' => $periodConfig['label'],
                    'outstanding_amount' => $amount,
                    'promised_settlement_date' => $finalsDueDate,
                    'submission_deadline' => $periodConfig['deadline'],
                    'student_contact' => $studentContact,
                    'guardian_name' => $guardianName,
                    'guardian_contact' => $guardianContact,
                    'signed_file_path' => $signedFilePath,
                    'original_filename' => $originalFilename,
                    'status' => $status,
                    'registrar_remarks' => $remarks,
                    'reviewed_by' => $reviewedBy,
                    'reviewed_at' => $reviewedAt,
                    'created_at' => $submittedAt,
                    'updated_at' => $reviewedAt ?? $submittedAt,
                ]
            );

            // Create submission audit log
            AuditLog::create([
                'action' => 'PROMISSORY_NOTE_UPLOADED',
                'description' => "Student {$student->name} uploaded signed Promissory Note {$noteNumber} for {$periodConfig['label']} (balance PHP " . number_format($amount, 2) . " to be moved to Finals).",
                'user_id' => $student->id,
                'actor_name' => $student->name,
                'actor_role' => 'student',
                'target_type' => 'PromissoryNote',
                'ip_address' => '127.0.0.1',
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt,
            ]);

            // If approved or rejected, log the registrar action
            if ($status === 'approved') {
                AuditLog::create([
                    'action' => 'PROMISSORY_NOTE_APPROVED',
                    'description' => "Registrar Elena Reyes approved Promissory Note {$noteNumber} for {$student->name}. Tuition balance moved to Finals term.",
                    'user_id' => $registrar?->id ?? 2,
                    'actor_name' => $registrar?->name ?? 'Elena Reyes (Registrar)',
                    'actor_role' => 'registrar',
                    'target_type' => 'PromissoryNote',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                ]);
            } elseif ($status === 'rejected') {
                AuditLog::create([
                    'action' => 'PROMISSORY_NOTE_REJECTED',
                    'description' => "Registrar Elena Reyes rejected Promissory Note {$noteNumber} for {$student->name}. Reason: {$remarks}",
                    'user_id' => $registrar?->id ?? 2,
                    'actor_name' => $registrar?->name ?? 'Elena Reyes (Registrar)',
                    'actor_role' => 'registrar',
                    'target_type' => 'PromissoryNote',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                ]);
            }

            $counter++;
        }
    }
}
