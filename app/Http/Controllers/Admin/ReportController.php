<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Daily report.
     * Default: today. Pass ?date=YYYY-MM-DD to view another day.
     */
    public function daily(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        // ─── Summary numbers ───────────────────────────────────────
        $summary = Order::whereDate('created_at', $date)
            ->selectRaw("
                COUNT(*)                                          AS total_orders,
                COALESCE(SUM(total), 0)                           AS total_revenue,
                SUM(CASE WHEN status = 'done'       THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'wait'       THEN 1 ELSE 0 END) AS waiting,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
                SUM(CASE WHEN delivery_type = 'delivery' THEN 1 ELSE 0 END) AS delivery_count,
                SUM(CASE WHEN delivery_type = 'takeaway' THEN 1 ELSE 0 END) AS takeaway_count
            ")
            ->first();

        // ─── List of orders that day ───────────────────────────────
        $orders = Order::with('user')
            ->whereDate('created_at', $date)
            ->latest()
            ->get();

        // ─── Top-selling products that day ────────────────────────
        $topProducts = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereDate('orders.created_at', $date)
            ->selectRaw('products.name, SUM(order_items.quantity) AS qty_sold, SUM(order_items.subtotal) AS revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty_sold')
            ->take(10)
            ->get();

        return view('admin.reports.daily', compact('summary', 'orders', 'topProducts', 'date'));
    }
}
