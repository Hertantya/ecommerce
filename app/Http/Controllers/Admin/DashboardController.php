<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

/**
 * AdminDashboardController
 * Shows summary cards: total orders today, pending orders, total products, total customers.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $todayOrders    = Order::whereDate('created_at', today())->count();
        $pendingOrders  = Order::whereIn('status', ['wait','processing'])->count();
        $totalProducts  = Product::count();
        $totalCustomers = User::where('role','customer')->count();
        $recentOrders   = Order::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'todayOrders', 'pendingOrders', 'totalProducts', 'totalCustomers', 'recentOrders'
        ));
    }
}
