<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketCheckinService
{
    /**
     * Verify and check-in a ticket by its code for an organizer.
     *
     * @throws BusinessException
     */
    public function checkIn(User $organizer, string $ticketCode): Ticket
    {
        $code = trim($ticketCode);

        return DB::transaction(function () use ($organizer, $code): Ticket {
            /** @var Ticket|null $ticket */
            $ticket = Ticket::with(['orderItem.order', 'orderItem.ticketType.event.venue'])
                ->where('ticket_code', $code)
                ->lockForUpdate()
                ->first();

            // 1. Ticket code existence check
            if (! $ticket) {
                throw new BusinessException("Mã vé '{$code}' không tồn tại trong hệ thống.");
            }

            $order = $ticket->orderItem->order;
            $event = $ticket->orderItem->ticketType->event;

            // 2. Order paid check
            if ($order->status !== Order::STATUS_PAID) {
                throw new BusinessException("Đơn hàng của vé này ({$order->order_code}) chưa hoàn tất thanh toán (trạng thái: {$order->statusLabel()}).");
            }

            // 3. Organizer ownership check
            if ($event->organizer_id !== $organizer->id && ! $organizer->isAdmin()) {
                throw new BusinessException('Vé này không thuộc bất kỳ sự kiện nào do bạn quản lý.');
            }

            // 4. Ticket cancelled check
            if ($ticket->isCancelled()) {
                throw new BusinessException('Vé này đã bị hủy, không còn giá trị sử dụng.');
            }

            // 5. Already used check
            if ($ticket->isUsed()) {
                $time = $ticket->checked_in_at?->format('H:i - d/m/Y') ?? 'trước đó';
                throw new BusinessException("Vé này đã được sử dụng (check-in) vào lúc {$time}.");
            }

            // Successfully check-in
            $ticket->status = Ticket::STATUS_USED;
            $ticket->checked_in_at = now();
            $ticket->checked_in_by = $organizer->id;
            $ticket->save();

            return $ticket;
        });
    }
}
