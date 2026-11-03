@extends('layouts.app')

@section('title', 'Student Dashboard | ESCR Portal')

@section('content')
<div class="escr-hero-banner p-3 p-md-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <span class="badge fw-bold px-2 py-1" style="background-color: #facc15; color: #7f1d1d;">
                    <i class="bi bi-mortarboard-fill me-1"></i>{{ $enrollment ? "{$enrollment->semester} • {$enrollment->academic_year}" : ($settings['semester'] ?? '1st Semester') }}
                </span>
                @if($student->student_number)
                    <span class="badge bg-white text-dark fw-bold font-mono">ID: {{ $student->student_number }}</span>
                @endif
            </div>
            <h4 class="fw-extrabold mb-1 text-white">Welcome, {{ $student->name }}!</h4>
            <div class="small" style="color: #fef08a;">
                @if($enrollment)
                    <span>{{ $enrollment->course }} &bull; {{ $enrollment->year_level }} ({{ $enrollment->student_type }})</span>
                @else
                    <span>Complete your online enrollment form to get started.</span>
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto mt-1 mt-md-0 align-items-center" style="max-width: 100%;">
            @if(isset($enrollments) && $enrollments->count() > 1)
                <form action="{{ route('student.dashboard') }}" method="GET" class="flex-fill flex-md-grow-0">
                    <select name="enrollment_id" class="form-select form-select-sm fw-bold border-warning" style="background-color:#fff;color:#7f1d1d;" onchange="this.form.submit()">
                        @foreach($enrollments as $enrItem)
                            <option value="{{ $enrItem->id }}" {{ $enrollment?->id === $enrItem->id ? 'selected' : '' }}>
                                {{ $enrItem->semester }} ({{ $enrItem->academic_year }}) &bull; {{ $enrItem->year_level }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('student.tela') }}" class="btn btn-light btn-sm fw-bold text-danger flex-fill flex-md-grow-0">
                <i class="bi bi-scissors me-1"></i>Order Uniform Tela
            </a>
            <a href="{{ route('student.billing', $enrollment ? ['enrollment_id' => $enrollment->id] : []) }}" class="btn btn-warning btn-sm px-3 fw-bold flex-fill flex-md-grow-0">
                <i class="bi bi-wallet2 me-1"></i>Pay Tuition / GCash
            </a>
        </div>
    </div>
</div>

@if(!empty($canEnrollNewTerm))
    <div class="alert alert-primary border-0 shadow-sm mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h6 class="fw-bold mb-1">
                <i class="bi bi-calendar2-plus-fill me-2"></i>{{ $activeSemester }} ({{ $activeYear }}) Enrollment is Now Open!
            </h6>
            <p class="small mb-0">
                You are viewing your <strong>{{ $enrollment?->semester }} ({{ $enrollment?->academic_year }})</strong> record. As a continuing student, your personal profile and verified DMS credentials will be automatically carried over when you enroll for <strong>{{ $activeSemester }}</strong>.
            </p>
        </div>
        <a href="{{ route('student.enrollment') }}" class="btn btn-primary btn-sm fw-bold px-3">
            <i class="bi bi-arrow-right-circle-fill me-1"></i>Enroll for {{ $activeSemester }}
        </a>
    </div>
@endif

<!-- Enrollment Progress Stepper -->
@php
    $status = $enrollment?->status ?? 'not_started';
    $step = match($status) {
        'pending', 'under_review', 'returned' => 2,
        'approved_awaiting_payment' => 3,
        'enrolled' => 4,
        default => 1,
    };
@endphp
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h6 class="fw-bold mb-0" style="font-size: 0.92rem;">
            <i class="bi bi-diagram-3-fill text-primary me-1"></i>Enrollment Progress
        </h6>
        @if($enrollment)
            <span class="badge {{ $enrollment->statusBadgeClass() }} px-2 py-1">{{ $enrollment->statusLabel() }}</span>
        @else
            <span class="badge bg-secondary">Not Yet Submitted</span>
        @endif
    </div>
    <div class="d-flex flex-wrap justify-content-between gap-2">
        <div class="stepper-item {{ $step > 1 ? 'step-completed' : ($step === 1 ? 'step-current' : '') }}">
            <div class="stepper-circle {{ $step > 1 ? 'bg-success text-white' : ($step === 1 ? 'bg-primary text-white' : 'bg-light text-muted border') }}">
                @if($step > 1)<i class="bi bi-check-lg"></i>@else 1 @endif
            </div>
            <div class="small fw-semibold">Submit Form &amp; Docs</div>
        </div>
        <div class="stepper-item {{ $step > 2 ? 'step-completed' : ($step === 2 ? 'step-current' : '') }}">
            <div class="stepper-circle {{ $status === 'returned' ? 'bg-danger text-white' : ($step > 2 ? 'bg-success text-white' : ($step === 2 ? 'bg-primary text-white' : 'bg-light text-muted border')) }}">
                @if($step > 2)<i class="bi bi-check-lg"></i>@else 2 @endif
            </div>
            <div class="small fw-semibold">Registrar Doc Review</div>
        </div>
        <div class="stepper-item {{ $step > 3 ? 'step-completed' : ($step === 3 ? 'step-current' : '') }}">
            <div class="stepper-circle {{ $step > 3 ? 'bg-success text-white' : ($step === 3 ? 'bg-primary text-white' : 'bg-light text-muted border') }}">
                @if($step > 3)<i class="bi bi-check-lg"></i>@else 3 @endif
            </div>
            <div class="small fw-semibold">Initial GCash Payment</div>
        </div>
        <div class="stepper-item {{ $step >= 4 ? 'step-completed' : '' }}">
            <div class="stepper-circle {{ $step >= 4 ? 'bg-success text-white' : 'bg-light text-muted border' }}">
                @if($step >= 4)<i class="bi bi-check-lg"></i>@else 4 @endif
            </div>
            <div class="small fw-semibold">Officially Enrolled</div>
        </div>
    </div>
</div>

<!-- Exam Clearance Banner -->
@if($enrollment && $enrollment->status === 'enrolled')
    @if($enrollment->exam_restricted)
        <div class="alert alert-danger border-0 shadow-sm p-3 p-md-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold mb-1"><i class="bi bi-shield-exclamation me-2"></i>ACCOUNT EXAM RESTRICTED</h5>
                <p class="mb-0 small">{{ $enrollment->exam_restriction_reason }}</p>
            </div>
            <div class="d-flex gap-2 w-100 w-md-auto">
                @php
                    $unpaidSpecial = $invoices->where('invoice_type', 'special_exam_fee')->where('status', '!=', 'paid')->first();
                @endphp
                @if($unpaidSpecial)
                    <a href="{{ route('student.billing.pay', $unpaidSpecial) }}" class="btn btn-gcash fw-bold px-3 w-100 w-md-auto">
                        <i class="bi bi-phone-fill me-1"></i>Pay Special Exam Fee (₱{{ number_format((float)$unpaidSpecial->amount, 2) }}) via GCash
                    </a>
                @else
                    <a href="{{ route('student.promissory') }}" class="btn btn-danger fw-bold px-3 w-100 w-md-auto">
                        <i class="bi bi-file-earmark-ruled me-1"></i>Submit Promissory Note
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-success border-0 shadow-sm p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-patch-check-fill fs-3 text-success"></i>
                <div>
                    <div class="fw-bold">CLEARED FOR EXAMINATION</div>
                    <div class="small">Your installment payments and exam requirements are up to date.</div>
                </div>
            </div>
            <a href="{{ route('student.clearance') }}" class="btn btn-success btn-sm fw-semibold w-100 w-sm-auto">
                <i class="bi bi-qr-code me-1"></i>View Digital Exam Permit
            </a>
        </div>
    @endif
@endif

<!-- Ready for Pickup Uniform Tela Alert -->
@php
    $readyTela = $telaOrders->where('status', 'ready_for_pickup');
@endphp
@if($readyTela->isNotEmpty())
    <div class="alert alert-primary border-0 shadow-sm p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="fw-bold"><i class="bi bi-box-seam-fill me-2"></i>UNIFORM FABRIC (TELA) READY FOR PICKUP AT SCHOOL!</div>
            <div class="small">
                @foreach($readyTela as $rt)
                    Order <strong>{{ $rt->order_number }}</strong> ({{ $rt->itemsSummary() }}) is ready at the Cashier's Office. {{ $rt->cashier_remarks }}
                @endforeach
            </div>
        </div>
        <a href="{{ route('student.tela') }}" class="btn btn-primary btn-sm fw-semibold w-100 w-sm-auto">View Order Details</a>
    </div>
@endif

<!-- Financial Summary Cards -->
<div class="row g-2 g-md-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card p-3 kpi-accent-primary h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Assessed &amp; Ordered</div>
                    <h4 class="fw-bold mb-0">₱{{ number_format($totalBilled, 2) }}</h4>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ strtoupper($enrollment?->payment_scheme ?? 'Installment') }} PLAN</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card p-3 kpi-accent-success h-100">
            <div class="text-muted" style="font-size: 0.76rem;">GCash Paid</div>
            <h5 class="fw-bold text-success mb-0">₱{{ number_format($totalPaid, 2) }}</h5>
            <div class="text-muted mt-1" style="font-size: 0.72rem;">{{ $invoices->where('status', 'paid')->count() }} Settled</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card p-3 kpi-accent-warning h-100">
            <div class="text-muted" style="font-size: 0.76rem;">Balance Due</div>
            <h5 class="fw-bold text-danger mb-0">₱{{ number_format($outstandingBalance, 2) }}</h5>
            <div class="text-muted mt-1" style="font-size: 0.72rem;">1st Sem 26–27</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Installment & Billing Schedule -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h6 class="fw-bold mb-0" style="font-size: 0.92rem;"><i class="bi bi-calendar2-week text-primary me-1"></i>1st Sem Payment Schedule</h6>
                <a href="{{ route('student.billing') }}" class="btn btn-sm btn-outline-primary text-nowrap">Full Ledger</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Deadline</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="mobile-card-lead" style="min-width: 150px;">
                                    <div class="fw-bold text-dark">{{ $inv->title }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $inv->invoice_number }}</div>
                                </td>
                                <td data-label="Deadline" class="text-nowrap">
                                    @if($inv->status === 'promised')
                                        <span class="text-primary fw-semibold">Moved to Finals ({{ $inv->promised_date?->format('M d, Y') }})</span>
                                    @elseif($inv->promised_date)
                                        <span class="text-primary fw-semibold">Promised: {{ $inv->promised_date->format('M d, Y') }}</span>
                                    @elseif($inv->due_date)
                                        {{ $inv->due_date->format('M d, Y') }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td data-label="Amount" class="fw-bold text-nowrap">
                                    @if($inv->status === 'promised')
                                        <span class="text-decoration-line-through text-muted me-1">₱{{ number_format((float)$inv->amount, 2) }}</span>
                                        <span class="text-primary small">(In Finals)</span>
                                    @else
                                        ₱{{ number_format((float)$inv->amount, 2) }}
                                    @endif
                                </td>
                                <td data-label="Status"><span class="badge {{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span></td>
                                <td class="text-end text-nowrap mobile-card-actions">
                                    @if(!in_array($inv->status, ['paid', 'pending_verification', 'promised'], true))
                                        <a href="{{ route('student.billing.pay', $inv) }}" class="btn btn-xs btn-gcash btn-sm py-1 px-3">
                                            <i class="bi bi-wallet2 me-1"></i>Pay (GCash / Cash)
                                        </a>
                                    @elseif($inv->latestPayment)
                                        <a href="{{ route('student.receipt', $inv->latestPayment) }}" class="btn btn-outline-secondary btn-sm py-1 px-3">
                                            <i class="bi bi-receipt me-1"></i>View Official Receipt
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No invoices generated yet. Once the Registrar approves your enrollment documents, your tuition &amp; installment schedule will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Automated Email & System Notifications -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>Automated Email &amp; System Alerts</h6>
            <div class="list-group list-group-flush small">
                @forelse($notifications as $notif)
                    <div class="list-group-item px-0 py-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-1 mb-1">
                            <span class="fw-bold text-dark">{{ $notif->subject }}</span>
                            <span class="badge bg-light text-muted border">{{ $notif->sent_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted mb-0" style="white-space: pre-line; font-size: 0.8rem;">{{ $notif->message }}</p>
                    </div>
                @empty
                    <div class="text-muted text-center py-4">No notifications yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
