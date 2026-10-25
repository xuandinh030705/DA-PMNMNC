<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
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
                ->where('status', 'paid');
        })
        ->with(['orderItem.order', 'orderItem.ticketType.event.venue'])
        ->latest()
        ->paginate(12);

        return view('storefront.tickets.index', compact('tickets'));
    }
}
