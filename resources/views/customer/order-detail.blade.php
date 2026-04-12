@extends('layouts.customer')
@section('title','Order Detail')

@push('styles')
@if($order->delivery_type === 'delivery' && $order->lat_from && $order->lat_to)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#miniMap { width:100%; height:260px; border-radius:10px; border:1px solid #dee2e6; z-index:0; }
.leaflet-routing-container { display:none !important; }
</style>
@endif
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

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
                    $curIdx   = array_search($order->status, $stepKeys);
                @endphp
                @foreach($steps as $k => $lbl)
                    @php $idx = array_search($k, $stepKeys); $passed = $idx <= $curIdx; @endphp
                    <div class="flex-fill">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                            {{ $passed ? 'bg-success' : 'bg-light border' }}"
                            style="width:34px;height:34px;font-size:.8rem;font-weight:700;color:{{ $passed?'#fff':'#bbb' }}">
                            @if($passed)<i class="bi bi-check-lg"></i>@else{{ $loop->iteration }}@endif
                        </div>
                        <div class="mt-1 {{ $passed?'fw-semibold':'text-muted' }}" style="font-size:.7rem">{{ $lbl }}</div>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-fill d-flex align-items-start pt-2">
                            <hr style="width:100%;border-top:2px solid {{ $passed?'#198754':'#dee2e6' }};margin:12px 0 0">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Order info --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6">
                    <div class="text-muted small">Order Type</div>
                    <span class="badge bg-dark fs-6">
                        <i class="bi {{ $order->typeIcon() }} me-1"></i>{{ $order->typeLabel() }}
                    </span>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Placed At</div>
                    <div class="fw-semibold small">{{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                @if($order->delivery_type === 'reservation')
                <div class="col-6">
                    <div class="text-muted small">Reservation Date</div>
                    <div class="fw-semibold">{{ $order->reservation_at?->format('d M Y H:i') }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Guests</div>
                    <div class="fw-semibold">{{ $order->guests }} people</div>
                </div>
                @endif
                @if($order->delivery_type === 'delivery')
                <div class="col-12">
                    <div class="text-muted small">Delivery Address</div>
                    <div class="fw-semibold small">{{ $order->address }}</div>
                </div>
                @if($order->distance_km)
                <div class="col-6">
                    <div class="text-muted small">Distance</div>
                    <div class="fw-semibold">{{ number_format($order->distance_km, 1) }} km</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Delivery Fee</div>
                    <div class="fw-semibold text-primary">{{ $order->formattedDeliveryFee() }}</div>
                </div>
                @endif
                @endif
                @if($order->notes)
                <div class="col-12">
                    <div class="text-muted small">Notes</div>
                    <div class="small fst-italic">{{ $order->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mini map (Leaflet — only for delivery with GPS data) --}}
    @if($order->delivery_type === 'delivery' && $order->lat_from && $order->lat_to)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2"></i>Delivery Route</h6>
        </div>
        <div class="card-body p-3">
            <div id="miniMap"></div>
            <div class="mt-2 d-flex gap-3 small text-muted flex-wrap">
                <span><span class="d-inline-block rounded-circle bg-primary me-1" style="width:10px;height:10px"></span>Your location</span>
                <span><i class="bi bi-geo-alt-fill text-primary me-1"></i>Destination</span>
                @if($order->distance_km)
                <span><i class="bi bi-rulers me-1"></i>{{ number_format($order->distance_km,1) }} km</span>
                @endif
                <span><i class="bi bi-cash me-1"></i>{{ $order->formattedDeliveryFee() }}</span>
            </div>
        </div>
    </div>
    @endif

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
                        <td class="text-primary fs-5">{{ $order->formattedGrandTotal() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

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

    const map = L.map('miniMap').setView(from, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Blue circle — origin
    L.circleMarker(from, {
        radius:10, fillColor:'#1a73e8', fillOpacity:1, color:'#fff', weight:3
    }).addTo(map).bindPopup('📍 Customer location');

    // Red marker — destination
    L.marker(to).addTo(map).bindPopup('🏠 Delivery destination');

    // Draw route via OSRM (free)
    L.Routing.control({
        waypoints: [from, to],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile:    'driving',
        }),
        lineOptions: {
            styles: [{ color:'#0d6efd', weight:4, opacity:.8 }]
        },
        addWaypoints:      false,
        draggableWaypoints:false,
        fitSelectedRoutes:  true,
        showAlternatives:   false,
        createMarker:       () => null,
    }).addTo(map);
});
</script>
@endpush
@endif
