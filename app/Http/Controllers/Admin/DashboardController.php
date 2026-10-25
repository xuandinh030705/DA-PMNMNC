<?php

namespace App\Http\Controllers\Admin;

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
        $adminCount = User::where('role', User::ROLE_ADMIN)->count();
        $organizerCount = User::where('role', User::ROLE_ORGANIZER)->count();
        $customerCount = User::where('role', User::ROLE_CUSTOMER)->count();
        $totalUsers = $adminCount + $organizerCount + $customerCount;

        // Events counts by status
        $draftEvents = Event::where('status', Event::STATUS_DRAFT)->count();
        $pendingEvents = Event::where('status', Event::STATUS_PENDING)->count();
        $publishedEvents = Event::where('status', Event::STATUS_PUBLISHED)->count();
        $cancelledEvents = Event::where('status', Event::STATUS_CANCELLED)->count();
        $totalEvents = $draftEvents + $pendingEvents + $publishedEvents + $cancelledEvents;

        // Total system revenue from paid orders
        $totalRevenue = Order::where('status', Order::STATUS_PAID)->sum('total_amount');
        $totalPaidOrders = Order::where('status', Order::STATUS_PAID)->count();

        // Top selling events using withSum to prevent N+1
        $topEvents = Event::with(['category', 'organizer'])
            ->withSum('ticketTypes as total_sold', 'sold')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Recent pending events that need review
        $reviewEvents = Event::where('status', Event::STATUS_PENDING)
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
