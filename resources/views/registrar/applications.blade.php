@extends('layouts.app')

@section('title', 'Enrollment Applications Queue | ESCR Registrar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-folder-check text-primary me-2"></i>Enrollment Applications &amp; DMS Verification Queue</h4>
        <p class="text-muted small mb-0">Verify uploaded student credentials to approve enrollment and trigger tuition/installment billing.</p>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('registrar.applications', ['status' => 'all']) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('registrar.applications', ['status' => 'pending']) }}" class="btn {{ $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">Pending Review</a>
        <a href="{{ route('registrar.applications', ['status' => 'approved_awaiting_payment']) }}" class="btn {{ $statusFilter === 'approved_awaiting_payment' ? 'btn-primary' : 'btn-outline-primary' }}">Awaiting Payment</a>
        <a href="{{ route('registrar.applications', ['status' => 'enrolled']) }}" class="btn {{ $statusFilter === 'enrolled' ? 'btn-primary' : 'btn-outline-primary' }}">Enrolled</a>
        <a href="{{ route('registrar.applications', ['status' => 'returned']) }}" class="btn {{ $statusFilter === 'returned' ? 'btn-primary' : 'btn-outline-primary' }}">Returned</a>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference #</th>
                    <th>Student Name</th>
                    <th>Course &amp; Year</th>
                    <th>Payment Plan</th>
                    <th>DMS Documents</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td>
                            <code>{{ $app->application_reference }}</code>
                            <div class="mt-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $app->semester }} &bull; {{ $app->academic_year }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $app->fullName() }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $app->user->email }} &bull; {{ $app->contact_number }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $app->course }}</div>
                            <div class="text-muted">{{ $app->year_level }} ({{ $app->student_type }})</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ strtoupper($app->payment_scheme) }}</span></td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $app->documents->count() }} Uploaded</span>
                        </td>
                        <td>
                            <span class="badge {{ $app->statusBadgeClass() }}">{{ $app->statusLabel() }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('registrar.applications.review', $app) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye-fill me-1"></i>Inspect &amp; Verify
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No enrollment applications match the selected filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
