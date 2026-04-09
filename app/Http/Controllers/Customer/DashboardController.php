<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * CustomerDashboardController
 * Shows the product catalogue.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('stock', '>', 0);

        // Optional: filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products   = $query->latest()->get();
        $categories = Category::orderBy('name')->get();
        $cart       = session('cart', []);

        return view('customer.dashboard', compact('products', 'categories', 'cart'));
    }
}
