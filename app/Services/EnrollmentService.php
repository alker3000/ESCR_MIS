<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\StudentDocument;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentService
{
    /**
     * Create or update a student's enrollment application and upload documents into private DMS storage.
     */
    public function submitApplication(User $student, array $data, array $uploadedFiles = []): Enrollment
    {
        return DB::transaction(function () use ($student, $data, $uploadedFiles) {
            $activeYear = (string) SystemSetting::getValue('academic_year', 'SY 2026-2027');
            $activeSemester = (string) SystemSetting::getValue('semester', '1st Semester');

            $enrollment = Enrollment::where('user_id', $student->id)
                ->where('academic_year', $activeYear)
                ->where('semester', $activeSemester)
                ->latest('id')
                ->first();
            $isNew = !$enrollment;

            $previousEnrollment = Enrollment::where('user_id', $student->id)
                ->where(function ($q) use ($activeYear, $activeSemester) {
                    $q->where('academic_year', '!=', $activeYear)
                        ->orWhere('semester', '!=', $activeSemester);
                })
                ->with('documents')
                ->latest('id')
                ->first();

            $payload = [
                'academic_year' => $activeYear,
                'semester' => $activeSemester,
                'course' => $data['course'],
                'year_level' => $data['year_level'],
                'student_type' => $data['student_type'] ?? ($previousEnrollment ? 'Continuing / Old' : 'New / Freshman'),
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'address' => $data['address'],
                'contact_number' => $data['contact_number'],
                'guardian_name' => $data['guardian_name'],
                'guardian_relationship' => $data['guardian_relationship'] ?? 'Parent/Guardian',
                'guardian_contact' => $data['guardian_contact'],
                'previous_school' => $data['previous_school'] ?? null,
                'previous_school_year' => $data['previous_school_year'] ?? null,
                'payment_scheme' => $data['payment_scheme'] ?? 'installment',
                'status' => 'pending',
            ];

            if ($isNew) {
                $payload['user_id'] = $student->id;
                $payload['application_reference'] = 'APP-2026-' . strtoupper(Str::random(4)) . rand(10, 99);
                $enrollment = Enrollment::create($payload);
            } else {
                $enrollment->update($payload);
            }

            $student->update([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'contact_number' => $data['contact_number'],
            ]);

            foreach ($uploadedFiles as $docType => $file) {
                if ($file instanceof UploadedFile) {
                    $this->storeDocument($enrollment, $student, $docType, $file);
                }
            }

            // Carry over previously verified DMS documents for Continuing Students enrolling in a new semester
            if ($isNew && $previousEnrollment && $previousEnrollment->documents->isNotEmpty()) {
                foreach ($previousEnrollment->documents as $prevDoc) {
                    if (!isset($uploadedFiles[$prevDoc->document_type])) {
                        StudentDocument::firstOrCreate(
                            [
                                'enrollment_id' => $enrollment->id,
                                'document_type' => $prevDoc->document_type,
                            ],
                            [
                                'user_id' => $student->id,
                                'original_filename' => $prevDoc->original_filename,
                                'file_path' => $prevDoc->file_path,
                                'mime_type' => $prevDoc->mime_type,
                                'file_size' => $prevDoc->file_size,
                                'status' => $prevDoc->status,
                                'remarks' => $prevDoc->remarks ?? "Carried over from {$previousEnrollment->semester} ({$previousEnrollment->academic_year}).",
                                'reviewed_by' => $prevDoc->reviewed_by,
                                'reviewed_at' => $prevDoc->reviewed_at,
                            ]
                        );
                    }
                }
            }

            AuditService::log(
                $isNew ? 'ENROLLMENT_SUBMITTED' : 'ENROLLMENT_UPDATED',
                "Student {$student->name} submitted enrollment application ({$enrollment->application_reference}) for {$enrollment->semester}, {$enrollment->academic_year}.",
                'Enrollment',
                $enrollment->id,
                null,
                [
                    'academic_year' => $enrollment->academic_year,
                    'semester' => $enrollment->semester,
                    'course' => $enrollment->course,
                    'payment_scheme' => $enrollment->payment_scheme,
                ],
                $student
            );

            NotificationService::send(
                $student,
                'enrollment_status',
                "Enrollment Application Received ({$enrollment->application_reference})",
                "Dear {$student->name},\n\nYour enrollment application for {$enrollment->course} ({$enrollment->academic_year} - {$enrollment->semester}) has been received and queued for Registrar document review.\n\nApplication Reference: {$enrollment->application_reference}\n\nThank you,\nEast Systems Colleges of Rizal"
            );

            return $enrollment;
        });
    }

    public function storeDocument(Enrollment $enrollment, User $student, string $docType, UploadedFile $file): StudentDocument
    {
        $ext = $file->getClientOriginalExtension() ?: 'pdf';
        $safeName = Str::uuid()->toString() . '.' . $ext;
        $path = $file->storeAs('documents/enrollments/' . $enrollment->id, $safeName, 'local');

        return StudentDocument::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'document_type' => $docType,
            ],
            [
                'user_id' => $student->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'remarks' => null,
            ]
        );
    }

    /**
     * Registrar approves an enrollment application and generates the corresponding invoices.
     */
    public function approveApplication(Enrollment $enrollment, User $registrar, ?string $remarks = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $registrar, $remarks) {
            $oldStatus = $enrollment->status;

            // Approve all pending documents for this enrollment
            $enrollment->documents()->where('status', 'pending')->update([
                'status' => 'approved',
                'reviewed_by' => $registrar->id,
                'reviewed_at' => now(),
            ]);

            $enrollment->update([
                'status' => 'approved_awaiting_payment',
                'registrar_remarks' => $remarks ?? 'All enrollment documents verified and approved.',
                'reviewed_by' => $registrar->id,
                'reviewed_at' => now(),
            ]);

            // Generate Tuition / Installment Invoices if not already generated
            $this->generateEnrollmentInvoices($enrollment);

            AuditService::log(
                'ENROLLMENT_APPROVED',
                "Registrar {$registrar->name} approved application {$enrollment->application_reference} for {$enrollment->fullName()}.",
                'Enrollment',
                $enrollment->id,
                ['status' => $oldStatus],
                ['status' => 'approved_awaiting_payment', 'payment_scheme' => $enrollment->payment_scheme],
                $registrar
            );

            NotificationService::send(
                $enrollment->user,
                'enrollment_status',
                'Enrollment Documents Approved - Proceed to Payment',
                "Dear {$enrollment->fullName()},\n\nYour enrollment documents for {$enrollment->course} have been verified and APPROVED by the Office of the Registrar.\n\nYour tuition invoice(s) under the " . strtoupper($enrollment->payment_scheme) . " plan have been generated. Please log in to your Student Portal and complete your initial GCash payment to be officially enrolled.\n\nEast Systems Colleges of Rizal"
            );

            return $enrollment;
        });
    }

    /**
     * Registrar returns application to student due to invalid/missing documents.
     */
    public function returnApplication(Enrollment $enrollment, User $registrar, string $remarks): Enrollment
    {
        $oldStatus = $enrollment->status;

        $enrollment->update([
            'status' => 'returned',
            'registrar_remarks' => $remarks,
            'reviewed_by' => $registrar->id,
            'reviewed_at' => now(),
        ]);

        AuditService::log(
            'ENROLLMENT_RETURNED',
            "Registrar {$registrar->name} returned application {$enrollment->application_reference} for revision: {$remarks}",
            'Enrollment',
            $enrollment->id,
            ['status' => $oldStatus],
            ['status' => 'returned', 'remarks' => $remarks],
            $registrar
        );

        NotificationService::send(
            $enrollment->user,
            'document_rejected',
            'Action Required: Enrollment Documents Need Revision',
            "Dear {$enrollment->fullName()},\n\nThe Registrar reviewed your application ({$enrollment->application_reference}) and requested an update:\n\n\"{$remarks}\"\n\nPlease log in to the ESCR Student Portal to re-upload the required documents.\n\nEast Systems Colleges of Rizal"
        );

        return $enrollment;
    }

    /**
     * Switch a student's payment scheme before they have paid their initial tuition/downpayment.
     */
    public function switchPaymentScheme(Enrollment $enrollment, string $scheme): void
    {
        if ($enrollment->status === 'enrolled') {
            return;
        }

        DB::transaction(function () use ($enrollment, $scheme) {
            $enrollment->update(['payment_scheme' => $scheme]);

            // Remove unpaid tuition/installment invoices and regenerate
            $enrollment->invoices()
                ->whereIn('invoice_type', [
                    'full_tuition',
                    'downpayment',
                    'prelim_installment',
                    'midterm_installment',
                    'semifinal_installment',
                    'finals_installment',
                ])
                ->where('status', 'unpaid')
                ->delete();

            if ($enrollment->status === 'approved_awaiting_payment') {
                $this->generateEnrollmentInvoices($enrollment);
            }
        });
    }

    /**
     * Generate Full Payment or 5-part Installment invoices (Downpayment + Prelim, Midterm, Semi-Finals, Finals).
     */
    public function generateEnrollmentInvoices(Enrollment $enrollment): void
    {
        $existingTuitionInvoices = $enrollment->invoices()
            ->whereIn('invoice_type', ['full_tuition', 'downpayment'])
            ->count();

        if ($existingTuitionInvoices > 0) {
            return;
        }

        $totalTuition = (float) SystemSetting::getValue('base_tuition_fee', 25000.00);
        $downpayment = (float) SystemSetting::getValue('downpayment_amount', 5000.00);

        if ($enrollment->payment_scheme === 'full') {
            Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber('TUI'),
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'invoice_type' => 'full_tuition',
                'exam_period' => null,
                'title' => "Full Tuition & Miscellaneous Fee ({$enrollment->semester}, {$enrollment->academic_year})",
                'description' => "Full semester payment for {$enrollment->course} ({$enrollment->year_level}).",
                'amount' => $totalTuition,
                'amount_paid' => 0,
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'unpaid',
            ]);
            return;
        }

        // Installment Plan (1st Semester): Downpayment + 4 Equal Installments
        $remaining = max(0, $totalTuition - $downpayment);
        $perInstallment = round($remaining / 4, 2);

        // 1. Initial Downpayment (Required upon enrollment)
        Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber('DP'),
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'invoice_type' => 'downpayment',
            'exam_period' => null,
            'title' => 'Upon Enrollment: Initial Downpayment',
            'description' => "Required initial downpayment to activate official enrollment for {$enrollment->semester} ({$enrollment->academic_year}).",
            'amount' => $downpayment,
            'amount_paid' => 0,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'unpaid',
        ]);

        // 2. Four Exam Period Installments (per ESCR Schedule)
        $installments = [
            [
                'type' => 'prelim_installment',
                'period' => 'Prelim',
                'title' => '1st Installment (Before Prelim Week)',
                'due_date' => SystemSetting::getValue('prelim_deadline', '2027-08-17'),
                'pn_deadline' => SystemSetting::getValue('prelim_pn_deadline', '2027-08-10'),
            ],
            [
                'type' => 'midterm_installment',
                'period' => 'Midterm',
                'title' => '2nd Installment (Before Midterm Week)',
                'due_date' => SystemSetting::getValue('midterm_deadline', '2027-09-14'),
                'pn_deadline' => SystemSetting::getValue('midterm_pn_deadline', '2027-09-07'),
            ],
            [
                'type' => 'semifinal_installment',
                'period' => 'Semi-Finals',
                'title' => '3rd Installment (Before Semi-Finals Week)',
                'due_date' => SystemSetting::getValue('semifinal_deadline', '2027-10-12'),
                'pn_deadline' => SystemSetting::getValue('semifinal_pn_deadline', '2027-10-05'),
            ],
            [
                'type' => 'finals_installment',
                'period' => 'Finals',
                'title' => '4th Installment (Before Finals Week)',
                'due_date' => SystemSetting::getValue('finals_deadline', '2027-11-09'),
                'pn_deadline' => SystemSetting::getValue('finals_pn_deadline', '2027-11-02'),
            ],
        ];

        foreach ($installments as $inst) {
            Invoice::create([
                'invoice_number' => $this->nextInvoiceNumber('INS'),
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'invoice_type' => $inst['type'],
                'exam_period' => $inst['period'],
                'title' => $inst['title'],
                'description' => "Installment due before {$inst['period']} week ({$inst['due_date']}). Promissory note submission deadline: {$inst['pn_deadline']}.",
                'amount' => $perInstallment,
                'amount_paid' => 0,
                'due_date' => $inst['due_date'],
                'status' => 'unpaid',
            ]);
        }
    }

    public function nextInvoiceNumber(string $prefix = 'INV'): string
    {
        return $prefix . '-2026-' . strtoupper(Str::random(3)) . rand(100, 999);
    }
}
