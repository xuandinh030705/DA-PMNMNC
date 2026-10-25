<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrderItem;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $organizer = $request->user();

        // Total events
        $totalEvents = Event::where('organizer_id', $organizer->id)->count();

        // Published events count
        $publishedEvents = Event::where('organizer_id', $organizer->id)->published()->count();

        // Total tickets sold
        $totalTicketsSold = TicketType::whereHas('event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->sum('sold');

        // Total revenue from paid orders
        $totalRevenue = OrderItem::whereHas('ticketType.event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->whereHas('order', function ($query) {
            $query->where('status', 'paid');
        })->sum('subtotal');

        // Recent events with eager-loaded relations to avoid N+1
        $recentEvents = Event::where('organizer_id', $organizer->id)
            ->with(['category', 'venue', 'ticketTypes'])
            ->withCount('ticketTypes')
            ->latest()
            ->take(6)
            ->get();

        return view('organizer.dashboard', compact(
            'totalEvents',
            'publishedEvents',
            'totalTicketsSold',
            'totalRevenue',
            'recentEvents'
        ));
    }
}
