@extends('layouts.app')

@section('title', 'Payment Checkout (GCash & Cash) | ESCR Online Payment')

@section('content')
@php
    $merchantName = $settings['gcash_merchant_name'] ?? 'EAST SYSTEMS COLLEGES OF RIZAL';
    $merchantNumber = $settings['gcash_merchant_number'] ?? '0917-888-3727';
    $amountDue = (float) $invoice->balance();
    $autoGcashRef = '9018' . rand(100000000, 999999999);
    $autoCashRef = 'CSH-2026-' . strtoupper(\Illuminate\Support\Str::random(3)) . rand(100, 999);
@endphp
<div class="row justify-content-center py-2 py-md-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow overflow-hidden">
            <!-- Checkout Header -->
            <div id="checkoutHeaderBanner" class="p-3 p-md-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: linear-gradient(90deg, #0057e7 0%, #007dfe 100%); transition: background 0.25s ease;">
                <div class="d-flex align-items-center gap-2">
                    <span id="checkoutHeaderBadge" class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-extrabold fs-4 flex-shrink-0" style="width: 44px; height: 44px;">G</span>
                    <div>
                        <h5 class="fw-bold mb-0" id="checkoutHeaderTitle">ESCR Official Payment Checkout</h5>
                        <small class="opacity-75">Merchant: {{ $merchantName }} &bull; GCash &amp; Cash Accepted</small>
                    </div>
                </div>
                <div class="text-sm-end">
                    <div class="small opacity-75">Amount Due</div>
                    <h4 class="fw-extrabold mb-0">₱{{ number_format($amountDue, 2) }}</h4>
                </div>
            </div>

            <div class="p-3 p-md-4 bg-white">
                <!-- Invoice Particulars Summary -->
                <div class="bg-light rounded p-3 border mb-4">
                    <div class="row small g-2">
                        <div class="col-sm-6">
                            <span class="text-muted">Invoice Number:</span>
                            <strong class="d-block">{{ $invoice->invoice_number }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Student:</span>
                            <strong class="d-block">{{ auth()->user()->name }} ({{ auth()->user()->student_number ?? 'Applicant' }})</strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted">Particulars:</span>
                            <strong class="d-block text-primary">{{ $invoice->title }}</strong>
                            @if($invoice->description)
                                <span class="text-muted" style="font-size: 0.78rem;">{{ $invoice->description }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ route('student.billing.pay.submit', $invoice) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cash_reference_number" value="{{ $autoCashRef }}">

                    <label class="form-label fw-bold small text-uppercase text-muted mb-2">1. Select Payment Method</label>
                    <div class="row g-2 g-md-3 mb-4">
                        <!-- Option 1: Instant GCash Sandbox -->
                        <div class="col-md-4">
                            <label class="card p-3 h-100 cursor-pointer payment-option-card border-primary bg-primary-subtle bg-opacity-10" id="card_sandbox_instant">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" value="sandbox_instant" checked onchange="togglePaymentMode(this.value)">
                                    <span class="form-check-label fw-bold text-primary d-block">
                                        <i class="bi bi-lightning-charge-fill me-1"></i>Instant GCash Checkout
                                    </span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.76rem;">
                                    Express online GCash authorization. Immediately verifies payment &amp; issues your Official Receipt.
                                </div>
                            </label>
                        </div>

                        <!-- Option 2: Manual GCash QR Code & Number -->
                        <div class="col-md-4">
                            <label class="card p-3 h-100 cursor-pointer payment-option-card" id="card_manual_reference">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" value="manual_reference" onchange="togglePaymentMode(this.value)">
                                    <span class="form-check-label fw-bold text-dark d-block">
                                        <i class="bi bi-qr-code-scan text-primary me-1"></i>Manual GCash QR / Number
                                    </span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.76rem;">
                                    Scan ESCR's GCash QR code or send to <strong>{{ $merchantNumber }}</strong> and upload your 13-digit Reference No.
                                </div>
                            </label>
                        </div>

                        <!-- Option 3: Walk-In Cash Payment at School Cashier -->
                        <div class="col-md-4">
                            <label class="card p-3 h-100 cursor-pointer payment-option-card" id="card_walk_in_cash">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" value="walk_in_cash" onchange="togglePaymentMode(this.value)">
                                    <span class="form-check-label fw-bold text-success d-block">
                                        <i class="bi bi-cash-coin me-1"></i>Cash (School Cashier)
                                    </span>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.76rem;">
                                    Generate a Walk-In Cash Payment Slip and pay in cash over-the-counter at the ESCR Cashier's Office.
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- DYNAMIC PANEL A: MANUAL GCASH QR CODE & MERCHANT NUMBER BOX -->
                    <div id="manualQrSection" class="mb-4 d-none">
                        <div class="card border-primary border-2 overflow-hidden shadow-sm">
                            <div class="bg-primary text-white px-3 py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small"><i class="bi bi-qr-code me-1"></i>SCAN TO PAY VIA GCASH APP (INSTAPAY / GCASH EXPRESS)</span>
                                <span class="badge bg-white text-primary fw-bold">Verified School Merchant</span>
                            </div>
                            <div class="p-3 p-md-4 bg-primary-subtle bg-opacity-10">
                                <div class="row align-items-center g-3">
                                    <!-- Scannable Sample QR Code Card -->
                                    <div class="col-sm-5 text-center">
                                        <div class="bg-white p-3 rounded-3 border shadow-sm d-inline-block">
                                            <div class="small fw-bold text-primary mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">GCASH QR PH</div>
                                            <!-- Crisp Offline-Ready SVG QR Code -->
                                            <svg width="168" height="168" viewBox="0 0 29 29" xmlns="http://www.w3.org/2000/svg" class="d-block mx-auto" style="shape-rendering: crispEdges;">
                                                <rect width="29" height="29" fill="#ffffff"/>
                                                <!-- Top-Left Finder Pattern -->
                                                <path d="M1,1 h7 v7 h-7 z M2,2 v5 h5 v-5 z M3,3 h3 v3 h-3 z" fill="#0057e7"/>
                                                <!-- Top-Right Finder Pattern -->
                                                <path d="M21,1 h7 v7 h-7 z M22,2 v5 h5 v-5 z M23,3 h3 v3 h-3 z" fill="#0057e7"/>
                                                <!-- Bottom-Left Finder Pattern -->
                                                <path d="M1,21 h7 v7 h-7 z M2,22 v5 h5 v-5 z M3,23 h3 v3 h-3 z" fill="#0057e7"/>
                                                <!-- Alignment Pattern -->
                                                <path d="M20,20 h5 v5 h-5 z M21,21 v3 h3 v-3 z M22,22 h1 v1 h-1 z" fill="#0f172a"/>
                                                <!-- Timing & Data Modules -->
                                                <path d="M9,1 h2 v1 h-2 z M12,1 h1 v2 h-1 z M15,1 h2 v1 h-2 z M18,1 h2 v2 h-2 z
                                                         M9,3 h1 v2 h-1 z M11,4 h2 v1 h-2 z M14,3 h3 v1 h-3 z M19,4 h1 v2 h-1 z
                                                         M6,9 h1 v1 h-1 z M8,9 h3 v1 h-3 z M12,9 h2 v2 h-2 z M16,9 h1 v2 h-1 z M19,9 h2 v1 h-2 z
                                                         M1,9 h2 v2 h-2 z M4,10 h2 v1 h-2 z M1,12 h1 v2 h-1 z M3,13 h3 v1 h-3 z M7,11 h2 v2 h-2 z
                                                         M1,15 h3 v1 h-3 z M5,15 h2 v2 h-2 z M8,14 h1 v3 h-1 z M1,18 h2 v2 h-2 z M4,19 h3 v1 h-3 z
                                                         M9,12 h1 v2 h-1 z M9,16 h2 v1 h-2 z M9,19 h1 v2 h-1 z M11,18 h2 v1 h-2 z
                                                         M12,6 h1 v2 h-1 z M15,6 h2 v1 h-2 z M18,7 h1 v1 h-1 z
                                                         M21,9 h2 v1 h-2 z M24,9 h3 v2 h-3 z M22,11 h2 v2 h-2 z M26,12 h2 v1 h-2 z
                                                         M19,12 h2 v2 h-2 z M23,14 h2 v1 h-2 z M26,15 h2 v2 h-2 z M20,16 h3 v1 h-3 z M24,17 h2 v2 h-2 z
                                                         M9,22 h2 v2 h-2 z M12,21 h2 v1 h-2 z M15,21 h1 v3 h-1 z M17,22 h2 v1 h-2 z
                                                         M9,25 h1 v3 h-1 z M11,26 h3 v1 h-3 z M15,25 h2 v2 h-2 z M18,24 h1 v3 h-1 z
                                                         M26,21 h2 v2 h-2 z M25,24 h3 v1 h-3 z M21,26 h3 v2 h-3 z M26,26 h2 v2 h-2 z" fill="#0f172a"/>
                                                <!-- Center GCash Emblem -->
                                                <rect x="11" y="11" width="7" height="7" rx="1.5" fill="#007dfe"/>
                                                <text x="14.5" y="16.3" font-family="Arial, sans-serif" font-size="5.2" font-weight="bold" fill="#ffffff" text-anchor="middle">G</text>
                                            </svg>
                                            <div class="text-muted mt-1" style="font-size: 0.68rem;">ESCR Official Tuition QR</div>
                                        </div>
                                    </div>

                                    <!-- Merchant Details & Copyable Number -->
                                    <div class="col-sm-7">
                                        <div class="mb-2">
                                            <div class="text-muted small">GCash Account Name:</div>
                                            <div class="fw-bold text-dark fs-6">{{ $merchantName }}</div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="text-muted small">Official School GCash Number:</div>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <span class="fs-5 fw-extrabold text-primary font-monospace bg-white px-3 py-1 rounded border" id="escrGcashNum">{{ $merchantNumber }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="copyGcashNumber()" id="copyNumBtn">
                                                    <i class="bi bi-clipboard me-1"></i>Copy Number
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="text-muted small">Exact Amount to Send:</div>
                                            <div class="fw-extrabold text-danger fs-5">₱{{ number_format($amountDue, 2) }}</div>
                                        </div>

                                        <div class="alert alert-info py-2 px-3 small mb-0">
                                            <i class="bi bi-info-circle-fill me-1"></i>
                                            <strong>How to Pay Manually:</strong> Scan the QR code above or send <strong>₱{{ number_format($amountDue, 2) }}</strong> to <strong>{{ $merchantNumber }}</strong> in your GCash app, then enter your <strong>13-digit Reference No.</strong> and upload your screenshot below.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DYNAMIC PANEL B: WALK-IN CASH PAYMENT INSTRUCTIONS BOX -->
                    <div id="walkInCashSection" class="mb-4 d-none">
                        <div class="card border-success border-2 overflow-hidden shadow-sm">
                            <div class="bg-success text-white px-3 py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small"><i class="bi bi-cash-stack me-1"></i>OVER-THE-COUNTER CASH PAYMENT (SCHOOL CASHIER WINDOW)</span>
                                <span class="badge bg-white text-success fw-bold">Walk-In Payment Slip</span>
                            </div>
                            <div class="p-3 p-md-4 bg-success-subtle bg-opacity-10">
                                <div class="row align-items-center g-3">
                                    <div class="col-sm-5 text-center">
                                        <div class="bg-white p-3 rounded-3 border border-success-subtle shadow-sm">
                                            <div class="text-muted small">Walk-In Cash Reference No.</div>
                                            <div class="fs-5 fw-extrabold text-success font-monospace my-1">{{ $autoCashRef }}</div>
                                            <span class="badge bg-warning text-dark">Present at Cashier Window</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-7 small">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-success me-1"></i>ESCR Office of the School Cashier (Morong, Rizal Campus)</div>
                                        <ul class="mb-0 ps-3 text-muted">
                                            <li>Click <strong>Confirm &amp; Generate Cash Payment Slip</strong> below to queue your invoice in the Cashier's system.</li>
                                            <li>Proceed to the <strong>School Cashier's Window</strong> (Mon–Sat, 8:00 AM – 5:00 PM) and present Reference <strong>{{ $autoCashRef }}</strong> with <strong>₱{{ number_format($amountDue, 2) }}</strong> cash.</li>
                                            <li>Once the Cashier receives your cash payment, your Official Receipt is immediately issued to your portal and email.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="form-label fw-bold small text-uppercase text-muted mb-2" id="payerDetailsHeading">2. Payer &amp; Transaction Details</label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" id="senderNameLabel">GCash Account Sender Name</label>
                            <input type="text" name="gcash_sender_name" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold" id="senderNumberLabel">GCash Mobile Number</label>
                            <input type="text" name="gcash_sender_number" class="form-control" value="{{ auth()->user()->contact_number ?: '09175550101' }}" required>
                        </div>

                        <div class="col-md-6" id="refInputGroup">
                            <label class="form-label small fw-semibold" id="refNumberLabel">GCash 13-Digit Reference Number</label>
                            <input type="text" name="gcash_reference_number" id="gcashRefInput" class="form-control" value="{{ $autoGcashRef }}">
                            <div class="form-text small" id="refHelpText">Auto-generated in Instant Sandbox mode, or enter your 13-digit receipt reference.</div>
                        </div>

                        <div class="col-md-6" id="proofUploadGroup">
                            <label class="form-label small fw-semibold" id="proofUploadLabel">Upload GCash Receipt Screenshot (Optional)</label>
                            <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text small">Upload a screenshot/photo of your GCash receipt for faster Cashier verification.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 pt-2">
                        <a href="{{ route('student.billing') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" id="submitPaymentBtn" class="btn btn-gcash btn-lg px-4">
                            <i class="bi bi-shield-check me-2"></i><span id="submitBtnText">Confirm &amp; Pay ₱{{ number_format($amountDue, 2) }} via GCash</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePaymentMode(mode) {
    var manualQrSection = document.getElementById('manualQrSection');
    var walkInCashSection = document.getElementById('walkInCashSection');
    var refInputGroup = document.getElementById('refInputGroup');
    var proofUploadGroup = document.getElementById('proofUploadGroup');
    var gcashRefInput = document.getElementById('gcashRefInput');
    var senderNameLabel = document.getElementById('senderNameLabel');
    var senderNumberLabel = document.getElementById('senderNumberLabel');
    var refHelpText = document.getElementById('refHelpText');
    var submitBtn = document.getElementById('submitPaymentBtn');
    var submitBtnText = document.getElementById('submitBtnText');
    var headerBanner = document.getElementById('checkoutHeaderBanner');
    var headerBadge = document.getElementById('checkoutHeaderBadge');

    ['sandbox_instant', 'manual_reference', 'walk_in_cash'].forEach(function(key) {
        var card = document.getElementById('card_' + key);
        if (card) {
            card.classList.remove('border-primary', 'border-success', 'bg-primary-subtle', 'bg-success-subtle', 'bg-opacity-10');
        }
    });

    if (mode === 'manual_reference') {
        manualQrSection.classList.remove('d-none');
        walkInCashSection.classList.add('d-none');
        refInputGroup.classList.remove('d-none');
        proofUploadGroup.classList.remove('d-none');
        gcashRefInput.value = '';
        gcashRefInput.placeholder = 'Enter 13-digit GCash Ref No. (e.g. 9018345678912)';
        gcashRefInput.required = true;
        senderNameLabel.textContent = 'GCash Sender Full Name';
        senderNumberLabel.textContent = 'Sender GCash Mobile Number';
        refHelpText.textContent = 'Enter the 13-digit Reference No. from your GCash payment receipt.';
        submitBtn.className = 'btn btn-gcash btn-lg px-4';
        submitBtnText.textContent = 'Submit GCash Reference (₱{{ number_format($amountDue, 2) }})';
        headerBanner.style.background = 'linear-gradient(90deg, #0057e7 0%, #007dfe 100%)';
        headerBadge.textContent = 'G';
        headerBadge.className = 'bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-extrabold fs-4 flex-shrink-0';
        document.getElementById('card_manual_reference').classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
    } else if (mode === 'walk_in_cash') {
        manualQrSection.classList.add('d-none');
        walkInCashSection.classList.remove('d-none');
        refInputGroup.classList.add('d-none');
        proofUploadGroup.classList.add('d-none');
        gcashRefInput.required = false;
        senderNameLabel.textContent = 'Student / Payer Full Name';
        senderNumberLabel.textContent = 'Student Contact Number';
        submitBtn.className = 'btn btn-success btn-lg px-4 fw-bold';
        submitBtnText.textContent = 'Confirm & Generate Cash Payment Slip (₱{{ number_format($amountDue, 2) }})';
        headerBanner.style.background = 'linear-gradient(90deg, #15803d 0%, #16a34a 100%)';
        headerBadge.textContent = '₱';
        headerBadge.className = 'bg-white text-success rounded-circle d-inline-flex align-items-center justify-content-center fw-extrabold fs-4 flex-shrink-0';
        document.getElementById('card_walk_in_cash').classList.add('border-success', 'bg-success-subtle', 'bg-opacity-10');
    } else {
        manualQrSection.classList.add('d-none');
        walkInCashSection.classList.add('d-none');
        refInputGroup.classList.remove('d-none');
        proofUploadGroup.classList.remove('d-none');
        if (!gcashRefInput.value) {
            gcashRefInput.value = '{{ $autoGcashRef }}';
        }
        gcashRefInput.required = false;
        senderNameLabel.textContent = 'GCash Account Sender Name';
        senderNumberLabel.textContent = 'GCash Mobile Number';
        refHelpText.textContent = 'Auto-generated in Instant Sandbox mode, or enter your 13-digit receipt reference.';
        submitBtn.className = 'btn btn-gcash btn-lg px-4';
        submitBtnText.textContent = 'Confirm & Pay ₱{{ number_format($amountDue, 2) }} via GCash';
        headerBanner.style.background = 'linear-gradient(90deg, #0057e7 0%, #007dfe 100%)';
        headerBadge.textContent = 'G';
        headerBadge.className = 'bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-extrabold fs-4 flex-shrink-0';
        document.getElementById('card_sandbox_instant').classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
    }
}

function copyGcashNumber() {
    var numText = document.getElementById('escrGcashNum').textContent.trim();
    var btn = document.getElementById('copyNumBtn');
    if (navigator.clipboard) {
        navigator.clipboard.writeText(numText);
    }
    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Copied!';
    btn.classList.replace('btn-outline-primary', 'btn-success');
    setTimeout(function() {
        btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy Number';
        btn.classList.replace('btn-success', 'btn-outline-primary');
    }, 2000);
}
</script>
@endpush
@endsection
