@extends('layouts.app')

@section('title', 'Uniform Fabric (Tela) Fulfillment Workbench | ESCR Cashier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>Uniform Fabric (<em>Tela</em>) &amp; Patch Fulfillment Workbench</h4>
        <p class="text-muted small mb-0">
            Prepare paid uniform fabric orders and click <strong>"Mark Ready for Pickup"</strong> to automatically notify the student via Email &amp; Portal.
        </p>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('cashier.tela', ['status' => 'all']) }}" class="btn {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All Orders</a>
        <a href="{{ route('cashier.tela', ['status' => 'paid_processing']) }}" class="btn {{ $statusFilter === 'paid_processing' ? 'btn-primary' : 'btn-outline-primary' }}">Paid (To Prepare)</a>
        <a href="{{ route('cashier.tela', ['status' => 'ready_for_pickup']) }}" class="btn {{ $statusFilter === 'ready_for_pickup' ? 'btn-primary' : 'btn-outline-primary' }}">Ready for Pickup</a>
        <a href="{{ route('cashier.tela', ['status' => 'claimed']) }}" class="btn {{ $statusFilter === 'claimed' ? 'btn-primary' : 'btn-outline-primary' }}">Claimed</a>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Student Details</th>
                    <th>Blouse / Polo</th>
                    <th>Skirt</th>
                    <th>Logo Patch</th>
                    <th>Total Paid</th>
                    <th>Status</th>
                    <th class="text-end">Fulfillment Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $ord)
                    <tr>
                        <td class="fw-bold">
                            <code>{{ $ord->order_number }}</code>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $ord->created_at->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $ord->user->name }}</div>
                            <div class="text-muted">{{ $ord->user->email }} &bull; {{ $ord->user->contact_number }}</div>
                            @if($ord->student_notes)
                                <div class="text-primary small">Note: "{{ $ord->student_notes }}"</div>
                            @endif
                        </td>
                        <td>
                            @if((float)$ord->blouse_polo_yards > 0)
                                <span class="fw-bold">{{ $ord->blouse_polo_yards }} yd</span>
                                <span class="text-muted">(@ ₱{{ number_format((float)$ord->blouse_polo_unit_price, 0) }})</span>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td>
                            @if((float)$ord->skirt_yards > 0)
                                <span class="fw-bold">{{ $ord->skirt_yards }} yd</span>
                                <span class="text-muted">(@ ₱{{ number_format((float)$ord->skirt_unit_price, 0) }})</span>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td>
                            @if((int)$ord->logo_patch_qty > 0)
                                <span class="fw-bold">{{ $ord->logo_patch_qty }} pc</span>
                                <span class="text-muted">(@ ₱{{ number_format((float)$ord->logo_patch_unit_price, 0) }})</span>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td class="fw-bold">₱{{ number_format((float)$ord->total_amount, 2) }}</td>
                        <td>
                            <span class="badge {{ $ord->statusBadgeClass() }}">{{ $ord->statusLabel() }}</span>
                            @if($ord->ready_at)
                                <div class="text-muted mt-1" style="font-size:0.72rem;">Ready: {{ $ord->ready_at->format('M d, h:i A') }}</div>
                            @endif
                            @if($ord->claimed_at)
                                <div class="text-success fw-semibold" style="font-size:0.72rem;">Claimed: {{ $ord->claimed_at->format('M d, h:i A') }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($ord->status === 'paid_processing')
                                <form action="{{ route('cashier.tela.ready', $ord) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="cashier_remarks" value="Your uniform tela and patches are packed and ready for pickup at Cashier Window 2.">
                                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                                        <i class="bi bi-envelope-check-fill me-1"></i>Click "Ready for Pickup" (Notify Student)
                                    </button>
                                </form>
                            @elseif($ord->status === 'ready_for_pickup')
                                <form action="{{ route('cashier.tela.claimed', $ord) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                        <i class="bi bi-bag-check-fill me-1"></i>Mark Order Claimed
                                    </button>
                                </form>
                            @elseif($ord->status === 'claimed')
                                <span class="badge bg-light text-success border"><i class="bi bi-check2-all me-1"></i>Completed</span>
                            @else
                                <span class="text-muted small">Awaiting GCash Payment</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No Uniform Tela orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="mt-3 pt-2 border-top">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
