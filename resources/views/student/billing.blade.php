@extends('layouts.app')

@section('title', 'Billing & GCash Payments | ESCR Student Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Student Billing, Installments &amp; GCash Payments</h4>
        <p class="text-muted small mb-0">
            @if($enrollment)
                Viewing Ledger: <span class="badge bg-primary">{{ $enrollment->semester }} &bull; {{ $enrollment->academic_year }}</span> ({{ $enrollment->course }} &bull; {{ $enrollment->year_level }})
            @else
                Manage your tuition downpayment, 4 exam installments, special examination fees, and remedial completion fees.
            @endif
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
        @if(isset($enrollments) && $enrollments->count() > 1)
            <form action="{{ route('student.billing') }}" method="GET" class="flex-fill flex-md-grow-0">
                <select name="enrollment_id" class="form-select form-select-sm fw-semibold border-primary text-primary" onchange="this.form.submit()">
                    @foreach($enrollments as $enrItem)
                        <option value="{{ $enrItem->id }}" {{ $enrollment?->id === $enrItem->id ? 'selected' : '' }}>
                            {{ $enrItem->semester }} ({{ $enrItem->academic_year }}) &bull; {{ $enrItem->year_level }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        @if($enrollment && $enrollment->status === 'approved_awaiting_payment')
            <form action="{{ route('student.enrollment.scheme') }}" method="POST" class="d-flex align-items-center gap-2 flex-fill flex-md-grow-0">
                @csrf
                <span class="small fw-semibold text-nowrap">Payment Plan:</span>
                <select name="payment_scheme" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="installment" {{ $enrollment->payment_scheme === 'installment' ? 'selected' : '' }}>4-Part Installment Plan</option>
                    <option value="full" {{ $enrollment->payment_scheme === 'full' ? 'selected' : '' }}>Full Payment Plan</option>
                </select>
            </form>
        @endif
    </div>
</div>

<!-- Invoices Ledger -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">Statement of Account &amp; Invoices</h5>
        <div class="d-flex gap-1 flex-wrap">
            <span class="badge gcash-badge px-3 py-2"><i class="bi bi-phone-fill me-1"></i>GCash QR / Instant</span>
            <span class="badge bg-success px-3 py-2"><i class="bi bi-cash-coin me-1"></i>Walk-In Cash</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-mobile-cards">
            <thead class="table-light small">
                <tr>
                    <th>Invoice #</th>
                    <th>Fee Description</th>
                    <th>Period / Type</th>
                    <th>Deadline / Promised</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td data-label="Invoice #" class="fw-mono small fw-bold text-nowrap">{{ $inv->invoice_number }}</td>
                        <td class="mobile-card-lead" style="min-width: 180px;">
                            <div class="fw-bold text-dark">{{ $inv->title }}</div>
                            <div class="small text-muted">{{ $inv->description }}</div>
                        </td>
                        <td data-label="Period / Type" class="text-nowrap">
                            <span class="badge bg-light text-dark border">{{ $inv->exam_period ?? strtoupper(str_replace('_', ' ', $inv->invoice_type)) }}</span>
                        </td>
                        <td data-label="Deadline" class="small text-nowrap">
                            @if($inv->status === 'promised')
                                <div class="text-primary fw-bold">Moved to Finals</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Finals Due: {{ $inv->promised_date?->format('M d, Y') }}</div>
                            @elseif($inv->promised_date)
                                <div class="text-primary fw-bold">Promised: {{ $inv->promised_date->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Orig: {{ $inv->due_date?->format('M d, Y') }}</div>
                            @elseif($inv->due_date)
                                <div class="fw-medium">{{ $inv->due_date->format('M d, Y') }}</div>
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
                        <td data-label="Status">
                            <span class="badge {{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span>
                        </td>
                        <td class="text-end text-nowrap mobile-card-actions">
                            <div class="d-flex justify-content-end gap-1 flex-wrap w-100">
                                @if(!in_array($inv->status, ['paid', 'pending_verification', 'promised'], true))
                                    <a href="{{ route('student.billing.pay', $inv) }}" class="btn btn-sm {{ $inv->status === 'overdue' ? 'btn-danger fw-bold' : 'btn-gcash' }}">
                                        <i class="bi bi-wallet2 me-1"></i>{{ $inv->status === 'overdue' ? 'Pay Overdue (Lift Exam Restriction)' : 'Pay (GCash / Cash)' }}
                                    </a>
                                @endif

                                @if($inv->isInstallment() && $inv->status === 'unpaid' && $inv->invoice_type !== 'finals_installment')
                                    <form action="{{ route('student.billing.simulate_overdue', $inv) }}" method="POST" class="d-inline flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Demo Trigger: Simulate missing this payment deadline (Account becomes Exam Restricted until paid)">
                                            <i class="bi bi-clock-history"></i> Simulate Late
                                        </button>
                                    </form>
                                    <a href="{{ route('student.promissory') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-ruled me-1"></i>Promissory Note
                                    </a>
                                @endif

                                @if($inv->latestPayment)
                                    <a href="{{ route('student.receipt', $inv->latestPayment) }}" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-receipt me-1"></i>Receipt
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No invoices generated yet. Ensure your enrollment application has been approved by the Registrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <!-- Incomplete Completion (Remedial) Fee Request -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-2"><i class="bi bi-journal-medical text-primary me-2"></i>Incomplete Completion (Remedial) Fee</h6>
            <p class="text-muted small mb-3">
                Need to settle an Incomplete (INC) grade or remedial requirement? Generate an official Completion Fee invoice (₱{{ number_format((float)($settings['completion_fee'] ?? 500), 2) }}) and pay via GCash.
            </p>
            <form action="{{ route('student.billing.completion') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Subject Code &amp; Description</label>
                    <input type="text" name="subject_code" class="form-control form-control-sm" placeholder="e.g. IT-204 Database Management Systems" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Instructor / Term Remarks (Optional)</label>
                    <input type="text" name="remarks" class="form-control form-control-sm" placeholder="e.g. 2nd Sem INC Completion">
                </div>
                <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i>Generate Completion Fee Invoice &amp; Pay
                </button>
            </form>
        </div>
    </div>

    <!-- Payment History & Official Receipts -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-receipt-cutoff text-success me-2"></i>Payment History &amp; Official Receipts (GCash &amp; Cash)</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle small mb-0 table-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Method &amp; Ref #</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">PDF Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pmt)
                            <tr>
                                <td class="mobile-card-lead fw-bold text-nowrap">{{ $pmt->receipt_number }} &bull; <span class="fw-normal text-muted">{{ $pmt->invoice?->title }}</span></td>
                                <td data-label="Method & Ref #" class="text-nowrap">
                                    <span class="badge {{ $pmt->methodBadgeClass() }} me-1">{{ $pmt->payment_method === 'cash' ? 'CASH' : 'GCASH' }}</span>
                                    <code>{{ $pmt->gcash_reference_number }}</code>
                                </td>
                                <td data-label="Particulars" class="d-none d-md-table-cell" style="min-width: 140px;">{{ $pmt->invoice?->title }}</td>
                                <td data-label="Amount" class="fw-bold text-nowrap">₱{{ number_format((float)$pmt->amount, 2) }}</td>
                                <td data-label="Status"><span class="badge {{ $pmt->statusBadgeClass() }}">{{ strtoupper($pmt->status) }}</span></td>
                                <td class="text-end text-nowrap mobile-card-actions">
                                    <a href="{{ route('student.receipt', $pmt) }}" class="btn btn-outline-primary btn-sm py-1 px-2">
                                        <i class="bi bi-printer me-1"></i>View / Print OR
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No GCash transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
