<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with(['organizer', 'category', 'venue'])
            ->withCount('ticketTypes')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhereHas('organizer', fn($sub) => $sub->where('name', 'like', "%{$keyword}%"));
            });
        }

        $events = $query->paginate(10)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $event->load(['organizer', 'category', 'venue', 'ticketTypes']);

        return view('admin.events.show', compact('event'));
    }

    public function approve(Event $event): RedirectResponse
    {
        $this->authorize('approve', $event);

        $event->status = Event::STATUS_PUBLISHED;
        $event->save();

        return back()->with('success', "Đã phê duyệt sự kiện '{$event->title}'. Sự kiện hiện đã được công khai trên hệ thống.");
    }

    public function reject(Event $event): RedirectResponse
    {
        $this->authorize('reject', $event);

        $event->status = Event::STATUS_DRAFT;
        $event->save();

        return back()->with('warning', "Đã từ chối duyệt sự kiện '{$event->title}'. Trạng thái sự kiện đã quay về Bản nháp.");
    }

    public function cancel(Event $event): RedirectResponse
    {
        $this->authorize('cancel', $event);

        $event->status = Event::STATUS_CANCELLED;
        $event->save();

        return back()->with('success', "Đã hủy sự kiện '{$event->title}'.");
    }
}
