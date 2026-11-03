@extends('layouts.app')

@section('title', 'East Systems Colleges of Rizal | Online Enrollment & Payment System')

@section('content')
<div class="py-5" style="background: linear-gradient(135deg, #650f0f 0%, #991b1b 55%, #b91c1c 100%); color: #fff; border-bottom: 4px solid #facc15;">
    <div class="container py-3">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge fw-bold px-3 py-2 mb-3 shadow-sm" style="background-color: #facc15; color: #7f1d1d;">
                    <i class="bi bi-stars me-1"></i> {{ $settings['academic_year'] ?? 'SY 2026-2027' }} &bull; {{ $settings['semester'] ?? '1st Semester' }} Enrollment Open
                </span>
                <h1 class="display-5 fw-extrabold mb-3 text-white">East Systems Colleges of Rizal</h1>
                <p class="lead mb-4" style="color: #fef9c3;">
                    Official Online Enrollment, Document Management, GCash &amp; Cash Installment Billing, Digital Promissory Note, and Uniform Fabric (<em>Tela</em>) Ordering Portal for Morong, Rizal.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-warning btn-lg fw-bold px-4">
                            <i class="bi bi-arrow-right-circle-fill me-2"></i>Go to My Portal
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-warning btn-lg fw-bold px-4">
                            <i class="bi bi-person-plus-fill me-2"></i>Enroll Now Online
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg fw-semibold px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Portal Login
                        </a>
                    @endauth
                    <a href="{{ route('public.track') }}" class="btn btn-light btn-lg fw-semibold px-4">
                        <i class="bi bi-search me-2"></i>Track Application
                    </a>
                </div>
            </div>
            <div class="col-lg-5">
                @php
                    $activeSemLabel = $settings['semester'] ?? '1st Semester';
                    $prelimLabel = strtoupper(\Carbon\Carbon::parse($settings['prelim_deadline'] ?? '2027-08-17')->format('M j'));
                    $midtermLabel = strtoupper(\Carbon\Carbon::parse($settings['midterm_deadline'] ?? '2027-09-14')->format('M j'));
                    $semifinalLabel = strtoupper(\Carbon\Carbon::parse($settings['semifinal_deadline'] ?? '2027-10-12')->format('M j'));
                    $finalsLabel = strtoupper(\Carbon\Carbon::parse($settings['finals_deadline'] ?? '2027-11-09')->format('M j'));
                    $prelimPnLabel = strtoupper(\Carbon\Carbon::parse($settings['prelim_pn_deadline'] ?? '2027-08-10')->format('M j'));
                    $midtermPnLabel = strtoupper(\Carbon\Carbon::parse($settings['midterm_pn_deadline'] ?? '2027-09-07')->format('M j'));
                    $semifinalPnLabel = strtoupper(\Carbon\Carbon::parse($settings['semifinal_pn_deadline'] ?? '2027-10-05')->format('M j'));
                @endphp
                <div class="card bg-white text-dark border-0 shadow-lg p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-check-fill text-primary me-2"></i>{{ $activeSemLabel }} Schedule</h5>
                        <span class="badge gcash-badge px-2 py-1">GCash Integrated</span>
                    </div>
                    <div class="small text-uppercase fw-bold text-danger mb-2">Deadline of Payment ({{ $activeSemLabel }}):</div>
                    <ul class="list-group list-group-flush mb-3 small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><strong>Upon Enrollment:</strong> Initial Downpayment</span>
                            <span class="fw-bold text-primary">₱{{ number_format((float)($settings['downpayment_amount'] ?? 5000), 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><strong>Before PRELIM Week:</strong></span>
                            <span class="badge bg-light text-dark border">Before {{ $prelimLabel }} ({{ $settings['prelim_deadline'] ?? '2027-08-17' }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><strong>Before MIDTERM Week:</strong></span>
                            <span class="badge bg-light text-dark border">Before {{ $midtermLabel }} ({{ $settings['midterm_deadline'] ?? '2027-09-14' }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><strong>Before SEMI-FINALS Week:</strong></span>
                            <span class="badge bg-light text-dark border">Before {{ $semifinalLabel }} ({{ $settings['semifinal_deadline'] ?? '2027-10-12' }})</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><strong>Before FINALS Week:</strong></span>
                            <span class="badge bg-light text-dark border">Before {{ $finalsLabel }} ({{ $settings['finals_deadline'] ?? '2027-11-09' }})</span>
                        </li>
                    </ul>
                    <div class="p-3 rounded bg-warning-subtle border border-warning-subtle small">
                        <div class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text-fill text-danger me-1"></i>Promissory Note Submission Deadlines:</div>
                        <div class="d-flex justify-content-between">
                            <span><strong>PRELIM:</strong> {{ $prelimPnLabel }}</span>
                            <span><strong>MIDTERM:</strong> {{ $midtermPnLabel }}</span>
                            <span><strong>SEMI-FINALS:</strong> {{ $semifinalPnLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 p-4">
                <div class="fs-2 text-primary mb-2"><i class="bi bi-folder-check"></i></div>
                <h5 class="fw-bold">1. Online Enrollment &amp; DMS</h5>
                <p class="text-muted small mb-0">
                    Submit your enrollment form and upload your PSA Birth Certificate, Form 138, and Good Moral Certificate. Once verified by the Registrar, your tuition invoice is automatically generated.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4">
                <div class="fs-2 text-danger mb-2"><i class="bi bi-file-earmark-medical"></i></div>
                <h5 class="fw-bold">2. Promissory Note &amp; Special Exam</h5>
                <p class="text-muted small mb-0">
                    Missed an installment deadline? Download a pre-filled ESCR Promissory Note, upload the parent-signed copy, and once approved, pay the Special Examination Fee (₱{{ number_format((float)($settings['special_exam_fee'] ?? 350), 2) }}) via GCash to unlock your exam clearance.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4">
                <div class="fs-2 text-success mb-2"><i class="bi bi-scissors"></i></div>
                <h5 class="fw-bold">3. Uniform Fabric (<em>Tela</em>) &amp; Patch</h5>
                <p class="text-muted small mb-2">
                    Order official school fabric and logo patches online via GCash and receive an automatic email notification when ready for pickup at the Cashier's Office:
                </p>
                <div class="d-flex flex-wrap gap-2 small">
                    <span class="badge bg-light text-dark border">Blouse/Polo: ₱{{ number_format((float)($settings['tela_blouse_polo_price'] ?? 160), 0) }}/yd</span>
                    <span class="badge bg-light text-dark border">Skirt: ₱{{ number_format((float)($settings['tela_skirt_price'] ?? 100), 0) }}/yd</span>
                    <span class="badge bg-light text-dark border">Logo Patch: ₱{{ number_format((float)($settings['logo_patch_price'] ?? 100), 0) }}/pc</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
