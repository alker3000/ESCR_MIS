@extends('layouts.app')

@section('title', 'Promissory Notes Verification | ESCR Registrar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i>Promissory Notes Review (Move Term Tuition to Finals)</h4>
        <p class="text-muted small mb-0">
            Approving a student's parent-signed Promissory Note moves their unpaid term balance into their <strong>4th (Finals) Installment</strong> and immediately clears them for the examination.
        </p>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('registrar.promissory', ['status' => 'all']) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('registrar.promissory', ['status' => 'pending_review']) }}" class="btn {{ $statusFilter === 'pending_review' ? 'btn-primary' : 'btn-outline-primary' }}">Pending Review</a>
        <a href="{{ route('registrar.promissory', ['status' => 'approved']) }}" class="btn {{ $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">Approved</a>
        <a href="{{ route('registrar.promissory', ['status' => 'rejected']) }}" class="btn {{ $statusFilter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rejected</a>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Note #</th>
                    <th>Student &amp; Course</th>
                    <th>Term &amp; Balance to Move</th>
                    <th>Parent / Guardian Conforme</th>
                    <th>Moved To (Finals Due)</th>
                    <th>Signed Scan</th>
                    <th>Status &amp; Rollover</th>
                    <th class="text-end">Registrar Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td class="fw-bold"><code>{{ $note->note_number }}</code></td>
                        <td>
                            <div class="fw-bold">{{ $note->user->name }}</div>
                            <div class="text-muted">{{ $note->enrollment?->course }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $note->installment_period }}</div>
                            <div class="text-danger fw-bold">₱{{ number_format((float)$note->outstanding_amount, 2) }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $note->guardian_name }}</div>
                            <div class="text-muted">{{ $note->guardian_contact }}</div>
                        </td>
                        <td class="fw-bold text-primary">
                            <div>Final Term Installment</div>
                            <div class="small text-muted">{{ $note->promised_settlement_date->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('documents.promissory', $note) }}" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-2">
                                <i class="bi bi-file-earmark-medical me-1"></i>Inspect Scan
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $note->statusBadgeClass() }}">{{ $note->statusLabel() }}</span>
                            @if($note->registrar_remarks)
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">{{ $note->registrar_remarks }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($note->status === 'pending_review')
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="{{ route('registrar.promissory.approve', $note) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i>Approve &amp; Move to Finals
                                        </button>
                                    </form>
                                    <form action="{{ route('registrar.promissory.reject', $note) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="registrar_remarks" value="Missing parent/guardian signature on Conforme section.">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted">Reviewed by {{ $note->reviewer?->name ?? 'Registrar' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No Promissory Notes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notes->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $notes->links() }}
        </div>
    @endif
</div>
@endsection
