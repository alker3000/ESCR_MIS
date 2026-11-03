@extends('layouts.app')

@section('title', 'Security Audit Trail | ESCR Super Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check text-danger me-2"></i>Security Audit Trail &amp; Activity Logs</h4>
        <p class="text-muted small mb-0">Immutable record of all enrollment approvals, GCash payment verifications, promissory note decisions, and tela status changes.</p>
    </div>
    <form action="{{ route('admin.audit') }}" method="GET" class="d-flex gap-2">
        <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all">All Event Types</option>
            @foreach($distinctActions as $act)
                <option value="{{ $act }}" {{ $actionFilter === $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Description &amp; Payload Diff</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted">#{{ $log->id }}</td>
                        <td class="text-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="fw-bold">{{ $log->actor_name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ strtoupper($log->actor_role) }}</span></td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->target_type ? "{$log->target_type} #{$log->target_id}" : '—' }}</td>
                        <td>
                            <div>{{ $log->description }}</div>
                            @if($log->old_values || $log->new_values)
                                <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                    @if($log->old_values)
                                        <span class="me-2"><strong>Old:</strong> <code>{{ json_encode($log->old_values) }}</code></span>
                                    @endif
                                    @if($log->new_values)
                                        <span><strong>New:</strong> <code>{{ json_encode($log->new_values) }}</code></span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No audit trail records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
