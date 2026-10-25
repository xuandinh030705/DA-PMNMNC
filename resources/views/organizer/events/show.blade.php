@extends('layouts.panel')

@section('title', $event->title)
@section('page_title', 'Chi Tiết Sự Kiện & Quản Lý Loại Vé')

@section('content')
<!-- Header Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('organizer.events.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
            <i class="bi bi-arrow-left me-1"></i> Danh sách sự kiện
        </a>
        <h4 class="fw-bold mb-0 text-dark">{{ $event->title }}</h4>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <span class="badge {{ $event->statusBadgeClass() }} fs-6 px-3 py-2">
            {{ $event->statusLabel() }}
        </span>

        @if ($event->isDraft())
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Sửa sự kiện
            </a>

            @if ($event->canBeSubmitted())
                <form method="POST" action="{{ route('organizer.events.submit', $event) }}" onsubmit="return confirm('Bạn có chắc chắn muốn gửi duyệt sự kiện này đến Ban Quản Trị?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-send-check me-1"></i> Gửi duyệt sự kiện
                    </button>
                </form>
            @else
                <button class="btn btn-sm btn-secondary" disabled title="Cần ít nhất 01 loại vé và thời gian bắt đầu ở tương lai để gửi duyệt">
                    <i class="bi bi-send me-1"></i> Chưa đủ điều kiện gửi duyệt
                </button>
            @endif
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Event Overview -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
            @if ($event->banner_path)
                <div class="rounded-3 overflow-hidden mb-3" style="max-height: 200px;">
                    <img src="{{ asset('storage/' . $event->banner_path) }}" class="w-100 h-100 object-fit-cover" alt="Banner">
                </div>
            @endif

            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Thông tin tổng quan</h5>

            <ul class="list-unstyled small d-flex flex-column gap-2 mb-3">
                <li><strong class="text-muted">Danh mục:</strong> <span class="badge bg-light text-dark border">{{ $event->category->name }}</span></li>
                <li><strong class="text-muted">Địa điểm:</strong> {{ $event->venue->name }} ({{ $event->venue->city }})</li>
                <li><strong class="text-muted">Địa chỉ:</strong> {{ $event->venue->address }}</li>
                <li><strong class="text-muted">Thời gian bắt đầu:</strong> <span class="text-primary fw-semibold">{{ $event->start_time->format('H:i - d/m/Y') }}</span></li>
                <li><strong class="text-muted">Thời gian kết thúc:</strong> {{ $event->end_time->format('H:i - d/m/Y') }}</li>
                <li><strong class="text-muted">Slug:</strong> <code>{{ $event->slug }}</code></li>
            </ul>

            <h6 class="fw-bold text-dark mb-2">Mô tả:</h6>
            <div class="small text-secondary lh-base" style="white-space: pre-line;">
                {{ $event->description }}
            </div>
        </div>
    </div>

    <!-- Right Column: Ticket Types Management -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-tags me-2 text-primary"></i>Danh Sách Loại Vé</h5>
                    <small class="text-muted">Quản lý giá vé, số lượng và thời gian phát hành</small>
                </div>
                @if ($event->isDraft())
                    <a href="{{ route('organizer.events.ticket-types.create', $event) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Thêm loại vé
                    </a>
                @endif
            </div>

            @if ($event->ticketTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Tên loại vé</th>
                                <th>Giá vé</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Đã bán</th>
                                <th class="text-center">Còn lại</th>
                                @if ($event->isDraft())
                                    <th class="text-end">Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->ticketTypes as $type)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $type->name }}</div>
                                        <small class="text-muted">Tối đa: {{ $type->max_per_order }} vé/đơn</small>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        {{ number_format($type->price, 0, ',', '.') }} đ
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($type->quantity) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ number_format($type->sold) }}</td>
                                    <td class="text-center text-secondary fw-semibold">{{ number_format($type->remaining) }}</td>
                                    @if ($event->isDraft())
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('organizer.ticket-types.edit', $type) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if ($type->sold === 0)
                                                <form method="POST" action="{{ route('organizer.ticket-types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại vé này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tag fs-1 mb-2 d-block"></i>
                    <p class="mb-3">Chưa có loại vé nào cho sự kiện này.</p>
                    @if ($event->isDraft())
                        <a href="{{ route('organizer.events.ticket-types.create', $event) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Tạo loại vé đầu tiên
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
