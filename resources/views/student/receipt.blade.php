@extends('layouts.app')

@section('title', 'Official Receipt ' . $payment->receipt_number . ' | ESCR')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
            <a href="{{ auth()->user()->isStudent() ? route('student.billing') : route('cashier.receipts') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-printer-fill me-1"></i>Print / Save Official Receipt as PDF
            </button>
        </div>

        <div class="card p-3 p-md-5 bg-white shadow-sm border-2">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border-bottom pb-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/escr-logo.png') }}" alt="ESCR Official Seal" style="width: 60px; height: 60px; object-fit: contain;">
                    <div>
                        <h4 class="fw-extrabold mb-0 text-uppercase">EAST SYSTEMS COLLEGES OF RIZAL</h4>
                        <div class="small text-muted">Morong, Rizal &bull; Office of the School Cashier</div>
                        <div class="small text-muted">{{ $settings['academic_year'] ?? 'SY 2026-2027' }} &bull; {{ $settings['semester'] ?? '1st Semester' }}</div>
                    </div>
                </div>
                <div class="text-sm-end">
                    <span class="badge bg-success fs-6 px-3 py-1 mb-1">OFFICIAL RECEIPT</span>
                    <div class="fw-bold fs-5 text-danger">{{ $payment->receipt_number }}</div>
                    <div class="small text-muted">Date: {{ ($payment->verified_at ?? $payment->created_at)->format('F d, Y h:i A') }}</div>
                </div>
            </div>

            <div class="row g-3 mb-4 small">
                <div class="col-sm-6">
                    <div class="text-muted">Received From (Student):</div>
                    <div class="fw-bold fs-6">{{ $payment->user->name }}</div>
                    <div>Student No.: <strong>{{ $payment->user->student_number ?? 'Applicant' }}</strong></div>
                    <div>Course: {{ $payment->user->enrollment?->course ?? 'N/A' }}</div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="text-muted">Payment Channel:</div>
                    <div class="fw-bold {{ $payment->payment_method === 'cash' ? 'text-success' : 'text-primary' }}">
                        {{ $payment->methodLabel() }}
                    </div>
                    <div>Reference No.: <code>{{ $payment->gcash_reference_number }}</code></div>
                    <div>Payer: {{ $payment->gcash_sender_name }} ({{ $payment->gcash_sender_number }})</div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Invoice Ref</th>
                            <th>Particulars / Fee Description</th>
                            <th class="text-end">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-nowrap">{{ $payment->invoice?->invoice_number }}</td>
                            <td>
                                <div class="fw-bold">{{ $payment->invoice?->title }}</div>
                                <div class="small text-muted">{{ $payment->invoice?->description }}</div>
                            </td>
                            <td class="text-end fw-bold fs-5 text-nowrap">₱{{ number_format((float)$payment->amount, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="2" class="text-end">TOTAL AMOUNT RECEIVED (PHP):</th>
                            <th class="text-end fs-5 text-nowrap">₱{{ number_format((float)$payment->amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row align-items-end small mt-4 pt-2">
                <div class="col-sm-7">
                    <div class="text-muted">Remarks: {{ $payment->cashier_remarks }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                        System-generated Official Receipt verified via ESCR Enrollment &amp; Payment System.
                    </div>
                </div>
                <div class="col-sm-5 text-center">
                    <div class="fw-bold border-bottom pb-1">{{ $payment->verifier?->name ?? 'Automated GCash Gateway / School Cashier' }}</div>
                    <div class="text-muted small">Authorized Cashier / Verifier</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
