@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- ─── Summary cards ─────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-receipt fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Orders Today</div>
                    <div class="fs-3 fw-bold">{{ $todayOrders }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending Orders</div>
                    <div class="fs-3 fw-bold">{{ $pendingOrders }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-box-seam fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-3 fw-bold">{{ $totalProducts }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Customers</div>
                    <div class="fs-3 fw-bold">{{ $totalCustomers }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── Recent orders table ─────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">Recent Orders</h6>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order Code</th><th>Customer</th><th>Type</th>
                        <th>Total</th><th>Status</th><th>Time</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr style="cursor:pointer" onclick="location.href='{{ route('admin.orders.show', $order->id) }}'">
                        <td class="fw-semibold">{{ $order->order_code }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>
                            <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }}">
                                {{ ucfirst($order->delivery_type) }}
                            </span>
                        </td>
                        <td>{{ $order->formattedTotal() }}</td>
                        <td>
                            <span class="badge bg-{{ $order->statusColor() }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
