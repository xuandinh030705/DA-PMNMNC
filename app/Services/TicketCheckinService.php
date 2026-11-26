<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Exceptions\BusinessException;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketCheckinService
{
    public function __construct(private readonly QrTicketService $qrTicketService) {}

    /**
     * Verify and check-in a ticket from a hand-typed code or a scanned QR payload.
     *
     * @throws BusinessException
     */
    public function checkIn(User $organizer, string $ticketInput): Ticket
    {
        [$ticketCode, $qrPayload] = $this->resolveTicketInput($ticketInput);

        return DB::transaction(function () use ($organizer, $ticketCode, $qrPayload): Ticket {
            /** @var Ticket|null $ticket */
            $ticket = Ticket::with(['orderItem.order', 'orderItem.ticketType.event.venue'])
                ->where('ticket_code', $ticketCode)
                ->lockForUpdate()
                ->first();

            // 1. Ticket code existence check
            if (! $ticket) {
                throw new BusinessException("Mã vé '{$ticketCode}' không tồn tại trong hệ thống.");
            }

            // 2. QR signature check (only for scanned QR payloads)
            if ($qrPayload !== null && ! $this->qrTicketService->verifySignature($qrPayload, (string) $ticket->qr_signature)) {
                throw new BusinessException('Chữ ký số của mã QR không hợp lệ. Vé này có thể đã bị chỉnh sửa hoặc làm giả.');
            }

            $order = $ticket->orderItem?->order;
            $event = $ticket->orderItem?->ticketType?->event;

            if (! $order || ! $event) {
                throw new BusinessException('Không tìm thấy thông tin sự kiện hợp lệ cho vé này.');
            }

            // 3. Order paid check
            if (! $order->isPaid()) {
                throw new BusinessException("Đơn hàng của vé này ({$order->order_code}) chưa hoàn tất thanh toán (trạng thái: {$order->statusLabel()}).");
            }

            // 4. Organizer ownership check
            if ($event->organizer_id !== $organizer->id && ! $organizer->isAdmin()) {
                throw new BusinessException('Vé này không thuộc bất kỳ sự kiện nào do bạn quản lý.');
            }

            // 5. Event / ticket cancelled check
            if ($event->isCancelled() || $ticket->isCancelled()) {
                throw new BusinessException('Vé này đã bị hủy, không còn giá trị sử dụng.');
            }

            // 6. Already used check
            if ($ticket->isUsed()) {
                $time = $ticket->checked_in_at?->format('H:i - d/m/Y') ?? 'trước đó';
                throw new BusinessException("Vé này đã được sử dụng (check-in) vào lúc {$time}.");
            }

            // Successfully check-in
            $ticket->status = TicketStatus::USED;
            $ticket->checked_in_at = now();
            $ticket->checked_in_by = $organizer->id;
            $ticket->save();

            return $ticket;
        });
    }

    /**
     * Accept either a typed ticket code or a scanned JSON QR payload.
     *
     * @return array{0: string, 1: string|null} [ticket code, signed QR payload]
     *
     * @throws BusinessException
     */
    private function resolveTicketInput(string $ticketInput): array
    {
        $input = trim($ticketInput);

        if ($input === '') {
            throw new BusinessException('Vui lòng nhập mã vé hoặc quét mã QR cần soát.');
        }

        if (! str_starts_with($input, '{')) {
            return [mb_strtoupper($input), null];
        }

        $decoded = $this->qrTicketService->decodePayload($input);

        if ($decoded === null) {
            throw new BusinessException('Dữ liệu mã QR không hợp lệ. Vui lòng quét lại hoặc nhập mã vé dạng TKT-XXXXXXXXXX.');
        }

        return [$decoded['ticket_code'], $input];
    }
}
