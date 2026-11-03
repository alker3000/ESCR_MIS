<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Services\AuditService;
use App\Services\EnrollmentService;
use App\Services\PaymentService;
use App\Services\PromissoryNoteService;
use App\Services\TelaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected PaymentService $paymentService,
        protected PromissoryNoteService $promissoryNoteService,
        protected TelaService $telaService
    ) {}

    protected function resolveEnrollmentContext(Request $request, $student): array
    {
        $settings = SystemSetting::getAllKeyed();
        $activeYear = (string) ($settings['academic_year'] ?? 'SY 2026-2027');
        $activeSemester = (string) ($settings['semester'] ?? '1st Semester');

        $enrollments = $student->enrollments()->with(['documents', 'invoices'])->latest('id')->get();
        $requestedId = $request->query('enrollment_id');

        $enrollment = $requestedId
            ? ($enrollments->firstWhere('id', (int) $requestedId) ?? $enrollments->first())
            : $enrollments->first();

        $hasActiveTermEnrollment = $enrollments->contains(
            fn ($e) => $e->academic_year === $activeYear && $e->semester === $activeSemester
        );
        $canEnrollNewTerm = !$hasActiveTermEnrollment && $enrollments->isNotEmpty();

        return [
            'enrollment' => $enrollment,
            'enrollments' => $enrollments,
            'settings' => $settings,
            'activeYear' => $activeYear,
            'activeSemester' => $activeSemester,
            'canEnrollNewTerm' => $canEnrollNewTerm,
        ];
    }

    public function dashboard(Request $request)
    {
        $student = Auth::user();
        $ctx = $this->resolveEnrollmentContext($request, $student);
        $enrollment = $ctx['enrollment'];
        $enrollments = $ctx['enrollments'];
        $settings = $ctx['settings'];
        $activeYear = $ctx['activeYear'];
        $activeSemester = $ctx['activeSemester'];
        $canEnrollNewTerm = $ctx['canEnrollNewTerm'];

        if ($enrollment) {
            $this->paymentService->refreshExamClearanceStatus($enrollment);
            $enrollment->refresh();
        }

        $isLatestEnrollment = !$enrollment || $enrollment->id === $enrollments->first()?->id;
        $invoices = $student->invoices()
            ->when($enrollment, function ($q) use ($enrollment, $isLatestEnrollment) {
                $q->where(function ($sub) use ($enrollment, $isLatestEnrollment) {
                    $sub->where('enrollment_id', $enrollment->id);
                    if ($isLatestEnrollment) {
                        $sub->orWhereNull('enrollment_id');
                    }
                });
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $totalBilled = (float) $invoices->where('status', '!=', 'promised')->sum('amount');
        $totalPaid = (float) $invoices->where('status', 'paid')->sum('amount_paid');
        $outstandingBalance = max(0, $totalBilled - $totalPaid);

        $telaOrders = $student->telaOrders()->with('invoice')->latest()->get();
        $promissoryNotes = $student->promissoryNotes()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->with(['invoice', 'specialExamInvoice'])
            ->latest()
            ->get();
        $notifications = $student->systemNotifications()->latest()->take(8)->get();

        return view('student.dashboard', compact(
            'student',
            'enrollment',
            'enrollments',
            'canEnrollNewTerm',
            'activeYear',
            'activeSemester',
            'invoices',
            'totalBilled',
            'totalPaid',
            'outstandingBalance',
            'telaOrders',
            'promissoryNotes',
            'notifications',
            'settings'
        ));
    }

    public function enrollment()
    {
        $student = Auth::user();
        $settings = SystemSetting::getAllKeyed();
        $activeYear = (string) ($settings['academic_year'] ?? 'SY 2026-2027');
        $activeSemester = (string) ($settings['semester'] ?? '1st Semester');

        $enrollments = $student->enrollments()->with('documents')->latest('id')->get();
        $enrollment = $enrollments->first(
            fn ($e) => $e->academic_year === $activeYear && $e->semester === $activeSemester
        );
        $previousEnrollment = $enrollments->first(
            fn ($e) => $e->academic_year !== $activeYear || $e->semester !== $activeSemester
        );

        $courses = [
            'BS in Information Technology (BSIT)',
            'BS in Computer Science (BSCS)',
            'BS in Business Administration (BSBA)',
            'BS in Hospitality Management (BSHM)',
            'BS in Accounting Information System (BSAIS)',
            'Bachelor of Secondary Education (BSED)',
            'Bachelor of Elementary Education (BEED)',
        ];

        return view('student.enrollment', compact(
            'student',
            'enrollment',
            'previousEnrollment',
            'enrollments',
            'activeYear',
            'activeSemester',
            'settings',
            'courses'
        ));
    }

    public function submitEnrollment(Request $request)
    {
        $validated = $request->validate([
            'course' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:50'],
            'student_type' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date'],
            'address' => ['required', 'string', 'max:500'],
            'contact_number' => ['required', 'string', 'max:40'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_relationship' => ['required', 'string', 'max:100'],
            'guardian_contact' => ['required', 'string', 'max:40'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'previous_school_year' => ['nullable', 'string', 'max:50'],
            'payment_scheme' => ['required', 'in:full,installment'],
            'psa_birth_certificate' => ['nullable', 'file', 'max:10240'],
            'form_138_report_card' => ['nullable', 'file', 'max:10240'],
            'good_moral_certificate' => ['nullable', 'file', 'max:10240'],
            'id_picture' => ['nullable', 'file', 'max:10240'],
        ]);

        $files = [];
        foreach (['psa_birth_certificate', 'form_138_report_card', 'good_moral_certificate', 'id_picture'] as $docType) {
            if ($request->hasFile($docType)) {
                $files[$docType] = $request->file($docType);
            }
        }

        $enrollment = $this->enrollmentService->submitApplication(Auth::user(), $validated, $files);

        return redirect()->route('student.enrollment')
            ->with('success', "Enrollment application ({$enrollment->application_reference}) submitted to the Registrar for document verification!");
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => ['required', 'string'],
            'document_file' => ['required', 'file', 'max:10240'],
        ]);

        $student = Auth::user();
        $enrollment = $student->enrollment;

        if (!$enrollment) {
            return back()->with('error', 'Please complete your enrollment application form first.');
        }

        $doc = $this->enrollmentService->storeDocument(
            $enrollment,
            $student,
            $request->input('document_type'),
            $request->file('document_file')
        );

        if ($enrollment->status === 'returned') {
            $enrollment->update(['status' => 'pending']);
        }

        AuditService::log(
            'DOCUMENT_UPLOADED',
            "Student {$student->name} uploaded {$doc->typeLabel()} ({$doc->original_filename}).",
            'StudentDocument',
            $doc->id,
            null,
            ['document_type' => $doc->document_type],
            $student
        );

        return back()->with('success', "{$doc->typeLabel()} uploaded to the Document Management System!");
    }

    public function switchScheme(Request $request)
    {
        $request->validate([
            'payment_scheme' => ['required', 'in:full,installment'],
        ]);

        $enrollment = Auth::user()->enrollment;
        if (!$enrollment) {
            return back()->with('error', 'No active enrollment application found.');
        }

        $this->enrollmentService->switchPaymentScheme($enrollment, $request->input('payment_scheme'));

        return back()->with('success', 'Payment scheme updated to ' . strtoupper($request->input('payment_scheme')) . ' plan.');
    }

    public function billing(Request $request)
    {
        $student = Auth::user();
        $ctx = $this->resolveEnrollmentContext($request, $student);
        $enrollment = $ctx['enrollment'];
        $enrollments = $ctx['enrollments'];
        $settings = $ctx['settings'];

        if ($enrollment) {
            $this->paymentService->refreshExamClearanceStatus($enrollment);
            $enrollment->refresh();
        }

        $isLatestEnrollment = !$enrollment || $enrollment->id === $enrollments->first()?->id;
        $invoices = $student->invoices()
            ->when($enrollment, function ($q) use ($enrollment, $isLatestEnrollment) {
                $q->where(function ($sub) use ($enrollment, $isLatestEnrollment) {
                    $sub->where('enrollment_id', $enrollment->id);
                    if ($isLatestEnrollment) {
                        $sub->orWhereNull('enrollment_id');
                    }
                });
            })
            ->with(['payments', 'promissoryNote'])
            ->orderBy('id')
            ->get();

        $invoiceIds = $invoices->pluck('id');
        $payments = $student->payments()
            ->whereIn('invoice_id', $invoiceIds)
            ->with('invoice')
            ->latest()
            ->get();

        return view('student.billing', compact('student', 'enrollment', 'enrollments', 'invoices', 'payments', 'settings'));
    }

    public function showPayInvoice(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('student.billing')->with('info', 'This invoice is already paid.');
        }

        if ($invoice->status === 'promised') {
            $finalsInvoice = Invoice::where('user_id', Auth::id())
                ->when($invoice->enrollment_id, fn ($q) => $q->where('enrollment_id', $invoice->enrollment_id))
                ->where('invoice_type', 'finals_installment')
                ->latest('id')
                ->first();
            if ($finalsInvoice && $finalsInvoice->status !== 'paid') {
                return redirect()->route('student.billing.pay', $finalsInvoice)
                    ->with('info', "Your {$invoice->title} balance was moved to your Final Term Installment ({$finalsInvoice->invoice_number}) via an approved Promissory Note.");
            }
            return redirect()->route('student.billing')
                ->with('info', 'This installment balance has already been moved to your Final Term Installment.');
        }

        $settings = SystemSetting::getAllKeyed();
        return view('student.pay_gcash', compact('invoice', 'settings'));
    }

    public function processPayment(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_mode' => ['required', 'in:sandbox_instant,manual_reference,walk_in_cash'],
            'gcash_sender_name' => ['required', 'string', 'max:255'],
            'gcash_sender_number' => ['required', 'string', 'max:30'],
            'gcash_reference_number' => ['nullable', 'string', 'max:40'],
            'cash_reference_number' => ['nullable', 'string', 'max:40'],
            'proof_file' => ['nullable', 'file', 'max:10240'],
        ]);

        $payment = $this->paymentService->processGCashPayment(
            $invoice,
            Auth::user(),
            $validated,
            $request->file('proof_file')
        );

        if ($payment->status === 'verified') {
            return redirect()->route('student.receipt', $payment)
                ->with('success', "GCash Payment Verified! Official Receipt #{$payment->receipt_number} has been issued.");
        }

        if ($payment->payment_method === 'cash') {
            return redirect()->route('student.billing')
                ->with('success', "Walk-In Cash Payment Slip #{$payment->gcash_reference_number} generated! Present this reference at the School Cashier Window to pay in cash and receive your Official Receipt.");
        }

        return redirect()->route('student.billing')
            ->with('success', "Manual GCash Reference #{$payment->gcash_reference_number} submitted! Awaiting Cashier verification.");
    }

    public function requestCompletionFee(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => ['required', 'string', 'max:120'],
            'remarks' => ['nullable', 'string', 'max:300'],
        ]);

        $invoice = $this->paymentService->createCompletionFeeInvoice(
            Auth::user(),
            $validated['subject_code'],
            $validated['remarks'] ?? ''
        );

        return redirect()->route('student.billing.pay', $invoice)
            ->with('success', "Incomplete Completion (Remedial) Fee invoice ({$invoice->invoice_number}) generated! You may now pay via GCash.");
    }

    /**
     * Demo helper: Simulates missing an installment deadline so the student/instructor can demonstrate
     * the Overdue (Late) -> Account Exam Restricted -> Lifted only after paying workflow!
     */
    public function simulateOverdue(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice->update([
            'status' => 'overdue',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        if ($invoice->enrollment) {
            $this->paymentService->refreshExamClearanceStatus($invoice->enrollment);
        }

        return redirect()->route('student.billing')
            ->with('warning', "Late Payment Simulated: {$invoice->title} is now OVERDUE. Because the deadline has passed, Promissory Note submission is closed for this installment and your account is Exam Restricted until the overdue balance is paid.");
    }

    public function receipt(Payment $payment)
    {
        $user = Auth::user();
        if ($user->isStudent() && $payment->user_id !== $user->id) {
            abort(403);
        }

        $payment->load(['invoice', 'user.enrollment', 'verifier']);
        $settings = SystemSetting::getAllKeyed();

        return view('student.receipt', compact('payment', 'settings'));
    }

    public function promissoryNotes(Request $request)
    {
        $student = Auth::user();
        $ctx = $this->resolveEnrollmentContext($request, $student);
        $enrollment = $ctx['enrollment'];
        $enrollments = $ctx['enrollments'];
        $settings = $ctx['settings'];

        if ($enrollment) {
            $this->paymentService->refreshExamClearanceStatus($enrollment);
            $enrollment->refresh();
        }

        // Only upcoming UNPAID installments (not late/overdue) are eligible for a Promissory Note
        $installmentInvoices = $student->invoices()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->whereIn('invoice_type', [
                'prelim_installment',
                'midterm_installment',
                'semifinal_installment',
                'finals_installment',
            ])
            ->where('status', 'unpaid')
            ->get();

        // Late / Overdue installments cannot pass a Promissory Note; they must be paid to lift restriction
        $overdueInstallments = $student->invoices()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->whereIn('invoice_type', [
                'prelim_installment',
                'midterm_installment',
                'semifinal_installment',
                'finals_installment',
            ])
            ->where('status', 'overdue')
            ->get();

        $promissoryNotes = $student->promissoryNotes()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->with(['invoice', 'specialExamInvoice', 'reviewer'])
            ->latest()
            ->get();

        return view('student.promissory_note', compact(
            'student',
            'enrollment',
            'enrollments',
            'installmentInvoices',
            'overdueInstallments',
            'promissoryNotes',
            'settings'
        ));
    }

    /**
     * Renders the pre-filled East Systems Colleges of Rizal Promissory Note template (matching the exact client photo!).
     */
    public function downloadPromissoryTemplate(Request $request)
    {
        $student = Auth::user();
        $enrollment = $student->enrollment;

        $invoiceId = $request->query('invoice_id');
        $selectedInvoice = $invoiceId
            ? Invoice::where('user_id', $student->id)->where('status', 'unpaid')->find($invoiceId)
            : $student->invoices()
                ->whereIn('invoice_type', ['prelim_installment', 'midterm_installment', 'semifinal_installment', 'finals_installment'])
                ->where('status', 'unpaid')
                ->first();

        $settings = SystemSetting::getAllKeyed();
        $finalsDeadline = \Carbon\Carbon::parse($settings['finals_due_date'] ?? '2027-11-09')->format('F d, Y');
        $promisedDate = $request->query('promised_date', "Final Term Examination ({$finalsDeadline})");

        return view('student.promissory_template_print', compact(
            'student',
            'enrollment',
            'selectedInvoice',
            'promisedDate',
            'settings'
        ));
    }

    public function uploadPromissoryNote(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'promised_settlement_date' => ['required', 'date', 'after_or_equal:today'],
            'student_contact' => ['required', 'string', 'max:40'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_contact' => ['required', 'string', 'max:40'],
            'signed_note_file' => ['required', 'file', 'max:10240'],
        ]);

        $student = Auth::user();
        $invoice = Invoice::where('user_id', $student->id)->findOrFail($validated['invoice_id']);

        if ($invoice->status === 'overdue' || ($invoice->due_date && $invoice->due_date->isPast())) {
            return redirect()->route('student.promissory')
                ->with('error', 'Promissory Notes cannot be submitted once an installment is late/overdue. Your account is Exam Restricted until the overdue balance is paid.');
        }

        $note = $this->promissoryNoteService->submitPromissoryNote(
            $student,
            $invoice,
            $validated,
            $request->file('signed_note_file')
        );

        return redirect()->route('student.promissory')
            ->with('success', "Signed Promissory Note ({$note->note_number}) uploaded to DMS! Awaiting Registrar approval.");
    }

    public function examClearance(Request $request)
    {
        $student = Auth::user();
        $ctx = $this->resolveEnrollmentContext($request, $student);
        $enrollment = $ctx['enrollment'];
        $enrollments = $ctx['enrollments'];
        $settings = $ctx['settings'];

        if ($enrollment) {
            $this->paymentService->refreshExamClearanceStatus($enrollment);
            $enrollment->refresh();
        }

        $invoices = $student->invoices()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->orderBy('id')
            ->get();
        $promissoryNotes = $student->promissoryNotes()
            ->when($enrollment, fn ($q) => $q->where('enrollment_id', $enrollment->id))
            ->with('specialExamInvoice')
            ->latest()
            ->get();

        return view('student.exam_clearance', compact('student', 'enrollment', 'enrollments', 'invoices', 'promissoryNotes', 'settings'));
    }

    public function telaOrders()
    {
        $student = Auth::user();
        $orders = $student->telaOrders()->with('invoice.latestPayment')->latest()->get();
        $settings = SystemSetting::getAllKeyed();

        return view('student.tela_orders', compact('student', 'orders', 'settings'));
    }

    public function storeTelaOrder(Request $request)
    {
        $validated = $request->validate([
            'blouse_polo_yards' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'skirt_yards' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'logo_patch_qty' => ['nullable', 'integer', 'min:0', 'max:50'],
            'student_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $blouse = (float) ($validated['blouse_polo_yards'] ?? 0);
        $skirt = (float) ($validated['skirt_yards'] ?? 0);
        $patch = (int) ($validated['logo_patch_qty'] ?? 0);

        if ($blouse <= 0 && $skirt <= 0 && $patch <= 0) {
            return back()->withErrors(['blouse_polo_yards' => 'Please select at least one fabric yardage or logo patch to order.']);
        }

        $order = $this->telaService->createOrder(Auth::user(), $validated);

        return redirect()->route('student.billing.pay', $order->invoice_id)
            ->with('success', "Uniform Fabric Order #{$order->order_number} created! Complete your GCash payment below so the Cashier can prepare your tela.");
    }

    public function markNotificationRead(SystemNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true]);
        }
        return back();
    }

    public function markAllNotificationsRead()
    {
        SystemNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
