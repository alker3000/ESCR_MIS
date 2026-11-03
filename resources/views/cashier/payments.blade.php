@extends('layouts.app')

@section('title', 'Payment Verification Ledger (GCash & Cash) | ESCR Cashier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-coin text-primary me-2"></i>Payment Transaction Ledger (GCash &amp; Walk-In Cash)</h4>
        <p class="text-muted small mb-0">Verify manual GCash QR/reference submissions, receive walk-in Cash payments over-the-counter, and inspect auto-verified transactions.</p>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('cashier.payments', ['status' => 'all']) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('cashier.payments', ['status' => 'pending']) }}" class="btn {{ $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">Pending Verification</a>
        <a href="{{ route('cashier.payments', ['status' => 'verified']) }}" class="btn {{ $statusFilter === 'verified' ? 'btn-primary' : 'btn-outline-primary' }}">Verified</a>
        <a href="{{ route('cashier.payments', ['status' => 'rejected']) }}" class="btn {{ $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rejected</a>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt #</th>
                    <th>Student</th>
                    <th>Invoice &amp; Fee Type</th>
                    <th>Method &amp; Ref # / Payer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Cashier Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pmt)
                    <tr>
                        <td class="fw-bold">{{ $pmt->receipt_number }}</td>
                        <td>
                            <div class="fw-bold">{{ $pmt->user->name }}</div>
                            <div class="text-muted">{{ $pmt->user->student_number ?? 'Applicant' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $pmt->invoice?->title }}</div>
                            <div class="text-muted"><code>{{ $pmt->invoice?->invoice_number }}</code></div>
                        </td>
                        <td>
                            <div class="mb-1">
                                <span class="badge {{ $pmt->methodBadgeClass() }} me-1">
                                    {{ $pmt->payment_method === 'cash' ? 'CASH (WALK-IN)' : 'GCASH' }}
                                </span>
                                <code>{{ $pmt->gcash_reference_number }}</code>
                            </div>
                            <div class="text-muted">{{ $pmt->gcash_sender_name }} ({{ $pmt->gcash_sender_number }})</div>
                            @if($pmt->proof_file_path)
                                <a href="{{ route('documents.payment_proof', $pmt) }}" target="_blank" class="small text-decoration-none">
                                    <i class="bi bi-image me-1"></i>View Screenshot
                                </a>
                            @endif
                        </td>
                        <td class="fw-bold fs-6">₱{{ number_format((float)$pmt->amount, 2) }}</td>
                        <td><span class="badge {{ $pmt->statusBadgeClass() }}">{{ strtoupper($pmt->status) }}</span></td>
                        <td class="text-end">
                            @if($pmt->status === 'pending')
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="{{ route('cashier.payments.verify', $pmt) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                            <i class="bi bi-check-lg me-1"></i>{{ $pmt->payment_method === 'cash' ? 'Confirm Cash Received & Issue OR' : 'Verify GCash & Issue OR' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('cashier.payments.reject', $pmt) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="cashier_remarks" value="{{ $pmt->payment_method === 'cash' ? 'Walk-in cash slip cancelled / not paid at counter.' : 'Invalid GCash Reference Number.' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('student.receipt', $pmt) }}" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-printer me-1"></i>Official Receipt
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No GCash transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
