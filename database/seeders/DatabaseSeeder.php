<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Models\StudentDocument;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\TelaOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Settings (ESCR Deadlines, Fees, Uniform Tela Pricing)
        $defaultSettings = [
            ['key' => 'school_name', 'value' => 'East Systems Colleges of Rizal', 'group' => 'general', 'label' => 'School Name'],
            ['key' => 'school_location', 'value' => 'Morong, Rizal', 'group' => 'general', 'label' => 'School Location'],
            ['key' => 'academic_year', 'value' => 'SY 2026-2027', 'group' => 'general', 'label' => 'Academic Year'],
            ['key' => 'semester', 'value' => '1st Semester', 'group' => 'general', 'label' => 'Active Semester'],
            ['key' => 'base_tuition_fee', 'value' => '25000.00', 'group' => 'fees', 'label' => 'Base Semester Tuition & Misc Fee (PHP)'],
            ['key' => 'downpayment_amount', 'value' => '5000.00', 'group' => 'fees', 'label' => 'Required Enrollment Downpayment (PHP)'],
            ['key' => 'special_exam_fee', 'value' => '350.00', 'group' => 'fees', 'label' => 'Special Examination Fee (PHP)'],
            ['key' => 'completion_fee', 'value' => '500.00', 'group' => 'fees', 'label' => 'Incomplete Completion (Remedial) Fee (PHP)'],
            ['key' => 'prelim_deadline', 'value' => '2027-08-17', 'group' => 'deadlines', 'label' => 'Before Prelim Week Payment Deadline'],
            ['key' => 'prelim_pn_deadline', 'value' => '2027-08-10', 'group' => 'deadlines', 'label' => 'Prelim Promissory Note Deadline'],
            ['key' => 'midterm_deadline', 'value' => '2027-09-14', 'group' => 'deadlines', 'label' => 'Before Midterm Week Payment Deadline'],
            ['key' => 'midterm_pn_deadline', 'value' => '2027-09-07', 'group' => 'deadlines', 'label' => 'Midterm Promissory Note Deadline'],
            ['key' => 'semifinal_deadline', 'value' => '2027-10-12', 'group' => 'deadlines', 'label' => 'Before Semi-Finals Week Payment Deadline'],
            ['key' => 'semifinal_pn_deadline', 'value' => '2027-10-05', 'group' => 'deadlines', 'label' => 'Semi-Finals Promissory Note Deadline'],
            ['key' => 'finals_deadline', 'value' => '2027-11-09', 'group' => 'deadlines', 'label' => 'Before Finals Week Payment Deadline'],
            ['key' => 'finals_pn_deadline', 'value' => '2027-11-02', 'group' => 'deadlines', 'label' => 'Finals Promissory Note Deadline'],
            ['key' => 'tela_blouse_polo_price', 'value' => '160.00', 'group' => 'tela', 'label' => 'Blouse / Polo Fabric Price per Yard (PHP)'],
            ['key' => 'tela_skirt_price', 'value' => '100.00', 'group' => 'tela', 'label' => 'Skirt Fabric Price per Yard (PHP)'],
            ['key' => 'logo_patch_price', 'value' => '100.00', 'group' => 'tela', 'label' => 'School Logo Patch Price per Piece (PHP)'],
            ['key' => 'gcash_merchant_name', 'value' => 'EAST SYSTEMS COLLEGES OF RIZAL', 'group' => 'gcash', 'label' => 'GCash Merchant Name'],
            ['key' => 'gcash_merchant_number', 'value' => '0917-888-3727', 'group' => 'gcash', 'label' => 'GCash Merchant Account Number'],
        ];

        foreach ($defaultSettings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. Seed Staff Accounts (Super Admin, Registrar, Cashier)
        $admin = User::updateOrCreate(
            ['email' => 'admin@escr.edu.ph'],
            [
                'name' => 'Dr. Maria Santos (Super Admin)',
                'role' => 'super_admin',
                'contact_number' => '09171001001',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        $registrar = User::updateOrCreate(
            ['email' => 'registrar@escr.edu.ph'],
            [
                'name' => 'Elena Reyes (Registrar)',
                'role' => 'registrar',
                'contact_number' => '09171001002',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@escr.edu.ph'],
            [
                'name' => 'Roberto Cruz (Cashier)',
                'role' => 'cashier',
                'contact_number' => '09171001003',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Seed Student 1: Juan Dela Cruz (Enrolled, On-Time Installment Paid, Uniform Tela Order Ready for Pickup)
        $student1 = User::updateOrCreate(
            ['email' => 'student@escr.edu.ph'],
            [
                'name' => 'Juan Dela Cruz',
                'role' => 'student',
                'student_number' => 'ESCR-2026-0001',
                'contact_number' => '09175550101',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        $enr1 = Enrollment::updateOrCreate(
            ['application_reference' => 'APP-2026-JUAN01'],
            [
                'user_id' => $student1->id,
                'academic_year' => 'SY 2026-2027',
                'semester' => '1st Semester',
                'course' => 'BS in Information Technology (BSIT)',
                'year_level' => '2nd Year',
                'student_type' => 'Continuing / Old',
                'first_name' => 'Juan',
                'middle_name' => 'Reyes',
                'last_name' => 'Dela Cruz',
                'gender' => 'Male',
                'birth_date' => '2006-04-15',
                'address' => 'Brgy. San Juan, Morong, Rizal',
                'contact_number' => '09175550101',
                'guardian_name' => 'Ricardo Dela Cruz',
                'guardian_relationship' => 'Father',
                'guardian_contact' => '09175550199',
                'previous_school' => 'Morong National High School',
                'previous_school_year' => '2024-2025',
                'payment_scheme' => 'installment',
                'status' => 'enrolled',
                'exam_restricted' => false,
                'registrar_remarks' => 'All enrollment documents verified.',
                'reviewed_by' => $registrar->id,
                'reviewed_at' => now()->subDays(10),
                'enrolled_at' => now()->subDays(9),
            ]
        );

        foreach ([
            ['psa_birth_certificate', 'juan_psa_birth_cert.pdf'],
            ['form_138_report_card', 'juan_form138_grades.pdf'],
            ['good_moral_certificate', 'juan_good_moral.pdf'],
        ] as [$docType, $fileName]) {
            StudentDocument::updateOrCreate(
                ['enrollment_id' => $enr1->id, 'document_type' => $docType],
                [
                    'user_id' => $student1->id,
                    'original_filename' => $fileName,
                    'file_path' => "documents/enrollments/{$enr1->id}/{$fileName}",
                    'mime_type' => 'application/pdf',
                    'file_size' => 245760,
                    'status' => 'approved',
                    'reviewed_by' => $registrar->id,
                    'reviewed_at' => now()->subDays(10),
                ]
            );
        }

        // Student 1 Invoices & Payments
        $dp1 = Invoice::updateOrCreate(
            ['invoice_number' => 'DP-2026-JUAN01'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'downpayment',
                'exam_period' => null,
                'title' => 'Upon Enrollment: Initial Downpayment',
                'description' => 'Required initial downpayment to activate official enrollment for 1st Semester (SY 2026-2027).',
                'amount' => 5000.00,
                'amount_paid' => 5000.00,
                'due_date' => '2026-07-25',
                'status' => 'paid',
                'paid_at' => now()->subDays(9),
            ]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'OR-2026-DP1001'],
            [
                'invoice_id' => $dp1->id,
                'user_id' => $student1->id,
                'payment_method' => 'gcash',
                'payment_mode' => 'sandbox_instant',
                'gcash_reference_number' => '9018442100112',
                'gcash_sender_name' => 'Juan Dela Cruz',
                'gcash_sender_number' => '09175550101',
                'amount' => 5000.00,
                'status' => 'verified',
                'verified_by' => $cashier->id,
                'verified_at' => now()->subDays(9),
                'cashier_remarks' => 'Verified initial enrollment downpayment.',
            ]
        );

        $prelim1 = Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-JUAN01'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'prelim_installment',
                'exam_period' => 'Prelim',
                'title' => '1st Installment (Before Prelim Week)',
                'description' => 'Installment due before Prelim week (2027-08-17). Promissory note deadline: 2027-08-10.',
                'amount' => 5000.00,
                'amount_paid' => 5000.00,
                'due_date' => '2027-08-17',
                'status' => 'paid',
                'paid_at' => now()->subDays(5),
            ]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'OR-2026-PR1002'],
            [
                'invoice_id' => $prelim1->id,
                'user_id' => $student1->id,
                'payment_method' => 'gcash',
                'payment_mode' => 'sandbox_instant',
                'gcash_reference_number' => '9018442100889',
                'gcash_sender_name' => 'Juan Dela Cruz',
                'gcash_sender_number' => '09175550101',
                'amount' => 5000.00,
                'status' => 'verified',
                'verified_by' => $cashier->id,
                'verified_at' => now()->subDays(5),
                'cashier_remarks' => 'Paid on time before Prelim deadline.',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-JUAN02'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'midterm_installment',
                'exam_period' => 'Midterm',
                'title' => '2nd Installment (Before Midterm Week)',
                'description' => 'Installment due before Midterm week (2027-09-14). Promissory note deadline: 2027-09-07.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-09-14',
                'status' => 'unpaid',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-JUAN03'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'semifinal_installment',
                'exam_period' => 'Semi-Finals',
                'title' => '3rd Installment (Before Semi-Finals Week)',
                'description' => 'Installment due before Semi-Finals week (2027-10-12). Promissory note deadline: 2027-10-05.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-10-12',
                'status' => 'unpaid',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-JUAN04'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'finals_installment',
                'exam_period' => 'Finals',
                'title' => '4th Installment (Before Finals Week)',
                'description' => 'Installment due before Finals week (2027-11-09). Promissory note deadline: 2027-11-02.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-11-09',
                'status' => 'unpaid',
            ]
        );

        // Student 1 Uniform Tela Order (Paid & Ready for Pickup!)
        $telaInv1 = Invoice::updateOrCreate(
            ['invoice_number' => 'FAB-2026-TELA01'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_type' => 'tela_order',
                'title' => 'Uniform Fabric (Tela) & Patch Order (TELA-2026-ORD01)',
                'description' => '2.5 yd Blouse/Polo (PHP 160/yd) + 2 pc Logo Patch (PHP 100/pc).',
                'amount' => 600.00,
                'amount_paid' => 600.00,
                'due_date' => now()->subDays(2)->toDateString(),
                'status' => 'paid',
                'paid_at' => now()->subDays(2),
            ]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'OR-2026-TL1003'],
            [
                'invoice_id' => $telaInv1->id,
                'user_id' => $student1->id,
                'payment_method' => 'gcash',
                'payment_mode' => 'sandbox_instant',
                'gcash_reference_number' => '9018442100955',
                'gcash_sender_name' => 'Juan Dela Cruz',
                'gcash_sender_number' => '09175550101',
                'amount' => 600.00,
                'status' => 'verified',
                'verified_by' => $cashier->id,
                'verified_at' => now()->subDays(2),
                'cashier_remarks' => 'Paid Uniform Tela order via GCash.',
            ]
        );

        TelaOrder::updateOrCreate(
            ['order_number' => 'TELA-2026-ORD01'],
            [
                'user_id' => $student1->id,
                'enrollment_id' => $enr1->id,
                'invoice_id' => $telaInv1->id,
                'blouse_polo_yards' => 2.50,
                'blouse_polo_unit_price' => 160.00,
                'skirt_yards' => 0,
                'skirt_unit_price' => 100.00,
                'logo_patch_qty' => 2,
                'logo_patch_unit_price' => 100.00,
                'total_amount' => 600.00,
                'student_notes' => '2.5 yards Polo fabric + 2 school patches for Men uniform.',
                'status' => 'ready_for_pickup',
                'ready_at' => now()->subDay(),
                'processed_by' => $cashier->id,
                'cashier_remarks' => 'Your 2.5 yards polo fabric and 2 logo patches are packed and ready at Window 2 (Cashier Office).',
            ]
        );

        SystemNotification::updateOrCreate(
            ['user_id' => $student1->id, 'subject' => 'Uniform Tela Ready for Pickup! (Order #TELA-2026-ORD01)'],
            [
                'type' => 'tela_ready_pickup',
                'message' => 'Your Uniform Fabric (Tela) & Logo Patch order (2.5 yd Blouse/Polo, 2 pc Logo Patch) is now READY FOR PICKUP at the ESCR Cashier Office.',
                'channel' => 'email_and_portal',
                'is_read' => false,
                'sent_at' => now()->subDay(),
            ]
        );

        // 4. Seed Student 2: Angela Mendoza (Unhappy Path Demo: Promissory Note Approved -> Must Pay Special Exam Fee via GCash!)
        $student2 = User::updateOrCreate(
            ['email' => 'promissory@escr.edu.ph'],
            [
                'name' => 'Angela Mendoza',
                'role' => 'student',
                'student_number' => 'ESCR-2026-0002',
                'contact_number' => '09175550202',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        $enr2 = Enrollment::updateOrCreate(
            ['application_reference' => 'APP-2026-ANG02'],
            [
                'user_id' => $student2->id,
                'academic_year' => 'SY 2026-2027',
                'semester' => '1st Semester',
                'course' => 'BS in Business Administration (BSBA)',
                'year_level' => '3rd Year',
                'student_type' => 'Continuing / Old',
                'first_name' => 'Angela',
                'middle_name' => 'Castro',
                'last_name' => 'Mendoza',
                'gender' => 'Female',
                'birth_date' => '2005-09-21',
                'address' => 'Brgy. Maybancal, Morong, Rizal',
                'contact_number' => '09175550202',
                'guardian_name' => 'Lorna Mendoza',
                'guardian_relationship' => 'Mother',
                'guardian_contact' => '09175550299',
                'previous_school' => 'Tomas Claudio Colleges',
                'previous_school_year' => '2023-2024',
                'payment_scheme' => 'installment',
                'status' => 'enrolled',
                'exam_restricted' => false,
                'exam_restriction_reason' => null,
                'registrar_remarks' => 'Documents verified.',
                'reviewed_by' => $registrar->id,
                'reviewed_at' => now()->subDays(14),
                'enrolled_at' => now()->subDays(13),
            ]
        );

        $dp2 = Invoice::updateOrCreate(
            ['invoice_number' => 'DP-2026-ANG01'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'downpayment',
                'title' => 'Upon Enrollment: Initial Downpayment',
                'description' => 'Required initial downpayment.',
                'amount' => 5000.00,
                'amount_paid' => 5000.00,
                'due_date' => '2026-07-25',
                'status' => 'paid',
                'paid_at' => now()->subDays(13),
            ]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'OR-2026-DP2001'],
            [
                'invoice_id' => $dp2->id,
                'user_id' => $student2->id,
                'payment_method' => 'gcash',
                'payment_mode' => 'sandbox_instant',
                'gcash_reference_number' => '9018442200115',
                'gcash_sender_name' => 'Angela Mendoza',
                'gcash_sender_number' => '09175550202',
                'amount' => 5000.00,
                'status' => 'verified',
                'verified_by' => $cashier->id,
                'verified_at' => now()->subDays(13),
                'cashier_remarks' => 'Downpayment verified.',
            ]
        );

        $prelim2 = Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-ANG01'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'prelim_installment',
                'exam_period' => 'Prelim',
                'title' => '1st Installment (Before Prelim Week)',
                'description' => 'Installment due before Prelim week (2027-08-17) — Balance of PHP 5,000.00 moved to 4th Installment (Finals) via Promissory Note PN-2026-DEMO01.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-08-17',
                'promised_date' => '2027-11-09',
                'status' => 'promised',
            ]
        );

        PromissoryNote::updateOrCreate(
            ['note_number' => 'PN-2026-DEMO01'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_id' => $prelim2->id,
                'special_exam_invoice_id' => null,
                'installment_period' => 'Prelim Installment',
                'outstanding_amount' => 5000.00,
                'promised_settlement_date' => '2027-11-09',
                'submission_deadline' => '2027-08-10',
                'student_contact' => '09175550202',
                'guardian_name' => 'Lorna Mendoza',
                'guardian_contact' => '09175550299',
                'signed_file_path' => "documents/promissory_notes/{$student2->id}/angela_signed_pn.pdf",
                'original_filename' => 'Angela_Mendoza_Signed_Promissory_Note.pdf',
                'status' => 'approved',
                'registrar_remarks' => 'Promissory Note approved. Tuition balance of PHP 5,000.00 moved to Final Term Installment (INS-2026-ANG04). Student is cleared for examination.',
                'reviewed_by' => $registrar->id,
                'reviewed_at' => now()->subDay(),
            ]
        );

        $midterm2 = Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-ANG02'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'midterm_installment',
                'exam_period' => 'Midterm',
                'title' => '2nd Installment (Before Midterm Week)',
                'description' => 'Installment due before Midterm week (2027-09-14). Promissory note deadline: 2027-09-07.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-09-14',
                'status' => 'unpaid',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-ANG03'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'semifinal_installment',
                'exam_period' => 'Semi-Finals',
                'title' => '3rd Installment (Before Semi-Finals Week)',
                'description' => 'Installment due before Semi-Finals week (2027-10-12). Promissory note deadline: 2027-10-05.',
                'amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-10-12',
                'status' => 'unpaid',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INS-2026-ANG04'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'finals_installment',
                'exam_period' => 'Finals',
                'title' => '4th Installment (Before Finals Week)',
                'description' => 'Installment due before Finals week (2027-11-09). Includes PHP 5,000.00 moved from Prelim Installment (Promissory Note PN-2026-DEMO01).',
                'amount' => 10000.00,
                'amount_paid' => 0.00,
                'due_date' => '2027-11-09',
                'status' => 'unpaid',
            ]
        );

        // Also create a Tela Order for Angela that is Paid & Awaiting Cashier Preparation
        $telaInv2 = Invoice::updateOrCreate(
            ['invoice_number' => 'FAB-2026-TELA02'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_type' => 'tela_order',
                'title' => 'Uniform Fabric (Tela) & Patch Order (TELA-2026-ORD02)',
                'description' => '2 yd Blouse/Polo (PHP 160/yd) + 2 yd Skirt (PHP 100/yd) + 1 pc Logo Patch (PHP 100/pc).',
                'amount' => 620.00,
                'amount_paid' => 620.00,
                'due_date' => now()->toDateString(),
                'status' => 'paid',
                'paid_at' => now()->subHours(5),
            ]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'OR-2026-TL2002'],
            [
                'invoice_id' => $telaInv2->id,
                'user_id' => $student2->id,
                'payment_method' => 'gcash',
                'payment_mode' => 'sandbox_instant',
                'gcash_reference_number' => '9018442200774',
                'gcash_sender_name' => 'Angela Mendoza',
                'gcash_sender_number' => '09175550202',
                'amount' => 620.00,
                'status' => 'verified',
                'verified_by' => $cashier->id,
                'verified_at' => now()->subHours(5),
                'cashier_remarks' => 'Paid via GCash.',
            ]
        );

        TelaOrder::updateOrCreate(
            ['order_number' => 'TELA-2026-ORD02'],
            [
                'user_id' => $student2->id,
                'enrollment_id' => $enr2->id,
                'invoice_id' => $telaInv2->id,
                'blouse_polo_yards' => 2.00,
                'blouse_polo_unit_price' => 160.00,
                'skirt_yards' => 2.00,
                'skirt_unit_price' => 100.00,
                'logo_patch_qty' => 1,
                'logo_patch_unit_price' => 100.00,
                'total_amount' => 620.00,
                'student_notes' => 'Female uniform set: 2 yds blouse, 2 yds skirt, 1 patch.',
                'status' => 'paid_processing',
            ]
        );

        // 5. Seed Student 3: Mark Anthony Villanueva (Pending Registrar Document Verification + Pending Promissory Note Demo)
        $student3 = User::updateOrCreate(
            ['email' => 'applicant@escr.edu.ph'],
            [
                'name' => 'Mark Anthony Villanueva',
                'role' => 'student',
                'student_number' => null,
                'contact_number' => '09175550303',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ]
        );

        $enr3 = Enrollment::updateOrCreate(
            ['application_reference' => 'APP-2026-ESCR01'],
            [
                'user_id' => $student3->id,
                'academic_year' => 'SY 2026-2027',
                'semester' => '1st Semester',
                'course' => 'BS in Computer Science (BSCS)',
                'year_level' => '1st Year',
                'student_type' => 'New / Freshman',
                'first_name' => 'Mark Anthony',
                'middle_name' => 'Gomez',
                'last_name' => 'Villanueva',
                'gender' => 'Male',
                'birth_date' => '2007-11-08',
                'address' => 'Brgy. San Guillermo, Morong, Rizal',
                'contact_number' => '09175550303',
                'guardian_name' => 'Evelyn Villanueva',
                'guardian_relationship' => 'Mother',
                'guardian_contact' => '09175550399',
                'previous_school' => 'Rizal National Science High School',
                'previous_school_year' => '2025-2026',
                'payment_scheme' => 'installment',
                'status' => 'pending',
                'exam_restricted' => false,
            ]
        );

        foreach ([
            ['psa_birth_certificate', 'mark_psa_birth_cert.pdf'],
            ['form_138_report_card', 'mark_form138_report_card.pdf'],
            ['good_moral_certificate', 'mark_good_moral_cert.pdf'],
            ['id_picture', 'mark_2x2_id_photo.jpg'],
        ] as [$docType, $fileName]) {
            StudentDocument::updateOrCreate(
                ['enrollment_id' => $enr3->id, 'document_type' => $docType],
                [
                    'user_id' => $student3->id,
                    'original_filename' => $fileName,
                    'file_path' => "documents/enrollments/{$enr3->id}/{$fileName}",
                    'mime_type' => 'application/pdf',
                    'file_size' => 198400,
                    'status' => 'pending',
                ]
            );
        }

        // Initial Audit Logs
        AuditLog::firstOrCreate(
            ['action' => 'SYSTEM_INITIALIZED', 'description' => 'Seeded ESCR SY 2026-2027 Enrollment & Payment System initial data.'],
            [
                'user_id' => $admin->id,
                'actor_name' => $admin->name,
                'actor_role' => 'super_admin',
                'target_type' => 'SystemSetting',
                'ip_address' => '127.0.0.1',
            ]
        );

        AuditLog::firstOrCreate(
            ['action' => 'PROMISSORY_NOTE_APPROVED', 'description' => 'Registrar Elena Reyes approved Promissory Note PN-2026-DEMO01 for Angela Mendoza and generated Special Exam Fee invoice SPC-2026-DEMO01 (PHP 350.00).'],
            [
                'user_id' => $registrar->id,
                'actor_name' => $registrar->name,
                'actor_role' => 'registrar',
                'target_type' => 'PromissoryNote',
                'target_id' => 1,
                'ip_address' => '127.0.0.1',
            ]
        );

        AuditLog::firstOrCreate(
            ['action' => 'TELA_READY_FOR_PICKUP', 'description' => 'Cashier Roberto Cruz marked Tela Order TELA-2026-ORD01 as Ready for Pickup for Juan Dela Cruz.'],
            [
                'user_id' => $cashier->id,
                'actor_name' => $cashier->name,
                'actor_role' => 'cashier',
                'target_type' => 'TelaOrder',
                'target_id' => 1,
                'ip_address' => '127.0.0.1',
            ]
        );
    }
}
