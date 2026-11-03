@extends('layouts.app')

@section('title', 'Registrar Dashboard | ESCR Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Office of the Registrar Dashboard</h4>
        <p class="text-muted small mb-0">Inspect student DMS enrollment credentials, approve applications, and review parent-signed Promissory Notes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('registrar.applications') }}" class="btn btn-primary btn-sm fw-semibold">
            <i class="bi bi-folder-check me-1"></i>Review Applications ({{ $pendingApplicationsCount }})
        </a>
        <a href="{{ route('registrar.promissory') }}" class="btn btn-danger btn-sm fw-semibold">
            <i class="bi bi-journal-check me-1"></i>Review Promissory Notes ({{ $pendingPromissoryCount }})
        </a>
    </div>
</div>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-warning">
            <div class="text-muted small">Pending Document Review</div>
            <h3 class="fw-bold mb-0">{{ $pendingApplicationsCount }}</h3>
            <div class="small text-muted">Awaiting Registrar Inspection</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-primary">
            <div class="text-muted small">Approved (Awaiting Payment)</div>
            <h3 class="fw-bold mb-0">{{ $approvedAwaitingPaymentCount }}</h3>
            <div class="small text-muted">Tuition Invoice Generated</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-success">
            <div class="text-muted small">Officially Enrolled</div>
            <h3 class="fw-bold text-success mb-0">{{ $enrolledCount }}</h3>
            <div class="small text-muted">1st Semester SY 2026–2027</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-danger">
            <div class="text-muted small">Pending Promissory Notes</div>
            <h3 class="fw-bold text-danger mb-0">{{ $pendingPromissoryCount }}</h3>
            <div class="small text-muted">Requires Conforme Verification</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Enrollment Applications -->
    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Enrollment Applications</h6>
                <a href="{{ route('registrar.applications') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref #</th>
                            <th>Applicant</th>
                            <th>Docs</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentApplications as $app)
                            <tr>
                                <td class="fw-bold"><code>{{ $app->application_reference }}</code></td>
                                <td>
                                    <div class="fw-bold">{{ $app->fullName() }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $app->course }} ({{ $app->year_level }})</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $app->documents->count() }} files</span></td>
                                <td><span class="badge {{ $app->statusBadgeClass() }}">{{ $app->statusLabel() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('registrar.applications.review', $app) }}" class="btn btn-sm btn-primary py-1 px-2">
                                        Inspect Docs
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No applications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Promissory Notes Queue -->
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Pending Promissory Notes</h6>
                <a href="{{ route('registrar.promissory') }}" class="btn btn-sm btn-outline-danger">Manage Notes</a>
            </div>
            @forelse($pendingPromissoryNotes as $pn)
                <div class="border rounded p-3 mb-2 bg-light small">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <strong>{{ $pn->user->name }}</strong>
                            <span class="badge bg-warning text-dark ms-1">{{ $pn->note_number }}</span>
                        </div>
                        <span class="fw-bold text-danger">₱{{ number_format((float)$pn->outstanding_amount, 2) }}</span>
                    </div>
                    <div class="text-muted mb-2">
                        {{ $pn->installment_period }} &bull; Promised Date: <strong>{{ $pn->promised_settlement_date->format('M d, Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('documents.promissory', $pn) }}" target="_blank" class="text-decoration-none fw-semibold">
                            <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>View Signed Scan
                        </a>
                        <form action="{{ route('registrar.promissory.approve', $pn) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success py-1 px-2">
                                <i class="bi bi-check-lg me-1"></i>Approve &amp; Add Special Fee
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4 small">
                    No pending Promissory Notes awaiting review.
                    <div class="mt-2">
                        <a href="{{ route('registrar.promissory') }}" class="btn btn-sm btn-outline-secondary">View Approved History</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
