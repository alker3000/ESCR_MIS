@extends('layouts.app')

@section('title', 'Forgot Password | ESCR Online Enrollment & Payment System')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
                        <i class="bi bi-envelope-paper-fill fs-4"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Forgot Your Password?</h4>
                    <p class="text-muted small mb-0">
                        Enter your registered ESCR Portal email address and we will send you a password reset link.
                    </p>
                </div>

                @if(session('reset_link_preview'))
                    <div class="alert alert-info border-0 shadow-sm small mb-4">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-envelope-check-fill me-1"></i>
                            {{ session('email_sent_live') ? 'Email Sent via SMTP!' : 'Offline Demo Mode: Reset Link Generated' }}
                        </div>
                        <p class="mb-2">
                            Check your inbox or click the direct reset link below to open the Reset Password page:
                        </p>
                        <a href="{{ session('reset_link_preview') }}" class="btn btn-sm btn-primary fw-semibold w-100">
                            <i class="bi bi-key-fill me-1"></i>Open Password Reset Page Now
                        </a>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Registered Email Address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="e.g. student@escr.edu.ph or your Gmail"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="bi bi-send-fill me-1"></i>Send Password Reset Link
                    </button>
                </form>

                <div class="text-center mt-4 small">
                    Remembered your password?
                    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
