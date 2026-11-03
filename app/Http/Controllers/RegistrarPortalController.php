<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\PromissoryNote;
use App\Models\StudentDocument;
use App\Services\AuditService;
use App\Services\EnrollmentService;
use App\Services\PromissoryNoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrarPortalController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected PromissoryNoteService $promissoryNoteService
    ) {}

    public function dashboard()
    {
        $pendingApplicationsCount = Enrollment::whereIn('status', ['pending', 'under_review'])->count();
        $approvedAwaitingPaymentCount = Enrollment::where('status', 'approved_awaiting_payment')->count();
        $enrolledCount = Enrollment::where('status', 'enrolled')->count();
        $pendingPromissoryCount = PromissoryNote::where('status', 'pending_review')->count();

        $recentApplications = Enrollment::with(['user', 'documents'])
            ->latest()
            ->take(6)
            ->get();

        $pendingPromissoryNotes = PromissoryNote::with(['user', 'enrollment', 'invoice'])
            ->where('status', 'pending_review')
            ->latest()
            ->take(5)
            ->get();

        return view('registrar.dashboard', compact(
            'pendingApplicationsCount',
            'approvedAwaitingPaymentCount',
            'enrolledCount',
            'pendingPromissoryCount',
            'recentApplications',
            'pendingPromissoryNotes'
        ));
    }

    public function applications(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = Enrollment::with(['user', 'documents'])->latest('id');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('registrar.applications', compact('applications', 'statusFilter'));
    }

    public function reviewApplication(Enrollment $enrollment)
    {
        $enrollment->load(['user', 'documents', 'invoices', 'reviewer']);
        return view('registrar.review_application', compact('enrollment'));
    }

    public function approveApplication(Request $request, Enrollment $enrollment)
    {
        $this->enrollmentService->approveApplication(
            $enrollment,
            Auth::user(),
            $request->input('registrar_remarks')
        );

        return redirect()->route('registrar.applications')
            ->with('success', "Application {$enrollment->application_reference} ({$enrollment->fullName()}) approved! Tuition/Installment invoices generated.");
    }

    public function returnApplication(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'registrar_remarks' => ['required', 'string', 'max:500'],
        ]);

        $this->enrollmentService->returnApplication(
            $enrollment,
            Auth::user(),
            $validated['registrar_remarks']
        );

        return redirect()->route('registrar.applications')
            ->with('warning', "Application {$enrollment->application_reference} returned to student for document revision.");
    }

    public function reviewDocument(Request $request, StudentDocument $document)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:300'],
        ]);

        $document->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        AuditService::log(
            'DOCUMENT_' . strtoupper($validated['status']),
            "Registrar " . Auth::user()->name . " marked {$document->typeLabel()} as {$validated['status']} for {$document->user->name}.",
            'StudentDocument',
            $document->id,
            null,
            ['status' => $validated['status'], 'remarks' => $validated['remarks'] ?? null]
        );

        return back()->with('success', "{$document->typeLabel()} marked as " . strtoupper($validated['status']) . '.');
    }

    public function promissoryNotes(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = PromissoryNote::with(['user', 'enrollment', 'invoice', 'specialExamInvoice', 'reviewer'])->latest('id');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $notes = $query->paginate(20)->withQueryString();

        return view('registrar.promissory_notes', compact('notes', 'statusFilter'));
    }

    public function approvePromissoryNote(Request $request, PromissoryNote $note)
    {
        $this->promissoryNoteService->approvePromissoryNote(
            $note,
            Auth::user(),
            $request->input('registrar_remarks')
        );

        return back()->with(
            'success',
            "Promissory Note {$note->note_number} approved! The tuition balance of PHP " . number_format((float) $note->outstanding_amount, 2) . " has been moved to {$note->user->name}'s Final Term Installment, and the student is now Cleared for Examination."
        );
    }

    public function rejectPromissoryNote(Request $request, PromissoryNote $note)
    {
        $validated = $request->validate([
            'registrar_remarks' => ['required', 'string', 'max:500'],
        ]);

        $this->promissoryNoteService->rejectPromissoryNote(
            $note,
            Auth::user(),
            $validated['registrar_remarks']
        );

        return back()->with('warning', "Promissory Note {$note->note_number} rejected.");
    }

    public function students(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $query = Enrollment::with(['user', 'invoices'])->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%")
                    ->orWhere('application_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('student_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $enrollments = $query->paginate(20)->withQueryString();
        return view('registrar.students', compact('enrollments', 'search'));
    }
}
