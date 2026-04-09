@extends('layouts.customer')
@section('title','Checkout')

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-bag-check me-2"></i>Checkout</h4>

<div class="row g-4">
    {{-- Left: order form --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('customer.order.place') }}" id="checkoutForm">
                    @csrf

                    {{-- ── Delivery type toggle ────────────── --}}
                    <h6 class="fw-semibold mb-3">Delivery Method</h6>
                    <div class="d-flex gap-3 mb-4">
                        {{-- Takeaway --}}
                        <label class="flex-fill">
                            <input type="radio" name="delivery_type" value="takeaway" class="d-none delivery-radio"
                                   {{ old('delivery_type', 'takeaway') === 'takeaway' ? 'checked' : '' }}>
                            <div class="card border-2 text-center p-3 delivery-option
                                        {{ old('delivery_type', 'takeaway') === 'takeaway' ? 'border-dark' : '' }}"
                                 style="cursor:pointer;border-radius:12px">
                                <i class="bi bi-bag-check fs-2 mb-1"></i>
                                <div class="fw-semibold">Take Away</div>
                                <div class="text-muted small">Pick up in store</div>
                            </div>
                        </label>

                        {{-- Delivery --}}
                        <label class="flex-fill">
                            <input type="radio" name="delivery_type" value="delivery" class="d-none delivery-radio"
                                   {{ old('delivery_type') === 'delivery' ? 'checked' : '' }}>
                            <div class="card border-2 text-center p-3 delivery-option
                                        {{ old('delivery_type') === 'delivery' ? 'border-dark' : '' }}"
                                 style="cursor:pointer;border-radius:12px">
                                <i class="bi bi-truck fs-2 mb-1"></i>
                                <div class="fw-semibold">Delivery</div>
                                <div class="text-muted small">Send to address</div>
                            </div>
                        </label>
                    </div>

                    {{-- ── Address (shown only for delivery) ── --}}
                    <div id="addressBlock" style="{{ old('delivery_type') === 'delivery' ? '' : 'display:none' }}">
                        <h6 class="fw-semibold mb-2">Delivery Address <span class="text-danger">*</span></h6>
                        <textarea name="address" class="form-control mb-3" rows="3"
                                  placeholder="Enter your full delivery address">{{ old('address', auth()->user()->address) }}</textarea>
                    </div>

                    {{-- ── Notes ────────────────────────────── --}}
                    <h6 class="fw-semibold mb-2">Notes <span class="text-muted fw-normal">(optional)</span></h6>
                    <textarea name="notes" class="form-control mb-4" rows="2"
                              placeholder="Special requests, allergies, etc.">{{ old('notes') }}</textarea>

                    <button class="btn btn-dark w-100 py-2 fs-5">
                        <i class="bi bi-check-circle me-2"></i>Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: order summary --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-semibold">Order Summary</h6>
            </div>
            <div class="card-body">
                @foreach($cart as $item)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $item['name'] }} <span class="text-muted">×{{ $item['quantity'] }}</span></span>
                    <span>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <a href="{{ route('customer.cart.index') }}" class="btn btn-outline-secondary w-100 mt-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Cart
        </a>
    </div>
</div>

@push('scripts')
<script>
    // Toggle address field based on delivery type selection
    const radios   = document.querySelectorAll('.delivery-radio');
    const options  = document.querySelectorAll('.delivery-option');
    const addrBlock = document.getElementById('addressBlock');

    radios.forEach((radio, i) => {
        radio.addEventListener('change', () => {
            // Visual: highlight selected card
            options.forEach(o => o.classList.remove('border-dark'));
            options[i].classList.add('border-dark');

            // Show/hide address textarea
            addrBlock.style.display = radio.value === 'delivery' ? '' : 'none';
        });
    });
</script>
@endpush
@endsection
