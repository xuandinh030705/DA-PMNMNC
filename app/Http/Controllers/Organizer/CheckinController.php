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

            return back()->with('success_ticket', $ticket)
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
