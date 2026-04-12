@extends('layouts.customer')
@section('title','Checkout')

@push('styles')
{{-- Leaflet CSS (free, no API key needed) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"/>
<style>
.type-card {
    border: 2px solid #dee2e6; border-radius: 14px;
    cursor: pointer; padding: 18px 12px; text-align: center;
    transition: border-color .2s, background .2s, transform .15s;
    user-select: none;
}
.type-card:hover         { border-color:#adb5bd; transform:translateY(-2px); }
.type-card.selected      { border-color:#212529; background:#f8f9fa; }
.type-card .type-icon    { font-size:2rem; margin-bottom:6px; }
.type-card .type-label   { font-weight:700; font-size:.95rem; }
.type-card .type-sub     { font-size:.75rem; color:#6c757d; margin-top:2px; }

#mapCanvas {
    width:100%; height:380px;
    border-radius:12px; border:1px solid #dee2e6;
    z-index:0;                  /* keep below Bootstrap modals */
}
#routeInfo {
    background:#f0f4ff; border:1px solid #c7d6f7;
    border-radius:10px; padding:10px 16px; display:none;
}
.section-label {
    font-weight:700; font-size:.85rem;
    text-transform:uppercase; letter-spacing:.05em;
    color:#6c757d; margin-bottom:10px;
}
/* Hide the default Leaflet Routing Machine panel — we show our own */
.leaflet-routing-container { display:none !important; }
</style>
@endpush

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-bag-check me-2"></i>Checkout</h4>

<form method="POST" action="{{ route('customer.order.place') }}" id="checkoutForm">
@csrf
<div class="row g-4">

{{-- ════════════════ LEFT — options ════════════════ --}}
<div class="col-lg-7">
<div class="card border-0 shadow-sm"><div class="card-body p-4">

    {{-- Step 1: order type --}}
    <div class="section-label">Step 1 — Choose Order Type</div>
    <div class="row g-2 mb-4" id="typeSelector">
        <div class="col-4">
            <label style="display:block">
                <input type="radio" name="delivery_type" value="takeaway"
                       class="d-none type-radio"
                       {{ old('delivery_type','takeaway')==='takeaway'?'checked':'' }}>
                <div class="type-card {{ old('delivery_type','takeaway')==='takeaway'?'selected':'' }}">
                    <div class="type-icon">🛍️</div>
                    <div class="type-label">Take Away</div>
                    <div class="type-sub">Pick up at store</div>
                </div>
            </label>
        </div>
        <div class="col-4">
            <label style="display:block">
                <input type="radio" name="delivery_type" value="delivery"
                       class="d-none type-radio"
                       {{ old('delivery_type')==='delivery'?'checked':'' }}>
                <div class="type-card {{ old('delivery_type')==='delivery'?'selected':'' }}">
                    <div class="type-icon">🚚</div>
                    <div class="type-label">Delivery</div>
                    <div class="type-sub">Send to address</div>
                </div>
            </label>
        </div>
        <div class="col-4">
            <label style="display:block">
                <input type="radio" name="delivery_type" value="reservation"
                       class="d-none type-radio"
                       {{ old('delivery_type')==='reservation'?'checked':'' }}>
                <div class="type-card {{ old('delivery_type')==='reservation'?'selected':'' }}">
                    <div class="type-icon">📅</div>
                    <div class="type-label">Reservation</div>
                    <div class="type-sub">Book a table</div>
                </div>
            </label>
        </div>
    </div>

    {{-- ── Delivery section ────────────────────────────── --}}
    <div id="deliverySection" style="display:none">
        <div class="section-label">Step 2 — Set Delivery Location</div>

        {{-- GPS status bar --}}
        <div id="gpsStatus" class="alert alert-info py-2 small mb-3">
            <i class="bi bi-geo-alt-fill me-1"></i>
            <span id="gpsText">Getting your GPS location…</span>
        </div>

        {{-- Map --}}
        <div id="mapCanvas" class="mb-2"></div>
        {{-- Route info bar --}}
        <div id="routeInfo" class="mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-rulers me-1 text-primary"></i>
                    Distance: <strong id="distanceText">—</strong>
                </div>
                <div>
                    <i class="bi bi-currency-exchange me-1 text-success"></i>
                    Delivery Fee: <strong id="feeDisplay" class="text-primary">—</strong>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Rp 3,000 / 3 km
                </div>
            </div>
        </div>

        {{-- Address text --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Delivery Address Detail</label>
            <textarea name="address" id="addressInput" class="form-control" rows="2"
                      placeholder="e.g. Jl. Sudirman No. 5, Lt. 3">{{ old('address', auth()->user()->address) }}</textarea>
        </div>

        {{-- Hidden GPS + distance fields --}}
        <input type="hidden" name="lat_from"    id="latFrom">
        <input type="hidden" name="lng_from"    id="lngFrom">
        <input type="hidden" name="lat_to"      id="latTo">
        <input type="hidden" name="lng_to"      id="lngTo">
        <input type="hidden" name="distance_km" id="distanceKm">
    </div>

    {{-- ── Reservation section ─────────────────────────── --}}
    <div id="reservationSection" style="display:none">
        <div class="section-label">Step 2 — Reservation Details</div>
        <div class="row g-3 mb-3">
            <div class="col-md-7">
                <label class="form-label fw-semibold">
                    Date &amp; Time <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" name="reservation_at" id="reservationAt"
                       class="form-control"
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                       value="{{ old('reservation_at') }}">
                <div class="form-text">Minimum 1 hour from now.</div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">
                    Number of Guests <span class="text-danger">*</span>
                </label>
                <input type="number" name="guests" id="guestsInput"
                       class="form-control" min="1" max="50"
                       value="{{ old('guests', 2) }}">
            </div>
        </div>
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-clock me-1"></i>
            Please arrive 10 minutes before your scheduled time.
        </div>
    </div>

    {{-- Notes --}}
    <div class="section-label mt-3">Additional Notes</div>
    <textarea name="notes" class="form-control mb-4" rows="2"
              placeholder="Allergies, special requests…">{{ old('notes') }}</textarea>

    <button type="submit" class="btn btn-dark w-100 py-2 fs-5" id="submitBtn">
        <i class="bi bi-check-circle me-2"></i>Place Order
    </button>

</div></div>
</div><!-- col-lg-7 -->

{{-- ════════════════ RIGHT — summary ════════════════ --}}
<div class="col-lg-5">
<div class="card border-0 shadow-sm" style="position:sticky;top:80px">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold">Order Summary</h6>
    </div>
    <div class="card-body">
        @foreach($cart as $item)
        <div class="d-flex justify-content-between mb-2">
            <span>{{ $item['name'] }} <span class="text-muted">×{{ $item['quantity'] }}</span></span>
            <span>Rp {{ number_format($item['subtotal'],0,',','.') }}</span>
        </div>
        @endforeach
        <hr>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Subtotal</span>
            <span>Rp {{ number_format($subtotal,0,',','.') }}</span>
        </div>
        <div class="d-flex justify-content-between mb-2" id="feeRow" style="display:none!important">
            <span class="text-muted">Delivery Fee</span>
            <span id="feeSummary" class="text-primary fw-semibold">—</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Grand Total</span>
            <span class="text-primary" id="grandTotal">
                Rp {{ number_format($subtotal,0,',','.') }}
            </span>
        </div>
        <div class="text-muted small mt-1" id="feeNote" style="display:none">
            * includes delivery fee
        </div>
    </div>
</div>
<a href="{{ route('customer.cart.index') }}" class="btn btn-outline-secondary w-100 mt-3">
    <i class="bi bi-arrow-left me-1"></i> Back to Cart
</a>
</div>

</div><!-- row -->
</form>
@endsection

@push('scripts')
{{-- ══════════════════════════════════════════════════════════
     Leaflet JS  +  Leaflet Routing Machine
     100% FREE — uses OpenStreetMap tiles + OSRM routing
     No API key required at all!
══════════════════════════════════════════════════════════ --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

<script>
// ── Constants ────────────────────────────────────────────
const SUBTOTAL      = {{ $subtotal }};
const FEE_PER_BLOCK = 3000;
const KM_PER_BLOCK  = 3;

// ── Globals ──────────────────────────────────────────────
let map           = null;
let routeControl  = null;   // Leaflet Routing Machine instance
let markerFrom    = null;   // blue dot — user GPS
let markerTo      = null;   // red pin — destination (draggable)
let currentLat    = null;
let currentLng    = null;

// ── Helpers ──────────────────────────────────────────────
const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function calcFee(km) {
    if (km <= 0) return 0;
    return Math.ceil(km / KM_PER_BLOCK) * FEE_PER_BLOCK;
}

function updateSummary(fee) {
    const grand = SUBTOTAL + fee;
    document.getElementById('feeSummary').textContent = rupiah(fee);
    document.getElementById('grandTotal').textContent  = rupiah(grand);
    document.getElementById('feeRow').style.display    = fee > 0 ? '' : 'none';
    document.getElementById('feeNote').style.display   = fee > 0 ? '' : 'none';
}

// ── Draw / update the route using OSRM (free routing engine) ─
function drawRoute(fromLat, fromLng, toLat, toLng) {
    const from = L.latLng(fromLat, fromLng);
    const to   = L.latLng(toLat,   toLng);

    if (routeControl) {
        // Update existing route
        routeControl.setWaypoints([from, to]);
    } else {
        // Create first-time route
        routeControl = L.Routing.control({
            waypoints: [from, to],
            // OSRM public demo server — free, no key needed
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile:    'driving',
            }),
            lineOptions: {
                styles: [{ color: '#0d6efd', weight: 5, opacity: .8 }]
            },
            addWaypoints:      false,   // prevent adding extra stops
            draggableWaypoints:false,
            fitSelectedRoutes:  true,
            showAlternatives:   false,
            createMarker: () => null,   // suppress default markers
        }).addTo(map);

        // Called every time OSRM returns a result
        routeControl.on('routesfound', function(e) {
            const route   = e.routes[0];
            const distM   = route.summary.totalDistance;   // metres
            const distKm  = distM / 1000;
            const fee     = calcFee(distKm);

            // Show route info bar
            document.getElementById('routeInfo').style.display = '';
            document.getElementById('distanceText').textContent =
                distKm >= 1
                    ? distKm.toFixed(1) + ' km'
                    : Math.round(distM) + ' m';
            document.getElementById('feeDisplay').textContent = rupiah(fee);

            // Save to hidden inputs
            document.getElementById('distanceKm').value = distKm.toFixed(3);

            updateSummary(fee);
        });

        routeControl.on('routingerror', function() {
            document.getElementById('distanceText').textContent = 'Route not found';
            document.getElementById('feeDisplay').textContent   = '—';
        });
    }
}

// ── Place / move destination pin ─────────────────────────
function setDestination(lat, lng) {
    document.getElementById('latTo').value = lat;
    document.getElementById('lngTo').value = lng;

    if (markerTo) {
        markerTo.setLatLng([lat, lng]);
    } else {
        markerTo = L.marker([lat, lng], {
            draggable: true,
            title:     'Delivery destination',
            // Custom red icon using Leaflet's built-in icon factory
            icon: L.icon({
                iconUrl:    'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                iconSize:   [25, 41],
                iconAnchor: [12, 41],
                className:  '',   // default blue — we tint via CSS below
            })
        }).addTo(map);

        // Allow dragging the pin to fine-tune
        markerTo.on('dragend', function() {
            const p = markerTo.getLatLng();
            document.getElementById('latTo').value = p.lat;
            document.getElementById('lngTo').value = p.lng;
            if (currentLat) drawRoute(currentLat, currentLng, p.lat, p.lng);
        });
    }

    if (currentLat) drawRoute(currentLat, currentLng, lat, lng);
}

// ── Initialise map (called when delivery section is shown) ─
function initMap() {
    if (map) return;   // already initialised

    // Default centre: Jakarta
    map = L.map('mapCanvas').setView([-6.2088, 106.8456], 13);

    // OpenStreetMap tiles — completely free
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Click on map → set destination pin
    map.on('click', function(e) {
        setDestination(e.latlng.lat, e.latlng.lng);
    });

    // Get GPS location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                currentLat = pos.coords.latitude;
                currentLng = pos.coords.longitude;

                document.getElementById('latFrom').value = currentLat;
                document.getElementById('lngFrom').value = currentLng;

                // Move map to user
                map.setView([currentLat, currentLng], 15);

                // Blue circle for "from"
                markerFrom = L.circleMarker([currentLat, currentLng], {
                    radius:      10,
                    fillColor:   '#1a73e8',
                    fillOpacity: 1,
                    color:       '#fff',
                    weight:      3,
                }).addTo(map).bindPopup('Your location').openPopup();

                document.getElementById('gpsStatus').className = 'alert alert-success py-2 small mb-3';
                document.getElementById('gpsText').textContent  =
                    '✅ GPS detected (' + currentLat.toFixed(5) + ', ' + currentLng.toFixed(5) + '). Click the map to set your delivery destination.';
            },
            function() {
                document.getElementById('gpsStatus').className = 'alert alert-warning py-2 small mb-3';
                document.getElementById('gpsText').textContent  =
                    '⚠️ GPS not available. Please allow location access. You can still pin on the map manually.';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
}

// ══════════════════════════════════════════════════════════
//  UI switching — show/hide sections on delivery type change
// ══════════════════════════════════════════════════════════
const radios    = document.querySelectorAll('.type-radio');
const typeCards = document.querySelectorAll('.type-card');
const delivSec  = document.getElementById('deliverySection');
const resvSec   = document.getElementById('reservationSection');

function switchType(val) {
    radios.forEach((r, i) => typeCards[i].classList.toggle('selected', r.value === val));

    delivSec.style.display = val === 'delivery'    ? '' : 'none';
    resvSec.style.display  = val === 'reservation' ? '' : 'none';

    if (val !== 'delivery') {
        updateSummary(0);
        document.getElementById('routeInfo').style.display = 'none';
    }

    document.getElementById('reservationAt').required = (val === 'reservation');
    document.getElementById('guestsInput').required   = (val === 'reservation');

    // Init map lazily only when delivery is chosen
    if (val === 'delivery') {
        // Small delay so the div is visible before Leaflet measures it
        setTimeout(() => { initMap(); map && map.invalidateSize(); }, 150);
    }
}

radios.forEach(r => r.addEventListener('change', () => switchType(r.value)));

const checkedRadio = document.querySelector('.type-radio:checked');
if (checkedRadio) switchType(checkedRadio.value);

// ── Validate before submit ───────────────────────────────
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const type = document.querySelector('.type-radio:checked')?.value;
    if (type === 'delivery') {
        if (!document.getElementById('latTo').value) {
            e.preventDefault();
            alert('Please click on the map to pin your delivery destination.');
        }
    }
});
</script>
@endpush
