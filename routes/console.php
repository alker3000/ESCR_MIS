<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('escr:seed-bulk {count=500}', function (int $count = 500) {
    $start = microtime(true);
    $count = max(1, (int) $count);
    $this->info("Generating {$count} realistic ESCR students with enrollments, DMS docs, invoices, and payments...");

    $registrarId = User::where('role', 'registrar')->value('id');
    $cashierId = User::where('role', 'cashier')->value('id');
    $passwordHash = Hash::make('password123');
    $now = now();

    $firstNames = [
        'Miguel', 'Gabriel', 'Joshua', 'Christian', 'John Paul', 'Mark Anthony', 'Carlo', 'Rafael', 'Daniel', 'Kenneth',
        'Jerome', 'Angelo', 'Bryan', 'Kevin', 'Vincent', 'Renz', 'Patrick', 'Adrian', 'Francis', 'Jomar',
        'Camille', 'Angelica', 'Beatriz', 'Clarisse', 'Princess', 'Sophia', 'Patricia', 'Jasmine', 'Alyssa', 'Nicole',
        'Katrina', 'bianca', 'Hannah', 'Christine', 'Maricar', 'Erica', 'Joyce', 'Rica', 'Denise', 'Michaela',
    ];
    $middleNames = ['Reyes', 'Santos', 'Cruz', 'Bautista', 'Mendoza', 'Villanueva', 'Ramos', 'Garcia', 'Flores', 'Gonzales', 'Aquino', 'Castro'];
    $lastNames = [
        'Dela Cruz', 'Santos', 'Reyes', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Tomas', 'Andres',
        'Castillo', 'Villanueva', 'Ramos', 'Castro', 'Rivera', 'Flores', 'Navarro', 'Salazar', 'Mercado', 'Aguilar',
        'Pascual', 'Soriano', 'Tolentino', 'Valdez', 'De Guzman', 'San Juan', 'halili', 'Manalo', 'Dimaculangan', 'Panganiban',
    ];
    $towns = [
        'Brgy. San Juan, Morong, Rizal',
        'Brgy. San Pedro, Morong, Rizal',
        'Brgy. Maybancal, Morong, Rizal',
        'Brgy. CCL, Morong, Rizal',
        'Brgy. San Jose, Baras, Rizal',
        'Brgy. Plaza Aldea, Tanay, Rizal',
        'Brgy. Dalig, Teresa, Rizal',
        'Brgy. Looc, Cardona, Rizal',
        'Brgy. Calumpang, Binangonan, Rizal',
    ];
    $courses = [
        'BS in Information Technology (BSIT)',
        'BS in Computer Science (BSCS)',
        'BS in Business Administration (BSBA)',
        'BS in Hospitality Management (BSHM)',
        'BS in Accounting Information System (BSAIS)',
        'Bachelor of Secondary Education (BSED)',
        'Bachelor of Elementary Education (BEED)',
    ];
    $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    $existingBulkUsers = User::where('email', 'like', 'student.bulk.%@escr.edu.ph')->count();
    $startIndex = $existingBulkUsers + 1;

    DB::transaction(function () use (
        $count,
        $startIndex,
        $passwordHash,
        $now,
        $registrarId,
        $cashierId,
        $firstNames,
        $middleNames,
        $lastNames,
        $towns,
        $courses,
        $yearLevels
    ) {
        $userRows = [];
        for ($i = 0; $i < $count; $i++) {
            $seq = $startIndex + $i;
            $fn = ucfirst($firstNames[$seq % count($firstNames)]);
            $mn = $middleNames[($seq * 3) % count($middleNames)];
            $ln = ucfirst($lastNames[($seq * 7) % count($lastNames)]);
            $isEnrolled = ($seq % 10) < 8; // 80% officially enrolled, 10% awaiting payment, 10% pending review

            $userRows[] = [
                'name' => "{$fn} {$mn} {$ln}",
                'email' => sprintf('student.bulk.%04d@escr.edu.ph', $seq),
                'role' => 'student',
                'student_number' => $isEnrolled ? sprintf('2026-%05d', 1000 + $seq) : null,
                'contact_number' => sprintf('0917%07d', 1000000 + $seq),
                'is_active' => true,
                'email_verified_at' => $now,
                'password' => $passwordHash,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($userRows, 200) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $insertedUsers = DB::table('users')
            ->where('email', 'like', 'student.bulk.%@escr.edu.ph')
            ->orderBy('id')
            ->skip($startIndex - 1)
            ->take($count)
            ->get(['id', 'name', 'email', 'student_number', 'contact_number']);

        $enrollmentRows = [];
        foreach ($insertedUsers as $idx => $u) {
            $seq = $startIndex + $idx;
            $fn = ucfirst($firstNames[$seq % count($firstNames)]);
            $mn = $middleNames[($seq * 3) % count($middleNames)];
            $ln = ucfirst($lastNames[($seq * 7) % count($lastNames)]);
            $bucket = $seq % 10;
            $status = $bucket < 8 ? 'enrolled' : ($bucket === 8 ? 'approved_awaiting_payment' : 'pending');

            $enrollmentRows[] = [
                'user_id' => $u->id,
                'application_reference' => sprintf('APP-2026-B%04d', $seq),
                'academic_year' => 'SY 2026-2027',
                'semester' => '1st Semester',
                'course' => $courses[$seq % count($courses)],
                'year_level' => $yearLevels[$seq % count($yearLevels)],
                'student_type' => $seq % 3 === 0 ? 'Continuing / Old' : 'New / Freshman',
                'first_name' => $fn,
                'middle_name' => $mn,
                'last_name' => $ln,
                'gender' => $seq % 2 === 0 ? 'Male' : 'Female',
                'birth_date' => sprintf('200%d-%02d-%02d', 4 + ($seq % 4), 1 + ($seq % 12), 1 + ($seq % 27)),
                'address' => $towns[$seq % count($towns)],
                'contact_number' => $u->contact_number,
                'guardian_name' => "Roberto {$ln}",
                'guardian_relationship' => 'Parent',
                'guardian_contact' => sprintf('0918%07d', 2000000 + $seq),
                'previous_school' => 'Morong National High School',
                'previous_school_year' => '2025-2026',
                'payment_scheme' => 'installment',
                'status' => $status,
                'exam_restricted' => false,
                'exam_restriction_reason' => null,
                'registrar_remarks' => $status === 'pending' ? null : 'All enrollment credentials verified.',
                'reviewed_by' => $status === 'pending' ? null : $registrarId,
                'reviewed_at' => $status === 'pending' ? null : $now,
                'enrolled_at' => $status === 'enrolled' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($enrollmentRows, 200) as $chunk) {
            DB::table('enrollments')->insert($chunk);
        }

        $insertedEnrollments = DB::table('enrollments')
            ->where('application_reference', 'like', 'APP-2026-B%')
            ->orderBy('id')
            ->skip($startIndex - 1)
            ->take($count)
            ->get(['id', 'user_id', 'status', 'application_reference']);

        $docRows = [];
        $invoiceRows = [];

        foreach ($insertedEnrollments as $idx => $enr) {
            $seq = $startIndex + $idx;
            $docStatus = $enr->status === 'pending' ? 'pending' : 'approved';

            foreach (['psa_birth_certificate', 'form_138_report_card', 'good_moral_certificate'] as $docType) {
                $docRows[] = [
                    'enrollment_id' => $enr->id,
                    'user_id' => $enr->user_id,
                    'document_type' => $docType,
                    'original_filename' => "{$docType}_{$seq}.pdf",
                    'file_path' => "documents/demo/{$docType}.pdf",
                    'mime_type' => 'application/pdf',
                    'file_size' => 145000,
                    'status' => $docStatus,
                    'remarks' => $docStatus === 'approved' ? 'Verified' : null,
                    'reviewed_by' => $docStatus === 'approved' ? $registrarId : null,
                    'reviewed_at' => $docStatus === 'approved' ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($enr->status !== 'pending') {
                $dpPaid = $enr->status === 'enrolled';
                $prelimPaid = $dpPaid && ($seq % 2 === 0);

                $invoiceRows[] = [
                    'invoice_number' => sprintf('DP-2026-B%04d', $seq),
                    'user_id' => $enr->user_id,
                    'enrollment_id' => $enr->id,
                    'invoice_type' => 'downpayment',
                    'exam_period' => null,
                    'title' => 'Upon Enrollment: Initial Downpayment',
                    'description' => 'Required initial downpayment to activate official enrollment for 1st Semester (SY 2026-2027).',
                    'amount' => 5000.00,
                    'amount_paid' => $dpPaid ? 5000.00 : 0.00,
                    'due_date' => '2026-07-25',
                    'promised_date' => null,
                    'status' => $dpPaid ? 'paid' : 'unpaid',
                    'paid_at' => $dpPaid ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $periods = [
                    ['code' => 'PRL', 'type' => 'prelim_installment', 'period' => 'Prelim', 'title' => '1st Installment (Before Prelim Week)', 'due' => '2027-08-17', 'paid' => $prelimPaid],
                    ['code' => 'MID', 'type' => 'midterm_installment', 'period' => 'Midterm', 'title' => '2nd Installment (Before Midterm Week)', 'due' => '2027-09-14', 'paid' => false],
                    ['code' => 'SEMI', 'type' => 'semifinal_installment', 'period' => 'Semi-Finals', 'title' => '3rd Installment (Before Semi-Finals Week)', 'due' => '2027-10-12', 'paid' => false],
                    ['code' => 'FIN', 'type' => 'finals_installment', 'period' => 'Finals', 'title' => '4th Installment (Before Finals Week)', 'due' => '2027-11-09', 'paid' => false],
                ];

                foreach ($periods as $p) {
                    $invoiceRows[] = [
                        'invoice_number' => sprintf('%s-2026-B%04d', $p['code'], $seq),
                        'user_id' => $enr->user_id,
                        'enrollment_id' => $enr->id,
                        'invoice_type' => $p['type'],
                        'exam_period' => $p['period'],
                        'title' => $p['title'],
                        'description' => "Installment due before {$p['period']} week ({$p['due']}).",
                        'amount' => 5000.00,
                        'amount_paid' => $p['paid'] ? 5000.00 : 0.00,
                        'due_date' => $p['due'],
                        'promised_date' => null,
                        'status' => $p['paid'] ? 'paid' : 'unpaid',
                        'paid_at' => $p['paid'] ? $now : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($docRows, 300) as $chunk) {
            DB::table('student_documents')->insert($chunk);
        }

        foreach (array_chunk($invoiceRows, 300) as $chunk) {
            DB::table('invoices')->insert($chunk);
        }

        $paidInvoices = DB::table('invoices')
            ->where('invoice_number', 'like', '%-2026-B%')
            ->where('status', 'paid')
            ->orderBy('id')
            ->get(['id', 'invoice_number', 'user_id', 'amount']);

        $paymentRows = [];
        foreach ($paidInvoices as $idx => $inv) {
            $seq = $idx + 1;
            $isCash = $seq % 4 === 0;
            $paymentRows[] = [
                'receipt_number' => sprintf('OR-2026-BLK%05d', $startIndex * 10 + $seq),
                'invoice_id' => $inv->id,
                'user_id' => $inv->user_id,
                'payment_method' => $isCash ? 'cash' : 'gcash',
                'payment_mode' => $isCash ? 'walk_in_cash' : 'sandbox_instant',
                'gcash_reference_number' => $isCash
                    ? sprintf('CSH-2026-B%05d', $startIndex * 10 + $seq)
                    : sprintf('9018%09d', 100000 + $seq),
                'gcash_sender_name' => 'ESCR Student Payer',
                'gcash_sender_number' => '09178883727',
                'amount' => $inv->amount,
                'proof_file_path' => null,
                'status' => 'verified',
                'verified_by' => $cashierId,
                'verified_at' => $now,
                'cashier_remarks' => $isCash ? 'Walk-in cash payment verified at Cashier Window.' : 'GCash payment verified.',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($paymentRows, 300) as $chunk) {
            DB::table('payments')->insert($chunk);
        }
    });

    $elapsed = round((microtime(true) - $start) * 1000, 1);
    $totalStudents = DB::table('users')->where('role', 'student')->count();
    $totalEnrollments = DB::table('enrollments')->count();
    $totalInvoices = DB::table('invoices')->count();
    $totalPayments = DB::table('payments')->count();

    $this->info("Completed in {$elapsed} ms!");
    $this->line("Database Totals -> Students: {$totalStudents} | Enrollments: {$totalEnrollments} | Invoices: {$totalInvoices} | Payments: {$totalPayments}");
})->purpose('Bulk-populate realistic ESCR students, enrollments, documents, invoices, and payments');
