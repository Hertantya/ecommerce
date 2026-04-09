<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — E-Commerce</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f6f9; }
        .sidebar {
            width: 240px; min-height: 100vh;
            background: #1e2a3a; position: fixed; top: 0; left: 0;
            z-index: 100; padding-top: 0;
        }
        .sidebar .brand {
            padding: 20px 18px 16px;
            font-size: 1.1rem; font-weight: 700;
            color: #fff; border-bottom: 1px solid #2e3f54;
        }
        .sidebar .nav-link {
            color: #a8b4c0; padding: 10px 18px;
            font-size: .9rem; border-radius: 0;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #2e3f54; color: #fff;
        }
        .sidebar .nav-section {
            color: #5a7a9a; font-size: .72rem;
            text-transform: uppercase; letter-spacing: .06em;
            padding: 14px 18px 4px;
        }
        .main-content { margin-left: 240px; padding: 0; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e0e6ed;
            padding: 12px 24px; display: flex;
            align-items: center; justify-content: space-between;
        }
        .page-body { padding: 24px; }
    </style>
    @stack('styles')
</head>
<body>

<!-- ─── Sidebar ─────────────────────────────────── -->
<aside class="sidebar">
    <div class="brand"><i class="bi bi-shop me-2"></i>Admin Panel</div>
    <nav class="mt-2">
        <div class="nav-section">Main</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link @active('admin/dashboard')">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Catalogue</div>
        <a href="{{ route('admin.categories.index') }}"
           class="nav-link @active('admin/categories*')">
            <i class="bi bi-tags"></i> Categories
        </a>
        <a href="{{ route('admin.products.index') }}"
           class="nav-link @active('admin/products*')">
            <i class="bi bi-box-seam"></i> Products
        </a>

        <div class="nav-section">Sales</div>
        <a href="{{ route('admin.orders.index') }}"
           class="nav-link @active('admin/orders*')">
            <i class="bi bi-receipt"></i> Orders
        </a>
        <a href="{{ route('admin.customers.index') }}"
           class="nav-link @active('admin/customers*')">
            <i class="bi bi-people"></i> Customers
        </a>

        <div class="nav-section">Analytics</div>
        <a href="{{ route('admin.reports.daily') }}"
           class="nav-link @active('admin/reports*')">
            <i class="bi bi-bar-chart-line"></i> Daily Report
        </a>
    </nav>
</aside>

<!-- ─── Main content ─────────────────────────────── -->
<div class="main-content">
    <div class="topbar">
        <span class="fw-semibold">@yield('page-title', 'Dashboard')</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="page-body">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
