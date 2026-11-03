@extends('layouts.app')

@section('title', 'Promissory Note (Move Term Tuition to Finals) | ESCR Student Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-ruled-fill text-danger me-2"></i>ESCR Promissory Note (Move Term Tuition to Finals)</h4>
        <p class="text-muted small mb-0">
            Submit a parent-signed Promissory Note to notify the Registrar ahead of time if you cannot pay your current term installment on time. Once approved, your installment balance is moved to your <strong>Final Term Installment</strong> and you are <strong>Cleared for Examination</strong>.
        </p>
    </div>
    <a href="{{ route('student.promissory.template') }}" target="_blank" class="btn btn-primary fw-semibold w-100 w-md-auto">
        <i class="bi bi-printer-fill me-1"></i>Open &amp; Print Pre-Filled Promissory Note Template
    </a>
</div>

<!-- Promissory Note Submission Deadlines Banner (Matches Client Image) -->
<div class="card border-danger-subtle bg-danger-subtle p-3 mb-4">
    <div class="row align-items-center g-2">
        <div class="col-md-5">
            <div class="fw-bold text-danger small text-uppercase">
                <i class="bi bi-calendar-event-fill me-1"></i>Deadline of Submission of Promissory Notes:
            </div>
            <div class="small text-dark">
                (*PLS. DOWNLOAD, PRINT &amp; FILL OUT THE PROMISSORY NOTE TEMPLATE THEN SUBMIT TO THE OFFICE / DMS)
            </div>
        </div>
        <div class="col-md-7 d-flex flex-wrap gap-2 justify-content-md-end">
            <span class="badge bg-white text-dark border px-3 py-2"><strong>PRELIM:</strong> AUG 10 ({{ $settings['prelim_pn_deadline'] ?? '2027-08-10' }})</span>
            <span class="badge bg-white text-dark border px-3 py-2"><strong>MIDTERM:</strong> SEP 7 ({{ $settings['midterm_pn_deadline'] ?? '2027-09-07' }})</span>
            <span class="badge bg-white text-dark border px-3 py-2"><strong>SEMI-FINALS:</strong> OCT 5 ({{ $settings['semifinal_pn_deadline'] ?? '2027-10-05' }})</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Upload Signed Promissory Note -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-2">Submit Signed Promissory Note</h5>
            <p class="text-muted small mb-3">
                Under <strong>East Systems Colleges of Rizal</strong> policy, a Promissory Note must be submitted <strong>before the installment deadline</strong> to notify the Registrar ahead of time. Once an installment becomes <strong>late/overdue</strong>, Promissory Note submission is closed and the account is restricted from taking the exam until the overdue tuition is paid.
            </p>

            @if(isset($overdueInstallments) && $overdueInstallments->isNotEmpty())
                <div class="alert alert-danger small mb-3">
                    <div class="fw-bold mb-1"><i class="bi bi-shield-lock-fill me-1"></i>Late / Overdue Installment — Exam Restricted</div>
                    <p class="mb-2">
                        You have {{ $overdueInstallments->count() }} overdue installment(s). Because the deadline has passed, a Promissory Note can no longer be submitted for:
                    </p>
                    @foreach($overdueInstallments as $ovInv)
                        <div class="d-flex justify-content-between align-items-center bg-white rounded p-2 border mb-1">
                            <div>
                                <div class="fw-bold text-dark">{{ $ovInv->title }}</div>
                                <div class="text-danger fw-semibold">₱{{ number_format((float)$ovInv->balance(), 2) }} (Overdue)</div>
                            </div>
                            <a href="{{ route('student.billing.pay', $ovInv) }}" class="btn btn-danger btn-sm fw-bold">
                                <i class="bi bi-wallet2 me-1"></i>Pay to Lift Restriction
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($installmentInvoices->isNotEmpty())
                <form action="{{ route('student.promissory.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Upcoming Unpaid Term Installment (Before Deadline)</label>
                        <select name="invoice_id" id="pnInvoiceSelect" class="form-select" required onchange="updateTemplateLink()">
                            @foreach($installmentInvoices as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->title }} &mdash; ₱{{ number_format((float)$inv->balance(), 2) }} ({{ strtoupper($inv->status) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <a id="dynamicTemplateLink" href="{{ route('student.promissory.template', ['invoice_id' => $installmentInvoices->first()->id]) }}" target="_blank" class="small fw-semibold text-decoration-none">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Preview &amp; Print Pre-Filled Template for Selected Installment &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Settlement Date (Final Term Deadline)</label>
                        <input type="date" name="promised_settlement_date" class="form-control" value="{{ $settings['finals_due_date'] ?? '2027-11-09' }}" required>
                        <div class="form-text small">Defaults to the Final Term Examination deadline ({{ \Carbon\Carbon::parse($settings['finals_due_date'] ?? '2027-11-09')->format('F d, Y') }}).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Student Contact Number</label>
                        <input type="text" name="student_contact" class="form-control" value="{{ $student->contact_number ?: $enrollment?->contact_number }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">Parent / Guardian Name (Conforme)</label>
                            <input type="text" name="guardian_name" class="form-control" value="{{ $enrollment?->guardian_name ?? 'Parent / Guardian' }}" required>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label small fw-semibold">Guardian Contact No.</label>
                            <input type="text" name="guardian_contact" class="form-control" value="{{ $enrollment?->guardian_contact ?? '09175550199' }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Upload Scanned Signed Promissory Note (PDF/JPG/PNG)</label>
                        <input type="file" name="signed_note_file" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 fw-semibold py-2">
                        <i class="bi bi-cloud-upload-fill me-1"></i>Submit Signed Promissory Note to Registrar
                    </button>
                </form>
            @else
                <div class="alert alert-info small mb-0">
                    You currently have no unpaid or overdue installment invoices requiring a Promissory Note.
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Submitted Promissory Notes & Rollover Status -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3">My Submitted Promissory Notes &amp; Rollover Status</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Note #</th>
                            <th>Term &amp; Moved Amount</th>
                            <th>Moved To (Target Date)</th>
                            <th>Registrar Status</th>
                            <th>Exam Clearance Effect</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promissoryNotes as $note)
                            <tr>
                                <td class="mobile-card-lead">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">{{ $note->note_number }}</span>
                                        <a href="{{ route('documents.promissory', $note) }}" target="_blank" class="small text-decoration-none">
                                            <i class="bi bi-paperclip"></i> View Scan
                                        </a>
                                    </div>
                                </td>
                                <td data-label="Term & Amount">
                                    <span class="fw-semibold">{{ $note->installment_period }}</span>
                                    <span class="text-muted ms-1">(₱{{ number_format((float)$note->outstanding_amount, 2) }})</span>
                                </td>
                                <td data-label="Moved To" class="fw-medium">
                                    <div>Final Term Installment</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Due: {{ $note->promised_settlement_date->format('M d, Y') }}</div>
                                </td>
                                <td data-label="Note Status">
                                    <div>
                                        <span class="badge {{ $note->statusBadgeClass() }}">{{ $note->statusLabel() }}</span>
                                        @if($note->registrar_remarks)
                                            <div class="text-muted mt-1" style="font-size:0.72rem;">{{ $note->registrar_remarks }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Exam Clearance">
                                    <div>
                                        @if($note->status === 'approved')
                                            <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Cleared for Exam</span>
                                            <div class="mt-1">
                                                <a href="{{ route('student.clearance') }}" class="small fw-semibold text-success text-decoration-none">
                                                    <i class="bi bi-printer me-1"></i>View Exam Permit &rarr;
                                                </a>
                                            </div>
                                        @elseif($note->status === 'rejected')
                                            <span class="badge bg-danger">Re-upload Required</span>
                                        @else
                                            <span class="text-muted">Cleared upon approval</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No promissory notes submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateTemplateLink() {
    var select = document.getElementById('pnInvoiceSelect');
    var link = document.getElementById('dynamicTemplateLink');
    if (select && link) {
        link.href = "{{ route('student.promissory.template') }}?invoice_id=" + encodeURIComponent(select.value);
    }
}
</script>
@endpush
@endsection
