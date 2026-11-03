@extends('layouts.app')

@section('title', 'Registrar Dashboard | ESCR Portal')

@section('content')
<!-- Screen 1 Style Header: ESCR OVERVIEW (Registrar Hub) -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="aurum-title-uppercase fs-4">ESCR OVERVIEW</span>
            <span class="badge fw-bold px-2 py-1" style="background: linear-gradient(135deg, #F3D87D, #D4AF37); color: #24070B;">
                Office of the Registrar Dashboard
            </span>
        </div>
        <p class="text-muted small mb-0">Inspect student DMS enrollment credentials, approve applications, and review parent-signed Promissory Notes.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('registrar.applications') }}" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-folder-check me-1"></i>Review Applications ({{ $pendingApplicationsCount }})
        </a>
        <a href="{{ route('registrar.promissory') }}" class="btn btn-warning btn-sm fw-bold">
            <i class="bi bi-journal-check me-1"></i>Review Promissory Notes ({{ $pendingPromissoryCount }})
        </a>
    </div>
</div>

<!-- Screen 1 Top Row: 2 Deep Imperial Burgundy Chart Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold text-white" style="font-size: 0.92rem;">DMS Document &amp; Enrollment Pipeline</div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">PSA Birth Cert, Form 138 &amp; Good Moral Inspection</div>
                </div>
                <span class="badge" style="background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.4);">
                    {{ $pendingApplicationsCount }} Pending
                </span>
            </div>
            <div class="aurum-mini-bars">
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 88%;"></div><div class="aurum-mini-bar-label">BSIT</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 76%;"></div><div class="aurum-mini-bar-label">BSCS</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 82%;"></div><div class="aurum-mini-bar-label">BSBA</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 68%;"></div><div class="aurum-mini-bar-label">BSHM</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 72%;"></div><div class="aurum-mini-bar-label">BSED</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 64%;"></div><div class="aurum-mini-bar-label">BSCRIM</div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size: 0.74rem; color: #D9C3B0;">Officially Enrolled Students</div>
                    <div class="fw-extrabold font-mono mt-1" style="font-size: 1.65rem; color: #F3D87D; line-height: 1.1;">
                        {{ $enrolledCount }} ENROLLEES
                    </div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">Awaiting Payment: <strong class="text-white">{{ $approvedAwaitingPaymentCount }}</strong> &bull; Promissory Queue: <strong style="color: #F3D87D;">{{ $pendingPromissoryCount }}</strong></div>
                </div>
                <a href="{{ route('registrar.students') }}" class="btn btn-sm fw-bold" style="background: rgba(212, 175, 55, 0.16); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.45); font-size: 0.74rem;">
                    <i class="bi bi-people-fill me-1"></i>Directory
                </a>
            </div>
            <div class="mt-2">
                <svg viewBox="0 0 360 64" class="w-100" style="height: 60px; display: block;">
                    <defs>
                        <linearGradient id="aurumRegWave" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#D4AF37" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,52 Q45,30 90,38 T180,20 T270,26 T360,10 L360,64 L0,64 Z" fill="url(#aurumRegWave)"/>
                    <path d="M0,52 Q45,30 90,38 T180,20 T270,26 T360,10" fill="none" stroke="#F3D87D" stroke-width="2.2"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Screen 1 Key Metrics Row (4 White KPI Cards) -->
<div class="mb-2 d-flex justify-content-between align-items-center">
    <span class="fw-bold small text-uppercase" style="letter-spacing: 0.06em; color: #5C4346;">Key Metrics</span>
    <span class="small text-muted">Registrar Operations</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">{{ $pendingApplicationsCount }}</div>
            <div class="aurum-kpi-label">Pending Document Review</div>
            <div class="aurum-kpi-sub">Awaiting Registrar Inspection</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">{{ $approvedAwaitingPaymentCount }}</div>
            <div class="aurum-kpi-label">Approved (Awaiting Payment)</div>
            <div class="aurum-kpi-sub">Tuition Invoice Generated</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #15803D;">{{ $enrolledCount }}</div>
            <div class="aurum-kpi-label">Officially Enrolled</div>
            <div class="aurum-kpi-sub">1st Semester SY 2026–2027</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #991B1B;">{{ $pendingPromissoryCount }}</div>
            <div class="aurum-kpi-label">Pending Promissory Notes</div>
            <div class="aurum-kpi-sub">Requires Conforme Verification</div>
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
