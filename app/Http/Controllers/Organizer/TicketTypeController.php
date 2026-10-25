<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\TicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function create(Event $event): View
    {
        $this->authorize('create', [TicketType::class, $event]);

        return view('organizer.ticket-types.create', compact('event'));
    }

    public function store(TicketTypeRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('create', [TicketType::class, $event]);

        $data = $request->validated();
        $data['event_id'] = $event->id;
        $data['sold'] = 0;

        TicketType::create($data);

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Thêm loại vé mới thành công.');
    }

    public function edit(TicketType $ticketType): View
    {
        $this->authorize('update', $ticketType);

        return view('organizer.ticket-types.edit', compact('ticketType'));
    }

    public function update(TicketTypeRequest $request, TicketType $ticketType): RedirectResponse
    {
        $this->authorize('update', $ticketType);

        $ticketType->update($request->validated());

        return redirect()->route('organizer.events.show', $ticketType->event)
            ->with('success', 'Cập nhật thông tin loại vé thành công.');
    }

    public function destroy(TicketType $ticketType): RedirectResponse
    {
        $this->authorize('delete', $ticketType);

        if ($ticketType->sold > 0) {
            return back()->with('error', 'Không thể xóa loại vé đã có khách hàng đặt mua hoặc đang giữ chỗ.');
        }

        $event = $ticketType->event;
        $ticketType->delete();

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Đã xóa loại vé thành công.');
    }
}
