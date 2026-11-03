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
    <!-- Google Fonts: Cinzel (Crest Serif), Outfit (Headings), Plus Jakarta Sans (Body/UI), JetBrains Mono (IDs & Currency) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --aurum-burgundy-deep: #24070B;
            --aurum-burgundy: #360B10;
            --aurum-burgundy-card: #3D0E14;
            --aurum-burgundy-light: #52141D;
            --aurum-burgundy-border: #5C1A24;
            --aurum-crimson: #8C1D28;
            --aurum-gold: #D4AF37;
            --aurum-gold-bright: #F3D87D;
            --aurum-gold-deep: #AA821B;
            --aurum-ivory: #FAF8F5;
            --aurum-ivory-border: #EBE5DC;
            --escr-red-dark: #360B10;
            --escr-red: #6E1722;
            --escr-red-bright: #991B1B;
            --escr-red-soft: #FDF8F6;
            --escr-gold: #D4AF37;
            --escr-gold-deep: #AA821B;
            --escr-gold-soft: #FBF6E8;
            --bs-primary: #52141D;
            --bs-primary-rgb: 82, 20, 29;
            --bs-link-color: #6E1722;
            --bs-link-hover-color: #360B10;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--aurum-ivory);
            color: #231517;
            letter-spacing: -0.01em;
            line-height: 1.55;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6,
        .escr-brand-title,
        .aurum-heading,
        .display-5,
        .display-6 {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
        }
        .aurum-title-uppercase {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #2A0A0E;
        }
        .aurum-crest-letter {
            font-family: 'Cinzel', Georgia, serif;
            font-weight: 800;
            background: linear-gradient(180deg, #F3D87D 0%, #D4AF37 55%, #9A7418 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            filter: drop-shadow(0 2px 6px rgba(212, 175, 55, 0.28));
        }
        code, .font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
            color: #52141D;
            background-color: #F8F1EC;
            padding: 0.15rem 0.45rem;
            border-radius: 0.35rem;
            border: 1px solid #E6D7C3;
        }
        .table td, .table th, .fs-4, .fs-5, .aurum-metric-val {
            font-variant-numeric: tabular-nums;
        }
        .btn-primary {
            background: linear-gradient(135deg, #52141D 0%, #360B10 100%);
            border-color: #5C1A24;
            color: #F3D87D;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(54, 11, 16, 0.18);
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #6E1722 0%, #24070B 100%) !important;
            border-color: #D4AF37 !important;
            color: #FFFFFF !important;
        }
        .btn-outline-primary {
            color: #52141D;
            border-color: #C7B299;
            background-color: #FFFFFF;
            font-weight: 600;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary.active {
            background-color: #360B10 !important;
            border-color: #360B10 !important;
            color: #F3D87D !important;
        }
        .btn-warning, .btn-aurum-gold {
            background: linear-gradient(135deg, #F3D87D 0%, #D4AF37 55%, #B68D24 100%);
            border: 1px solid #D4AF37;
            color: #24070B;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(212, 175, 55, 0.22);
        }
        .btn-warning:hover, .btn-aurum-gold:hover {
            background: linear-gradient(135deg, #F7E297 0%, #DFB93E 55%, #C49A29 100%);
            border-color: #AA821B;
            color: #160406;
        }
        .text-primary {
            color: #6E1722 !important;
        }
        .bg-primary {
            background-color: #360B10 !important;
        }
        .border-primary {
            border-color: #6E1722 !important;
        }
        /* Executive Topbar (Screen 1 Ivory/White Header with Gold Hairline) */
        .escr-topbar {
            background: #FFFFFF;
            color: #24070B;
            border-top: 3px solid #D4AF37;
            border-bottom: 1px solid var(--aurum-ivory-border);
        }
        .escr-topbar .navbar-brand {
            color: #24070B !important;
        }
        /* Deep Imperial Burgundy Sidebar (Screen 1 Aurum Sidebar) */
        .escr-sidebar {
            background: linear-gradient(180deg, #24070B 0%, #360B10 60%, #2A080C 100%);
            border-right: 1px solid #4A121B;
            box-shadow: 4px 0 20px rgba(36, 7, 11, 0.14);
            color: #F5EFE6;
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 768px) {
            .escr-sidebar {
                min-height: calc(100vh - 64px);
            }
        }
        .escr-sidebar .sidebar-section-label,
        .escr-offcanvas-nav .sidebar-section-label {
            color: #A37B82;
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 0.65rem;
            margin-bottom: 0.35rem;
        }
        .escr-sidebar .nav-link,
        .escr-offcanvas-nav .nav-link {
            color: #CFAEB4;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.62rem 0.85rem;
            border-radius: 0.55rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.18s ease;
            border-left: 3px solid transparent;
        }
        .escr-sidebar .nav-link i,
        .escr-offcanvas-nav .nav-link i {
            color: #B99256;
            font-size: 0.98rem;
            transition: color 0.18s ease;
        }
        .escr-sidebar .nav-link:hover,
        .escr-offcanvas-nav .nav-link:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: #F3D87D;
            border-left-color: rgba(212, 175, 55, 0.55);
        }
        .escr-sidebar .nav-link:hover i,
        .escr-offcanvas-nav .nav-link:hover i {
            color: #F3D87D;
        }
        .escr-sidebar .nav-link.active,
        .escr-offcanvas-nav .nav-link.active {
            background: linear-gradient(90deg, rgba(140, 29, 40, 0.85) 0%, rgba(74, 15, 22, 0.65) 100%);
            color: #F3D87D;
            font-weight: 700;
            border-left: 3px solid #D4AF37;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.22);
        }
        .escr-sidebar .nav-link.active i,
        .escr-offcanvas-nav .nav-link.active i {
            color: #F3D87D;
        }
        /* Gold Sidebar Profile ID Card (From Reference Image) */
        .aurum-sidebar-id-card {
            background: linear-gradient(160deg, #E8C967 0%, #C59B27 55%, #9B7415 100%);
            border-radius: 0.85rem;
            padding: 0.85rem;
            text-align: center;
            color: #24070B;
            border: 1px solid #F3D87D;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.28);
        }
        .aurum-sidebar-id-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #360B10 0%, #24070B 100%);
            color: #F3D87D;
            border: 2px solid #FAF8F5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
            margin-bottom: 0.35rem;
            box-shadow: 0 3px 8px rgba(36, 7, 11, 0.25);
        }
        /* Cards & Dark Burgundy Chart Cards (Screen 1 Overview) */
        .card {
            background-color: #FFFFFF;
            border: 1px solid var(--aurum-ivory-border);
            border-radius: 0.85rem;
            box-shadow: 0 4px 14px rgba(36, 7, 11, 0.035);
        }
        .aurum-dark-card {
            background: linear-gradient(145deg, #3D0E14 0%, #26070B 100%);
            border: 1px solid #5C1A24;
            border-radius: 0.9rem;
            color: #FFFFFF;
            box-shadow: 0 10px 24px rgba(36, 7, 11, 0.16);
            position: relative;
            overflow: hidden;
        }
        .aurum-dark-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #D4AF37, transparent);
            opacity: 0.7;
        }
        .aurum-kpi-card {
            background: #FFFFFF;
            border: 1px solid var(--aurum-ivory-border);
            border-radius: 0.85rem;
            padding: 1rem 1.15rem;
            text-align: center;
            box-shadow: 0 4px 14px rgba(36, 7, 11, 0.035);
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .aurum-kpi-card:hover {
            transform: translateY(-2px);
            border-color: #D4AF37;
        }
        .aurum-kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.55rem;
            font-weight: 800;
            color: #24070B;
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .aurum-kpi-label {
            font-size: 0.74rem;
            font-weight: 600;
            color: #7D6B6E;
            margin-top: 0.2rem;
        }
        .aurum-kpi-sub {
            font-size: 0.68rem;
            font-weight: 700;
            color: #AA821B;
            margin-top: 0.15rem;
        }
        .aurum-segmented-group {
            display: inline-flex;
            background: #FFFFFF;
            border: 1px solid #DFD7CC;
            border-radius: 0.55rem;
            padding: 0.18rem;
            gap: 0.15rem;
        }
        .aurum-segmented-btn {
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.32rem 0.75rem;
            border-radius: 0.4rem;
            color: #6E5A5D;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .aurum-segmented-btn:hover {
            color: #24070B;
            background: #FAF8F5;
        }
        .aurum-segmented-btn.active {
            background: #360B10;
            color: #F3D87D;
            font-weight: 700;
        }
        /* Mini Gold Bar Chart inside Screen 1 Overview Cards */
        .aurum-mini-bars {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            height: 76px;
            padding-top: 8px;
        }
        .aurum-mini-bar-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            justify-content: flex-end;
        }
        .aurum-mini-bar {
            width: 100%;
            border-radius: 4px 4px 0 0;
            background: linear-gradient(180deg, #F3D87D 0%, #D4AF37 65%, #7A5911 100%);
            min-height: 8px;
            transition: height 0.3s ease;
        }
        .aurum-mini-bar.bar-muted {
            background: linear-gradient(180deg, #8C3B47 0%, #52141D 100%);
            opacity: 0.75;
        }
        .aurum-mini-bar-label {
            font-size: 0.6rem;
            color: #D9C3B0;
            margin-top: 4px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .card.border-start {
            border-top: 1px solid var(--aurum-ivory-border) !important;
            border-right: 1px solid var(--aurum-ivory-border) !important;
            border-bottom: 1px solid var(--aurum-ivory-border) !important;
        }
        .kpi-accent-primary {
            border-left: 4px solid #360B10 !important;
        }
        .kpi-accent-success {
            border-left: 4px solid #D4AF37 !important;
        }
        .kpi-accent-warning {
            border-left: 4px solid #AA821B !important;
        }
        .kpi-accent-danger {
            border-left: 4px solid #991B1B !important;
        }
        .escr-hero-banner {
            background: linear-gradient(135deg, #24070B 0%, #3D0E14 60%, #52141D 100%);
            border: 1px solid #5C1A24;
            border-bottom: 3px solid #D4AF37;
            border-radius: 0.9rem;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(36, 7, 11, 0.16);
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
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        /* Activity Avatar Circle (Screen 1 Recent Activity) */
        .aurum-activity-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #F3D87D 0%, #D4AF37 100%);
            color: #24070B;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 0.78rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1.5px solid #EBE5DC;
        }
        /* Mobile Bottom App Bar for Students */
        .escr-mobile-bottom-nav {
            background: #24070B;
            border-top: 2px solid #D4AF37;
            box-shadow: 0 -4px 16px rgba(36, 7, 11, 0.24);
            z-index: 1030;
            padding: 0.25rem 0.2rem calc(0.25rem + env(safe-area-inset-bottom, 0px));
        }
        .escr-mobile-bottom-nav .mobile-tab {
            flex: 1;
            text-align: center;
            padding: 0.3rem 0.1rem;
            color: #CFAEB4;
            text-decoration: none;
            font-size: 0.68rem;
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
            color: #F3D87D;
            font-weight: 700;
        }
        .escr-mobile-bottom-nav .mobile-tab.active i {
            background-color: rgba(212, 175, 55, 0.18);
            color: #F3D87D;
            box-shadow: inset 0 0 0 1px #D4AF37;
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
    <!-- Top Navigation Bar (Screen 1 Ivory/White Header with Gold Crest & Burgundy Accents) -->
    <nav class="navbar navbar-expand-lg escr-topbar py-2 sticky-top d-print-none">
        <div class="container-fluid px-2 px-sm-3 px-lg-4">
            <div class="d-flex align-items-center gap-2">
                @auth
                    @php
                        $headerNotifications = auth()->user()->systemNotifications()->latest()->take(12)->get();
                        $unreadNotifCount = $headerNotifications->where('is_read', false)->count();
                    @endphp
                    @if(!auth()->user()->isStudent())
                        <!-- Mobile Offcanvas Drawer Trigger for Staff/Admin (< 768px) -->
                        <button class="btn btn-sm btn-outline-primary d-md-none px-2 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarDrawer" aria-label="Open Portal Menu">
                            <i class="bi bi-layout-sidebar-inset fs-5"></i>
                        </button>
                    @endif

                    <!-- Top-Left Notification Button (Opens Recent Activity & System Alerts Drawer) -->
                    <button class="btn btn-sm position-relative d-inline-flex align-items-center gap-1 px-2 py-1 shadow-sm"
                            type="button"
                            id="topLeftNotificationBtn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#notificationsDrawer"
                            aria-controls="notificationsDrawer"
                            title="Recent Activity & System Alerts"
                            style="background: linear-gradient(145deg, #360B10 0%, #24070B 100%); color: #F3D87D; border: 1.5px solid #D4AF37; border-radius: 0.55rem;">
                        <i class="bi bi-bell-fill"></i>
                        <span class="d-none d-sm-inline fw-bold" style="font-size: 0.74rem;">Alerts</span>
                        @if($unreadNotifCount > 0)
                            <span class="badge rounded-pill" style="background: #D4AF37; color: #24070B; font-size: 0.65rem;">{{ $unreadNotifCount }}</span>
                        @elseif($headerNotifications->isNotEmpty())
                            <span class="badge rounded-pill" style="background: rgba(212, 175, 55, 0.22); color: #F3D87D; font-size: 0.65rem;">{{ $headerNotifications->count() }}</span>
                        @endif
                    </button>
                @endauth

                <a class="navbar-brand d-flex align-items-center gap-2 fw-bold me-0" href="{{ route('home') }}">
                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width:38px;height:38px;background:linear-gradient(145deg,#360B10 0%,#24070B 100%);border:1.5px solid #D4AF37;">
                        <span class="aurum-crest-letter" style="font-size: 1.35rem;">E</span>
                    </span>
                    <div class="text-truncate">
                        <div class="lh-1 escr-brand-title text-truncate fw-bold" style="font-size: 0.92rem; letter-spacing: 0.08em; color: #24070B;">EAST SYSTEMS COLLEGES OF RIZAL</div>
                        <small class="fw-semibold escr-brand-subtitle d-block text-truncate" style="font-size: 0.7rem; color: #8C6D23; letter-spacing: 0.02em;">
                            <span class="d-none d-sm-inline">AURUM ANALYTICS &bull; Morong, Rizal &bull; Online Enrollment &amp; Payment System</span>
                            <span class="d-inline d-sm-none">Morong, Rizal &bull; SY 2026–2027 Portal</span>
                        </small>
                    </div>
                </a>
            </div>

            <button class="navbar-toggler border-0 px-2" style="color: #360B10;" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-label="Toggle navigation">
                <i class="bi bi-three-dots-vertical fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="topNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-2 mt-lg-0 pb-2 pb-lg-0">
                    <!-- Quick Demo Switcher Dropdown for Easy Testing & Presentation -->
                    <li class="nav-item dropdown">
                        <a class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center justify-content-center gap-1 w-100 w-lg-auto" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge-fill" style="color: #D4AF37;"></i>
                            <span>Quick Demo Role Switcher</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 285px; border: 1px solid #EBE5DC !important;">
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
                        <a class="nav-link small fw-semibold text-center text-lg-start" style="color: #5C4346;" href="{{ route('public.track') }}" title="Track Application">
                            <i class="bi bi-search me-1"></i>Track Application
                        </a>
                    </li>

                    @auth
                        <li class="nav-item d-flex align-items-center justify-content-between justify-content-lg-start gap-2 ms-lg-1 rounded-pill px-2 py-1" style="background: #FAF8F5; border: 1px solid #E5DEC9;">
                            <div class="d-flex align-items-center gap-2 ps-1">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width:26px;height:26px;background:#360B10;color:#F3D87D;font-size:0.72rem;border:1px solid #D4AF37;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="small fw-bold" style="color: #24070B;">{{ auth()->user()->name }}</span>
                                <span class="badge {{ auth()->user()->roleBadgeClass() }}" style="font-size: 0.65rem;">{{ auth()->user()->roleLabel() }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" data-bs-toggle="modal" data-bs-target="#changePasswordModal" title="Change Password">
                                    <i class="bi bi-key-fill" style="color: #AA821B;"></i>
                                    <span class="d-inline d-lg-none ms-1 small">Password</span>
                                </button>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-2 py-0" title="Sign Out">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @else
                        <li class="nav-item d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary fw-semibold flex-fill px-3">Sign In</a>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-warning fw-bold flex-fill px-3">Enroll Online</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @auth
        <!-- Top-Left Slide-Out Notification Drawer (Recent Activity & System Alerts) -->
        <div class="offcanvas offcanvas-start d-print-none" tabindex="-1" id="notificationsDrawer" aria-labelledby="notificationsDrawerLabel" style="width: 380px; max-width: 92vw; background-color: #FAF8F5; border-right: 2px solid #D4AF37;">
            <div class="offcanvas-header text-white" style="background: linear-gradient(135deg, #24070B 0%, #3D0E14 100%); border-bottom: 2px solid #D4AF37;">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: rgba(212, 175, 55, 0.18); color: #F3D87D; border: 1px solid #D4AF37;">
                        <i class="bi bi-bell-fill"></i>
                    </span>
                    <div>
                        <h6 class="offcanvas-title fw-bold mb-0 text-white" id="notificationsDrawerLabel">Recent Activity &amp; System Alerts</h6>
                        <small style="font-size: 0.72rem; color: #F3D87D;">
                            {{ $unreadNotifCount > 0 ? "{$unreadNotifCount} Unread Alert(s)" : 'Automated Email & Portal Feed' }}
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-3">
                @if($unreadNotifCount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #EBE5DC !important;">
                        <span class="small fw-semibold text-muted">You have {{ $unreadNotifCount }} unread alert(s)</span>
                        <form action="{{ route('notifications.read_all') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">
                                <i class="bi bi-check2-all me-1"></i>Mark All Read
                            </button>
                        </form>
                    </div>
                @endif

                <div class="d-flex flex-column gap-2">
                    @forelse($headerNotifications as $notif)
                        <div class="card p-3 border {{ !$notif->is_read ? 'border-warning shadow-sm' : '' }}" style="background: {{ !$notif->is_read ? '#FFFDF7' : '#FFFFFF' }};">
                            <div class="d-flex align-items-start gap-2">
                                <div class="aurum-activity-avatar mt-1">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                                        <span class="fw-bold text-dark small">{{ $notif->subject }}</span>
                                        <span class="badge bg-light text-muted border flex-shrink-0" style="font-size: 0.65rem;">{{ $notif->sent_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-muted mb-0" style="white-space: pre-line; font-size: 0.78rem;">{{ $notif->message }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 small">
                            <i class="bi bi-bell-slash fs-2 d-block mb-2" style="color: #B99256;"></i>
                            No recent activity or system alerts yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Mobile Slide-Out Offcanvas Sidebar Drawer (< 768px) -->
        <div class="offcanvas offcanvas-start d-md-none d-print-none" tabindex="-1" id="mobileSidebarDrawer" aria-labelledby="mobileSidebarDrawerLabel" style="width: 280px; background: linear-gradient(180deg, #24070B 0%, #360B10 100%); color: #FAF8F5;">
            <div class="offcanvas-header border-bottom" style="border-color: #4A121B !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="aurum-crest-letter" style="font-size: 2rem;">E</span>
                    <div>
                        <h6 class="offcanvas-title fw-bold mb-0 text-white" id="mobileSidebarDrawerLabel">ESCR Aurum Portal</h6>
                        <small style="font-size: 0.72rem; color: #F3D87D;">{{ auth()->user()->name }} ({{ auth()->user()->roleLabel() }})</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-3 escr-offcanvas-nav">
                @if(auth()->user()->isStudent())
                    <div class="sidebar-section-label">Student Portal</div>
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
                    <div class="sidebar-section-label mt-3">Registrar Office</div>
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
                    <div class="sidebar-section-label mt-3">Cashier Office</div>
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
                    <div class="sidebar-section-label mt-3">Super Admin</div>
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

                <hr class="my-3" style="border-color: #5C1A24;">
                <button type="button" class="btn btn-warning btn-sm w-100 fw-bold mb-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key-fill me-1"></i>Change Password
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100 fw-semibold">
                        <i class="bi bi-box-arrow-right me-1"></i>Sign Out ({{ auth()->user()->name }})
                    </button>
                </form>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Desktop Sidebar Navigation (Screen 1 Deep Imperial Burgundy Sidebar with Gold Crest & Bottom ID Card) -->
                <aside class="col-lg-2 col-md-3 d-none d-md-flex escr-sidebar p-3 d-print-none">
                    <!-- Aurum Gold Serif Crest Header -->
                    <div class="text-center pb-3 mb-3 border-bottom" style="border-color: #4A121B !important;">
                        <div class="aurum-crest-letter" style="font-size: 2.6rem;">E</div>
                        <div class="fw-bold text-uppercase mt-1" style="font-size: 0.65rem; letter-spacing: 0.16em; color: #F3D87D;">ESCR AURUM</div>
                        <div style="font-size: 0.6rem; color: #A37B82;">Morong, Rizal &bull; MIS</div>
                    </div>

                    @if(auth()->user()->isStudent())
                        <div class="sidebar-section-label">Student Portal</div>
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
                        <div class="sidebar-section-label mt-3">Registrar Office</div>
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
                        <div class="sidebar-section-label mt-3">Cashier Office</div>
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
                        <div class="sidebar-section-label mt-3">Super Admin</div>
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

                    <!-- Bottom Gold Profile ID Card (From Reference UI) -->
                    <div class="mt-auto pt-3">
                        <div class="aurum-sidebar-id-card">
                            <div class="aurum-sidebar-id-avatar">
                                @php
                                    $nameParts = explode(' ', trim(auth()->user()->name));
                                    $initials = strtoupper(substr($nameParts[0] ?? 'E', 0, 1) . substr(end($nameParts) ?: '', 0, 1));
                                @endphp
                                {{ $initials }}
                            </div>
                            <div class="fw-bold text-truncate" style="font-size: 0.8rem; color: #24070B;">{{ auth()->user()->name }}</div>
                            <div class="text-truncate mb-2" style="font-size: 0.68rem; color: #4A121B; font-weight: 600;">
                                {{ auth()->user()->student_number ? auth()->user()->student_number : auth()->user()->roleLabel() }}
                            </div>
                            <button type="button" class="btn btn-sm w-100 py-1 fw-bold" style="background:#360B10;color:#F3D87D;font-size:0.68rem;border-radius:999px;" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="bi bi-key-fill me-1"></i>Account Security
                            </button>
                        </div>
                    </div>
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
                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #24070B 0%, #3D0E14 100%); border-bottom: 2px solid #D4AF37;">
                        <h6 class="modal-title fw-bold" id="changePasswordModalLabel">
                            <i class="bi bi-key-fill me-2" style="color: #F3D87D;"></i>Change Account Password
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
