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

    /**
     * Place the order.
     * delivery_type: takeaway | delivery | reservation
     *
     * For delivery:
     *   - address, lat_from, lng_from, lat_to, lng_to, distance_km required
     *   - delivery_fee calculated server-side from distance_km
     *
     * For reservation:
     *   - reservation_at (datetime), guests required
     */
    public function place(Request $request)
    {
        $request->validate([
            'delivery_type'  => 'required|in:takeaway,delivery,reservation',
            'address'        => 'required_if:delivery_type,delivery|nullable|string',
            'lat_from'       => 'required_if:delivery_type,delivery|nullable|numeric',
            'lng_from'       => 'required_if:delivery_type,delivery|nullable|numeric',
            'lat_to'         => 'required_if:delivery_type,delivery|nullable|numeric',
            'lng_to'         => 'required_if:delivery_type,delivery|nullable|numeric',
            'distance_km'    => 'required_if:delivery_type,delivery|nullable|numeric|min:0',
            'reservation_at' => 'required_if:delivery_type,reservation|nullable|date|after:now',
            'guests'         => 'required_if:delivery_type,reservation|nullable|integer|min:1|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.dashboard')->with('error', 'Cart is empty.');
        }

        // Calculate delivery fee server-side (never trust client)
        $deliveryFee = 0;
        if ($request->delivery_type === 'delivery') {
            $deliveryFee = Order::calcDeliveryFee((float) $request->distance_km);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;

            foreach ($cart as $pid => $item) {
                $product = Product::lockForUpdate()->find($pid);
                if (!$product || $product->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->with('error', 'Sorry, "' . $item['name'] . '" is out of stock.');
                }
                $subtotal += $item['subtotal'];
            }

            $orderCode = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $order = Order::create([
                'user_id'        => auth()->id(),
                'order_code'     => $orderCode,
                'delivery_type'  => $request->delivery_type,
                'address'        => $request->delivery_type === 'delivery' ? $request->address : null,
                'status'         => 'wait',
                'total'          => $subtotal,
                'delivery_fee'   => $deliveryFee,
                'lat_from'       => $request->lat_from,
                'lng_from'       => $request->lng_from,
                'lat_to'         => $request->lat_to,
                'lng_to'         => $request->lng_to,
                'distance_km'    => $request->distance_km,
                'reservation_at' => $request->delivery_type === 'reservation' ? $request->reservation_at : null,
                'guests'         => $request->delivery_type === 'reservation' ? $request->guests : null,
                'notes'          => $request->notes,
            ]);

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
                ->with('success', 'Order placed! Code: ' . $orderCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order. Please try again.');
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
