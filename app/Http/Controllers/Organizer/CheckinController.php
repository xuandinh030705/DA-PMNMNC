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
        try {
            $ticket = $checkinService->checkIn($request->user(), $request->ticket_code);

            return back()->with('success_ticket', $ticket)
                ->with('success', "Soát vé THÀNH CÔNG! Vé '{$ticket->ticket_code}' hợp lệ.");
        } catch (BusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
