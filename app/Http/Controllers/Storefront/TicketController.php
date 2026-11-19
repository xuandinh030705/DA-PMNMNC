<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a list of all tickets purchased by the authenticated customer.
     */
    public function index(Request $request): View
    {
        $tickets = Ticket::whereHas('orderItem.order', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                ->where('status', OrderStatus::PAID);
        })
            ->with(['orderItem.order', 'orderItem.ticketType.event.venue'])
            ->latest()
            ->paginate(12);

        return view('storefront.tickets.index', compact('tickets'));
    }

    /**
     * Download the dynamic PDF version of a purchased e-ticket.
     */
    public function downloadPdf(Ticket $ticket, TicketPdfService $ticketPdfService): Response
    {
        $ticket->loadMissing('orderItem.order');

        $this->authorize('view', $ticket->orderItem->order);

        return $ticketPdfService->render($ticket)
            ->download($ticketPdfService->filenameFor($ticket));
    }
}
