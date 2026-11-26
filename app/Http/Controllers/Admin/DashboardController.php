<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Users counts by role
        $adminCount = User::where('role', UserRole::ADMIN)->count();
        $organizerCount = User::where('role', UserRole::ORGANIZER)->count();
        $customerCount = User::where('role', UserRole::CUSTOMER)->count();
        $totalUsers = $adminCount + $organizerCount + $customerCount;

        // Events counts by status
        $draftEvents = Event::where('status', EventStatus::DRAFT)->count();
        $pendingEvents = Event::where('status', EventStatus::PENDING)->count();
        $publishedEvents = Event::where('status', EventStatus::PUBLISHED)->count();
        $cancelledEvents = Event::where('status', EventStatus::CANCELLED)->count();
        $totalEvents = $draftEvents + $pendingEvents + $publishedEvents + $cancelledEvents;

        // Total system revenue from paid orders
        $totalRevenue = Order::where('status', OrderStatus::PAID)->sum('total_amount');
        $totalPaidOrders = Order::where('status', OrderStatus::PAID)->count();

        // Top selling events using withSum to prevent N+1 (portable NULL-as-0 ordering across SQLite & MySQL)
        $topEvents = Event::with(['category', 'organizer'])
            ->withSum('ticketTypes as total_sold', 'sold')
            ->orderByRaw('COALESCE((SELECT SUM(sold) FROM ticket_types WHERE ticket_types.event_id = events.id), 0) DESC')
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // Recent pending events that need review
        $reviewEvents = Event::where('status', EventStatus::PENDING)
            ->with(['organizer', 'category', 'venue'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'adminCount',
            'organizerCount',
            'customerCount',
            'totalEvents',
            'draftEvents',
            'pendingEvents',
            'publishedEvents',
            'cancelledEvents',
            'totalRevenue',
            'totalPaidOrders',
            'topEvents',
            'reviewEvents'
        ));
    }
}
