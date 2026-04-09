@extends('layouts.admin')
@section('title','Daily Report')
@section('page-title','Daily Report')

@section('content')
{{-- Date picker --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-3">
            <label class="fw-semibold mb-0">Select Date:</label>
            <input type="date" name="date" class="form-control" style="width:auto"
                   value="{{ $date }}" max="{{ today()->toDateString() }}">
            <button class="btn btn-primary btn-sm">View Report</button>
            @if($date !== today()->toDateString())
                <a href="{{ route('admin.reports.daily') }}" class="btn btn-outline-secondary btn-sm">Today</a>
            @endif
        </form>
    </div>
</div>

<h5 class="mb-3">
    Report for <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong>
</h5>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ $summary->total_orders ?? 0 }}</div>
            <div class="text-muted small">Total Orders</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">
                Rp {{ number_format($summary->total_revenue ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-muted small">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-info">{{ $summary->delivery_count ?? 0 }}</div>
            <div class="text-muted small"><i class="bi bi-truck me-1"></i>Delivery</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-secondary">{{ $summary->takeaway_count ?? 0 }}</div>
            <div class="text-muted small"><i class="bi bi-bag-check me-1"></i>Takeaway</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top products table --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold">🏆 Top Selling Products</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Product</th><th class="text-center">Qty</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                    @forelse($topProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-center fw-bold">{{ $p->qty_sold }}</td>
                            <td>Rp {{ number_format($p->revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Orders list for the day --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold">Orders on This Day</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:420px;overflow-y:auto">
                    <table class="table table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Code</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr onclick="location.href='{{ route('admin.orders.show', $order->id) }}'" style="cursor:pointer">
                                <td class="fw-semibold small">{{ $order->order_code }}</td>
                                <td class="small">{{ $order->user->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }} small">
                                        {{ ucfirst($order->delivery_type) }}
                                    </span>
                                </td>
                                <td class="small">{{ $order->formattedTotal() }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No orders on this day.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
