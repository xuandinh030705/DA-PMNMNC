<?php

namespace App\Http\Controllers\Storefront;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Http\Requests\Storefront\PayOrderRequest;
use App\Models\Event;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Create an order from selected tickets.
     */
    public function checkout(CheckoutRequest $request, Event $event, OrderService $orderService): RedirectResponse
    {
        try {
            $order = $orderService->createOrder(
                $request->user(),
                $event,
                $request->input('tickets', []),
                $request->only(['customer_name', 'customer_email', 'customer_phone'])
            );

            return redirect()->route('orders.show', $order->order_code)
                ->with('success', 'Tạo đơn hàng thành công! Bạn có 15 phút để hoàn tất thanh toán giữ chỗ.');
        } catch (BusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display customer's order history.
     */
    public function index(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['orderItems.ticketType.event.venue'])
            ->latest()
            ->paginate(10);

        return view('storefront.orders.index', compact('orders'));
    }

    /**
     * Display a specific order with payment options or ticket codes.
     */
    public function show(Order $order, OrderService $orderService): View
    {
        $this->authorize('view', $order);

        // Perform lazy expiration check
        $orderService->checkAndExpire($order);

        $order->load(['orderItems.ticketType.event.venue', 'orderItems.tickets']);

        return view('storefront.orders.show', compact('order'));
    }

    /**
     * Process mock payment for the order.
     */
    public function pay(PayOrderRequest $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->authorize('pay', $order);

        try {
            $orderService->payOrder($order, $request->payment_method);

            return redirect()->route('orders.show', $order->order_code)
                ->with('success', 'Thanh toán thành công! Vé điện tử của bạn đã được khởi tạo.');
        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending order.
     */
    public function cancel(Order $order, OrderService $orderService): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $orderService->cancelOrder($order);

            return redirect()->route('orders.show', $order->order_code)
                ->with('success', 'Đã hủy đơn hàng thành công và hoàn trả số lượng vé vào kho.');
        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
