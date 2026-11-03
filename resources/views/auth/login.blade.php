@extends('layouts.app')

@section('title', 'Sign In | ESCR Online Enrollment & Payment System')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center g-4">
        <div class="col-lg-5">
            <div class="card p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/escr-logo.png') }}" alt="ESCR Official Seal" class="mb-2" style="width: 72px; height: 72px; object-fit: contain; filter: drop-shadow(0 3px 6px rgba(36, 7, 11, 0.16));">
                    <h4 class="fw-bold mb-1">ESCR Portal Sign In</h4>
                    <p class="text-muted small mb-0">Sign in to access Student, Registrar, Cashier, or Admin services</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', 'student@escr.edu.ph') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-medium small mb-0">Password</label>
                            <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none">
                                Forgot Password?
                            </a>
                        </div>
                        <input type="password" name="password" class="form-control" value="password123" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Keep me signed in</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-4 small">
                    New applicant? <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Create Student Account &amp; Enroll</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-4 shadow-sm bg-white h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-lightning-charge-fill text-warning fs-4"></i>
                    <h5 class="fw-bold mb-0">1-Click Demo Role Access (Password: <code>password123</code>)</h5>
                </div>
                <p class="text-muted small mb-3">
                    Click any persona below to immediately test the complete workflows from our system flowchart:
                </p>

                <div class="row g-2">
                    <div class="col-12">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="student@escr.edu.ph">
                            <button type="submit" class="btn btn-outline-primary w-100 text-start p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">1. Juan Dela Cruz (Enrolled Student &mdash; Happy Path &amp; Tela Pickup)</div>
                                    <div class="small text-muted">student@escr.edu.ph &bull; Cleared for Prelim Exam &bull; Tela Ready for Pickup</div>
                                </div>
                                <span class="badge bg-primary">Login &rarr;</span>
                            </button>
                        </form>
                    </div>

                    <div class="col-12">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="promissory@escr.edu.ph">
                            <button type="submit" class="btn btn-outline-danger w-100 text-start p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">2. Angela Mendoza (Promissory Note &mdash; Tuition Moved to Finals Demo)</div>
                                    <div class="small text-muted">promissory@escr.edu.ph &bull; Prelim Note Approved &rarr; Balance Moved to Finals (₱10,000) &amp; Cleared for Exam</div>
                                </div>
                                <span class="badge bg-danger">Login &rarr;</span>
                            </button>
                        </form>
                    </div>

                    <div class="col-12">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="applicant@escr.edu.ph">
                            <button type="submit" class="btn btn-outline-secondary w-100 text-start p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">3. Mark Villanueva (New Applicant &mdash; Pending Document Verification)</div>
                                    <div class="small text-muted">applicant@escr.edu.ph &bull; Uploaded PSA, Form 138, Good Moral</div>
                                </div>
                                <span class="badge bg-secondary">Login &rarr;</span>
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="registrar@escr.edu.ph">
                            <button type="submit" class="btn btn-primary w-100 py-3 text-center">
                                <i class="bi bi-folder-check d-block fs-4 mb-1"></i>
                                <span class="fw-bold small d-block">Registrar Portal</span>
                                <span class="small opacity-75" style="font-size:0.7rem;">Approve Docs &amp; Notes</span>
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="cashier@escr.edu.ph">
                            <button type="submit" class="btn btn-success w-100 py-3 text-center">
                                <i class="bi bi-cash-coin d-block fs-4 mb-1"></i>
                                <span class="fw-bold small d-block">Cashier Portal</span>
                                <span class="small opacity-75" style="font-size:0.7rem;">GCash &amp; Tela Pickup</span>
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <form action="{{ route('login.demo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="demo_email" value="admin@escr.edu.ph">
                            <button type="submit" class="btn btn-dark w-100 py-3 text-center">
                                <i class="bi bi-bar-chart-fill d-block fs-4 mb-1"></i>
                                <span class="fw-bold small d-block">Super Admin</span>
                                <span class="small opacity-75" style="font-size:0.7rem;">Analytics &amp; Audit</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
