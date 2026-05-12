<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.cart.index')->with('error', 'Your cart is empty.');
        }
        $subtotal = array_sum(array_column($cart, 'subtotal'));
        return view('customer.checkout', compact('cart', 'subtotal'));
    }

    public function place(Request $request)
    {
        // ── Validation ────────────────────────────────────────────────
        $request->validate([

            // reservation_at is NOW required for ALL order types
            // because you moved it outside the type selector
            'reservation_at' => [
                'required',
                'date',
                'after:' . now()->addMinutes(30)->toDateTimeString(), // at least 30 min from now
            ],
            // delivery_type only has 2 options now (reservation radio is commented out)
            'delivery_type' => 'required|in:takeaway,delivery',

            // address only required when delivery is chosen
            'address' => 'required_if:delivery_type,delivery|nullable|string|max:500',

            // GPS fields only required when delivery is chosen
            'lat_from'    => 'nullable|numeric',
            'lng_from'    => 'nullable|numeric',
            'lat_to'      => 'required_if:delivery_type,delivery|nullable|numeric',
            'lng_to'      => 'required_if:delivery_type,delivery|nullable|numeric',
            'distance_km' => 'required_if:delivery_type,delivery|nullable|numeric|min:0',

            'notes' => 'nullable|string|max:500',
        ],
        // ── Custom error messages ─────────────────────────────────────
        [
            'reservation_at.required' => 'Please select a booking date and time.',
            'reservation_at.after'    => 'Booking time must be at least 30 minutes from now.',
            'delivery_type.required'  => 'Please choose an order type (Take Away or Delivery).',
            'delivery_type.in'        => 'Invalid order type selected.',
            'address.required_if'     => 'Please enter a delivery address.',
            'lat_to.required_if'      => 'Please pin your delivery destination on the map.',
            'lng_to.required_if'      => 'Please pin your delivery destination on the map.',
            'distance_km.required_if' => 'Distance could not be calculated. Please re-pin your location.',
        ]);

        // ── Cart check ────────────────────────────────────────────────
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.dashboard')->with('error', 'Your cart is empty.');
        }

        // ── Delivery fee (server-side, never trust JS) ─────────────
        $deliveryFee = 0;
        if ($request->delivery_type === 'delivery') {
            $deliveryFee = Order::calcDeliveryFee((float) $request->distance_km);
        }

        // ── Database transaction ──────────────────────────────────────
        DB::beginTransaction();
        try {
            $subtotal = 0;

            // Re-check stock for every cart item
            foreach ($cart as $pid => $item) {
                $product = Product::lockForUpdate()->find($pid);
                if (!$product || $product->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->with('error', 'Sorry, "' . $item['name'] . '" is out of stock or has insufficient quantity.');
                }
                $subtotal += $item['subtotal'];
            }

            // Generate unique order code
            $orderCode = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // Create the order
            // NOTE: delivery_type is 'takeaway' or 'delivery'
            // reservation_at are now always filled regardless of type
            $order = Order::create([
                'user_id'        => auth()->id(),
                'order_code'     => $orderCode,
                'delivery_type'  => $request->delivery_type,
                'address'        => $request->delivery_type === 'delivery' ? $request->address : null,
                'status'         => 'wait',
                'total'          => $subtotal,
                'delivery_fee'   => $deliveryFee,

                // GPS — only filled for delivery
                'lat_from'    => $request->delivery_type === 'delivery' ? $request->lat_from    : null,
                'lng_from'    => $request->delivery_type === 'delivery' ? $request->lng_from    : null,
                'lat_to'      => $request->delivery_type === 'delivery' ? $request->lat_to      : null,
                'lng_to'      => $request->delivery_type === 'delivery' ? $request->lng_to      : null,
                'distance_km' => $request->delivery_type === 'delivery' ? $request->distance_km : null,
                'reservation_at' => $request->reservation_at,
                'notes' => $request->notes,
            ]);

            // Create order items + deduct stock
            foreach ($cart as $pid => $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $pid,
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);
                Product::where('id', $pid)->decrement('stock', $item['quantity']);
            }

            DB::commit();
            session()->forget('cart');

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Order placed! Your booking code is: ' . $orderCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->latest()->paginate(10);
        return view('customer.orders', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        return view('customer.order-detail', compact('order'));
    }
}