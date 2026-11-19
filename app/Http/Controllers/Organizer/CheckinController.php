<?php

namespace App\Http\Controllers\Organizer;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\CheckinRequest;
use App\Services\TicketCheckinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckinController extends Controller
{
    public function form(): View
    {
        return view('organizer.checkin.form');
    }

    public function store(CheckinRequest $request, TicketCheckinService $checkinService): RedirectResponse
    {
        $ticketInput = trim((string) $request->input('ticket_code'));

        try {
            $ticket = $checkinService->checkIn($request->user(), $ticketInput);

            $item = $ticket->orderItem;
            $order = $item->order;
            $ticketType = $item->ticketType;

            // Flash plain data instead of the Eloquent model: Laravel's JSON session
            // serialization would turn the model into an array on the next request.
            $ticketSummary = [
                'ticket_code' => $ticket->ticket_code,
                'event_title' => $ticketType->event->title,
                'ticket_type_name' => $ticketType->name,
                'zone' => $ticketType->zone,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'checked_in_at' => $ticket->checked_in_at?->format('H:i:s - d/m/Y'),
                'checked_in_by' => $ticket->checkedInBy?->name ?? 'Không xác định',
                'qr_signature_valid' => $ticket->hasValidQrSignature(),
                'order_code' => $order->order_code,
            ];

            return back()->with('success_ticket', $ticketSummary)
                ->with('success', "Soát vé THÀNH CÔNG! Vé '{$ticket->ticket_code}' hợp lệ.");
        } catch (BusinessException $e) {
            $redirect = back()->with('error', $e->getMessage());

            // Do not refill a scanned QR payload into the manual input after a failure.
            return str_starts_with($ticketInput, '{')
                ? $redirect
                : $redirect->withInput();
        }
    }
}
