@extends('layouts.app')

@section('title', 'Cashier Dashboard | ESCR Portal')

@section('content')
<!-- Screen 1 Style Header: ESCR OVERVIEW (Cashier Hub) -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="aurum-title-uppercase fs-4">ESCR OVERVIEW</span>
            <span class="badge fw-bold px-2 py-1" style="background: linear-gradient(135deg, #F3D87D, #D4AF37); color: #24070B;">
                School Cashier Financial &amp; Fulfillment Hub
            </span>
        </div>
        <p class="text-muted small mb-0">Reconcile GCash payments, issue Official Receipts, and manage Uniform Fabric (<em>Tela</em>) pickup notifications.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('cashier.payments') }}" class="btn btn-gcash btn-sm fw-bold">
            <i class="bi bi-phone-vibrate-fill me-1"></i>GCash Transactions ({{ $pendingVerificationsCount }} Pending)
        </a>
        <a href="{{ route('cashier.tela') }}" class="btn btn-warning btn-sm fw-bold">
            <i class="bi bi-box-seam-fill me-1"></i>Tela Orders ({{ $paidProcessingTelaCount }} To Prepare)
        </a>
    </div>
</div>

<!-- Screen 1 Top Row: 2 Deep Imperial Burgundy Chart Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold text-white" style="font-size: 0.92rem;">Revenue Category Breakdown</div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">Tuition, Special Exam, Remedial &amp; Uniform Tela</div>
                </div>
                <span class="badge" style="background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.4);">
                    {{ $pendingVerificationsCount }} Pending Verify
                </span>
            </div>
            <div class="aurum-mini-bars">
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 92%;"></div><div class="aurum-mini-bar-label">Tuition</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 68%;"></div><div class="aurum-mini-bar-label">Prelim</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 74%;"></div><div class="aurum-mini-bar-label">Midterm</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 52%;"></div><div class="aurum-mini-bar-label">Special</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 64%;"></div><div class="aurum-mini-bar-label">Tela</div></div>
                <div class="aurum-mini-bar-col"><div class="aurum-mini-bar" style="height: 85%;"></div><div class="aurum-mini-bar-label">Today</div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aurum-dark-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size: 0.74rem; color: #D9C3B0;">Total Verified Collections</div>
                    <div class="fw-extrabold font-mono mt-1" style="font-size: 1.65rem; color: #F3D87D; line-height: 1.1;">
                        ₱{{ number_format($totalRevenue, 2) }}
                    </div>
                    <div style="font-size: 0.72rem; color: #D9C3B0;">Today: <strong class="text-white">₱{{ number_format($todayRevenue, 2) }}</strong> &bull; GCash &amp; Cash Verified</div>
                </div>
                <a href="{{ route('cashier.receipts') }}" class="btn btn-sm fw-bold" style="background: rgba(212, 175, 55, 0.16); color: #F3D87D; border: 1px solid rgba(212, 175, 55, 0.45); font-size: 0.74rem;">
                    <i class="bi bi-receipt-cutoff me-1"></i>Official Receipts
                </a>
            </div>
            <div class="mt-2">
                <svg viewBox="0 0 360 64" class="w-100" style="height: 60px; display: block;">
                    <defs>
                        <linearGradient id="aurumCashierWave" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#D4AF37" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,54 Q45,32 90,42 T180,22 T270,28 T360,12 L360,64 L0,64 Z" fill="url(#aurumCashierWave)"/>
                    <path d="M0,54 Q45,32 90,42 T180,22 T270,28 T360,12" fill="none" stroke="#F3D87D" stroke-width="2.2"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Screen 1 Key Metrics Row (4 White KPI Cards) -->
<div class="mb-2 d-flex justify-content-between align-items-center">
    <span class="fw-bold small text-uppercase" style="letter-spacing: 0.06em; color: #5C4346;">Key Metrics</span>
    <span class="small text-muted">Cashier Collection Summary</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #15803D;">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="aurum-kpi-label">Total Verified Collections</div>
            <div class="aurum-kpi-sub">Today: ₱{{ number_format($todayRevenue, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">₱{{ number_format($tuitionRevenue, 2) }}</div>
            <div class="aurum-kpi-label">Tuition &amp; Installments</div>
            <div class="aurum-kpi-sub">Downpayment &amp; 4 Terms</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value">₱{{ number_format($specialExamRevenue + $completionRevenue, 2) }}</div>
            <div class="aurum-kpi-label">Special Exam &amp; Completion</div>
            <div class="aurum-kpi-sub">Special: ₱{{ number_format($specialExamRevenue, 0) }} | Remedial: ₱{{ number_format($completionRevenue, 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aurum-kpi-card h-100">
            <div class="aurum-kpi-value" style="color: #6E1722;">₱{{ number_format($telaRevenue, 2) }}</div>
            <div class="aurum-kpi-label">Uniform Tela &amp; Patch Sales</div>
            <div class="aurum-kpi-sub">{{ $paidProcessingTelaCount }} To Prepare &bull; {{ $readyForPickupTelaCount }} Ready</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Uniform Tela Fulfillment Queue -->
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-scissors text-primary me-2"></i>Active Uniform Tela Orders (Fulfillment Queue)</h6>
                <a href="{{ route('cashier.tela') }}" class="btn btn-sm btn-outline-primary">Full Workbench</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Student</th>
                            <th>Fabric / Patches</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingTelaOrders as $ord)
                            <tr>
                                <td class="fw-bold">{{ $ord->order_number }}</td>
                                <td>{{ $ord->user->name }}</td>
                                <td>{{ $ord->itemsSummary() }}</td>
                                <td><span class="badge {{ $ord->statusBadgeClass() }}">{{ $ord->statusLabel() }}</span></td>
                                <td class="text-end">
                                    @if($ord->status === 'paid_processing')
                                        <form action="{{ route('cashier.tela.ready', $ord) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fw-semibold">
                                                <i class="bi bi-bell-fill me-1"></i>Mark Ready (Send Email)
                                            </button>
                                        </form>
                                    @elseif($ord->status === 'ready_for_pickup')
                                        <form action="{{ route('cashier.tela.claimed', $ord) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold">
                                                <i class="bi bi-check2-all me-1"></i>Mark Claimed
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active tela orders waiting for preparation.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent GCash Payments -->
    <div class="col-lg-6">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-phone-fill text-primary me-2"></i>Recent GCash Transactions</h6>
                <a href="{{ route('cashier.payments') }}" class="btn btn-sm btn-outline-success">All Payments</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Student</th>
                            <th>Particulars</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $pmt)
                            <tr>
                                <td class="fw-bold">
                                    <a href="{{ route('student.receipt', $pmt) }}" class="text-decoration-none">{{ $pmt->receipt_number }}</a>
                                </td>
                                <td>{{ $pmt->user->name }}</td>
                                <td>{{ $pmt->invoice?->title }}</td>
                                <td class="fw-bold">₱{{ number_format((float)$pmt->amount, 2) }}</td>
                                <td><span class="badge {{ $pmt->statusBadgeClass() }}">{{ strtoupper($pmt->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
