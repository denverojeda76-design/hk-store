<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display orders belonging to the logged-in customer.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Display tracking details for a customer's own order.
     */
    public function show(Request $request, Order $order)
    {
        // Prevent customers from viewing another customer's order.
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            404
        );

        $order->load(['items', 'rider']);

        return view('customer.orders.show', compact('order'));
    }
}
