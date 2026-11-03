@extends('layouts.app')

@section('title', 'Reset Password | ESCR Online Enrollment & Payment System')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/escr-logo.png') }}" alt="ESCR Official Seal" class="mb-2" style="width: 68px; height: 68px; object-fit: contain;">
                    <h4 class="fw-bold mb-1">Reset Your Password</h4>
                    <p class="text-muted small mb-0">
                        Enter your current password, your new password, and repeat your new password below.
                    </p>
                </div>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Account Email Address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control bg-light @error('email') is-invalid @enderror"
                            value="{{ old('email', $email) }}"
                            required
                            readonly
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Current Password</label>
                        <input
                            type="password"
                            name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            placeholder="Enter your current password"
                            required
                            autofocus
                        >
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">New Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimum 6 characters"
                            minlength="6"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Repeat New Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Re-enter your new password"
                            minlength="6"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-check-circle-fill me-1"></i>Reset Password
                    </button>
                </form>

                <div class="text-center mt-4 small">
                    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
