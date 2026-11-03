@extends('layouts.app')

@section('title', 'Review Application ' . $enrollment->application_reference . ' | ESCR Registrar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('registrar.applications') }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Back to Applications Queue</a>
        <h4 class="fw-bold mb-0 mt-1">Review Application: {{ $enrollment->fullName() }} (<code>{{ $enrollment->application_reference }}</code>)</h4>
    </div>
    <span class="badge {{ $enrollment->statusBadgeClass() }} fs-6 px-3 py-2">{{ $enrollment->statusLabel() }}</span>
</div>

<div class="row g-4">
    <!-- Left Column: Applicant Profile -->
    <div class="col-lg-5">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold text-uppercase text-muted small mb-3">Applicant Information</h6>
            <dl class="row small mb-0">
                <dt class="col-sm-5 text-muted">Full Name:</dt>
                <dd class="col-sm-7 fw-bold">{{ $enrollment->fullName() }}</dd>

                <dt class="col-sm-5 text-muted">Academic Program:</dt>
                <dd class="col-sm-7 fw-semibold text-primary">{{ $enrollment->course }}</dd>

                <dt class="col-sm-5 text-muted">Year &amp; Type:</dt>
                <dd class="col-sm-7">{{ $enrollment->year_level }} &bull; {{ $enrollment->student_type }}</dd>

                <dt class="col-sm-5 text-muted">Payment Plan:</dt>
                <dd class="col-sm-7"><span class="badge bg-dark">{{ strtoupper($enrollment->payment_scheme) }} PLAN</span></dd>

                <dt class="col-sm-5 text-muted">Gender / Birth Date:</dt>
                <dd class="col-sm-7">{{ $enrollment->gender }} &bull; {{ $enrollment->birth_date?->format('M d, Y') }}</dd>

                <dt class="col-sm-5 text-muted">Contact / Email:</dt>
                <dd class="col-sm-7">{{ $enrollment->contact_number }}<br>{{ $enrollment->user->email }}</dd>

                <dt class="col-sm-5 text-muted">Home Address:</dt>
                <dd class="col-sm-7">{{ $enrollment->address }}</dd>

                <dt class="col-sm-5 text-muted">Parent / Guardian:</dt>
                <dd class="col-sm-7">{{ $enrollment->guardian_name }} ({{ $enrollment->guardian_relationship }})<br>{{ $enrollment->guardian_contact }}</dd>

                <dt class="col-sm-5 text-muted">Previous School:</dt>
                <dd class="col-sm-7">{{ $enrollment->previous_school ?? 'N/A' }} ({{ $enrollment->previous_school_year ?? 'N/A' }})</dd>
            </dl>
        </div>

        <!-- Final Registrar Decision Box -->
        @if($enrollment->status !== 'enrolled')
            <div class="card p-4 border-primary">
                <h6 class="fw-bold mb-2">Registrar Verification Decision</h6>
                <p class="text-muted small mb-3">
                    Approving this application marks all documents valid, updates status to <strong>Approved: Awaiting Payment</strong>, and automatically generates the student's tuition/installment invoices.
                </p>

                <form action="{{ route('registrar.applications.approve', $enrollment) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Approval Remarks (Optional)</label>
                        <input type="text" name="registrar_remarks" class="form-control form-control-sm" value="All enrollment credentials verified. Proceed to GCash payment.">
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                        <i class="bi bi-check2-circle me-1"></i>Approve Documents &amp; Generate Tuition Invoices
                    </button>
                </form>

                <hr>

                <form action="{{ route('registrar.applications.return', $enrollment) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-danger">Or Return to Student for Document Revision</label>
                        <input type="text" name="registrar_remarks" class="form-control form-control-sm" placeholder="Specify missing or blurry document (e.g. Blurry Form 138 scan)..." required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                        <i class="bi bi-arrow-return-left me-1"></i>Return Application to Student
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Right Column: Document Management System (DMS) Files -->
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-check-fill text-primary me-2"></i>Uploaded DMS Credentials</h5>

            @forelse($enrollment->documents as $doc)
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold mb-0">{{ $doc->typeLabel() }}</h6>
                            <div class="small text-muted">{{ $doc->original_filename }} ({{ number_format($doc->file_size / 1024, 1) }} KB)</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $doc->statusBadgeClass() }}">{{ strtoupper($doc->status) }}</span>
                            <a href="{{ route('documents.enrollment', $doc) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Preview File
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('registrar.documents.review', $doc) }}" method="POST" class="row g-2 align-items-center pt-2 border-top">
                        @csrf
                        <div class="col-sm-6">
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Document remarks (optional)" value="{{ $doc->remarks }}">
                        </div>
                        <div class="col-sm-3 d-grid">
                            <button type="submit" name="status" value="approved" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-check-lg"></i> Approve Doc
                            </button>
                        </div>
                        <div class="col-sm-3 d-grid">
                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg"></i> Reject Doc
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="alert alert-warning mb-0">No documents uploaded by this applicant yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
