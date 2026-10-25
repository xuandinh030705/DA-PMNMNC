<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Display the storefront event listing with search, filtering, and sorting.
     */
    public function index(Request $request): View
    {
        $query = Event::query()
            ->published()
            ->notEnded()
            ->with(['category', 'venue'])
            ->withMin('ticketTypes as min_price', 'price');

        // Search by keyword in title
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . trim($request->q) . '%');
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by City
        if ($request->filled('city')) {
            $query->whereHas('venue', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Filter by date range (from - to)
        if ($request->filled('from')) {
            $query->whereDate('start_time', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('start_time', '<=', $request->to);
        }

        // Sorting
        match ($request->sort) {
            'date_desc' => $query->orderBy('start_time', 'desc'),
            'price_asc' => $query->orderBy('min_price', 'asc'),
            'price_desc' => $query->orderBy('min_price', 'desc'),
            default => $query->orderBy('start_time', 'asc'),
        };

        $events = $query->paginate(9)->withQueryString();
        $categories = Category::all();
        $cities = Venue::distinct()->pluck('city')->sort();

        return view('storefront.index', compact('events', 'categories', 'cities'));
    }

    /**
     * Display event detail and ticket selection form.
     */
    public function show(Event $event): View
    {
        if (! $event->isPublished()) {
            abort(404, 'Sự kiện không tồn tại hoặc chưa được công khai.');
        }

        $event->load(['category', 'venue', 'ticketTypes' => function ($q) {
            $q->orderBy('price', 'asc');
        }]);

        return view('storefront.show', compact('event'));
    }
}
