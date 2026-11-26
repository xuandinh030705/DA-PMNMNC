<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\TicketPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đơn hàng #'.$this->order->order_code.' - Vé điện tử của bạn',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-paid',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $pdfService = app(TicketPdfService::class);

        $this->order->loadMissing([
            'orderItems.ticketType.event.venue',
            'orderItems.tickets.checkedInBy',
        ]);

        foreach ($this->order->orderItems as $item) {
            $item->setRelation('order', $this->order);

            foreach ($item->tickets as $ticket) {
                $ticket->setRelation('orderItem', $item);

                $attachments[] = Attachment::fromData(
                    fn () => $pdfService->output($ticket),
                    $pdfService->filenameFor($ticket)
                )->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
