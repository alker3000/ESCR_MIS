@extends('layouts.app')

@section('title', 'Cashier Dashboard | ESCR Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>School Cashier Financial &amp; Fulfillment Hub</h4>
        <p class="text-muted small mb-0">Reconcile GCash payments, issue Official Receipts, and manage Uniform Fabric (<em>Tela</em>) pickup notifications.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('cashier.payments') }}" class="btn btn-gcash btn-sm">
            <i class="bi bi-phone-vibrate-fill me-1"></i>GCash Transactions ({{ $pendingVerificationsCount }} Pending)
        </a>
        <a href="{{ route('cashier.tela') }}" class="btn btn-primary btn-sm fw-semibold">
            <i class="bi bi-box-seam-fill me-1"></i>Tela Orders ({{ $paidProcessingTelaCount }} To Prepare)
        </a>
    </div>
</div>

<!-- Revenue Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-success">
            <div class="text-muted small">Total Verified Collections</div>
            <h3 class="fw-bold text-success mb-0">₱{{ number_format($totalRevenue, 2) }}</h3>
            <div class="small text-muted">Today: ₱{{ number_format($todayRevenue, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-primary">
            <div class="text-muted small">Tuition &amp; Installments</div>
            <h3 class="fw-bold mb-0">₱{{ number_format($tuitionRevenue, 2) }}</h3>
            <div class="small text-muted">Downpayment &amp; 4 Terms</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-danger">
            <div class="text-muted small">Special Exam &amp; Completion</div>
            <h3 class="fw-bold mb-0">₱{{ number_format($specialExamRevenue + $completionRevenue, 2) }}</h3>
            <div class="small text-muted">Special: ₱{{ number_format($specialExamRevenue, 0) }} | Remedial: ₱{{ number_format($completionRevenue, 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-info">
            <div class="text-muted small">Uniform Tela &amp; Patch Sales</div>
            <h3 class="fw-bold text-primary mb-0">₱{{ number_format($telaRevenue, 2) }}</h3>
            <div class="small text-muted">{{ $paidProcessingTelaCount }} To Prepare &bull; {{ $readyForPickupTelaCount }} Ready</div>
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
