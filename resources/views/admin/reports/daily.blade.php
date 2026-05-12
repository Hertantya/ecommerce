@extends('layouts.admin')
@section('title','Daily Report')
@section('page-title','Daily Report')

@section('content')
{{-- Date picker --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
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

<h5 class="mb-3">Report for <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong></h5>

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
            <div class="fs-2 fw-bold text-success" style="font-size:1.3rem!important">
                Rp {{ number_format($summary->grand_revenue ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-muted small">Grand Revenue (incl. fees)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">
                Rp {{ number_format($summary->total_delivery_fees ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-muted small">Delivery Fees Collected</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-info">
                {{ number_format($summary->avg_distance ?? 0, 1) }} km
            </div>
            <div class="text-muted small">Avg. Delivery Distance</div>
        </div>
    </div>
</div>

{{-- Order type breakdown --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold">🛍️ {{ $summary->takeaway_count ?? 0 }}</div>
            <div class="text-muted small">Take Away</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold">🚚 {{ $summary->delivery_count ?? 0 }}</div>
            <div class="text-muted small">Delivery</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold">📅 {{ $summary->reservation_count ?? 0 }}</div>
            <div class="text-muted small">Reservation</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Top products --}}
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

    {{-- Orders list --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold">Orders on This Day</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:380px;overflow-y:auto">
                    <table class="table table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Code</th><th>Customer</th><th>Type</th><th>Grand Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr onclick="location.href='{{ route('admin.orders.show', $order->id) }}'" style="cursor:pointer">
                                <td class="fw-semibold small">{{ $order->order_code }}</td>
                                <td class="small">{{ $order->user->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : ($order->delivery_type === 'reservation' ? 'warning' : 'secondary') }} small">
                                        <i class="bi {{ $order->typeIcon() }} me-1"></i>{{ $order->typeLabel() }}
                                    </span>
                                </td>
                                <td class="small fw-semibold">{{ $order->formattedGrandTotal() }}</td>
                                <td><span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
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

{{-- Upcoming reservations (next 7 days) --}}
@if($upcomingReservations->isNotEmpty())
<div class="card border-0 shadow-sm border-warning">
    <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
        <h6 class="mb-0 fw-semibold">📅 Upcoming Reservations (Next 7 Days)</h6>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>Code</th><th>Customer</th><th>Date & Time</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($upcomingReservations as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->order_code }}</td>
                    <td>{{ $r->user->name }}</td>
                    <td>
                        <strong>{{ $r->reservation_at->format('d M Y') }}</strong>
                        <span class="text-primary ms-1">{{ $r->reservation_at->format('H:i') }}</span>
                    </td>
                    <td><span class="badge bg-{{ $r->statusColor() }}">{{ $r->statusLabel() }}</span></td>
                    <td>
                        <a href="{{ route('admin.orders.show', $r->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
