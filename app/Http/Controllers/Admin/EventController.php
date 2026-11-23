<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    ->orWhereHas('organizer', fn ($sub) => $sub->where('name', 'like', "%{$keyword}%"));
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

        $event->status = EventStatus::PUBLISHED;
        $event->save();

        return back()->with('success', "Đã phê duyệt sự kiện '{$event->title}'. Sự kiện hiện đã được công khai trên hệ thống.");
    }

    public function reject(Event $event): RedirectResponse
    {
        $this->authorize('reject', $event);

        $event->status = EventStatus::DRAFT;
        $event->save();

        return back()->with('warning', "Đã từ chối duyệt sự kiện '{$event->title}'. Trạng thái sự kiện đã quay về Bản nháp.");
    }

    /**
     * Cancel an event and cascade updates to pending orders and valid tickets.
     */
    public function cancel(Event $event): RedirectResponse
    {
        $this->authorize('cancel', $event);

        /** @var array{already_cancelled: bool, cancelled_orders: int, cancelled_tickets: int} $summary */
        $summary = DB::transaction(function () use ($event): array {
            /** @var Event $lockedEvent */
            $lockedEvent = Event::where('id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEvent->isCancelled()) {
                return [
                    'already_cancelled' => true,
                    'cancelled_orders' => 0,
                    'cancelled_tickets' => 0,
                ];
            }

            $lockedEvent->status = EventStatus::CANCELLED;
            $lockedEvent->save();

            $ticketTypeIds = TicketType::where('event_id', $lockedEvent->id)
                ->orderBy('id')
                ->pluck('id');

            if ($ticketTypeIds->isEmpty()) {
                return [
                    'already_cancelled' => false,
                    'cancelled_orders' => 0,
                    'cancelled_tickets' => 0,
                ];
            }

            $pendingOrders = Order::where('status', OrderStatus::PENDING)
                ->whereHas('orderItems', fn ($query) => $query->whereIn('ticket_type_id', $ticketTypeIds))
                ->with('orderItems')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $restoreByTicketType = [];

            foreach ($pendingOrders as $order) {
                foreach ($order->orderItems as $item) {
                    $restoreByTicketType[$item->ticket_type_id] = ($restoreByTicketType[$item->ticket_type_id] ?? 0) + $item->quantity;
                }

                $order->status = OrderStatus::CANCELLED;
                $order->save();
            }

            if (! empty($restoreByTicketType)) {
                ksort($restoreByTicketType);

                $lockedTicketTypes = TicketType::whereIn('id', array_keys($restoreByTicketType))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($restoreByTicketType as $ticketTypeId => $quantityToRestore) {
                    /** @var TicketType|null $ticketType */
                    $ticketType = $lockedTicketTypes->get($ticketTypeId);
                    if ($ticketType) {
                        $ticketType->sold = max(0, $ticketType->sold - $quantityToRestore);
                        $ticketType->save();
                    }
                }
            }

            $validTicketIds = Ticket::where('status', TicketStatus::VALID)
                ->whereHas('orderItem', fn ($query) => $query->whereIn('ticket_type_id', $ticketTypeIds))
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            $cancelledTicketsCount = 0;

            foreach ($validTicketIds->chunk(500) as $ticketIdChunk) {
                $cancelledTicketsCount += Ticket::whereIn('id', $ticketIdChunk)
                    ->update([
                        'status' => TicketStatus::CANCELLED->value,
                    ]);
            }

            return [
                'already_cancelled' => false,
                'cancelled_orders' => $pendingOrders->count(),
                'cancelled_tickets' => $cancelledTicketsCount,
            ];
        });

        if ($summary['already_cancelled']) {
            return back()->with('warning', "Sự kiện '{$event->title}' đã ở trạng thái hủy trước đó.");
        }

        $details = [];

        if ($summary['cancelled_orders'] > 0) {
            $details[] = "đã tự động hủy {$summary['cancelled_orders']} đơn chờ thanh toán và hoàn lại số lượng vé";
        }

        if ($summary['cancelled_tickets'] > 0) {
            $details[] = "đã hủy {$summary['cancelled_tickets']} vé hợp lệ";
        }

        if (! empty($details)) {
            $suffix = ' ('.implode('; ', $details).').';

            return back()->with('success', "Đã hủy sự kiện '{$event->title}'{$suffix}");
        }

        return back()->with('success', "Đã hủy sự kiện '{$event->title}'.");
    }
}
