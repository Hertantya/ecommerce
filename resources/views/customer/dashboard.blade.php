@extends('layouts.customer')
@section('title','Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Our Menu</h4>
    {{-- Category filter chips --}}
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('customer.dashboard') }}"
           class="btn btn-sm {{ !request('category') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
        @foreach($categories as $cat)
            <a href="{{ route('customer.dashboard', ['category' => $cat->id]) }}"
               class="btn btn-sm {{ request('category') == $cat->id ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>
</div>

@if($products->isEmpty())
    <div class="alert alert-info">No products available at the moment.</div>
@else
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
    @foreach($products as $product)
    <div class="col">
        <div class="card product-card h-100">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}"
                     class="card-img-top" alt="{{ $product->name }}">
            @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                     style="height:180px">
                    <i class="bi bi-image text-secondary fs-1"></i>
                </div>
            @endif
            <div class="card-body d-flex flex-column pb-2">
                <div class="text-muted small mb-1">{{ $product->category->name ?? '' }}</div>
                <h6 class="card-title fw-bold mb-1">{{ $product->name }}</h6>
                @if($product->description)
                    <p class="text-muted small mb-2" style="font-size:.8rem">
                        {{ Str::limit($product->description, 60) }}
                    </p>
                @endif
                <div class="mt-auto">
                    <div class="fw-bold text-primary mb-2">{{ $product->formattedPrice() }}</div>
                    <div class="text-muted small mb-2">
                        Stock: <span class="fw-semibold {{ $product->stock < 5 ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock }}
                        </span>
                    </div>

                    {{-- Add to cart form --}}
                    <form method="POST" action="{{ route('customer.cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="input-group input-group-sm">
                            <input type="number" name="quantity" class="form-control text-center"
                                   min="1" max="{{ $product->stock }}" value="1"
                                   style="max-width:60px">
                            <button class="btn btn-dark btn-sm">
                                <i class="bi bi-cart-plus"></i> Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
