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
    /** Show checkout form */
    public function checkout()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('customer.cart.index')->with('error', 'Your cart is empty.');
        }

        $total = array_sum(array_column($cart, 'subtotal'));

        return view('customer.checkout', compact('cart', 'total'));
    }

    /**
     * Place the order.
     * We wrap everything in a DB transaction so if anything fails,
     * no partial data is saved.
     */
    public function place(Request $request)
    {
        $request->validate([
            'delivery_type' => 'required|in:takeaway,delivery',
            'address'       => 'required_if:delivery_type,delivery|nullable|string',
            'notes'         => 'nullable|string|max:500',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.dashboard')->with('error', 'Cart is empty.');
        }

        DB::beginTransaction();
        try {
            $total = 0;

            // Re-validate stock for each item (stock may have changed since adding to cart)
            foreach ($cart as $pid => $item) {
                $product = Product::lockForUpdate()->find($pid);
                if (!$product || $product->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->with('error', 'Sorry, "' . $item['name'] . '" is out of stock or insufficient quantity.');
                }
                $total += $item['subtotal'];
            }

            // Generate a unique order code: ORD-YYYYMMDD-XXXXX
            $orderCode = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // Create the order
            $order = Order::create([
                'user_id'       => auth()->id(),
                'order_code'    => $orderCode,
                'delivery_type' => $request->delivery_type,
                'address'       => $request->delivery_type === 'delivery' ? $request->address : null,
                'status'        => 'wait',
                'total'         => $total,
                'notes'         => $request->notes,
            ]);

            // Create order items & deduct stock
            foreach ($cart as $pid => $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $pid,
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);

                // Deduct stock
                Product::where('id', $pid)->decrement('stock', $item['quantity']);
            }

            DB::commit();

            // Clear the cart after successful order
            session()->forget('cart');

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Order placed! Code: ' . $orderCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    /** List customer's own orders */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->latest()->paginate(10);
        return view('customer.orders', compact('orders'));
    }

    /** Show one order detail */
    public function show($id)
    {
        // findOrFail + ensure the order belongs to this customer
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('customer.order-detail', compact('order'));
    }
}
