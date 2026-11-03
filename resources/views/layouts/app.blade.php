<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ESCR Online Enrollment & Payment System')</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            overflow-x: hidden;
        }
        .escr-topbar {
            background: linear-gradient(90deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
        }
        .escr-sidebar {
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
        }
        @media (min-width: 768px) {
            .escr-sidebar {
                min-height: calc(100vh - 64px);
            }
        }
        .escr-sidebar .nav-link,
        .escr-offcanvas-nav .nav-link {
            color: #475569;
            font-weight: 500;
            padding: 0.68rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.15s ease;
        }
        .escr-sidebar .nav-link:hover,
        .escr-offcanvas-nav .nav-link:hover {
            background-color: #f1f5f9;
            color: #1e3a8a;
        }
        .escr-sidebar .nav-link.active,
        .escr-offcanvas-nav .nav-link.active {
            background-color: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .card.border-start {
            border-top: 1px solid #e2e8f0 !important;
            border-right: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .kpi-accent-primary {
            border-left: 4px solid #2563eb !important;
        }
        .kpi-accent-success {
            border-left: 4px solid #16a34a !important;
        }
        .kpi-accent-warning {
            border-left: 4px solid #d97706 !important;
        }
        .kpi-accent-danger {
            border-left: 4px solid #dc2626 !important;
        }
        .gcash-badge {
            background-color: #007dfe;
            color: #ffffff;
            font-weight: 700;
        }
        .btn-gcash {
            background-color: #007dfe;
            color: #ffffff;
            font-weight: 600;
            border: none;
        }
        .btn-gcash:hover {
            background-color: #0062cc;
            color: #ffffff;
        }
        .stepper-item {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .stepper-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        /* Mobile Bottom App Bar for Students */
        .escr-mobile-bottom-nav {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(8px);
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.07);
            z-index: 1030;
            padding: 0.25rem 0.2rem calc(0.25rem + env(safe-area-inset-bottom, 0px));
        }
        .escr-mobile-bottom-nav .mobile-tab {
            flex: 1;
            text-align: center;
            padding: 0.3rem 0.1rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.66rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.15;
            transition: all 0.15s ease;
        }
        .escr-mobile-bottom-nav .mobile-tab i {
            font-size: 1.1rem;
            padding: 0.12rem 0.7rem;
            border-radius: 999px;
            margin-bottom: 0.15rem;
            transition: all 0.15s ease;
        }
        .escr-mobile-bottom-nav .mobile-tab.active {
            color: #1d4ed8;
            font-weight: 700;
        }
        .escr-mobile-bottom-nav .mobile-tab.active i {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        @media (max-width: 767.98px) {
            body.has-student-bottom-nav {
                padding-bottom: 76px;
            }
            .escr-brand-title {
                font-size: 0.84rem !important;
            }
            .escr-brand-subtitle {
                font-size: 0.66rem !important;
            }
            /* Transform multi-column tables into sleek Fintech Mobile Cards on < 768px */
            .table-mobile-cards thead {
                display: none;
            }
            .table-mobile-cards,
            .table-mobile-cards tbody {
                display: block;
                width: 100%;
            }
            .table-mobile-cards tbody tr {
                display: block;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 0.65rem;
                padding: 0.75rem 0.85rem;
                margin-bottom: 0.65rem;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            }
            .table-mobile-cards tbody tr:last-child {
                margin-bottom: 0;
            }
            .table-mobile-cards tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.28rem 0;
                border: none !important;
                text-align: right;
                min-width: 0 !important;
            }
            .table-mobile-cards tbody td[data-label]::before {
                content: attr(data-label);
                font-weight: 600;
                color: #64748b;
                font-size: 0.76rem;
                text-align: left;
                margin-right: 0.75rem;
                flex-shrink: 0;
            }
            .table-mobile-cards tbody td.mobile-card-lead {
                display: block;
                text-align: left;
                border-bottom: 1px solid #f1f5f9 !important;
                padding-bottom: 0.45rem;
                margin-bottom: 0.25rem;
            }
            .table-mobile-cards tbody td.mobile-card-actions {
                padding-top: 0.5rem;
                margin-top: 0.25rem;
                border-top: 1px dashed #e2e8f0 !important;
                justify-content: flex-end;
            }
            .table-mobile-cards tbody td.mobile-card-actions .btn {
                flex: 1;
            }
        }
        @media (max-width: 575.98px) {
            .stepper-item {
                flex: 0 0 48%;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 0.65rem;
                padding: 0.6rem 0.35rem;
            }
            .stepper-item.step-completed {
                background: #f0fdf4;
                border-color: #bbf7d0;
            }
            .stepper-item.step-current {
                background: #eff6ff;
                border-color: #93c5fd;
            }
            .stepper-circle {
                width: 30px;
                height: 30px;
                font-size: 0.82rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="{{ auth()->check() && auth()->user()->isStudent() ? 'has-student-bottom-nav' : '' }}">
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg escr-topbar py-2 shadow-sm sticky-top d-print-none">
        <div class="container-fluid px-2 px-sm-3 px-lg-4">
            <div class="d-flex align-items-center gap-2">
                @auth
                    @if(!auth()->user()->isStudent())
                        <!-- Mobile Offcanvas Drawer Trigger for Staff/Admin (< 768px) -->
                        <button class="btn btn-sm btn-outline-light d-md-none px-2 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer" aria-label="Open Portal Menu">
                            <i class="bi bi-layout-sidebar-inset fs-5"></i>
                        </button>
                    @endif
                @endauth

                <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold me-0" href="{{ route('home') }}">
                    <span class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center fw-extrabold flex-shrink-0" style="width:34px;height:34px;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </span>
                    <div class="text-truncate">
                        <div class="lh-1 escr-brand-title text-truncate" style="font-size: 0.96rem; letter-spacing: 0.3px;">EAST SYSTEMS COLLEGES OF RIZAL</div>
                        <small class="text-info fw-normal escr-brand-subtitle d-block text-truncate" style="font-size: 0.72rem;">
                            <span class="d-none d-sm-inline">Morong, Rizal &bull; Online Enrollment &amp; Payment System (SY 2026–2027)</span>
                            <span class="d-inline d-sm-none">Morong, Rizal &bull; SY 2026–2027 Portal</span>
                        </small>
                    </div>
                </a>
            </div>

            <button class="navbar-toggler border-0 text-white px-2" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-label="Toggle navigation">
                <i class="bi bi-three-dots-vertical fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="topNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-2 mt-lg-0 pb-2 pb-lg-0">
                    <!-- Quick Demo Switcher Dropdown for Easy Testing & Presentation -->
                    <li class="nav-item dropdown">
                        <a class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center justify-content-center gap-1 w-100 w-lg-auto" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Quick Demo Role Switcher</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 280px;">
                            <li class="dropdown-header fw-bold text-uppercase small text-muted">Switch Active Persona</li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="student@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-person-check text-primary me-2"></i>Juan Dela Cruz (Enrolled)</span>
                                        <span class="badge bg-info text-dark">Student</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="promissory@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-file-earmark-medical text-primary me-2"></i>Angela (Promissory &rarr; Finals)</span>
                                        <span class="badge bg-primary">PN Approved</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="applicant@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-person-plus text-secondary me-2"></i>Mark Villanueva (Applicant)</span>
                                        <span class="badge bg-secondary">Pending Docs</span>
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="registrar@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-folder-check text-primary me-2"></i>Elena Reyes</span>
                                        <span class="badge bg-primary">Registrar</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="cashier@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-cash-coin text-success me-2"></i>Roberto Cruz</span>
                                        <span class="badge bg-success">Cashier</span>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('login.demo') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="demo_email" value="admin@escr.edu.ph">
                                    <button type="submit" class="dropdown-item rounded py-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-shield-lock text-danger me-2"></i>Dr. Maria Santos</span>
                                        <span class="badge bg-danger">Super Admin</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white-50 small text-center text-lg-start" href="{{ route('public.track') }}">
                            <i class="bi bi-search me-1"></i>Track Application
                        </a>
                    </li>

                    @auth
                        <li class="nav-item d-flex align-items-center justify-content-between justify-content-lg-start gap-2 ms-lg-2 bg-dark bg-opacity-25 rounded px-2 py-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ auth()->user()->roleBadgeClass() }}">{{ auth()->user()->roleLabel() }}</span>
                                <span class="text-white small fw-semibold">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#changePasswordModal" title="Change Password">
                                    <i class="bi bi-key-fill text-warning"></i>
                                    <span class="d-inline d-lg-none ms-1 small">Password</span>
                                </button>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" title="Sign Out">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @else
                        <li class="nav-item d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-light fw-semibold flex-fill">Sign In</a>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-warning fw-semibold flex-fill">Enroll Online</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @auth
        <!-- Mobile Slide-Out Offcanvas Sidebar Drawer (< 768px) -->
        <div class="offcanvas offcanvas-start d-md-none d-print-none" tabindex="-1" id="mobileSidebarDrawer" aria-labelledby="mobileSidebarDrawerLabel" style="width: 280px;">
            <div class="offcanvas-header escr-topbar text-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </span>
                    <div>
                        <h6 class="offcanvas-title fw-bold mb-0" id="mobileSidebarDrawerLabel">ESCR Portal Menu</h6>
                        <small class="text-info" style="font-size: 0.72rem;">{{ auth()->user()->name }} ({{ auth()->user()->roleLabel() }})</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-3 escr-offcanvas-nav">
                @if(auth()->user()->isStudent())
                    <div class="text-uppercase text-muted fw-bold small px-2 mb-2" style="font-size: 0.7rem;">Student Portal</div>
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.enrollment*') ? 'active' : '' }}" href="{{ route('student.enrollment') }}">
                            <i class="bi bi-file-earmark-person-fill"></i> Enrollment &amp; Docs
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.billing*') ? 'active' : '' }}" href="{{ route('student.billing') }}">
                            <i class="bi bi-wallet2"></i> Billing &amp; GCash Pay
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.promissory*') ? 'active' : '' }}" href="{{ route('student.promissory') }}">
                            <i class="bi bi-file-earmark-ruled-fill"></i> Promissory Note
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.clearance*') ? 'active' : '' }}" href="{{ route('student.clearance') }}">
                            <i class="bi bi-patch-check-fill"></i> Exam Clearance Slip
                        </a>
                        <a class="nav-link {{ request()->routeIs('student.tela*') ? 'active' : '' }}" href="{{ route('student.tela') }}">
                            <i class="bi bi-scissors"></i> Uniform Tela Store
                        </a>
                    </nav>
                @endif

                @if(auth()->user()->isRegistrar() || auth()->user()->isSuperAdmin())
                    <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Registrar Office</div>
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}" href="{{ route('registrar.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Registrar Hub
                        </a>
                        <a class="nav-link {{ request()->routeIs('registrar.applications*') ? 'active' : '' }}" href="{{ route('registrar.applications') }}">
                            <i class="bi bi-folder-check"></i> Verify Applications
                        </a>
                        <a class="nav-link {{ request()->routeIs('registrar.promissory*') ? 'active' : '' }}" href="{{ route('registrar.promissory') }}">
                            <i class="bi bi-journal-check"></i> Promissory Notes
                        </a>
                        <a class="nav-link {{ request()->routeIs('registrar.students*') ? 'active' : '' }}" href="{{ route('registrar.students') }}">
                            <i class="bi bi-people-fill"></i> Student Directory
                        </a>
                    </nav>
                @endif

                @if(auth()->user()->isCashier() || auth()->user()->isSuperAdmin())
                    <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Cashier Office</div>
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}">
                            <i class="bi bi-cash-stack"></i> Cashier Hub
                        </a>
                        <a class="nav-link {{ request()->routeIs('cashier.payments*') ? 'active' : '' }}" href="{{ route('cashier.payments') }}">
                            <i class="bi bi-phone-vibrate-fill"></i> GCash Payments
                        </a>
                        <a class="nav-link {{ request()->routeIs('cashier.tela*') ? 'active' : '' }}" href="{{ route('cashier.tela') }}">
                            <i class="bi bi-box-seam-fill"></i> Tela Fulfillment
                        </a>
                        <a class="nav-link {{ request()->routeIs('cashier.receipts*') ? 'active' : '' }}" href="{{ route('cashier.receipts') }}">
                            <i class="bi bi-receipt-cutoff"></i> Official Receipts
                        </a>
                    </nav>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Super Admin</div>
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-bar-chart-line-fill"></i> Analytics Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                            <i class="bi bi-person-gear"></i> Users &amp; RBAC
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings.fees') }}">
                            <i class="bi bi-sliders"></i> Deadlines &amp; Pricing
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.audit*') ? 'active' : '' }}" href="{{ route('admin.audit') }}">
                            <i class="bi bi-shield-check"></i> Security Audit Trail
                        </a>
                    </nav>
                @endif

                <hr class="my-3">
                <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-semibold mb-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key-fill me-1"></i>Change Password
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                        <i class="bi bi-box-arrow-right me-1"></i>Sign Out ({{ auth()->user()->name }})
                    </button>
                </form>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Desktop Sidebar Navigation (>= 768px) -->
                <aside class="col-lg-2 col-md-3 d-none d-md-block escr-sidebar p-3 d-print-none">
                    @if(auth()->user()->isStudent())
                        <div class="text-uppercase text-muted fw-bold small px-2 mb-2" style="font-size: 0.7rem;">Student Portal</div>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                <i class="bi bi-grid-1x2-fill"></i> Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.enrollment*') ? 'active' : '' }}" href="{{ route('student.enrollment') }}">
                                <i class="bi bi-file-earmark-person-fill"></i> Enrollment &amp; Docs
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.billing*') ? 'active' : '' }}" href="{{ route('student.billing') }}">
                                <i class="bi bi-wallet2"></i> Billing &amp; GCash Pay
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.promissory*') ? 'active' : '' }}" href="{{ route('student.promissory') }}">
                                <i class="bi bi-file-earmark-ruled-fill"></i> Promissory Note
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.clearance*') ? 'active' : '' }}" href="{{ route('student.clearance') }}">
                                <i class="bi bi-patch-check-fill"></i> Exam Clearance Slip
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.tela*') ? 'active' : '' }}" href="{{ route('student.tela') }}">
                                <i class="bi bi-scissors"></i> Uniform Tela Store
                            </a>
                        </nav>
                    @endif

                    @if(auth()->user()->isRegistrar() || auth()->user()->isSuperAdmin())
                        <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Registrar Office</div>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}" href="{{ route('registrar.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Registrar Hub
                            </a>
                            <a class="nav-link {{ request()->routeIs('registrar.applications*') ? 'active' : '' }}" href="{{ route('registrar.applications') }}">
                                <i class="bi bi-folder-check"></i> Verify Applications
                            </a>
                            <a class="nav-link {{ request()->routeIs('registrar.promissory*') ? 'active' : '' }}" href="{{ route('registrar.promissory') }}">
                                <i class="bi bi-journal-check"></i> Promissory Notes
                            </a>
                            <a class="nav-link {{ request()->routeIs('registrar.students*') ? 'active' : '' }}" href="{{ route('registrar.students') }}">
                                <i class="bi bi-people-fill"></i> Student Directory
                            </a>
                        </nav>
                    @endif

                    @if(auth()->user()->isCashier() || auth()->user()->isSuperAdmin())
                        <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Cashier Office</div>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}">
                                <i class="bi bi-cash-stack"></i> Cashier Hub
                            </a>
                            <a class="nav-link {{ request()->routeIs('cashier.payments*') ? 'active' : '' }}" href="{{ route('cashier.payments') }}">
                                <i class="bi bi-phone-vibrate-fill"></i> GCash Payments
                            </a>
                            <a class="nav-link {{ request()->routeIs('cashier.tela*') ? 'active' : '' }}" href="{{ route('cashier.tela') }}">
                                <i class="bi bi-box-seam-fill"></i> Tela Fulfillment
                            </a>
                            <a class="nav-link {{ request()->routeIs('cashier.receipts*') ? 'active' : '' }}" href="{{ route('cashier.receipts') }}">
                                <i class="bi bi-receipt-cutoff"></i> Official Receipts
                            </a>
                        </nav>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <div class="text-uppercase text-muted fw-bold small px-2 mt-3 mb-2" style="font-size: 0.7rem;">Super Admin</div>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-bar-chart-line-fill"></i> Analytics Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                <i class="bi bi-person-gear"></i> Users &amp; RBAC
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings.fees') }}">
                                <i class="bi bi-sliders"></i> Deadlines &amp; Pricing
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.audit*') ? 'active' : '' }}" href="{{ route('admin.audit') }}">
                                <i class="bi bi-shield-check"></i> Security Audit Trail
                            </a>
                        </nav>
                    @endif
                    <hr class="my-3">
                    <div class="text-uppercase text-muted fw-bold small px-2 mb-2" style="font-size: 0.7rem;">Account Security</div>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key-fill text-primary"></i> Change Password
                        </a>
                    </nav>
                </aside>

                <!-- Main Content Area -->
                <main class="col-lg-10 col-md-9 col-12 p-3 p-lg-4">
                    @include('layouts.partials.alerts')
                    @yield('content')
                </main>
            </div>
        </div>

        @if(auth()->user()->isStudent())
            <!-- Fixed Mobile Bottom Navigation Bar for Students (< 768px) -->
            <nav class="escr-mobile-bottom-nav d-flex d-md-none fixed-bottom d-print-none" aria-label="Student Mobile Navigation">
                <a href="{{ route('student.dashboard') }}" class="mobile-tab {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('student.enrollment') }}" class="mobile-tab {{ request()->routeIs('student.enrollment*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-person-fill"></i>
                    <span>Enroll</span>
                </a>
                <a href="{{ route('student.billing') }}" class="mobile-tab {{ request()->routeIs('student.billing*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>GCash Pay</span>
                </a>
                <a href="{{ route('student.promissory') }}" class="mobile-tab {{ request()->routeIs('student.promissory*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-ruled-fill"></i>
                    <span>Promissory</span>
                </a>
                <a href="{{ route('student.clearance') }}" class="mobile-tab {{ request()->routeIs('student.clearance*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Permit</span>
                </a>
                <a href="{{ route('student.tela') }}" class="mobile-tab {{ request()->routeIs('student.tela*') ? 'active' : '' }}">
                    <i class="bi bi-scissors"></i>
                    <span>Tela</span>
                </a>
            </nav>
        @endif
        <!-- Change Password Modal (Available to All Authenticated Users) -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header escr-topbar text-white">
                        <h6 class="modal-title fw-bold" id="changePasswordModalLabel">
                            <i class="bi bi-key-fill text-warning me-2"></i>Change Account Password
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('account.password.update') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="bg-light rounded p-2 px-3 border mb-3 small d-flex justify-content-between align-items-center">
                                <span>Signed in as: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</span>
                                <span class="badge {{ auth()->user()->roleBadgeClass() }}">{{ auth()->user()->roleLabel() }}</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">New Password (Min. 6 characters)</label>
                                <input type="password" name="password" class="form-control" minlength="6" placeholder="Enter new password" required>
                            </div>

                            <div class="mb-1">
                                <label class="form-label small fw-semibold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" minlength="6" placeholder="Re-type new password" required>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                                <i class="bi bi-shield-check me-1"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <main>
            <div class="container pt-3">
                @include('layouts.partials.alerts')
            </div>
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
