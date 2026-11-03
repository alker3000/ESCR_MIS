@extends('layouts.app')

@section('title', 'RBAC User Management | ESCR Super Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-person-gear text-primary me-2"></i>Role-Based Access Control (RBAC) &amp; User Accounts</h4>
        <p class="text-muted small mb-0">Manage Student, Registrar, Cashier, and Super Admin accounts and permissions.</p>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('admin.users', ['role' => 'all']) }}" class="btn {{ $roleFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All Roles</a>
        <a href="{{ route('admin.users', ['role' => 'student']) }}" class="btn {{ $roleFilter === 'student' ? 'btn-primary' : 'btn-outline-primary' }}">Students</a>
        <a href="{{ route('admin.users', ['role' => 'registrar']) }}" class="btn {{ $roleFilter === 'registrar' ? 'btn-primary' : 'btn-outline-primary' }}">Registrar</a>
        <a href="{{ route('admin.users', ['role' => 'cashier']) }}" class="btn {{ $roleFilter === 'cashier' ? 'btn-primary' : 'btn-outline-primary' }}">Cashier</a>
        <a href="{{ route('admin.users', ['role' => 'super_admin']) }}" class="btn {{ $roleFilter === 'super_admin' ? 'btn-primary' : 'btn-outline-primary' }}">Super Admin</a>
    </div>
</div>

<div class="row g-4">
    <!-- Create New Staff / User Account -->
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-primary me-2"></i>Create New Staff / User Account</h6>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Ana Gomez (Cashier)" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="staff@escr.edu.ph" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">System Role (RBAC)</label>
                    <select name="role" class="form-select form-select-sm" required>
                        <option value="registrar">Registrar (Enrollment &amp; Promissory Notes)</option>
                        <option value="cashier">Cashier (GCash Payments &amp; Tela Pickup)</option>
                        <option value="super_admin">Super Admin (Full System Control)</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control form-control-sm" placeholder="0917XXXXXXX">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Initial Password</label>
                    <input type="password" name="password" class="form-control form-control-sm" value="password123" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i>Create Account
                </button>
            </form>
        </div>
    </div>

    <!-- User Directory & Role Assignment -->
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name &amp; ID</th>
                            <th>Email &amp; Contact</th>
                            <th>Assigned Role (RBAC)</th>
                            <th>Status</th>
                            <th class="text-end">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $u->name }}</div>
                                    @if($u->student_number)
                                        <div class="text-muted">{{ $u->student_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $u->email }}</div>
                                    <div class="text-muted">{{ $u->contact_number }}</div>
                                </td>
                                <td>
                                    <form action="{{ route('admin.users.role', $u) }}" method="POST" class="d-flex align-items-center gap-1">
                                        @csrf
                                        <select name="role" class="form-select form-select-sm py-0" style="width:140px;" onchange="this.form.submit()">
                                            <option value="student" {{ $u->role === 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="registrar" {{ $u->role === 'registrar' ? 'selected' : '' }}>Registrar</option>
                                            <option value="cashier" {{ $u->role === 'cashier' ? 'selected' : '' }}>Cashier</option>
                                            <option value="super_admin" {{ $u->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $u) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} py-0 px-2">
                                                {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">Current Session</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="mt-3 pt-2 border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
