@extends('layouts.app')

@section('title', 'Official Receipts Archive | ESCR Cashier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-success me-2"></i>Official Receipts Archive</h4>
        <p class="text-muted small mb-0">Search and reprint official payment receipts for all verified GCash transactions.</p>
    </div>
    <form action="{{ route('cashier.receipts') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search OR #, GCash Ref, Student..." value="{{ $search }}">
        <button type="submit" class="btn btn-sm btn-primary">Search</button>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Official Receipt #</th>
                    <th>Date Verified</th>
                    <th>Student</th>
                    <th>Particulars</th>
                    <th>GCash Ref #</th>
                    <th>Amount</th>
                    <th class="text-end">Print / View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $rcp)
                    <tr>
                        <td class="fw-bold text-danger">{{ $rcp->receipt_number }}</td>
                        <td>{{ ($rcp->verified_at ?? $rcp->created_at)->format('M d, Y h:i A') }}</td>
                        <td>
                            <div class="fw-bold">{{ $rcp->user->name }}</div>
                            <div class="text-muted">{{ $rcp->user->student_number ?? 'Applicant' }}</div>
                        </td>
                        <td>{{ $rcp->invoice?->title }}</td>
                        <td><code>{{ $rcp->gcash_reference_number }}</code></td>
                        <td class="fw-bold">₱{{ number_format((float)$rcp->amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('student.receipt', $rcp) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-printer-fill me-1"></i>Open Official Receipt
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No official receipts match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receipts->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $receipts->links() }}
        </div>
    @endif
</div>
@endsection
