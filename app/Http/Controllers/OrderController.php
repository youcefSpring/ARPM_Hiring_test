<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
                'customer:id,name',
                'items:order_id,price,quantity,product_id',
                'latestCartItem:id,order_id,created_at'
            ])
            ->select('id', 'customer_id', 'status', 'created_at', 'completed_at')
            ->get()
            ->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'total_amount' => $order->items->sum(fn($item) => $item->price * $item->quantity),
                    'items_count' => $order->items->count(),
                    'last_added_to_cart' => optional($order->latestCartItem)->created_at,
                    'completed_order_exists' => $order->status === 'completed',
                    'created_at' => $order->created_at,
                ];
            })
            ->sortByDesc('completed_at')
            ->values();

        return view('orders.index', ['orders' => $orders]);
    }
}
