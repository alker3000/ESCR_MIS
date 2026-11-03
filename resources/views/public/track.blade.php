@extends('layouts.app')

@section('title', 'Track Application | ESCR Online Portal')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 p-md-5">
                <h4 class="fw-bold mb-2"><i class="bi bi-search text-primary me-2"></i>Track Your Enrollment Application</h4>
                <p class="text-muted small mb-4">
                    Enter your Application Reference Number (e.g., <code>APP-2026-JUAN01</code>, <code>APP-2026-ESCR01</code>) or Student Number (<code>ESCR-2026-0001</code>) to check your document verification and enrollment status.
                </p>

                <form action="{{ route('public.track') }}" method="GET" class="row g-2 mb-4">
                    <div class="col-sm-9">
                        <input type="text" name="ref" class="form-control form-control-lg" placeholder="Enter Reference e.g. APP-2026-ESCR01" value="{{ $reference }}" required>
                    </div>
                    <div class="col-sm-3 d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">Track Status</button>
                    </div>
                </form>

                <div class="small text-muted mb-3">
                    Try sample references:
                    <a href="{{ route('public.track', ['ref' => 'APP-2026-ESCR01']) }}" class="badge bg-light text-dark border text-decoration-none ms-1">APP-2026-ESCR01 (Pending Docs)</a>
                    <a href="{{ route('public.track', ['ref' => 'APP-2026-JUAN01']) }}" class="badge bg-light text-dark border text-decoration-none ms-1">APP-2026-JUAN01 (Enrolled)</a>
                    <a href="{{ route('public.track', ['ref' => 'APP-2026-ANG02']) }}" class="badge bg-light text-dark border text-decoration-none ms-1">APP-2026-ANG02 (Promissory Demo)</a>
                </div>

                @if($reference !== '')
                    <hr>
                    @if($enrollment)
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted small">Applicant Name</div>
                                <h5 class="fw-bold mb-0">{{ $enrollment->fullName() }}</h5>
                                <div class="small text-muted">{{ $enrollment->course }} &bull; {{ $enrollment->year_level }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $enrollment->statusBadgeClass() }} fs-6 px-3 py-2">
                                    {{ $enrollment->statusLabel() }}
                                </span>
                                @if($enrollment->user?->student_number)
                                    <div class="small fw-bold text-success mt-1">Student ID: {{ $enrollment->user->student_number }}</div>
                                @endif
                            </div>
                        </div>

                        @if($enrollment->registrar_remarks)
                            <div class="alert alert-info small mb-3">
                                <strong>Registrar Remarks:</strong> {{ $enrollment->registrar_remarks }}
                            </div>
                        @endif

                        <h6 class="fw-bold mt-3 mb-2">Submitted Documents (DMS Checklist)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document</th>
                                        <th>File</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($enrollment->documents as $doc)
                                        <tr>
                                            <td class="fw-medium">{{ $doc->typeLabel() }}</td>
                                            <td class="small text-muted">{{ $doc->original_filename }}</td>
                                            <td><span class="badge {{ $doc->statusBadgeClass() }}">{{ strtoupper($doc->status) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted small">No documents uploaded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            No application found matching reference <strong>{{ $reference }}</strong>. Please check the reference code and try again.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
