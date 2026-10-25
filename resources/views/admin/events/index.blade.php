@extends('layouts.panel')

@section('title', 'Phê Duyệt & Quản Lý Sự Kiện')
@section('page_title', 'Quản Lý Sự Kiện Toàn Sàn')

@section('content')
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('admin.events.index') }}" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Tiêu đề sự kiện, tên ban tổ chức...">
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái sự kiện</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã duyệt / Công khai (Published)</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp (Draft)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex justify-content-md-end">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-filter"></i> Lọc
                    </button>
                </div>
            </div>
        </form>

        @if ($events->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Ảnh</th>
                            <th class="py-3">Tiêu đề sự kiện</th>
                            <th class="py-3">Ban tổ chức</th>
                            <th class="py-3">Thời gian diễn ra</th>
                            <th class="py-3 text-center">Loại vé</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="text-end pe-3 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td class="ps-3 py-3" style="width: 70px;">
                                    <div class="rounded-2 overflow-hidden bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 42px;">
                                        @if ($event->banner_path)
                                            <img src="{{ asset('storage/' . $event->banner_path) }}" class="w-100 h-100 object-fit-cover" alt="Banner">
                                        @else
                                            <i class="bi bi-image text-muted"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.events.show', $event) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $event->title }}
                                    </a>
                                    <div class="small text-muted">{{ $event->category->name }} &bull; {{ $event->venue->city }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $event->organizer->name }}</div>
                                    <small class="text-muted">{{ $event->organizer->email }}</small>
                                </td>
                                <td class="small">
                                    <div class="fw-semibold text-dark">{{ $event->start_time->format('H:i d/m/Y') }}</div>
                                    <small class="text-muted">Đến {{ $event->end_time->format('H:i d/m/Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $event->ticket_types_count }} loại vé
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $event->statusBadgeClass() }} px-2 py-1">
                                        {{ $event->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if ($event->isPending())
                                        <form method="POST" action="{{ route('admin.events.approve', $event) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt sự kiện này?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Phê duyệt sự kiện">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.events.reject', $event) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn từ chối sự kiện này? Trạng thái sẽ về Bản nháp.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning text-dark" title="Từ chối duyệt">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if (! $event->isCancelled())
                                        <form method="POST" action="{{ route('admin.events.cancel', $event) }}" class="d-inline" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn HỦY sự kiện này không?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hủy sự kiện">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $events->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1 mb-2 d-block"></i>
                <p>Không tìm thấy sự kiện nào phù hợp.</p>
            </div>
        @endif
    </div>
</div>
@endsection
