<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        $ticketCode = Ticket::generateUniqueTicketCode();
        $qrPayload = Ticket::buildQrPayload($ticketCode);

        return [
            'order_item_id' => OrderItem::factory(),
            'ticket_code' => $ticketCode,
            'qr_payload' => $qrPayload,
            'qr_signature' => Ticket::generateQrSignature($qrPayload),
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ];
    }

    /**
     * Keep the QR payload and signature aligned with the (possibly overridden) ticket code.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Ticket $ticket): void {
            $ticket->qr_payload = Ticket::buildQrPayload($ticket->ticket_code);
            $ticket->qr_signature = Ticket::generateQrSignature($ticket->qr_payload);
        });
    }

    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_USED,
            'checked_in_at' => now(),
            'checked_in_by' => User::factory()->organizer(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_CANCELLED,
        ]);
    }
}
