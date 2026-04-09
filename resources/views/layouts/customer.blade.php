<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Shop') — E-Commerce</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; }
        .navbar-brand { font-weight: 700; font-size: 1.2rem; }
        .cart-badge { font-size: .7rem; }
        .product-card {
            border: none; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            transition: transform .15s, box-shadow .15s;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,.12);
        }
        .product-card .card-img-top {
            height: 180px; object-fit: cover;
            border-radius: 12px 12px 0 0;
        }
        .status-badge { font-size: .78rem; }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
        <i class="bi bi-shop me-1"></i> My Shop
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a href="{{ route('customer.cart.index') }}" class="btn btn-outline-light btn-sm position-relative">
            <i class="bi bi-cart3"></i> Cart
            @php $cartCount = count(session('cart', [])); @endphp
            @if($cartCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-receipt"></i> My Orders
        </a>
        <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</nav>

<div class="container py-4">
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
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
