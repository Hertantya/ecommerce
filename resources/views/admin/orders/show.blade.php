@extends('layouts.admin')
@section('title','Order Detail')
@section('page-title','Order Detail')

@push('styles')
@if($order->delivery_type === 'delivery' && $order->lat_from && $order->lat_to)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#adminMap { width:100%; height:280px; border-radius:10px; border:1px solid #dee2e6; z-index:0; }
.leaflet-routing-container { display:none !important; }
</style>
@endif
@endpush

@section('content')
<div class="row g-4">

    {{-- ── Left: items + map ──────────────────────────── --}}
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
                            <td>{{ $item->product->name ?? '(deleted)' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->price,    0,',','.') }}</td>
                            <td>Rp {{ number_format($item->subtotal, 0,',','.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end">Items Subtotal</td>
                            <td>{{ $order->formattedTotal() }}</td>
                        </tr>
                        @if($order->delivery_fee > 0)
                        <tr>
                            <td colspan="3" class="text-end">Delivery Fee
                                @if($order->distance_km)
                                    <span class="text-muted small">({{ number_format($order->distance_km,1) }} km)</span>
                                @endif
                            </td>
                            <td class="text-primary">{{ $order->formattedDeliveryFee() }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Grand Total</td>
                            <td class="text-primary">{{ $order->formattedGrandTotal() }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Leaflet delivery map --}}
        @if($order->delivery_type === 'delivery' && $order->lat_from && $order->lat_to)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2"></i>Delivery Route (OpenStreetMap)</h6>
            </div>
            <div class="card-body p-3">
                <div id="adminMap"></div>
                <div class="mt-2 d-flex gap-3 small text-muted flex-wrap">
                    <span><span class="d-inline-block rounded-circle bg-primary me-1" style="width:10px;height:10px;vertical-align:middle"></span>Customer</span>
                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i>Destination</span>
                    @if($order->distance_km)
                    <span><i class="bi bi-rulers me-1"></i>{{ number_format($order->distance_km,1) }} km</span>
                    @endif
                    <span><i class="bi bi-cash me-1"></i>{{ $order->formattedDeliveryFee() }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Reservation card --}}
        @if($order->delivery_type === 'reservation')
        <div class="card border-0 shadow-sm border-warning mb-3">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-check me-2"></i>Reservation Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Date & Time</div>
                        <div class="fw-bold fs-5">{{ $order->reservation_at?->format('d M Y') }}</div>
                        <div class="fw-semibold text-primary">{{ $order->reservation_at?->format('H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($order->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold">Customer Notes</h6>
                <p class="mb-0 text-muted">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Right: info + status changer ────────────────── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Order Information</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Customer</td><td class="fw-semibold">{{ $order->user->name }}</td></tr>
                    <tr><td class="text-muted">Email</td>   <td>{{ $order->user->email }}</td></tr>
                    <tr><td class="text-muted">Phone</td>   <td>{{ $order->user->phone ?? '—' }}</td></tr>
                    <tr>
                        <td class="text-muted">Type</td>
                        <td><span class="badge bg-dark">
                            <i class="bi {{ $order->typeIcon() }} me-1"></i>{{ $order->typeLabel() }}
                        </span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment</td>
                        <td>
                            @if($order->payment_method === 'qris_driver')
                                <span class="badge bg-primary">
                                    <i class="bi bi-qr-code me-1"></i>QRIS
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-cash-coin me-1"></i>Cash
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($order->delivery_type === 'delivery' && $order->address)
                    <tr><td class="text-muted">Address</td><td class="small">{{ $order->address }}</td></tr>
                    @endif
                    @if($order->delivery_type === 'reservation')
                    <tr><td class="text-muted">Reserved</td><td>{{ $order->reservation_at?->format('d M Y H:i') }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Placed</td>  <td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Update Order Status</h6>
                <div class="mb-3">
                    Current: <span class="badge bg-{{ $order->statusColor() }} fs-6">{{ $order->statusLabel() }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3" style="font-size:.72rem;text-align:center">
                    @foreach(\App\Models\Order::STATUS_LABELS as $k => $lbl)
                        <div class="flex-fill">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                                bg-{{ $order->status === $k ? 'primary' : 'light' }} border {{ $order->status === $k ? 'border-primary' : '' }}"
                                style="width:32px;height:32px;font-size:.75rem;font-weight:600;color:{{ $order->status===$k?'#fff':'#aaa' }}">
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
                    <select name="status" class="form-select mb-3">
                        @foreach(\App\Models\Order::STATUS_LABELS as $k => $lbl)
                            <option value="{{ $k }}" {{ $order->status===$k?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
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

@if($order->delivery_type === 'delivery' && $order->lat_from && $order->lat_to)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const from = L.latLng({{ $order->lat_from }}, {{ $order->lng_from }});
    const to   = L.latLng({{ $order->lat_to }},   {{ $order->lng_to }});

    const map = L.map('adminMap').setView(from, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    L.circleMarker(from, {
        radius:10, fillColor:'#1a73e8', fillOpacity:1, color:'#fff', weight:3
    }).addTo(map).bindPopup('📍 Customer');

    L.marker(to).addTo(map).bindPopup('🏠 Delivery destination');

    L.Routing.control({
        waypoints: [from, to],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'driving',
        }),
        lineOptions: { styles: [{ color:'#0d6efd', weight:4, opacity:.8 }] },
        addWaypoints: false, draggableWaypoints: false,
        fitSelectedRoutes: true, showAlternatives: false,
        createMarker: () => null,
    }).addTo(map);
});
</script>
@endpush
@endif
