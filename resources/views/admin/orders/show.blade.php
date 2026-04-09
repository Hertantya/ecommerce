@extends('layouts.admin')
@section('title','Order Detail')
@section('page-title','Order Detail')

@section('content')
<div class="row g-4">
    <!-- ─── Left: order items ──────────────────── -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold">
                    Items in <span class="text-primary">{{ $order->order_code }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Product</th><th class="text-center">Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '(deleted product)' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="fw-bold text-primary">{{ $order->formattedTotal() }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold">Customer Notes</h6>
                <p class="mb-0 text-muted">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- ─── Right: info + status changer ─────────── -->
    <div class="col-lg-4">
        <!-- Order info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Order Information</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Customer</td><td class="fw-semibold">{{ $order->user->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $order->user->email }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $order->user->phone ?? '—' }}</td></tr>
                    <tr>
                        <td class="text-muted">Type</td>
                        <td>
                            <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }}">
                                {{ ucfirst($order->delivery_type) }}
                            </span>
                        </td>
                    </tr>
                    @if($order->delivery_type === 'delivery')
                    <tr><td class="text-muted">Address</td><td>{{ $order->address }}</td></tr>
                    @endif
                    <tr>
                        <td class="text-muted">Placed At</td>
                        <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Status changer -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Update Order Status</h6>

                {{-- Current status badge --}}
                <div class="mb-3">
                    Current: <span class="badge bg-{{ $order->statusColor() }} fs-6">{{ $order->statusLabel() }}</span>
                </div>

                {{-- Visual status pipeline --}}
                <div class="d-flex justify-content-between mb-3" style="font-size:.72rem;text-align:center">
                    @foreach(\App\Models\Order::STATUS_LABELS as $k => $lbl)
                        <div class="flex-fill">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                                bg-{{ $order->status === $k ? 'primary' : 'light' }}
                                border {{ $order->status === $k ? 'border-primary' : '' }}"
                                style="width:32px;height:32px;font-size:.75rem;font-weight:600;
                                       color:{{ $order->status === $k ? '#fff' : '#aaa' }}">
                                {{ $loop->iteration }}
                            </div>
                            <div class="mt-1 text-muted" style="font-size:.68rem">{{ $lbl }}</div>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-fill d-flex align-items-start pt-2">
                                <hr style="width:100%;border-top:2px dashed #dee2e6;margin:12px 0 0">
                            </div>
                        @endif
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            @foreach(\App\Models\Order::STATUS_LABELS as $k => $lbl)
                                <option value="{{ $k }}" {{ $order->status === $k ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100 mt-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
</div>
@endsection
