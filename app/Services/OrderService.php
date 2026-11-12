<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a pending order with 15-minute reservation and pessimistic locking.
     *
     * @param  array<int, int>  $ticketQuantities  [ticket_type_id => quantity]
     *
     * @throws BusinessException
     */
    public function createOrder(User $user, Event $event, array $ticketQuantities, array $customerData): Order
    {
        if (! $event->isPublished() || $event->start_time <= now()) {
            throw new BusinessException('Sự kiện này hiện không mở bán vé hoặc đã bắt đầu.');
        }

        // Filter only positive quantities
        $quantities = array_filter($ticketQuantities, fn ($qty) => (int) $qty > 0);
        if (empty($quantities)) {
            throw new BusinessException('Vui lòng chọn ít nhất một loại vé với số lượng lớn hơn 0.');
        }

        return DB::transaction(function () use ($user, $event, $quantities, $customerData) {
            $totalAmount = 0;
            $itemsToCreate = [];

            foreach ($quantities as $ticketTypeId => $qty) {
                $qty = (int) $qty;

                /** @var TicketType|null $ticketType */
                $ticketType = TicketType::where('id', $ticketTypeId)
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->first();

                if (! $ticketType) {
                    throw new BusinessException('Loại vé không tồn tại hoặc không thuộc sự kiện này.');
                }

                $now = now();
                if ($now->lt($ticketType->sale_start) || $now->gt($ticketType->sale_end)) {
                    throw new BusinessException("Loại vé '{$ticketType->name}' hiện nằm ngoài thời gian mở bán.");
                }

                if ($qty > $ticketType->max_per_order) {
                    throw new BusinessException("Số lượng vé '{$ticketType->name}' vượt quá giới hạn cho phép ({$ticketType->max_per_order} vé/đơn).");
                }

                $remaining = $ticketType->quantity - $ticketType->sold;
                if ($remaining < $qty) {
                    throw new BusinessException("Loại vé '{$ticketType->name}' không còn đủ số lượng (còn lại: {$remaining} vé).");
                }

                // Reserve tickets by incrementing sold count
                $ticketType->increment('sold', $qty);

                $subtotal = $qty * $ticketType->price;
                $totalAmount += $subtotal;

                $itemsToCreate[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $qty,
                    'unit_price' => $ticketType->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => Order::generateUniqueOrderCode(),
                'customer_name' => $customerData['customer_name'] ?? $user->name,
                'customer_email' => $customerData['customer_email'] ?? $user->email,
                'customer_phone' => $customerData['customer_phone'] ?? $user->phone,
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
                'payment_method' => null,
                'paid_at' => null,
                'expires_at' => now()->addMinutes(15),
            ]);

            foreach ($itemsToCreate as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            return $order;
        });
    }

    /**
     * Process mock payment for a pending order.
     *
     *
     * @throws BusinessException
     */
    public function payOrder(Order $order, string $paymentMethod): Order
    {
        /** @var Order|null $paidOrder */
        $paidOrder = null;

        $expired = DB::transaction(function () use ($order, $paymentMethod, &$paidOrder): bool {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (! $lockedOrder->isPending()) {
                throw new BusinessException('Đơn hàng không ở trạng thái chờ thanh toán.');
            }

            if ($lockedOrder->expires_at && now()->gt($lockedOrder->expires_at)) {
                $this->cancelOrder($lockedOrder);

                return true;
            }

            $lockedOrder->status = Order::STATUS_PAID;
            $lockedOrder->payment_method = $paymentMethod;
            $lockedOrder->paid_at = now();
            $lockedOrder->expires_at = null;
            $lockedOrder->save();

            // Generate unique tickets for each item
            foreach ($lockedOrder->orderItems as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $ticketCode = Ticket::generateUniqueTicketCode();
                    $qrPayload = Ticket::buildQrPayload($ticketCode);

                    Ticket::create([
                        'order_item_id' => $item->id,
                        'ticket_code' => $ticketCode,
                        'qr_payload' => $qrPayload,
                        'qr_signature' => Ticket::generateQrSignature($qrPayload),
                        'status' => Ticket::STATUS_VALID,
                        'checked_in_at' => null,
                        'checked_in_by' => null,
                    ]);
                }
            }

            $paidOrder = $lockedOrder;

            return false;
        });

        if ($expired) {
            throw new BusinessException('Đơn hàng đã hết thời gian giữ chỗ (15 phút) và đã tự động hủy.');
        }

        return $paidOrder;
    }

    /**
     * Cancel a pending order and restore ticket quantities.
     *
     *
     * @throws BusinessException
     */
    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (! $lockedOrder->isPending()) {
                throw new BusinessException('Chỉ có thể hủy đơn hàng đang ở trạng thái chờ thanh toán.');
            }

            // Restore sold quantities for all ticket types
            foreach ($lockedOrder->orderItems as $item) {
                TicketType::where('id', $item->ticket_type_id)
                    ->lockForUpdate()
                    ->decrement('sold', $item->quantity);
            }

            $lockedOrder->status = Order::STATUS_CANCELLED;
            $lockedOrder->save();

            return $lockedOrder;
        });
    }

    /**
     * Lazy check: if an order is pending and expired, cancel it.
     */
    public function checkAndExpire(Order $order): void
    {
        if ($order->isPending() && $order->isExpired()) {
            $this->cancelOrder($order);
            $order->refresh();
        }
    }

    /**
     * Expire all pending orders past their expiration time.
     *
     * @return int Number of cancelled orders
     */
    public function expireAllPendingOrders(): int
    {
        $expiredOrders = Order::where('status', Order::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            try {
                $this->cancelOrder($order);
                $count++;
            } catch (\Exception $e) {
                // Ignore if already processed concurrently
            }
        }

        return $count;
    }
}
