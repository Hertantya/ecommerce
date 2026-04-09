@extends('layouts.customer')
@section('title','Cart')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Your Cart</h4>

@if(empty($cart))
    <div class="text-center py-5">
        <i class="bi bi-cart-x fs-1 text-muted"></i>
        <p class="text-muted mt-2">Your cart is empty.</p>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-dark">Browse Menu</a>
    </div>
@else
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($cart as $pid => $item)
                        <tr>
                            <td class="fw-semibold align-middle">{{ $item['name'] }}</td>
                            <td class="text-center align-middle">{{ $item['quantity'] }}</td>
                            <td class="align-middle">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="align-middle fw-bold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            <td class="align-middle">
                                <form method="POST" action="{{ route('customer.cart.remove') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $pid }}">
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
        </a>
    </div>

    {{-- Cart summary --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items ({{ count($cart) }})</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3 fw-bold">
                    <span>Total</span>
                    <span class="text-primary fs-5">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('customer.checkout') }}" class="btn btn-dark w-100 py-2">
                    <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
