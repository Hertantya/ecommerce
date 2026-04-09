<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List all orders, newest first.
     * Admin can filter by status using ?status=wait etc.
     */
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders   = $query->paginate(20)->withQueryString();
        $statuses = Order::STATUS_LABELS;

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    /** Show one order with all its items */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the status of an order.
     * Possible transitions: wait → processing → ready → done
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:wait,processing,ready,done',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', 'Order status updated to: ' . $order->statusLabel());
    }
}
