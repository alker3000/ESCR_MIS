<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TelaOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FeeAndTelaDataSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('role', 'cashier')->first() ?? User::find(3);
        $students = User::where('role', 'student')->with('enrollment')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found to seed fees and tela orders.');
            return;
        }

        $now = Carbon::now();

        // =========================================================================
        // 1. SPECIAL EXAM FEES (special_exam_fee) - 35 Records
        // =========================================================================
        $specialExamConfigs = [
            ['exam_period' => 'prelim', 'period_label' => 'Prelim Exam'],
            ['exam_period' => 'midterm', 'period_label' => 'Midterm Exam'],
            ['exam_period' => 'semifinal', 'period_label' => 'Semi-Final Exam'],
        ];

        $specialStudents = $students->slice(0, 36);
        $spxCounter = 1;

        foreach ($specialStudents as $student) {
            $config = $specialExamConfigs[($spxCounter - 1) % count($specialExamConfigs)];
            $invNumber = sprintf('SPX-2026-%04d', $spxCounter);
            $amount = 350.00;

            // Status: 22 Paid & Verified, 7 Pending Cashier Verification, 7 Unpaid
            if ($spxCounter <= 22) {
                $status = 'paid';
                $amountPaid = $amount;
                $paidAt = $now->copy()->subDays(rand(1, 20))->subHours(rand(1, 12));
            } elseif ($spxCounter <= 29) {
                $status = 'pending_verification';
                $amountPaid = 0.00;
                $paidAt = null;
            } else {
                $status = 'unpaid';
                $amountPaid = 0.00;
                $paidAt = null;
            }

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $invNumber],
                [
                    'user_id' => $student->id,
                    'enrollment_id' => $student->enrollment?->id,
                    'invoice_type' => 'special_exam_fee',
                    'exam_period' => $config['exam_period'],
                    'title' => "Special Examination Fee - {$config['period_label']}",
                    'description' => "Special examination permit fee for missed regular {$config['period_label']} schedule. Permitted after settlement.",
                    'amount' => $amount,
                    'amount_paid' => $amountPaid,
                    'due_date' => $now->copy()->addDays(5)->toDateString(),
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'created_at' => $paidAt ?? $now->copy()->subDays(rand(2, 15)),
                    'updated_at' => $now,
                ]
            );

            if ($status === 'paid') {
                $receiptNum = sprintf('RCP-2026-SPX%04d', $spxCounter);
                $gcashRef = '902' . sprintf('%010d', rand(1000000000, 9999999999));

                Payment::updateOrCreate(
                    ['receipt_number' => $receiptNum],
                    [
                        'invoice_id' => $invoice->id,
                        'user_id' => $student->id,
                        'payment_method' => 'gcash',
                        'payment_mode' => 'sandbox_instant',
                        'gcash_reference_number' => $gcashRef,
                        'gcash_sender_name' => $student->name,
                        'gcash_sender_number' => $student->contact_number ?? '0917' . rand(1000000, 9999999),
                        'amount' => $amount,
                        'status' => 'verified',
                        'verified_by' => $cashier?->id ?? 3,
                        'verified_at' => $paidAt,
                        'cashier_remarks' => 'Special Examination Fee verified. Exam clearance updated.',
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]
                );
            } elseif ($status === 'pending_verification') {
                $receiptNum = sprintf('RCP-2026-SPX%04d', $spxCounter);
                $gcashRef = '902' . sprintf('%010d', rand(1000000000, 9999999999));

                Payment::updateOrCreate(
                    ['receipt_number' => $receiptNum],
                    [
                        'invoice_id' => $invoice->id,
                        'user_id' => $student->id,
                        'payment_method' => 'gcash',
                        'payment_mode' => 'manual_upload',
                        'gcash_reference_number' => $gcashRef,
                        'gcash_sender_name' => $student->name,
                        'gcash_sender_number' => $student->contact_number ?? '0917' . rand(1000000, 9999999),
                        'amount' => $amount,
                        'status' => 'pending',
                        'verified_by' => null,
                        'verified_at' => null,
                        'cashier_remarks' => 'Awaiting cashier receipt verification.',
                        'created_at' => $now->copy()->subHours(rand(2, 36)),
                        'updated_at' => $now->copy()->subHours(rand(2, 36)),
                    ]
                );
            }

            $spxCounter++;
        }

        // =========================================================================
        // 2. COMPLETION & REMEDIAL FEES (completion_fee) - 35 Records
        // =========================================================================
        $remedialSubjects = [
            ['code' => 'CC101', 'title' => 'Introduction to Computing'],
            ['code' => 'MATH101', 'title' => 'College Algebra & Trigonometry'],
            ['code' => 'IT201', 'title' => 'Data Structures & Algorithms'],
            ['code' => 'ACT102', 'title' => 'Financial Accounting and Reporting'],
            ['code' => 'ENG102', 'title' => 'Purposive Communication'],
            ['code' => 'CS302', 'title' => 'Database Management Systems'],
            ['code' => 'HM104', 'title' => 'Food & Beverage Service Operations'],
            ['code' => 'ED201', 'title' => 'Facilitating Learner-Centered Teaching'],
            ['code' => 'IT303', 'title' => 'Systems Integration and Architecture'],
            ['code' => 'BA202', 'title' => 'Human Resource Management'],
        ];

        $completionStudents = $students->slice(10, 36);
        $cmpCounter = 1;

        foreach ($completionStudents as $student) {
            $subj = $remedialSubjects[($cmpCounter - 1) % count($remedialSubjects)];
            $invNumber = sprintf('CMP-2026-%04d', $cmpCounter);
            $amount = 500.00;

            // Status: 22 Paid & Verified, 7 Pending Cashier Verification, 7 Unpaid
            if ($cmpCounter <= 22) {
                $status = 'paid';
                $amountPaid = $amount;
                $paidAt = $now->copy()->subDays(rand(1, 25))->subHours(rand(1, 10));
            } elseif ($cmpCounter <= 29) {
                $status = 'pending_verification';
                $amountPaid = 0.00;
                $paidAt = null;
            } else {
                $status = 'unpaid';
                $amountPaid = 0.00;
                $paidAt = null;
            }

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $invNumber],
                [
                    'user_id' => $student->id,
                    'enrollment_id' => $student->enrollment?->id,
                    'invoice_type' => 'completion_fee',
                    'exam_period' => null,
                    'title' => "Incomplete Completion (Remedial) Fee - {$subj['code']}",
                    'description' => "Processing and examination fee for Incomplete (INC) grade removal / remedial course requirement in {$subj['code']} ({$subj['title']}).",
                    'amount' => $amount,
                    'amount_paid' => $amountPaid,
                    'due_date' => $now->copy()->addDays(7)->toDateString(),
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'created_at' => $paidAt ?? $now->copy()->subDays(rand(3, 20)),
                    'updated_at' => $now,
                ]
            );

            if ($status === 'paid') {
                $receiptNum = sprintf('RCP-2026-CMP%04d', $cmpCounter);
                $gcashRef = '903' . sprintf('%010d', rand(1000000000, 9999999999));

                Payment::updateOrCreate(
                    ['receipt_number' => $receiptNum],
                    [
                        'invoice_id' => $invoice->id,
                        'user_id' => $student->id,
                        'payment_method' => 'gcash',
                        'payment_mode' => 'sandbox_instant',
                        'gcash_reference_number' => $gcashRef,
                        'gcash_sender_name' => $student->name,
                        'gcash_sender_number' => $student->contact_number ?? '0917' . rand(1000000, 9999999),
                        'amount' => $amount,
                        'status' => 'verified',
                        'verified_by' => $cashier?->id ?? 3,
                        'verified_at' => $paidAt,
                        'cashier_remarks' => "Incomplete Completion Fee for {$subj['code']} verified and official completion receipt released.",
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]
                );
            } elseif ($status === 'pending_verification') {
                $receiptNum = sprintf('RCP-2026-CMP%04d', $cmpCounter);
                $gcashRef = '903' . sprintf('%010d', rand(1000000000, 9999999999));

                Payment::updateOrCreate(
                    ['receipt_number' => $receiptNum],
                    [
                        'invoice_id' => $invoice->id,
                        'user_id' => $student->id,
                        'payment_method' => 'gcash',
                        'payment_mode' => 'manual_upload',
                        'gcash_reference_number' => $gcashRef,
                        'gcash_sender_name' => $student->name,
                        'gcash_sender_number' => $student->contact_number ?? '0917' . rand(1000000, 9999999),
                        'amount' => $amount,
                        'status' => 'pending',
                        'verified_by' => null,
                        'verified_at' => null,
                        'cashier_remarks' => 'Awaiting cashier receipt verification.',
                        'created_at' => $now->copy()->subHours(rand(1, 24)),
                        'updated_at' => $now->copy()->subHours(rand(1, 24)),
                    ]
                );
            }

            $cmpCounter++;
        }

        // =========================================================================
        // 3. UNIFORM TELA & PATCHES (tela_orders + tela_order invoices) - 45 Records
        // =========================================================================
        $blousePrice = 160.00;
        $skirtPrice = 100.00;
        $patchPrice = 100.00;

        $telaOrderTemplates = [
            // [blouse, skirt, patches, note, gender]
            [2.5, 0.0, 2, '2.5 yds Polo fabric + 2 school patches for Men uniform.', 'Male'],
            [2.0, 2.0, 2, '2.0 yds Blouse + 2.0 yds Skirt + 2 logo patches for Women uniform set.', 'Female'],
            [3.0, 0.0, 3, '3.0 yds Polo fabric + 3 school patches (Tall size / reserve).', 'Male'],
            [2.5, 2.5, 2, '2.5 yds Blouse + 2.5 yds Skirt + 2 logo patches.', 'Female'],
            [0.0, 0.0, 3, '3 Extra school logo patches for replacement.', 'Any'],
            [0.0, 0.0, 4, '4 School logo patches for multiple uniforms.', 'Any'],
            [2.0, 0.0, 1, '2.0 yds Polo fabric + 1 logo patch.', 'Male'],
            [2.0, 2.5, 3, '2.0 yds Blouse + 2.5 yds Skirt + 3 logo patches set.', 'Female'],
            [3.0, 0.0, 2, '3.0 yds Polo fabric + 2 logo patches.', 'Male'],
            [0.0, 0.0, 2, '2 Logo patches only.', 'Any'],
        ];

        $telaStudents = $students->slice(15, 45);
        $telaCounter = 3; // 1 and 2 already exist from initial seed

        foreach ($telaStudents as $student) {
            $tpl = $telaOrderTemplates[($telaCounter - 3) % count($telaOrderTemplates)];
            $blouseYards = (float) $tpl[0];
            $skirtYards = (float) $tpl[1];
            $patches = (int) $tpl[2];
            $note = $tpl[3];

            $totalAmount = round(($blouseYards * $blousePrice) + ($skirtYards * $skirtPrice) + ($patches * $patchPrice), 2);
            $orderNumber = sprintf('TELA-2026-ORD%03d', $telaCounter);
            $invNumber = sprintf('FAB-2026-TELA%03d', $telaCounter);

            // Status Distribution:
            // 15 ready_for_pickup, 12 paid_processing, 10 claimed, 8 awaiting_payment
            if ($telaCounter <= 17) {
                $status = 'ready_for_pickup';
                $invStatus = 'paid';
                $amountPaid = $totalAmount;
                $paidAt = $now->copy()->subDays(rand(2, 8));
                $readyAt = $paidAt->copy()->addHours(rand(12, 36));
                $claimedAt = null;
                $processedBy = $cashier?->id ?? 3;
                $cashierRemarks = "Your uniform tela and patches have been measured, cut, and packed. Ready for pickup at Cashier Window 2.";
            } elseif ($telaCounter <= 29) {
                $status = 'paid_processing';
                $invStatus = 'paid';
                $amountPaid = $totalAmount;
                $paidAt = $now->copy()->subDays(rand(1, 3));
                $readyAt = null;
                $claimedAt = null;
                $processedBy = null;
                $cashierRemarks = null;
            } elseif ($telaCounter <= 39) {
                $status = 'claimed';
                $invStatus = 'paid';
                $amountPaid = $totalAmount;
                $paidAt = $now->copy()->subDays(rand(10, 25));
                $readyAt = $paidAt->copy()->addDays(1);
                $claimedAt = $readyAt->copy()->addDays(rand(1, 3));
                $processedBy = $cashier?->id ?? 3;
                $cashierRemarks = "Claimed in full by student at the Cashier Office on {$claimedAt->format('M d, Y')}.";
            } else {
                $status = 'awaiting_payment';
                $invStatus = 'unpaid';
                $amountPaid = 0.00;
                $paidAt = null;
                $readyAt = null;
                $claimedAt = null;
                $processedBy = null;
                $cashierRemarks = null;
            }

            // Description for invoice
            $descParts = [];
            if ($blouseYards > 0) $descParts[] = "{$blouseYards} yd Blouse/Polo fabric (PHP {$blousePrice}/yd)";
            if ($skirtYards > 0) $descParts[] = "{$skirtYards} yd Skirt fabric (PHP {$skirtPrice}/yd)";
            if ($patches > 0) $descParts[] = "{$patches} pc Logo Patch (PHP {$patchPrice}/pc)";
            $descStr = implode(' + ', $descParts) . '.';

            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => $invNumber],
                [
                    'user_id' => $student->id,
                    'enrollment_id' => $student->enrollment?->id,
                    'invoice_type' => 'tela_order',
                    'exam_period' => null,
                    'title' => "Uniform Fabric (Tela) & Patch Order ({$orderNumber})",
                    'description' => $descStr,
                    'amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'due_date' => $now->copy()->addDays(5)->toDateString(),
                    'status' => $invStatus,
                    'paid_at' => $paidAt,
                    'created_at' => $paidAt ?? $now->copy()->subDays(rand(1, 5)),
                    'updated_at' => $now,
                ]
            );

            if ($invStatus === 'paid') {
                $receiptNum = sprintf('OR-2026-TL%04d', $telaCounter);
                $gcashRef = '904' . sprintf('%010d', rand(1000000000, 9999999999));

                Payment::updateOrCreate(
                    ['receipt_number' => $receiptNum],
                    [
                        'invoice_id' => $invoice->id,
                        'user_id' => $student->id,
                        'payment_method' => 'gcash',
                        'payment_mode' => 'sandbox_instant',
                        'gcash_reference_number' => $gcashRef,
                        'gcash_sender_name' => $student->name,
                        'gcash_sender_number' => $student->contact_number ?? '0917' . rand(1000000, 9999999),
                        'amount' => $totalAmount,
                        'status' => 'verified',
                        'verified_by' => $cashier?->id ?? 3,
                        'verified_at' => $paidAt,
                        'cashier_remarks' => 'Uniform Tela & Patch payment verified via GCash.',
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]
                );
            }

            TelaOrder::updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $student->id,
                    'enrollment_id' => $student->enrollment?->id,
                    'invoice_id' => $invoice->id,
                    'blouse_polo_yards' => $blouseYards,
                    'blouse_polo_unit_price' => $blousePrice,
                    'skirt_yards' => $skirtYards,
                    'skirt_unit_price' => $skirtPrice,
                    'logo_patch_qty' => $patches,
                    'logo_patch_unit_price' => $patchPrice,
                    'total_amount' => $totalAmount,
                    'student_notes' => $note,
                    'status' => $status,
                    'ready_at' => $readyAt,
                    'claimed_at' => $claimedAt,
                    'processed_by' => $processedBy,
                    'cashier_remarks' => $cashierRemarks,
                    'created_at' => $paidAt ?? $now->copy()->subDays(rand(1, 5)),
                    'updated_at' => $readyAt ?? $paidAt ?? $now,
                ]
            );

            $telaCounter++;
        }

        // =========================================================================
        // 4. AUDIT LOGS FOR NEW SEEDED TRANSACTIONS
        // =========================================================================
        AuditLog::create([
            'action' => 'FEES_AND_TELA_BATCH_SEEDED',
            'description' => 'Successfully seeded comprehensive Special Exam Fees, Incomplete Remedial Fees, and Uniform Tela & Patch Orders across ESCR students.',
            'user_id' => 1,
            'actor_name' => 'Dr. Maria Santos (Super Admin)',
            'actor_role' => 'super_admin',
            'target_type' => 'Invoice',
            'ip_address' => '127.0.0.1',
            'new_values' => json_encode([
                'special_exam_fees_count' => count($specialStudents),
                'completion_fees_count' => count($completionStudents),
                'tela_orders_count' => count($telaStudents),
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
