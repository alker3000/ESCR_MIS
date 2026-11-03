@extends('layouts.app')

@section('title', 'Admin Analytics Dashboard | ESCR Super Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line-fill text-danger me-2"></i>Executive Analytics &amp; Operations Dashboard</h4>
        <p class="text-muted small mb-0">Real-time collection metrics, enrollment trends, uniform fabric fulfillment pipeline, and security audit trail.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.settings.fees') }}" class="btn btn-outline-dark btn-sm fw-semibold">
            <i class="bi bi-sliders me-1"></i>Configure Deadlines &amp; Pricing
        </a>
        <a href="{{ route('admin.audit') }}" class="btn btn-danger btn-sm fw-semibold">
            <i class="bi bi-shield-check me-1"></i>Security Audit Trail
        </a>
    </div>
</div>

<!-- Top Executive KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-success">
            <div class="text-muted small">Total Collected Revenue</div>
            <h3 class="fw-bold text-success mb-0">₱{{ number_format($totalCollected, 2) }}</h3>
            <div class="small text-muted">Of ₱{{ number_format($totalBilled, 2) }} Total Billed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-warning">
            <div class="text-muted small">Unpaid / Promised Balances</div>
            <h3 class="fw-bold text-danger mb-0">₱{{ number_format($outstandingBalance, 2) }}</h3>
            <div class="small text-muted">{{ $activePromissoryNotes }} Active Promissory Notes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-primary">
            <div class="text-muted small">Officially Enrolled Students</div>
            <h3 class="fw-bold mb-0">{{ $enrolledStudents }} <span class="fs-6 text-muted fw-normal">/ {{ $totalStudents }}</span></h3>
            <div class="small text-muted">{{ $pendingApplications }} Applications In Pipeline</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-info">
            <div class="text-muted small">Tela Fulfillment Pipeline</div>
            <h3 class="fw-bold mb-0">{{ array_sum($telaStats) }} Orders</h3>
            <div class="small text-muted">
                {{ $telaStats['paid_processing'] }} To Pack &bull; {{ $telaStats['ready_for_pickup'] }} Ready &bull; {{ $telaStats['claimed'] }} Claimed
            </div>
        </div>
    </div>
</div>

<!-- Visual Analytics Charts -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Verified Revenue Breakdown by Fee Category (PHP)</h6>
            <canvas id="revenueChart" height="130"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-mortarboard-fill text-success me-2"></i>Enrollment Distribution by Academic Program</h6>
            <canvas id="courseChart" height="185"></canvas>
        </div>
    </div>
</div>

<!-- Recent Security Audit Trail -->
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock-fill text-danger me-2"></i>Recent Security Audit Trail &amp; System Activity</h6>
        <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-outline-danger">View Full Audit Log</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Actor &amp; Role</th>
                    <th>Action Event</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAuditLogs as $log)
                    <tr>
                        <td class="text-muted">{{ $log->created_at->format('M d, Y h:i:s A') }}</td>
                        <td>
                            <div class="fw-bold">{{ $log->actor_name }}</div>
                            <span class="badge bg-light text-dark border">{{ strtoupper($log->actor_role) }}</span>
                        </td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->description }}</td>
                        <td class="text-muted">{{ $log->ip_address }}</td>
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
                backgroundColor: ['#1d4ed8', '#0284c7', '#dc2626', '#d97706', '#16a34a'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const courseLabels = {!! json_encode(array_keys($enrollmentsByCourse)) !!};
    const courseData = {!! json_encode(array_values($enrollmentsByCourse)) !!};

    new Chart(document.getElementById('courseChart'), {
        type: 'doughnut',
        data: {
            labels: courseLabels,
            datasets: [{
                data: courseData,
                backgroundColor: ['#1e3a8a', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0284c7']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endpush
@endsection
