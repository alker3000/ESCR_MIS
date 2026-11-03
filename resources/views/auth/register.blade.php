@extends('layouts.app')

@section('title', 'Student Registration | ESCR Online Portal')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card p-4 p-md-5 shadow-sm">
                <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i>Create Student Applicant Account</h4>
                <p class="text-muted small mb-4">Register your student portal credentials to begin your online enrollment for SY 2026–2027.</p>

                <form action="{{ route('register.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Maria Clara Reyes" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Email Address (for Automated Notifications &amp; Tela Pickup Alerts)</label>
                        <input type="email" name="email" class="form-control" placeholder="student@email.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Mobile / GCash Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="0917XXXXXXX" value="{{ old('contact_number') }}" required>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label fw-medium small">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium small">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        Create Account &amp; Proceed to Enrollment Form &rarr;
                    </button>
                </form>

                <div class="text-center mt-3 small">
                    Already registered? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Sign In here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
