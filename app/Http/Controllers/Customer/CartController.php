<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * CartController
 *
 * Cart is stored in the PHP session as:
 *   session('cart') = [
 *     product_id => [
 *       'name'     => 'Nasi Goreng',
 *       'price'    => 25000,
 *       'quantity' => 2,
 *       'subtotal' => 50000,
 *     ],
 *     ...
 *   ]
 */
class CartController extends Controller
{
    /** Add a product to the cart (or increase qty) */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty     = (int) $request->quantity;

        // Guard against ordering more than available stock
        if ($qty > $product->stock) {
            return back()->withErrors(['quantity' => 'Not enough stock for ' . $product->name]);
        }

        $cart = session('cart', []);
        $pid  = $product->id;

        if (isset($cart[$pid])) {
            // Already in cart – increase quantity
            $newQty = $cart[$pid]['quantity'] + $qty;
            if ($newQty > $product->stock) $newQty = $product->stock;
            $cart[$pid]['quantity'] = $newQty;
            $cart[$pid]['subtotal'] = $product->price * $newQty;
        } else {
            $cart[$pid] = [
                'name'     => $product->name,
                'price'    => $product->price,
                'quantity' => $qty,
                'subtotal' => $product->price * $qty,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', $product->name . ' added to cart.');
    }

    /** Remove a product from the cart */
    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required']);

        $cart = session('cart', []);
        unset($cart[$request->product_id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    /** Show cart page */
    public function index()
    {
        $cart  = session('cart', []);
        $total = array_sum(array_column($cart, 'subtotal'));

        return view('customer.cart', compact('cart', 'total'));
    }
}
