<?php

namespace App\Services;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class TicketPdfService
{
    public function __construct(private readonly QrTicketService $qrTicketService) {}

    /**
     * Build the in-memory PDF document for a single e-ticket.
     */
    public function render(Ticket $ticket): DomPdf
    {
        $ticket->loadMissing(['orderItem.order', 'orderItem.ticketType.event.venue', 'checkedInBy']);

        $ticketType = $ticket->orderItem->ticketType;

        return Pdf::loadView('pdf.ticket', [
            'ticket' => $ticket,
            'order' => $ticket->orderItem->order,
            'ticketType' => $ticketType,
            'event' => $ticketType->event,
            'qrCodeDataUri' => $this->qrTicketService->pngDataUri($ticket),
        ])->setPaper('a5', 'portrait');
    }

    /**
     * Render the PDF bytes without storing them on disk.
     */
    public function output(Ticket $ticket): string
    {
        return $this->render($ticket)->output();
    }

    /**
     * Suggested download filename for the generated PDF.
     */
    public function filenameFor(Ticket $ticket): string
    {
        return 've-'.$ticket->ticket_code.'.pdf';
    }
}
