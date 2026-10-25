<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\EventRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::where('organizer_id', $request->user()->id)
            ->with(['category', 'venue'])
            ->withCount('ticketTypes');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . trim($request->q) . '%');
        }

        $events = $query->latest()->paginate(10)->withQueryString();

        return view('organizer.events.index', compact('events'));
    }

    public function create(): View
    {
        $categories = Category::all();
        $venues = Venue::all();

        return view('organizer.events.create', compact('categories', 'venues'));
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organizer_id'] = $request->user()->id;
        $data['status'] = Event::STATUS_DRAFT;

        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('banners', 'public');
        }

        $event = Event::create($data);

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Tạo sự kiện mới (Bản nháp) thành công. Hãy thêm các loại vé để mở bán.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['category', 'venue', 'ticketTypes']);

        return view('organizer.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        if (! $event->isDraft()) {
            abort(403, 'Chỉ có thể chỉnh sửa sự kiện khi ở trạng thái Bản nháp.');
        }

        $categories = Category::all();
        $venues = Venue::all();

        return view('organizer.events.edit', compact('event', 'categories', 'venues'));
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        if (! $event->isDraft()) {
            abort(403, 'Chỉ có thể chỉnh sửa sự kiện khi ở trạng thái Bản nháp.');
        }

        $data = $request->validated();

        if ($request->hasFile('banner')) {
            if ($event->banner_path) {
                Storage::disk('public')->delete($event->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('banners', 'public');
        }

        if ($event->title !== $request->title) {
            $data['slug'] = Event::generateUniqueSlug($request->title, $event->id);
        }

        $event->update($data);

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Cập nhật thông tin sự kiện thành công.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        if (! $event->isDraft()) {
            return back()->with('error', 'Chỉ có thể xóa sự kiện khi ở trạng thái Bản nháp.');
        }

        if ($event->banner_path) {
            Storage::disk('public')->delete($event->banner_path);
        }

        $event->delete();

        return redirect()->route('organizer.events.index')
            ->with('success', 'Đã xóa sự kiện bản nháp thành công.');
    }

    public function submit(Event $event): RedirectResponse
    {
        $this->authorize('submit', $event);

        if (! $event->canBeSubmitted()) {
            return back()->with('error', 'Sự kiện phải có ít nhất 01 loại vé và thời gian bắt đầu phải trong tương lai để gửi duyệt.');
        }

        $event->status = Event::STATUS_PENDING;
        $event->save();

        return back()->with('success', 'Đã gửi duyệt sự kiện thành công! Quản trị viên sẽ sớm xem xét và phê duyệt.');
    }
}
