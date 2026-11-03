@extends('layouts.app')

@section('title', 'Digital Exam Clearance Slip | ESCR Student Portal')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <h4 class="fw-bold mb-0"><i class="bi bi-patch-check-fill text-primary me-2"></i>Digital Examination Clearance Permit</h4>
            @if($enrollment && !$enrollment->exam_restricted && $enrollment->status === 'enrolled')
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold w-100 w-sm-auto">
                    <i class="bi bi-printer-fill me-1"></i>Print Examination Permit
                </button>
            @endif
        </div>

        @if(!$enrollment || $enrollment->status !== 'enrolled')
            <div class="card p-5 text-center">
                <i class="bi bi-exclamation-circle text-warning display-4 mb-2"></i>
                <h5 class="fw-bold">Not Yet Officially Enrolled</h5>
                <p class="text-muted small mb-3">
                    Your Examination Permit is activated once your enrollment documents are approved and your initial tuition downpayment is verified via GCash.
                </p>
                <div>
                    <a href="{{ route('student.billing') }}" class="btn btn-primary">Go to Billing &amp; Payments</a>
                </div>
            </div>
        @elseif($enrollment->exam_restricted)
            <!-- Restricted State -->
            <div class="card border-danger p-4 p-md-5 shadow-sm">
                <div class="d-flex align-items-center gap-3 text-danger mb-3">
                    <i class="bi bi-shield-lock-fill display-5"></i>
                    <div>
                        <span class="badge bg-danger mb-1">CLEARANCE WITHHELD</span>
                        <h4 class="fw-extrabold mb-0">Account Currently Restricted from Taking Exam</h4>
                    </div>
                </div>
                <p class="text-dark mb-4">
                    {{ $enrollment->exam_restriction_reason }}
                </p>

                @php
                    $unpaidSpecial = $invoices->where('invoice_type', 'special_exam_fee')->where('status', '!=', 'paid')->first();
                    $overdueInvoice = $invoices->where('status', 'overdue')->first();
                @endphp

                <div class="bg-light p-3 rounded border mb-4 small">
                    <div class="fw-bold mb-1">How to Lift Your Exam Restriction (ESCR Policy):</div>
                    <ol class="mb-0">
                        <li>Because the tuition payment deadline has passed without an advance Promissory Note, Promissory Note submission is closed for the overdue term and your account is restricted from taking the examination.</li>
                        <li>Settle your overdue installment balance via <strong>GCash (Instant / Manual QR)</strong> or <strong>Walk-In Cash at the School Cashier</strong>.</li>
                        <li>Once your payment is verified, your exam restriction is <strong>immediately lifted</strong> and your Digital Exam Permit is unlocked!</li>
                    </ol>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if($unpaidSpecial)
                        <a href="{{ route('student.billing.pay', $unpaidSpecial) }}" class="btn btn-gcash btn-lg fw-bold w-100 w-sm-auto">
                            <i class="bi bi-phone-fill me-2"></i>Pay Special Exam Fee (₱{{ number_format((float)$unpaidSpecial->amount, 2) }}) via GCash Now
                        </a>
                    @elseif($overdueInvoice)
                        <a href="{{ route('student.billing.pay', $overdueInvoice) }}" class="btn btn-danger btn-lg fw-bold w-100 w-sm-auto">
                            <i class="bi bi-wallet2 me-2"></i>Pay Overdue {{ $overdueInvoice->title }} (₱{{ number_format((float)$overdueInvoice->balance(), 2) }}) to Lift Restriction
                        </a>
                        <a href="{{ route('student.billing') }}" class="btn btn-outline-dark btn-lg fw-semibold w-100 w-sm-auto">
                            <i class="bi bi-list-check me-2"></i>View Full Billing Ledger
                        </a>
                    @else
                        <a href="{{ route('student.billing') }}" class="btn btn-gcash btn-lg fw-bold w-100 w-sm-auto">
                            <i class="bi bi-wallet2 me-2"></i>Pay Installment via GCash / Cash to Lift Restriction
                        </a>
                    @endif
                </div>
            </div>
        @else
            <!-- Cleared Examination Permit -->
            <div class="card p-3 p-md-5 bg-white border-success border-2 shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border-bottom pb-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/escr-logo.png') }}" alt="ESCR Official Seal" style="width: 60px; height: 60px; object-fit: contain;">
                        <div>
                            <h4 class="fw-extrabold mb-0">EAST SYSTEMS COLLEGES OF RIZAL</h4>
                            <div class="small text-muted">Morong, Rizal &bull; Office of the Registrar &amp; Cashier</div>
                            <div class="small fw-semibold text-primary">{{ $enrollment->academic_year }} &bull; {{ $enrollment->semester }}</div>
                        </div>
                    </div>
                    <div class="text-sm-end">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-patch-check-fill me-1"></i>CLEARED FOR EXAM
                        </span>
                        <div class="small text-muted mt-1">Permit Code: <code>PRM-{{ $student->id }}-{{ date('Y') }}</code></div>
                    </div>
                </div>

                <div class="row g-3 mb-4 small">
                    <div class="col-sm-6">
                        <div class="text-muted">Student Name:</div>
                        <div class="fw-bold fs-5">{{ $enrollment->fullName() }}</div>
                        <div>Student ID: <strong>{{ $student->student_number }}</strong></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <div class="text-muted">Program &amp; Year:</div>
                        <div class="fw-bold">{{ $enrollment->course }}</div>
                        <div>{{ $enrollment->year_level }} ({{ strtoupper($enrollment->payment_scheme) }} PLAN)</div>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-muted mb-2">Installment &amp; Special Exam Clearance Ledger</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Period / Fee</th>
                                <th>Status</th>
                                <th>Clearance Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices->whereIn('invoice_type', ['full_tuition', 'downpayment', 'prelim_installment', 'midterm_installment', 'semifinal_installment', 'finals_installment', 'special_exam_fee']) as $inv)
                                <tr>
                                    <td class="fw-semibold">{{ $inv->title }}</td>
                                    <td><span class="badge {{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span></td>
                                    <td>
                                        @if($inv->status === 'paid')
                                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Verified Payment</span>
                                        @elseif($inv->status === 'promised')
                                            <span class="text-primary fw-bold"><i class="bi bi-arrow-right-circle-fill me-1"></i>Moved to Final Term Installment via Approved Promissory Note (Finals Due: {{ $inv->promised_date?->format('M d, Y') }})</span>
                                        @else
                                            <span class="text-muted">Upcoming (₱{{ number_format((float)$inv->amount, 2) }} &bull; Due {{ $inv->due_date?->format('M d, Y') }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success-subtle border border-success-subtle rounded p-3 small">
                    <div>
                        <div class="fw-bold text-success">OFFICIAL DIGITAL CLEARANCE STAMP</div>
                        <div class="text-muted">Present this digital or printed slip to your proctor on examination day.</div>
                    </div>
                    <div class="text-end fw-mono fw-bold text-success">
                        ESCR-VERIFIED-{{ strtoupper(substr(md5($student->email), 0, 8)) }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
