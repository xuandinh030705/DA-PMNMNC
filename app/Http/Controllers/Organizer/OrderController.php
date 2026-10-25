<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $organizer = $request->user();

        $query = Order::whereHas('orderItems.ticketType.event', function ($q) use ($organizer) {
            $q->where('organizer_id', $organizer->id);
        })
        ->with(['orderItems.ticketType.event.venue'])
        ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('organizer.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['orderItems.ticketType.event.venue', 'orderItems.tickets']);

        return view('organizer.orders.show', compact('order'));
    }
}
