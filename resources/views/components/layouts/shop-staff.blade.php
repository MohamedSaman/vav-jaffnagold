<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Shop Staff Portal' }} - JaffnaGold</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Barcode libraries -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Inter font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')

    <style>
        /* Theme tokens: Premium Blue & Black theme */
        :root {
            /* Clean Page Background */
            --page-bg: #fcfcfc;
            --surface: #ffffff;

            /* JG Logo Blue Palette */
            --primary: #161b97; /* Logo Blue */
            --primary-600: #12167d; /* Deep Blue */
            --primary-700: #0e1163; /* Darker Blue */
            --primary-50: #f0f2ff; /* Light Blue Tint */
            --primary-100: #e0e4ff;

            /* Functional Colors - JG Logo Red */
            --success: #1a1a1a;
            --success-real: #10b981;
            --warning: #f59e0b;
            --danger: #f30b1f; /* Logo Red */
            --info: #3b82f6;

            /* Refined Neutral Palette */
            --text-main: #000000;
            --text-muted: #4b5563;
            --text-light: #9ca3af;
            --border: #e5e7eb;
            --border-light: #f3f4f6;

            /* Effects */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--page-bg);
            color: var(--text-main);
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .fw-800 { font-weight: 800 !important; }
        .text-jg-blue { color: var(--primary) !important; }
        .bg-jg-blue { background-color: var(--primary) !important; }
        .text-jg-red { color: var(--danger) !important; }
        .bg-jg-red { background-color: var(--danger) !important; }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100vh;
            background: #ffffff;
            color: var(--text-main);
            z-index: 1030;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: 1px 0 0 var(--border);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar.collapsed {
            width: 70px;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            transition: all 0.3s ease;
        }

        /* Use :has to allow overflow only when hovering an item with a submenu */
        .sidebar.collapsed:has(.nav-item:hover) {
            overflow: visible !important;
        }

        .sidebar.collapsed::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center !important;
            padding: 15px 10px;
        }

        .sidebar.collapsed .logo-img {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 10px;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link.dropdown-toggle::after {
            display: none !important;
        }

        .sidebar .nav {
            padding-bottom: 50px;
        }

        .sidebar-header {
            padding: 12px 15px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .sidebar-title {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--danger);
            letter-spacing: -0.03em;
            white-space: nowrap;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        /* Navigation styles */
        .nav-item {
            margin: 2px 0;
        }

        .sidebar-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            margin: 5px 20px;
            opacity: 0.4;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 8px 16px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            font-weight: 500;
            color: var(--text-muted) !important;
        }

        .nav-link.active {
            background: var(--primary);
            color: #ffffff !important;
            border-left: none;
        }

        .nav-link:hover:not(.active) {
            color: var(--primary) !important;
            background: var(--primary-50);
            outline: none;
        }

        .nav-link i {
            margin-right: 12px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            transition: transform 0.2s;
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        /* Submenu and Category styles */
        .sidebar-heading {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 12px 18px 4px;
            margin-top: 6px;
            list-style: none;
        }

        .sidebar.collapsed .sidebar-heading {
            display: none !important;
        }

        .sidebar .nav-link.dropdown-toggle::after {
            margin-left: auto;
            border: none;
            content: "\F282";
            font-family: "bootstrap-icons";
            font-size: 0.85rem;
            transition: transform 0.25s ease;
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: var(--text-muted);
        }

        .sidebar .nav-link.dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .sidebar .nav-link.dropdown-toggle:hover::after {
            color: var(--primary);
        }

        .sidebar .nav-link.dropdown-toggle.parent-active {
            background: var(--primary-50);
            color: var(--primary) !important;
            font-weight: 600;
        }

        .sidebar .nav-link.dropdown-toggle.parent-active i {
            color: var(--primary) !important;
        }

        .sidebar.collapsed .nav-link.dropdown-toggle::after {
            display: none !important;
        }

        .sidebar-submenu {
            padding-left: 0;
            margin: 2px 0 6px 0;
            list-style: none;
        }

        .sidebar-submenu .nav-link {
            padding: 7px 14px 7px 22px;
            font-size: 0.88rem;
            margin: 2px 10px 2px 20px;
            border-left: 2px solid #e5e7eb;
            border-radius: 0 8px 8px 0;
            color: #64748b !important;
            font-weight: 500;
        }

        .sidebar-submenu .nav-link:hover:not(.active) {
            color: var(--primary) !important;
            background: var(--primary-50);
            border-left-color: var(--primary);
        }

        .sidebar-submenu .nav-link.active {
            background: var(--primary) !important;
            color: #ffffff !important;
            border-left-color: var(--primary);
            font-weight: 600;
        }

        .sidebar-submenu .nav-link i {
            font-size: 1rem;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        /* Floating submenu for collapsed sidebar */
        @media (min-width: 768px) {
            .sidebar.collapsed .nav-item {
                position: relative;
            }

            /* Force hide submenus in collapsed state, even if 'show' class is present */
            .sidebar.collapsed .nav-item > .collapse {
                display: none !important;
            }

            .sidebar.collapsed .nav-item:hover > .collapse {
                display: block !important;
                position: absolute !important;
                left: 68px;
                top: 0;
                width: 240px;
                background: #ffffff !important; 
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
                border-radius: 12px !important;
                border: 1px solid var(--border) !important;
                z-index: 9999 !important;
                padding: 10px 0 !important;
                height: auto !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .sidebar.collapsed .nav-item:hover > .collapse .nav,
            .sidebar.collapsed .nav-item:hover > .collapse .sidebar-submenu {
                padding-left: 0 !important;
                padding-top: 0 !important;
                margin-left: 0 !important;
            }

            .sidebar.collapsed .nav-item:hover > .collapse .nav-link {
                padding: 10px 20px !important;
                margin: 2px 10px !important;
                text-align: left !important;
                display: flex !important;
                justify-content: flex-start !important;
                border-radius: 8px !important;
                color: var(--text-main) !important;
            }

            .sidebar.collapsed .nav-item:hover > .collapse .nav-link span {
                display: inline !important;
                font-size: 0.9rem !important;
            }

            .sidebar.collapsed .nav-item:hover > .collapse .nav-link i {
                margin-right: 12px !important;
                font-size: 1.1rem !important;
                width: 24px !important;
            }
            
            /* Hide the normal transition for collapse when hovered in collapsed sidebar */
            .sidebar.collapsed .nav-item:hover > .collapse.collapsing {
                transition: none !important;
                height: auto !important;
                display: block !important;
            }
        }

        /* Top bar styles */
        .top-bar {
            height: 72px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            padding: 0 24px;
            position: fixed;
            top: 0;
            right: 0;
            left: 270px;
            z-index: 1000;
            display: flex;
            align-items: center;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid var(--border);
        }

        .top-bar.collapsed {
            left: 80px;
        }

        .top-bar .title {
            color: #1e293b;
        }

        /* User info styles */
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px;
            border-radius: 5px;
            transition: background-color 0.2s;
            color: #1e293b;
        }

        .admin-info:hover {
            background-color: #f7f8fb;
            color: #1e293b;
        }

        .admin-avatar,
        .staff-avatar,
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            letter-spacing: -0.03em;
            border: 2px solid var(--primary-600);
        }

        .admin-name {
            font-weight: 500;
        }

        /* Dropdown menu styles */
        .dropdown-toggle {
            cursor: pointer;
        }

        .top-bar .dropdown-toggle::after {
            display: none;
        }

        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 0;
            margin-top: 10px;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: var(--primary-100);
        }

        .dropdown-item i {
            font-size: 1rem;
        }

        /* Main content styles */
        .main-content {
            margin-left: 270px;
            margin-top: 70px;
            padding: 32px;
            background-color: var(--page-bg);
            min-height: calc(100vh - 70px);
            width: calc(100% - 270px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.collapsed {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* Responsive styles */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
                height: 100%;
                bottom: 0;
                top: 0;
                overflow-y: auto;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            .top-bar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
            <div class="sidebar-title">
                    Jaffna Gold
                    <small style="font-size: 11px; color: #f59e0b; display: block; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">{{ config('shop.branch', 'Vavuniya Branch') }}</small>
                </div>
            </div>
            <div class="sidebar-separator"></div>
            <ul class="nav flex-column">
                <li class="sidebar-heading">MAIN</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shop-staff.dashboard') ? 'active' : '' }}" href="{{ route('shop-staff.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> <span>Overview</span>
                    </a>
                </li>
                
                @php
                    $isSaleActive = request()->routeIs(
                        'shop-staff.store-billing',
                        'shop-staff.sales',
                        'shop-staff.due-sales',
                        'shop-staff.quotation-list',
                        'shop-staff.quotation-billing',
                        'shop-staff.sold-products*'
                    );

                    $isFinanceActive = request()->routeIs(
                        'shop-staff.expenses',
                        'shop-staff.purchasing-expenses*',
                        'shop-staff.daily-savings*',
                        'shop-staff.deposits*',
                        'shop-staff.salary'
                    );

                    $isServicesActive = request()->routeIs(
                        'shop-staff.gold-plating*',
                        'shop-staff.handmade-purchasing*'
                    );

                    $isOperationsActive = request()->routeIs(
                        'shop-staff.products',
                        'shop-staff.delivery-packing'
                    );

                    $staffType = auth()->user()->staff_type ?? 'shop_staff';
                    $permissionModel = new \App\Models\StaffTypePermission();
                @endphp

                {{-- ===== 1. SALE GROUP ===== --}}
                <li class="sidebar-heading">SALES</li>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle {{ $isSaleActive ? 'parent-active' : '' }}" 
                       href="#staffSalesSubmenu" 
                       data-bs-toggle="collapse" 
                       role="button" 
                       aria-expanded="{{ $isSaleActive ? 'true' : 'false' }}" 
                       aria-controls="staffSalesSubmenu">
                        <i class="bi bi-cart3"></i> <span>Sale</span>
                    </a>
                    <div class="collapse {{ $isSaleActive ? 'show' : '' }}" id="staffSalesSubmenu">
                        <ul class="nav flex-column sidebar-submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.store-billing') ? 'active' : '' }}" href="{{ route('shop-staff.store-billing') }}">
                                    <i class="bi bi-cart-plus"></i> <span>Store Billing</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.sales') ? 'active' : '' }}" href="{{ route('shop-staff.sales') }}">
                                    <i class="bi bi-receipt"></i> <span>Sale List</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.due-sales') ? 'active' : '' }}" href="{{ route('shop-staff.due-sales') }}">
                                    <i class="bi bi-exclamation-triangle"></i> <span>Due Sales</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.quotation-list') || request()->routeIs('shop-staff.quotation-billing') ? 'active' : '' }}" href="{{ route('shop-staff.quotation-list') }}">
                                    <i class="bi bi-file-earmark-text"></i> <span>My Quotations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.sold-products*') ? 'active' : '' }}" href="{{ route('shop-staff.sold-products') }}">
                                    <i class="bi bi-bar-chart-steps text-success"></i> <span>Sold Products</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ===== 2. FINANCE GROUP ===== --}}
                <li class="sidebar-heading">FINANCE</li>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle {{ $isFinanceActive ? 'parent-active' : '' }}" 
                       href="#staffFinanceSubmenu" 
                       data-bs-toggle="collapse" 
                       role="button" 
                       aria-expanded="{{ $isFinanceActive ? 'true' : 'false' }}" 
                       aria-controls="staffFinanceSubmenu">
                        <i class="bi bi-wallet2 text-success"></i> <span>Finance</span>
                    </a>
                    <div class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="staffFinanceSubmenu">
                        <ul class="nav flex-column sidebar-submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.expenses') ? 'active' : '' }}" href="{{ route('shop-staff.expenses') }}">
                                    <i class="bi bi-cash-stack"></i> <span>Daily Expenses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.purchasing-expenses*') ? 'active' : '' }}" href="{{ route('shop-staff.purchasing-expenses') }}">
                                    <i class="bi bi-bag-check text-info"></i> <span>Purchasing Expenses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.daily-savings*') || request()->routeIs('shop-staff.deposits*') ? 'active' : '' }}" href="{{ route('shop-staff.daily-savings') }}">
                                    <i class="bi bi-piggy-bank text-warning"></i> <span>Daily Savings &amp; Deposits</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.salary') ? 'active' : '' }}" href="{{ route('shop-staff.salary') }}">
                                    <i class="bi bi-people"></i> <span>Shop Staff Salary</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ===== 3. WORKSHOP & SERVICES GROUP ===== --}}
                <li class="sidebar-heading">SERVICES</li>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle {{ $isServicesActive ? 'parent-active' : '' }}" 
                       href="#staffServicesSubmenu" 
                       data-bs-toggle="collapse" 
                       role="button" 
                       aria-expanded="{{ $isServicesActive ? 'true' : 'false' }}" 
                       aria-controls="staffServicesSubmenu">
                        <i class="bi bi-tools text-warning"></i> <span>Workshop &amp; Services</span>
                    </a>
                    <div class="collapse {{ $isServicesActive ? 'show' : '' }}" id="staffServicesSubmenu">
                        <ul class="nav flex-column sidebar-submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.gold-plating*') ? 'active' : '' }}" href="{{ route('shop-staff.gold-plating') }}">
                                    <i class="bi bi-gem text-warning"></i> <span>Gold Plating</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.handmade-purchasing*') ? 'active' : '' }}" href="{{ route('shop-staff.handmade-purchasing') }}">
                                    <i class="bi bi-hammer text-danger"></i> <span>Handmade Purchases</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ===== 4. PRODUCTS & OPERATIONS GROUP ===== --}}
                <li class="sidebar-heading">OPERATIONS</li>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle {{ $isOperationsActive ? 'parent-active' : '' }}" 
                       href="#staffOperationsSubmenu" 
                       data-bs-toggle="collapse" 
                       role="button" 
                       aria-expanded="{{ $isOperationsActive ? 'true' : 'false' }}" 
                       aria-controls="staffOperationsSubmenu">
                        <i class="bi bi-box-seam text-info"></i> <span>Products &amp; Delivery</span>
                    </a>
                    <div class="collapse {{ $isOperationsActive ? 'show' : '' }}" id="staffOperationsSubmenu">
                        <ul class="nav flex-column sidebar-submenu">
                            @if($permissionModel->hasPermission($staffType, 'view_products'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.products') ? 'active' : '' }}" href="{{ route('shop-staff.products') }}">
                                    <i class="bi bi-list-ul"></i> <span>View Products</span>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('shop-staff.delivery-packing') ? 'active' : '' }}" href="{{ route('shop-staff.delivery-packing') }}">
                                    <i class="bi bi-box2"></i> <span>Delivery Packing</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Top Navigation Bar -->
        <nav class="top-bar d-flex justify-content-between align-items-center">
            <!-- Sidebar toggle button -->
            <button id="sidebarToggler" class="btn btn-light d-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <i class="bi bi-text-indent-left fs-4 text-dark" id="togglerIcon"></i>
            </button>

            <!-- Centered Company Name (hidden on small screens) -->
            <div class="flex-grow-1 d-none d-md-flex justify-content-center">
                <h5 class="m-0 fw-bold" style="letter-spacing: -0.02em; color:var(--primary);">Shop Staff Portal</h5>
            </div>

            <!-- Real-time Clock -->
            <div class="d-none d-lg-flex align-items-center gap-4 me-3">
                <button class="btn btn-jg-red rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" 
                        onclick="Livewire.dispatch('openDailyEntryBook')">
                    <i class="bi bi-door-closed fs-5"></i>
                    <span>Close Day</span>
                </button>
                <div id="digitalClock" class="fw-800 font-monospace text-jg-blue px-3 py-2 rounded-3 bg-white border shadow-sm" 
                    style="font-size: 1.5rem; letter-spacing: 0.1em; border-color: var(--primary); min-width: 150px; text-align: center;">
                    00:00:00
                </div>
            </div>

            <!-- Staff dropdown -->
            <div class="dropdown ms-auto">
                <div class="admin-info dropdown-toggle" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="admin-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="admin-name">{{ auth()->user()->name }}</div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person me-2"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            {{ $slot }}
        </main>
    </div>

    <livewire:shop-staff.daily-entry-book />

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <script>
        // Digital Clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('digitalClock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Sidebar toggle
        document.getElementById('sidebarToggler').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const topBar = document.querySelector('.top-bar');
            const mainContent = document.querySelector('.main-content');
            const togglerIcon = document.getElementById('togglerIcon');

            if (window.innerWidth < 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                topBar.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');
                
                if (sidebar.classList.contains('collapsed')) {
                    togglerIcon?.classList.replace('bi-text-indent-left', 'bi-text-indent-right');
                } else {
                    togglerIcon?.classList.replace('bi-text-indent-right', 'bi-text-indent-left');
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
