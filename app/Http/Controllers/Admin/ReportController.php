<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $summary = Order::whereDate('created_at', $date)
            ->selectRaw("
                COUNT(*)                                                     AS total_orders,
                COALESCE(SUM(total), 0)                                      AS total_revenue,
                COALESCE(SUM(delivery_fee), 0)                               AS total_delivery_fees,
                COALESCE(SUM(total + delivery_fee), 0)                       AS grand_revenue,
                SUM(CASE WHEN status='done'        THEN 1 ELSE 0 END)        AS completed,
                SUM(CASE WHEN status='wait'        THEN 1 ELSE 0 END)        AS waiting,
                SUM(CASE WHEN status='processing'  THEN 1 ELSE 0 END)        AS processing,
                SUM(CASE WHEN delivery_type='delivery'    THEN 1 ELSE 0 END) AS delivery_count,
                SUM(CASE WHEN delivery_type='takeaway'    THEN 1 ELSE 0 END) AS takeaway_count,
                SUM(CASE WHEN delivery_type='reservation' THEN 1 ELSE 0 END) AS reservation_count,
                COALESCE(AVG(CASE WHEN delivery_type='delivery' THEN distance_km END), 0) AS avg_distance
            ")
            ->first();

        $orders = Order::with('user')->whereDate('created_at', $date)->latest()->get();

        $topProducts = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereDate('orders.created_at', $date)
            ->selectRaw('products.name, SUM(order_items.quantity) AS qty_sold, SUM(order_items.subtotal) AS revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty_sold')
            ->take(10)
            ->get();

        // Upcoming reservations (next 7 days)
        $upcomingReservations = Order::with('user')
            ->where('delivery_type', 'reservation')
            ->whereNotNull('reservation_at')
            ->whereBetween('reservation_at', [now(), now()->addDays(7)])
            ->orderBy('reservation_at')
            ->get();

        return view('admin.reports.daily', compact(
            'summary', 'orders', 'topProducts', 'date', 'upcomingReservations'
        ));
    }
}
