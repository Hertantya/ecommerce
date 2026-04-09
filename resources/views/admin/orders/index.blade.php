@extends('layouts.admin')
@section('title','Orders')
@section('page-title','All Orders')

@section('content')
{{-- Filter bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label mb-0 fw-semibold">Filter by Status:</label>
            <a href="{{ route('admin.orders.index') }}"
               class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('admin.orders.index', ['status' => $key]) }}"
                   class="btn btn-sm {{ request('status') === $key ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th><th>Customer</th><th>Type</th>
                        <th>Total</th><th>Status</th><th>Date</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->order_code }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>
                            <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }}">
                                <i class="bi bi-{{ $order->delivery_type === 'delivery' ? 'truck' : 'bag-check' }} me-1"></i>
                                {{ ucfirst($order->delivery_type) }}
                            </span>
                        </td>
                        <td>{{ $order->formattedTotal() }}</td>
                        <td>
                            <span class="badge bg-{{ $order->statusColor() }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
