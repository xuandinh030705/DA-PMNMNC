<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VenueRequest;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(): View
    {
        $venues = Venue::withCount('events')->latest()->paginate(10);

        return view('admin.venues.index', compact('venues'));
    }

    public function create(): View
    {
        return view('admin.venues.create');
    }

    public function store(VenueRequest $request): RedirectResponse
    {
        Venue::create($request->validated());

        return redirect()->route('admin.venues.index')
            ->with('success', 'Thêm địa điểm tổ chức mới thành công.');
    }

    public function edit(Venue $venue): View
    {
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(VenueRequest $request, Venue $venue): RedirectResponse
    {
        $venue->update($request->validated());

        return redirect()->route('admin.venues.index')
            ->with('success', 'Cập nhật thông tin địa điểm thành công.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        if ($venue->events()->exists()) {
            return back()->with('error', "Không thể xóa địa điểm '{$venue->name}' vì đang có sự kiện liên kết.");
        }

        $venue->delete();

        return redirect()->route('admin.venues.index')
            ->with('success', 'Đã xóa địa điểm thành công.');
    }
}
