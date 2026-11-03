@extends('layouts.app')

@section('title', 'Student Dashboard | ESCR Portal')

@section('content')
@php
    $status = $enrollment?->status ?? 'not_started';
    $step = match($status) {
        'pending', 'under_review', 'returned' => 2,
        'approved_awaiting_payment' => 3,
        'enrolled' => 4,
        default => 1,
    };
    $paidPercent = $totalBilled > 0 ? min(100, round(($totalPaid / $totalBilled) * 100)) : 0;
@endphp

<!-- Screen 1 Style Header: ESCR OVERVIEW + Segmented Toggle & Semester Switcher -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="aurum-title-uppercase fs-4">ESCR OVERVIEW</span>
            <span class="badge fw-bold px-2 py-1" style="background: linear-gradient(135deg, #F3D87D, #D4AF37); color: #24070B;">
                <i class="bi bi-mortarboard-fill me-1"></i>{{ $enrollment ? "{$enrollment->semester} • {$enrollment->academic_year}" : ($settings['semester'] ?? '1st Semester') }}
            </span>
            @if($student->student_number)
                <span class="badge fw-bold font-mono" style="background: #360B10; color: #F3D87D; border: 1px solid #D4AF37;">ID: {{ $student->student_number }}</span>
            @endif
        </div>
        <div class="small" style="color: #6E5A5D;">
            <strong style="color: #24070B;">Welcome, {{ $student->name }}!</strong> &bull;
            @if($enrollment)
                <span>{{ $enrollment->course }} &bull; {{ $enrollment->year_level }} ({{ $enrollment->student_type }})</span>
            @else
                <span>Complete your online enrollment form to get started.</span>
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
        @if(isset($enrollments) && $enrollments->count() > 1)
            <form action="{{ route('student.dashboard') }}" method="GET">
                <select name="enrollment_id" class="form-select form-select-sm fw-bold" style="background-color:#FFFFFF;color:#24070B;border-color:#D4AF37;" onchange="this.form.submit()">
                    @foreach($enrollments as $enrItem)
                        <option value="{{ $enrItem->id }}" {{ $enrollment?->id === $enrItem->id ? 'selected' : '' }}>
                            {{ $enrItem->semester }} ({{ $enrItem->academic_year }}) &bull; {{ $enrItem->year_level }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        <div class="aurum-segmented-group">
            <a href="{{ route('student.dashboard') }}" class="aurum-segmented-btn active">Dashboard</a>
            <a href="{{ route('student.tela') }}" class="aurum-segmented-btn">
                <i class="bi bi-scissors me-1"></i>Order Uniform Tela
            </a>
        </div>
        <a href="{{ route('student.billing', $enrollment ? ['enrollment_id' => $enrollment->id] : []) }}" class="btn btn-warning btn-sm px-3 fw-bold">
            <i class="bi bi-wallet2 me-1"></i>Pay Tuition / GCash
        </a>
    </div>
</div>

<!-- Screen 1 Top Row: 2 Deep Imperial Burgundy Executive Chart Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold text-white" style="font-size: 0.92rem;">Installment Forecast &amp; Term Schedule</div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">
                        {{ strtoupper($enrollment?->payment_scheme ?? 'Installment') }} PLAN &bull; {{ $invoices->where('status', 'paid')->count() }} of {{ max(1, $invoices->count()) }} Invoices Settled
                    </div>
                </div>
                <span class="badge" style="background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.4);">
                    {{ $paidPercent }}% Paid
                </span>
            </div>

            <div class="aurum-mini-bars">
                @php
                    $barItems = $invoices->take(6);
                @endphp
                @forelse($barItems as $bInv)
                    @php
                        $isPaid = $bInv->status === 'paid';
                        $isPromised = $bInv->status === 'promised';
                        $barHeight = $isPaid ? 92 : ($isPromised ? 55 : 38);
                        $shortLabel = match($bInv->invoice_type) {
                            'downpayment' => 'Down',
                            'full_tuition' => 'Full',
                            'prelim_installment' => 'Prelim',
                            'midterm_installment' => 'Midterm',
                            'semifinal_installment' => 'Semi',
                            'finals_installment' => 'Finals',
                            'tela_order' => 'Tela',
                            'special_exam_fee' => 'Special',
                            'completion_fee' => 'Remedial',
                            default => \Illuminate\Support\Str::limit(explode(' ', $bInv->title)[0] ?? 'Term', 6, ''),
                        };
                    @endphp
                    <div class="aurum-mini-bar-col" title="{{ $bInv->title }} (₱{{ number_format((float)$bInv->amount, 2) }} - {{ $bInv->statusLabel() }})">
                        <div class="aurum-mini-bar {{ $isPaid ? '' : 'bar-muted' }}" style="height: {{ $barHeight }}%;"></div>
                        <div class="aurum-mini-bar-label">{{ $shortLabel }}</div>
                    </div>
                @empty
                    @foreach(['Down', 'Prelim', 'Midterm', 'Semi', 'Finals'] as $idx => $lbl)
                        <div class="aurum-mini-bar-col">
                            <div class="aurum-mini-bar {{ $idx < 2 ? '' : 'bar-muted' }}" style="height: {{ 45 + ($idx * 10) }}%;"></div>
                            <div class="aurum-mini-bar-label">{{ $lbl }}</div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size: 0.74rem; color: #D9C3B0;">Tuition &amp; Ledger Assessment</div>
                    <div class="fw-extrabold font-mono mt-1" style="font-size: 1.55rem; color: #F3D87D; line-height: 1.1;">
                        ₱{{ number_format($totalBilled, 2) }}
                    </div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">
                        Paid: <strong class="text-white">₱{{ number_format($totalPaid, 2) }}</strong> &bull; Balance Due: <strong style="color: #F3D87D;">₱{{ number_format($outstandingBalance, 2) }}</strong>
                    </div>
                </div>
                <a href="{{ route('student.clearance') }}" class="btn btn-sm fw-bold" style="background: rgba(212, 175, 55, 0.16); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.45); font-size: 0.74rem;">
                    <i class="bi bi-qr-code me-1"></i>Exam Permit
                </a>
            </div>

            <div class="mt-2">
                <svg viewBox="0 0 360 64" class="w-100" style="height: 60px; display: block;">
                    <defs>
                        <linearGradient id="aurumStudentWave" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#D4AF37" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,52 Q45,28 90,40 T180,24 T270,30 T360,10 L360,64 L0,64 Z" fill="url(#aurumStudentWave)"/>
                    <path d="M0,52 Q45,28 90,40 T180,24 T270,30 T360,10" fill="none" stroke="#F3D87D" stroke-width="2.2"/>
                </svg>
            </div>
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

<!-- Screen 1 Middle Row: Key Metrics (4 White Aurum KPI Cards) -->
<div class="mb-2 d-flex justify-content-between align-items-center">
    <span class="fw-bold small text-uppercase" style="letter-spacing: 0.06em; color: #5C4346;">Key Metrics</span>
    <span class="small text-muted">{{ $enrollment ? "{$enrollment->semester} ({$enrollment->academic_year})" : '1st Sem 26–27' }}</span>
</div>
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">₱{{ number_format($totalBilled, 2) }}</div>
            <div class="aurum-kpi-label">Total Assessed &amp; Ordered</div>
            <div class="aurum-kpi-sub">{{ strtoupper($enrollment?->payment_scheme ?? 'Installment') }} PLAN</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #15803D;">₱{{ number_format($totalPaid, 2) }}</div>
            <div class="aurum-kpi-label">GCash Paid</div>
            <div class="aurum-kpi-sub">{{ $invoices->where('status', 'paid')->count() }} Settled</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #991B1B;">₱{{ number_format($outstandingBalance, 2) }}</div>
            <div class="aurum-kpi-label">Balance Due</div>
            <div class="aurum-kpi-sub">1st Sem 26–27</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">{{ $step }}/4</div>
            <div class="aurum-kpi-label">Enrollment Stage</div>
            <div class="aurum-kpi-sub">
                @if($enrollment)
                    {{ $enrollment->statusLabel() }}
                @else
                    Not Yet Submitted
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Unified 3-Card Bento Row: Enrollment Progress (col-lg-6) + Exam Clearance (col-lg-3) + Uniform Tela (col-lg-3) -->
@php
    $readyTela = $telaOrders->where('status', 'ready_for_pickup');
    $overdueInvoice = $invoices->where('status', 'overdue')->first();
    $unpaidSpecial = $invoices->where('invoice_type', 'special_exam_fee')->where('status', '!=', 'paid')->first();
@endphp
<div class="row g-3 mb-4">
    <!-- 1. Enrollment Progress Stepper (col-lg-6) -->
    <div class="col-lg-6">
        <div class="card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
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
            <div class="d-flex flex-wrap justify-content-between gap-2 my-auto">
                <div class="stepper-item {{ $step > 1 ? 'step-completed' : ($step === 1 ? 'step-current' : '') }}">
                    <div class="stepper-circle {{ $step > 1 ? 'bg-success text-white' : ($step === 1 ? 'bg-primary text-white' : 'bg-light text-muted border') }}">
                        @if($step > 1)<i class="bi bi-check-lg"></i>@else 1 @endif
                    </div>
                    <div class="small fw-semibold" style="font-size: 0.76rem;">Submit Form &amp; Docs</div>
                </div>
                <div class="stepper-item {{ $step > 2 ? 'step-completed' : ($step === 2 ? 'step-current' : '') }}">
                    <div class="stepper-circle {{ $status === 'returned' ? 'bg-danger text-white' : ($step > 2 ? 'bg-success text-white' : ($step === 2 ? 'bg-primary text-white' : 'bg-light text-muted border')) }}">
                        @if($step > 2)<i class="bi bi-check-lg"></i>@else 2 @endif
                    </div>
                    <div class="small fw-semibold" style="font-size: 0.76rem;">Registrar Review</div>
                </div>
                <div class="stepper-item {{ $step > 3 ? 'step-completed' : ($step === 3 ? 'step-current' : '') }}">
                    <div class="stepper-circle {{ $step > 3 ? 'bg-success text-white' : ($step === 3 ? 'bg-primary text-white' : 'bg-light text-muted border') }}">
                        @if($step > 3)<i class="bi bi-check-lg"></i>@else 3 @endif
                    </div>
                    <div class="small fw-semibold" style="font-size: 0.76rem;">Initial Payment</div>
                </div>
                <div class="stepper-item {{ $step >= 4 ? 'step-completed' : '' }}">
                    <div class="stepper-circle {{ $step >= 4 ? 'bg-success text-white' : 'bg-light text-muted border' }}">
                        @if($step >= 4)<i class="bi bi-check-lg"></i>@else 4 @endif
                    </div>
                    <div class="small fw-semibold" style="font-size: 0.76rem;">Officially Enrolled</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Exam Clearance Status Card (col-lg-3) -->
    <div class="col-md-6 col-lg-3">
        @if($enrollment && $enrollment->status === 'enrolled')
            @if($enrollment->exam_restricted)
                <div class="card p-3 h-100 border-danger d-flex flex-column justify-content-between" style="background: #FEF2F2; border-left: 4px solid #DC2626 !important;">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-danger"><i class="bi bi-shield-exclamation me-1"></i>RESTRICTED</span>
                            <i class="bi bi-lock-fill text-danger fs-5"></i>
                        </div>
                        <div class="fw-bold text-danger" style="font-size: 0.88rem;">ACCOUNT EXAM RESTRICTED</div>
                        <p class="text-muted mb-2 mt-1" style="font-size: 0.74rem; line-height: 1.35;">
                            {{ $enrollment->exam_restriction_reason }}
                        </p>
                    </div>
                    @if($unpaidSpecial)
                        <a href="{{ route('student.billing.pay', $unpaidSpecial) }}" class="btn btn-gcash btn-sm fw-bold w-100">
                            <i class="bi bi-wallet2 me-1"></i>Pay Special Fee (₱{{ number_format((float)$unpaidSpecial->amount, 2) }})
                        </a>
                    @elseif($overdueInvoice)
                        <a href="{{ route('student.billing.pay', $overdueInvoice) }}" class="btn btn-danger btn-sm fw-bold w-100">
                            <i class="bi bi-wallet2 me-1"></i>Pay Overdue to Lift Restriction
                        </a>
                    @else
                        <a href="{{ route('student.billing') }}" class="btn btn-danger btn-sm fw-bold w-100">
                            <i class="bi bi-wallet2 me-1"></i>Settle Balance to Lift
                        </a>
                    @endif
                </div>
            @else
                <div class="card p-3 h-100 d-flex flex-column justify-content-between" style="background: #F0FDF4; border-color: #BBF7D0; border-left: 4px solid #16A34A !important;">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>PERMIT ACTIVE</span>
                            <i class="bi bi-qr-code-scan text-success fs-5"></i>
                        </div>
                        <div class="fw-bold text-success" style="font-size: 0.88rem;">CLEARED FOR EXAMINATION</div>
                        <p class="text-muted mb-2 mt-1" style="font-size: 0.74rem; line-height: 1.35;">
                            Your installment payments and exam requirements are up to date.
                        </p>
                    </div>
                    <a href="{{ route('student.clearance') }}" class="btn btn-success btn-sm fw-bold w-100">
                        <i class="bi bi-qr-code me-1"></i>View Digital Exam Permit
                    </a>
                </div>
            @endif
        @else
            <div class="card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-secondary">EXAM CLEARANCE</span>
                        <i class="bi bi-patch-check text-muted fs-5"></i>
                    </div>
                    <div class="fw-bold" style="font-size: 0.88rem; color: #24070B;">Exam Permit Pending</div>
                    <p class="text-muted mb-2 mt-1" style="font-size: 0.74rem; line-height: 1.35;">
                        Complete enrollment &amp; initial downpayment to activate your Digital Exam Permit.
                    </p>
                </div>
                <a href="{{ route('student.enrollment') }}" class="btn btn-outline-primary btn-sm fw-semibold w-100">
                    <i class="bi bi-file-earmark-person me-1"></i>Complete Enrollment
                </a>
            </div>
        @endif
    </div>

    <!-- 3. Uniform Tela & Patch Card (col-lg-3) -->
    <div class="col-md-6 col-lg-3">
        @if($readyTela->isNotEmpty())
            <div class="card p-3 h-100 d-flex flex-column justify-content-between" style="background: #FFFBEB; border-color: #FDE68A; border-left: 4px solid #D4AF37 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge text-dark fw-bold" style="background: #F3D87D;"><i class="bi bi-box-seam-fill me-1"></i>READY FOR PICKUP</span>
                        <i class="bi bi-scissors fs-5" style="color: #AA821B;"></i>
                    </div>
                    <div class="fw-bold" style="font-size: 0.84rem; color: #24070B;">UNIFORM FABRIC (TELA) READY FOR PICKUP AT SCHOOL!</div>
                    <div class="text-muted mt-1 mb-2" style="font-size: 0.73rem; line-height: 1.35;">
                        @foreach($readyTela as $rt)
                            Order <strong>{{ $rt->order_number }}</strong> ({{ $rt->itemsSummary() }}) is ready at the Cashier.
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('student.tela') }}" class="btn btn-warning btn-sm fw-bold w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View Order Details
                </a>
            </div>
        @else
            <div class="card p-3 h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #360B10 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge" style="background: #F8F1EC; color: #52141D; border: 1px solid #E6D7C3;">TELA STORE</span>
                        <i class="bi bi-scissors text-primary fs-5"></i>
                    </div>
                    <div class="fw-bold" style="font-size: 0.88rem; color: #24070B;">Uniform Fabric &amp; Logo Patch</div>
                    <p class="text-muted mb-2 mt-1" style="font-size: 0.74rem; line-height: 1.35;">
                        @if($telaOrders->isNotEmpty())
                            You have <strong>{{ $telaOrders->count() }}</strong> uniform fabric order(s). Order official blouse/polo, skirt yardage &amp; patches online.
                        @else
                            Order official school uniform fabric (<em>tela</em>) and logo patches online for Cashier pickup.
                        @endif
                    </p>
                </div>
                <a href="{{ route('student.tela') }}" class="btn btn-outline-primary btn-sm fw-bold w-100">
                    <i class="bi bi-scissors me-1"></i>Order Uniform Tela
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Full-Width Extended Installment & Payment Schedule Table (col-12) -->
<div class="row g-4">
    <div class="col-12">
        <div class="card p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-0" style="font-size: 0.96rem;">
                        <i class="bi bi-calendar2-week text-primary me-1"></i>{{ $enrollment ? "{$enrollment->semester} Payment Schedule" : '1st Sem Payment Schedule' }}
                    </h6>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        Complete ledger of tuition installments, special fees, and uniform fabric orders. Submit a Promissory Note <strong>before</strong> the deadline if you need to move a term balance to Finals.
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#notificationsDrawer">
                        <i class="bi bi-bell-fill me-1" style="color: #D4AF37;"></i>Recent Activity &amp; Alerts ({{ $notifications->count() }})
                    </button>
                    <a href="{{ route('student.billing', $enrollment ? ['enrollment_id' => $enrollment->id] : []) }}" class="btn btn-sm btn-primary text-nowrap">
                        <i class="bi bi-wallet2 me-1"></i>Open Full Billing Ledger
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Description &amp; Particulars</th>
                            <th>Period / Type</th>
                            <th>Deadline</th>
                            <th>Assessed Amount</th>
                            <th>Paid / Balance</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td data-label="Invoice #" class="font-mono fw-bold text-nowrap">{{ $inv->invoice_number }}</td>
                                <td class="mobile-card-lead" style="min-width: 200px;">
                                    <div class="fw-bold text-dark">{{ $inv->title }}</div>
                                    <div class="text-muted" style="font-size: 0.74rem;">{{ \Illuminate\Support\Str::limit($inv->description, 85) }}</div>
                                </td>
                                <td data-label="Period / Type" class="text-nowrap">
                                    <span class="badge bg-light text-dark border">{{ $inv->exam_period ?? strtoupper(str_replace('_', ' ', $inv->invoice_type)) }}</span>
                                </td>
                                <td data-label="Deadline" class="text-nowrap">
                                    @if($inv->status === 'promised')
                                        <span class="text-primary fw-semibold">Moved to Finals ({{ $inv->promised_date?->format('M d, Y') }})</span>
                                    @elseif($inv->promised_date)
                                        <span class="text-primary fw-semibold">Promised: {{ $inv->promised_date->format('M d, Y') }}</span>
                                    @elseif($inv->due_date)
                                        <span class="{{ $inv->status === 'overdue' ? 'text-danger fw-bold' : '' }}">
                                            {{ $inv->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td data-label="Assessed" class="fw-bold text-nowrap">
                                    @if($inv->status === 'promised')
                                        <span class="text-decoration-line-through text-muted me-1">₱{{ number_format((float)$inv->amount, 2) }}</span>
                                        <span class="text-primary small">(In Finals)</span>
                                    @else
                                        ₱{{ number_format((float)$inv->amount, 2) }}
                                    @endif
                                </td>
                                <td data-label="Paid / Balance" class="text-nowrap">
                                    @if($inv->status === 'paid')
                                        <span class="text-success fw-semibold">Paid ₱{{ number_format((float)$inv->amount_paid, 2) }}</span>
                                    @elseif($inv->status === 'promised')
                                        <span class="text-muted small">₱0.00 (Rolled Over)</span>
                                    @else
                                        <span class="text-danger fw-semibold">Bal: ₱{{ number_format((float)$inv->balance(), 2) }}</span>
                                    @endif
                                </td>
                                <td data-label="Status"><span class="badge {{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span></td>
                                <td class="text-end text-nowrap mobile-card-actions">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        @if(!in_array($inv->status, ['paid', 'pending_verification', 'promised'], true))
                                            <a href="{{ route('student.billing.pay', $inv) }}" class="btn btn-xs {{ $inv->status === 'overdue' ? 'btn-danger' : 'btn-gcash' }} btn-sm py-1 px-3 fw-semibold">
                                                <i class="bi bi-wallet2 me-1"></i>{{ $inv->status === 'overdue' ? 'Pay Overdue (Lift Restriction)' : 'Pay (GCash / Cash)' }}
                                            </a>
                                        @endif
                                        @if($inv->isInstallment() && $inv->status === 'unpaid' && $inv->invoice_type !== 'finals_installment')
                                            <a href="{{ route('student.promissory') }}" class="btn btn-outline-primary btn-sm py-1 px-2" title="Submit Promissory Note before deadline to move balance to Finals">
                                                <i class="bi bi-file-earmark-ruled"></i> Promissory
                                            </a>
                                        @endif
                                        @if($inv->latestPayment)
                                            <a href="{{ route('student.receipt', $inv->latestPayment) }}" class="btn btn-outline-secondary btn-sm py-1 px-3">
                                                <i class="bi bi-receipt me-1"></i>View Official Receipt
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No invoices generated yet. Once the Registrar approves your enrollment documents, your tuition &amp; installment schedule will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
