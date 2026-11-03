@extends('layouts.app')

@section('title', 'Master Student Directory | ESCR Registrar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Master Student Directory</h4>
        <p class="text-muted small mb-0">View enrolled students, payment schemes, and examination clearance status.</p>
    </div>
    <form action="{{ route('registrar.students') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, ID, course..." value="{{ $search }}">
        <button type="submit" class="btn btn-sm btn-primary">Search</button>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Course &amp; Year</th>
                    <th>Payment Plan</th>
                    <th>Enrollment Status</th>
                    <th>Exam Clearance</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enr)
                    <tr>
                        <td class="fw-bold">
                            {{ $enr->user->student_number ?? $enr->application_reference }}
                            <div class="mt-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-normal">{{ $enr->semester }} &bull; {{ $enr->academic_year }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $enr->fullName() }}</div>
                            <div class="text-muted">{{ $enr->user->email }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $enr->course }}</div>
                            <div class="text-muted">{{ $enr->year_level }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ strtoupper($enr->payment_scheme) }}</span></td>
                        <td><span class="badge {{ $enr->statusBadgeClass() }}">{{ $enr->statusLabel() }}</span></td>
                        <td>
                            @if($enr->status !== 'enrolled')
                                <span class="badge bg-secondary">Not Enrolled</span>
                            @elseif($enr->exam_restricted)
                                <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Exam Restricted</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Cleared for Exam</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('registrar.applications.review', $enr) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                View Record
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No student records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enrollments->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>
@endsection
