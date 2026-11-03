@extends('layouts.app')

@section('title', 'Online Enrollment & DMS | ESCR Student Portal')

@section('content')
@php
    $activeYear = $activeYear ?? ($settings['academic_year'] ?? 'SY 2026-2027');
    $activeSemester = $activeSemester ?? ($settings['semester'] ?? '1st Semester');
    $profile = $enrollment ?? ($previousEnrollment ?? null);
    $isContinuingNewTerm = !$enrollment && isset($previousEnrollment) && $previousEnrollment;
    $prelimDateLabel = \Carbon\Carbon::parse($settings['prelim_deadline'] ?? '2027-08-17')->format('M d');
    $midtermDateLabel = \Carbon\Carbon::parse($settings['midterm_deadline'] ?? '2027-09-14')->format('M d');
    $semifinalDateLabel = \Carbon\Carbon::parse($settings['semifinal_deadline'] ?? '2027-10-12')->format('M d');
    $finalsDateLabel = \Carbon\Carbon::parse($settings['finals_deadline'] ?? '2027-11-09')->format('M d');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-person-fill text-primary me-2"></i>Online Enrollment &amp; Document Management (DMS)
        </h4>
        <p class="text-muted small mb-0">
            Active Term: <span class="badge bg-primary">{{ $activeSemester }} &bull; {{ $activeYear }}</span> &mdash; Submit your registration details and required credentials for Registrar verification.
        </p>
    </div>
    @if($enrollment)
        <div class="text-md-end">
            <span class="badge {{ $enrollment->statusBadgeClass() }} fs-6 px-3 py-2">{{ $enrollment->statusLabel() }}</span>
            <div class="small text-muted mt-1">Ref: <code>{{ $enrollment->application_reference }}</code> ({{ $enrollment->semester }})</div>
        </div>
    @elseif($isContinuingNewTerm)
        <div class="text-md-end">
            <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-arrow-repeat me-1"></i>Ready for {{ $activeSemester }} Re-Enrollment</span>
            <div class="small text-muted mt-1">Previous: <code>{{ $previousEnrollment->application_reference }}</code> ({{ $previousEnrollment->semester }})</div>
        </div>
    @endif
</div>

@if($isContinuingNewTerm)
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <h6 class="fw-bold mb-1"><i class="bi bi-stars me-2"></i>Continuing Student Re-Enrollment: {{ $activeSemester }} ({{ $activeYear }})</h6>
        <p class="small mb-0">
            Welcome back! Your personal profile and <strong>{{ $previousEnrollment->documents->count() }} verified DMS document(s)</strong> from <strong>{{ $previousEnrollment->semester }} ({{ $previousEnrollment->academic_year }})</strong> have been loaded below and will be automatically carried over when you submit your <strong>{{ $activeSemester }}</strong> enrollment application.
        </p>
    </div>
@endif

@if($enrollment && $enrollment->status === 'returned')
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-2"></i>Application Returned by Registrar for Update</h6>
        <p class="small mb-0"><strong>Feedback:</strong> {{ $enrollment->registrar_remarks }}</p>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Enrollment Application Form -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">1. Student Academic &amp; Personal Information</h5>
                <span class="badge bg-light text-primary border">{{ $activeSemester }} &bull; {{ $activeYear }}</span>
            </div>
            <form action="{{ route('student.enrollment.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Academic Program / Course</label>
                        <select name="course" class="form-select" required {{ $enrollment?->status === 'enrolled' ? 'disabled' : '' }}>
                            <option value="" disabled {{ !old('course', $profile?->course) ? 'selected' : '' }}>-- Select Academic Program --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c }}" {{ old('course', $profile?->course) === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                        @if($enrollment?->status === 'enrolled')
                            <input type="hidden" name="course" value="{{ $enrollment->course }}">
                        @endif
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Year Level</label>
                        <select name="year_level" class="form-select" required>
                            <option value="" disabled {{ !old('year_level', $profile?->year_level) ? 'selected' : '' }}>-- Select Year --</option>
                            @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year'] as $yl)
                                <option value="{{ $yl }}" {{ old('year_level', $profile?->year_level) === $yl ? 'selected' : '' }}>{{ $yl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Student Type</label>
                        @php
                            $defaultStudentType = $isContinuingNewTerm ? 'Continuing / Old' : $enrollment?->student_type;
                        @endphp
                        <select name="student_type" class="form-select" required>
                            <option value="" disabled {{ !old('student_type', $defaultStudentType) ? 'selected' : '' }}>-- Select Type --</option>
                            @foreach(['New / Freshman', 'Continuing / Old', 'Transferee'] as $st)
                                <option value="{{ $st }}" {{ old('student_type', $defaultStudentType) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $profile?->first_name ?? explode(' ', $student->name)[0]) }}" placeholder="Enter First Name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $profile?->middle_name) }}" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $profile?->last_name ?? (explode(' ', $student->name, 2)[1] ?? '')) }}" placeholder="Enter Last Name" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="" disabled {{ !old('gender', $profile?->gender) ? 'selected' : '' }}>-- Select Gender --</option>
                            <option value="Male" {{ old('gender', $profile?->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $profile?->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Date of Birth</label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Contact Number (GCash)</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $profile?->contact_number ?? $student->contact_number) }}" placeholder="e.g. 0917XXXXXXX" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Complete Home Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $profile?->address) }}" placeholder="House No., Street, Barangay, Municipality, Province" required>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Parent / Guardian Full Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $profile?->guardian_name) }}" placeholder="Required for Promissory Note Conforme" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Relationship</label>
                        <input type="text" name="guardian_relationship" class="form-control" value="{{ old('guardian_relationship', $profile?->guardian_relationship) }}" placeholder="e.g. Mother, Father, Guardian" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Guardian Contact No.</label>
                        <input type="text" name="guardian_contact" class="form-control" value="{{ old('guardian_contact', $profile?->guardian_contact) }}" placeholder="e.g. 0917XXXXXXX" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Previous School Attended</label>
                        <input type="text" name="previous_school" class="form-control" value="{{ old('previous_school', $profile?->previous_school) }}" placeholder="Name of Senior High School / Previous College">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">School Year Graduated</label>
                        <input type="text" name="previous_school_year" class="form-control" value="{{ old('previous_school_year', $profile?->previous_school_year) }}" placeholder="e.g. 2025-2026">
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                        <label class="form-label fw-bold">2. Select Tuition Payment Scheme ({{ $activeSemester }} &bull; {{ $activeYear }})</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card p-3 cursor-pointer h-100 border-primary">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_scheme" value="installment" {{ old('payment_scheme', $profile?->payment_scheme ?? 'installment') === 'installment' ? 'checked' : '' }}>
                                        <span class="form-check-label fw-bold">Installment Plan (Recommended)</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        ₱{{ number_format((float)($settings['downpayment_amount'] ?? 5000), 2) }} Downpayment upon enrollment + 4 installments (Before Prelim {{ $prelimDateLabel }}, Midterm {{ $midtermDateLabel }}, Semi-Finals {{ $semifinalDateLabel }}, Finals {{ $finalsDateLabel }}).
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card p-3 cursor-pointer h-100">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_scheme" value="full" {{ old('payment_scheme', $profile?->payment_scheme) === 'full' ? 'checked' : '' }}>
                                        <span class="form-check-label fw-bold">Full Semester Payment</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        One-time full payment of ₱{{ number_format((float)($settings['base_tuition_fee'] ?? 25000), 2) }} upon enrollment approval.
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    @if(!$enrollment)
                        <div class="col-12">
                            <hr class="my-2">
                            <label class="form-label fw-bold">
                                3. {{ $isContinuingNewTerm ? 'Optional: Upload Updated Documents (Existing DMS Docs Auto-Carried Over)' : 'Upload Initial Enrollment Documents (DMS)' }}
                            </label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">PSA Birth Certificate (PDF/JPG/PNG)</label>
                                    <input type="file" name="psa_birth_certificate" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Form 138 / Report Card</label>
                                    <input type="file" name="form_138_report_card" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Certificate of Good Moral Character</label>
                                    <input type="file" name="good_moral_certificate" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">2x2 ID Picture</label>
                                    <input type="file" name="id_picture" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 w-100 w-sm-auto" {{ $enrollment?->status === 'enrolled' ? 'disabled' : '' }}>
                            <i class="bi bi-send-check-fill me-1"></i>
                            @if($enrollment)
                                Update {{ $activeSemester }} Application Details
                            @elseif($isContinuingNewTerm)
                                Submit {{ $activeSemester }} ({{ $activeYear }}) Re-Enrollment
                            @else
                                Submit Enrollment Application
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Document Management System (DMS) Vault & Term History -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 mb-4">
            <h5 class="fw-bold mb-2"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Document Management System (DMS)</h5>
            <p class="text-muted small mb-3">
                Upload or replace required credentials. All files are stored in ESCR's private DMS storage and inspected by the Office of the Registrar.
            </p>

            @php
                $displayDocsEnrollment = $enrollment ?? ($previousEnrollment ?? null);
            @endphp

            @if($enrollment)
                <form action="{{ route('student.enrollment.document') }}" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded border mb-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Document Type</label>
                        <select name="document_type" class="form-select form-select-sm" required>
                            <option value="psa_birth_certificate">PSA Birth Certificate</option>
                            <option value="form_138_report_card">Form 138 / Report Card</option>
                            <option value="good_moral_certificate">Certificate of Good Moral Character</option>
                            <option value="id_picture">2x2 ID Picture</option>
                            <option value="transfer_credentials">Honorable Dismissal / Transfer Credentials</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Choose File (PDF, JPG, PNG)</label>
                        <input type="file" name="document_file" class="form-control form-control-sm" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i>Upload / Replace Document
                    </button>
                </form>
            @elseif($isContinuingNewTerm)
                <div class="alert alert-success small py-2 mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <strong>Verified Credentials on File:</strong> Your documents below from <strong>{{ $previousEnrollment->semester }}</strong> will be automatically linked to your <strong>{{ $activeSemester }}</strong> enrollment.
                </div>
            @endif

            @if($displayDocsEnrollment)
                <div class="table-responsive">
                    <table class="table table-sm align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Requirement</th>
                                <th>Status</th>
                                <th class="text-end">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($displayDocsEnrollment->documents as $doc)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $doc->typeLabel() }}</div>
                                        <div class="text-muted" style="font-size:0.72rem;">{{ $doc->original_filename }}</div>
                                        @if($doc->remarks)
                                            <div class="text-muted small">{{ $doc->remarks }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $doc->statusBadgeClass() }}">{{ strtoupper($doc->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('documents.enrollment', $doc) }}" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No documents uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning small mb-0">
                    Submit your enrollment form on the left first to activate your personal DMS document vault.
                </div>
            @endif
        </div>

        @if(isset($enrollments) && $enrollments->count() > 0)
            <div class="card p-3 p-md-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Academic Term Enrollment History</h6>
                <div class="list-group list-group-flush small">
                    @foreach($enrollments as $hist)
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $hist->semester }} &bull; {{ $hist->academic_year }}</div>
                                <div class="text-muted">{{ $hist->course }} ({{ $hist->year_level }}) &bull; <code>{{ $hist->application_reference }}</code></div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $hist->statusBadgeClass() }}">{{ $hist->statusLabel() }}</span>
                                <div class="mt-1">
                                    <a href="{{ route('student.billing', ['enrollment_id' => $hist->id]) }}" class="text-decoration-none small fw-semibold">
                                        View Ledger &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
