@extends('layouts.customer')
@section('title','Order Detail')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Order: {{ $order->order_code }}</h5>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Status tracker --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between" style="font-size:.75rem;text-align:center">
                @php
                    $steps    = \App\Models\Order::STATUS_LABELS;
                    $stepKeys = array_keys($steps);
                    $currentIndex = array_search($order->status, $stepKeys);
                @endphp
                @foreach($steps as $k => $lbl)
                    @php $idx = array_search($k, $stepKeys); $passed = $idx <= $currentIndex; @endphp
                    <div class="flex-fill">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                            {{ $passed ? 'bg-success' : 'bg-light border' }}"
                            style="width:34px;height:34px;font-size:.8rem;font-weight:700;
                                   color:{{ $passed ? '#fff' : '#bbb' }}">
                            @if($passed) <i class="bi bi-check-lg"></i>
                            @else {{ $loop->iteration }}
                            @endif
                        </div>
                        <div class="mt-1 {{ $passed ? 'fw-semibold' : 'text-muted' }}"
                             style="font-size:.7rem">{{ $lbl }}</div>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-fill d-flex align-items-start pt-2">
                            <hr style="width:100%;border-top:2px solid {{ $passed ? '#198754' : '#dee2e6' }};margin:12px 0 0">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Order info --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row text-sm g-2">
                <div class="col-6">
                    <div class="text-muted small">Delivery Method</div>
                    <span class="badge bg-{{ $order->delivery_type === 'delivery' ? 'info' : 'secondary' }}">
                        {{ ucfirst($order->delivery_type) }}
                    </span>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Placed At</div>
                    <div class="fw-semibold small">{{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                @if($order->delivery_type === 'delivery')
                <div class="col-12 mt-1">
                    <div class="text-muted small">Delivery Address</div>
                    <div class="fw-semibold small">{{ $order->address }}</div>
                </div>
                @endif
                @if($order->notes)
                <div class="col-12 mt-1">
                    <div class="text-muted small">Notes</div>
                    <div class="small fst-italic">{{ $order->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-semibold">Items Ordered</h6>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th class="text-center">Qty</th><th>Price</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '(deleted)' }}</td>
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

</div>
</div>
@endsection
