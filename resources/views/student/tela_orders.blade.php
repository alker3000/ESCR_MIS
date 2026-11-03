@extends('layouts.app')

@section('title', 'Uniform Fabric (Tela) & Logo Patch Store | ESCR Student Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-scissors text-primary me-2"></i>School Uniform Fabric (<em>Tela</em>) &amp; Logo Patch Ordering</h4>
        <p class="text-muted small mb-0">
            ESCR sells official uniform fabric (<em>tela</em>) and logo patches via GCash. Once the Cashier marks your order <strong>Ready for Pickup</strong>, you will receive an automated email notification to claim your fabric at the school and bring it to your preferred tailor.
        </p>
    </div>
    <span class="badge gcash-badge px-3 py-2 fs-6"><i class="bi bi-phone-fill me-1"></i>GCash Payment</span>
</div>

@php
    $blousePrice = (float) ($settings['tela_blouse_polo_price'] ?? 160);
    $skirtPrice = (float) ($settings['tela_skirt_price'] ?? 100);
    $patchPrice = (float) ($settings['logo_patch_price'] ?? 100);
@endphp

<div class="row g-4">
    <!-- Left Column: Order Builder & Yardage Calculator -->
    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <div class="bg-dark text-white rounded p-3 mb-3 text-center">
                <div class="fw-extrabold fs-6 tracking-wide">UNIFORM TELA PRICING</div>
                <div class="row mt-2 small g-1">
                    <div class="col-4 border-end border-secondary px-1">
                        <div class="text-warning fw-bold" style="font-size: 0.7rem;">BLOUSE / POLO</div>
                        <div class="fw-bold">₱{{ number_format($blousePrice, 0) }}/YD</div>
                    </div>
                    <div class="col-4 border-end border-secondary px-1">
                        <div class="text-warning fw-bold" style="font-size: 0.7rem;">SKIRT</div>
                        <div class="fw-bold">₱{{ number_format($skirtPrice, 0) }}/YD</div>
                    </div>
                    <div class="col-4 px-1">
                        <div class="text-warning fw-bold" style="font-size: 0.7rem;">LOGO PATCH</div>
                        <div class="fw-bold">₱{{ number_format($patchPrice, 0) }}/PC</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('student.tela.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold d-flex justify-content-between">
                        <span>Blouse / Polo Fabric (Yards)</span>
                        <span class="text-primary">₱{{ number_format($blousePrice, 2) }} / yard</span>
                    </label>
                    <input type="number" step="0.25" min="0" max="50" name="blouse_polo_yards" id="blouseYards" class="form-control" value="2.0" oninput="computeTelaTotal()">
                    <div class="form-text small">Recommended: 2 to 2.5 yards per top.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold d-flex justify-content-between">
                        <span>Skirt Fabric (Yards)</span>
                        <span class="text-primary">₱{{ number_format($skirtPrice, 2) }} / yard</span>
                    </label>
                    <input type="number" step="0.25" min="0" max="50" name="skirt_yards" id="skirtYards" class="form-control" value="1.5" oninput="computeTelaTotal()">
                    <div class="form-text small">Set to 0 if not ordering skirt fabric.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold d-flex justify-content-between">
                        <span>Official ESCR Logo Patch (Pieces)</span>
                        <span class="text-primary">₱{{ number_format($patchPrice, 2) }} / pc</span>
                    </label>
                    <input type="number" step="1" min="0" max="50" name="logo_patch_qty" id="patchQty" class="form-control" value="2" oninput="computeTelaTotal()">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Notes for Cashier (Optional)</label>
                    <input type="text" name="student_notes" class="form-control form-control-sm" placeholder="e.g. For 2 sets of BSIT uniform">
                </div>

                <div class="bg-primary-subtle border border-primary-subtle rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Estimated Total (GCash):</span>
                    <span class="fs-4 fw-extrabold text-primary" id="telaTotalDisplay">₱0.00</span>
                </div>

                <button type="submit" class="btn btn-gcash w-100 py-2 fw-bold">
                    <i class="bi bi-cart-check-fill me-1"></i>Place Tela Order &amp; Proceed to GCash Checkout
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: My Tela Orders & Pickup Status Tracker -->
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-box-seam-fill text-primary me-2"></i>My Uniform Tela Orders &amp; Pickup Status</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-mobile-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Items Ordered</th>
                            <th>Total</th>
                            <th>Fulfillment Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $ord)
                            <tr>
                                <td data-label="Order #" class="fw-bold">{{ $ord->order_number }}</td>
                                <td class="mobile-card-lead">
                                    <div class="fw-bold text-dark">{{ $ord->itemsSummary() }}</div>
                                    @if($ord->cashier_remarks)
                                        <div class="text-primary small"><i class="bi bi-info-circle me-1"></i>{{ $ord->cashier_remarks }}</div>
                                    @endif
                                </td>
                                <td data-label="Total" class="fw-bold">₱{{ number_format((float)$ord->total_amount, 2) }}</td>
                                <td data-label="Status">
                                    <div>
                                        <span class="badge {{ $ord->statusBadgeClass() }}">{{ $ord->statusLabel() }}</span>
                                        @if($ord->ready_at && $ord->status === 'ready_for_pickup')
                                            <div class="text-success fw-semibold mt-1" style="font-size:0.72rem;">
                                                <i class="bi bi-envelope-check me-1"></i>Pickup Email Sent {{ $ord->ready_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end mobile-card-actions">
                                    @if($ord->status === 'awaiting_payment' && $ord->invoice)
                                        <a href="{{ route('student.billing.pay', $ord->invoice) }}" class="btn btn-sm btn-gcash">
                                            <i class="bi bi-phone-fill me-1"></i>Pay via GCash
                                        </a>
                                    @elseif($ord->invoice?->latestPayment)
                                        <a href="{{ route('student.receipt', $ord->invoice->latestPayment) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-receipt me-1"></i>View Official Receipt
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    You haven't placed any Uniform Tela or Logo Patch orders yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function computeTelaTotal() {
    var b = parseFloat(document.getElementById('blouseYards').value || 0);
    var s = parseFloat(document.getElementById('skirtYards').value || 0);
    var p = parseInt(document.getElementById('patchQty').value || 0, 10);
    var total = (b * {{ $blousePrice }}) + (s * {{ $skirtPrice }}) + (p * {{ $patchPrice }});
    document.getElementById('telaTotalDisplay').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
document.addEventListener('DOMContentLoaded', computeTelaTotal);
</script>
@endpush
@endsection
