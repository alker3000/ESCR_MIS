@extends('layouts.app')

@section('title', 'Admin Analytics Dashboard | ESCR Super Admin')

@section('content')
<!-- Screen 1 Style Header: ESCR OVERVIEW -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="aurum-title-uppercase fs-4">ESCR OVERVIEW</span>
            <span class="badge fw-bold px-2 py-1" style="background: linear-gradient(135deg, #F3D87D, #D4AF37); color: #24070B;">
                Executive Analytics &amp; Operations Dashboard
            </span>
        </div>
        <p class="text-muted small mb-0">Real-time collection metrics, enrollment trends, uniform fabric fulfillment pipeline, and security audit trail.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="aurum-segmented-group">
            <a href="{{ route('admin.dashboard') }}" class="aurum-segmented-btn active">Dashboard</a>
            <a href="{{ route('admin.settings.fees') }}" class="aurum-segmented-btn">
                <i class="bi bi-sliders me-1"></i>Configure Deadlines &amp; Pricing
            </a>
        </div>
        <a href="{{ route('admin.audit') }}" class="btn btn-warning btn-sm fw-bold px-3">
            <i class="bi bi-shield-check me-1"></i>Security Audit Trail
        </a>
    </div>
</div>

<!-- Screen 1 Top Row: 2 Deep Imperial Burgundy Chart Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="fw-bold text-white" style="font-size: 0.95rem;">Forecast &amp; Revenue by Fee Category</div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">Verified Collections Breakdown (PHP)</div>
                </div>
                <span class="badge" style="background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.4);">
                    SY 2026–2027
                </span>
            </div>
            <canvas id="revenueChart" height="135"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div style="font-size: 0.74rem; color: #D9C3B0;">Revenue</div>
                    <div class="fw-extrabold font-mono" style="font-size: 1.65rem; color: #F3D87D; line-height: 1.1;">
                        ₱{{ number_format($totalCollected, 2) }}
                    </div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">Of ₱{{ number_format($totalBilled, 2) }} Total Billed Across All Programs</div>
                </div>
                <span class="badge" style="background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.4);">
                    {{ $enrolledStudents }} Enrolled
                </span>
            </div>
            <canvas id="courseChart" height="115"></canvas>
        </div>
    </div>
</div>

<!-- Screen 1 Middle Card: Active Enrollment Curve -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h6 class="fw-bold mb-0" style="color: #24070B;">Active Enrollees &amp; Pipeline Velocity</h6>
            <div class="text-muted" style="font-size: 0.75rem;">{{ $enrolledStudents }} Officially Enrolled &bull; {{ $pendingApplications }} Applications In Pipeline &bull; {{ $activePromissoryNotes }} Active Promissory Notes</div>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary py-1 px-3" style="font-size: 0.75rem;">Manage Users &amp; RBAC</a>
    </div>
    <svg viewBox="0 0 600 85" class="w-100" style="height: 78px; display: block;">
        <defs>
            <linearGradient id="adminIvoryGoldFill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="#D4AF37" stop-opacity="0.02"/>
            </linearGradient>
        </defs>
        <line x1="0" y1="22" x2="600" y2="22" stroke="#F2ECE4" stroke-width="1"/>
        <line x1="0" y1="52" x2="600" y2="52" stroke="#F2ECE4" stroke-width="1"/>
        <path d="M0,68 Q55,44 110,54 T220,30 T330,42 T440,16 T550,24 T600,12 L600,82 L0,82 Z" fill="url(#adminIvoryGoldFill)"/>
        <path d="M0,68 Q55,44 110,54 T220,30 T330,42 T440,16 T550,24 T600,12" fill="none" stroke="#C59B27" stroke-width="2.5"/>
    </svg>
</div>

<!-- Screen 1 Key Metrics Row (4 White KPI Cards) -->
<div class="mb-2 d-flex justify-content-between align-items-center">
    <span class="fw-bold small text-uppercase" style="letter-spacing: 0.06em; color: #5C4346;">Key Metrics</span>
    <span class="small text-muted">Live Institutional Ledger</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #15803D;">₱{{ number_format($totalCollected, 2) }}</div>
            <div class="aurum-kpi-label">Total Collected Revenue</div>
            <div class="aurum-kpi-sub">Of ₱{{ number_format($totalBilled, 2) }} Total Billed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #991B1B;">₱{{ number_format($outstandingBalance, 2) }}</div>
            <div class="aurum-kpi-label">Unpaid / Promised Balances</div>
            <div class="aurum-kpi-sub">{{ $activePromissoryNotes }} Active Promissory Notes</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">{{ $enrolledStudents }} <span class="fs-6 text-muted fw-normal">/ {{ $totalStudents }}</span></div>
            <div class="aurum-kpi-label">Officially Enrolled Students</div>
            <div class="aurum-kpi-sub">{{ $pendingApplications }} Applications In Pipeline</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">{{ array_sum($telaStats) }} Orders</div>
            <div class="aurum-kpi-label">Tela Fulfillment Pipeline</div>
            <div class="aurum-kpi-sub">
                {{ $telaStats['paid_processing'] }} To Pack &bull; {{ $telaStats['ready_for_pickup'] }} Ready &bull; {{ $telaStats['claimed'] }} Claimed
            </div>
        </div>
    </div>
</div>

<!-- Screen 1 Bottom Card: Recent Activity / Security Audit Trail -->
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Recent Activity &amp; Security Audit Trail</h6>
        <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-outline-primary">View Full Audit Log</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Actor &amp; Role</th>
                    <th>Action Event</th>
                    <th>Description</th>
                    <th>Timestamp</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAuditLogs as $log)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="aurum-activity-avatar">
                                    {{ strtoupper(substr($log->actor_name ?? 'S', 0, 1)) }}
                                </span>
                                <div>
                                    <div class="fw-bold text-dark">{{ $log->actor_name }}</div>
                                    <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ strtoupper($log->actor_role) }}</span>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->description }}</td>
                        <td class="text-muted text-nowrap">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td class="text-muted font-mono">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No audit events recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const revLabels = {!! json_encode(array_keys($revenueByType)) !!};
    const revData = {!! json_encode(array_values($revenueByType)) !!};

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revLabels,
            datasets: [{
                label: 'Verified Collections (PHP)',
                data: revData,
                backgroundColor: ['#F3D87D', '#D4AF37', '#B68D24', '#E5C158', '#8C6D23'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    ticks: { color: '#D9C3B0', font: { size: 10 } },
                    grid: { color: 'rgba(212, 175, 55, 0.08)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#D9C3B0', font: { size: 10 } },
                    grid: { color: 'rgba(212, 175, 55, 0.12)' }
                }
            }
        }
    });

    const courseLabels = {!! json_encode(array_keys($enrollmentsByCourse)) !!};
    const courseData = {!! json_encode(array_values($enrollmentsByCourse)) !!};

    new Chart(document.getElementById('courseChart'), {
        type: 'bar',
        data: {
            labels: courseLabels,
            datasets: [{
                label: 'Enrollees',
                data: courseData,
                backgroundColor: ['#D4AF37', '#F3D87D', '#C59B27', '#9A7418', '#E8C967', '#8C1D28'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { color: '#D9C3B0', font: { size: 10 } },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#D9C3B0', font: { size: 10 } },
                    grid: { color: 'rgba(212, 175, 55, 0.12)' }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
