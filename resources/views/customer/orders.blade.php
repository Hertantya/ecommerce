@extends('layouts.customer')
@section('title','My Orders')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>My Orders</h4>

@if($orders->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-receipt fs-1 text-muted"></i>
        <p class="text-muted mt-2">You haven't placed any orders yet.</p>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-dark">Browse Menu</a>
    </div>
@else
<div class="row g-3">
    @foreach($orders as $order)
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="fw-bold">{{ $order->order_code }}</div>
                        <div class="text-muted small">{{ $order->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }} me-1">
                            {{ ucfirst($order->delivery_type) }}
                        </span>
                        <span class="badge bg-{{ $order->statusColor() }}">
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-primary">{{ $order->formattedTotal() }}</span>
                    <a href="{{ route('customer.orders.show', $order->id) }}"
                       class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-eye me-1"></i>View Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endif
@endsection
