@extends('layouts.panel')

@section('title', 'Chi Tiết Sự Kiện: ' . $event->title)
@section('page_title', 'Chi Tiết & Phê Duyệt Sự Kiện')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
            <i class="bi bi-arrow-left me-1"></i> Danh sách sự kiện
        </a>
        <h4 class="fw-bold mb-0 text-dark">{{ $event->title }}</h4>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <span class="badge {{ $event->statusBadgeClass() }} fs-6 px-3 py-2">
            {{ $event->statusLabel() }}
        </span>

        @if ($event->isPending())
            <form method="POST" action="{{ route('admin.events.approve', $event) }}" onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt sự kiện này để mở bán công khai?');">
                @csrf
                <button type="submit" class="btn btn-success fw-semibold">
                    <i class="bi bi-check-lg me-1"></i> Phê Duyệt Sự Kiện
                </button>
            </form>

            <form method="POST" action="{{ route('admin.events.reject', $event) }}" onsubmit="return confirm('Bạn có chắc muốn từ chối sự kiện này?');">
                @csrf
                <button type="submit" class="btn btn-outline-warning text-dark fw-semibold">
                    <i class="bi bi-x-lg me-1"></i> Từ Chối Duyệt
                </button>
            </form>
        @endif

        @if (! $event->isCancelled())
            <form method="POST" action="{{ route('admin.events.cancel', $event) }}" onsubmit="return confirm('CẢNH BÁO: Hủy sự kiện sẽ chuyển trạng thái sang Đã Hủy. Bạn có chắc không?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-slash-circle me-1"></i> Hủy Sự Kiện
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Event Details & Organizer -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            @if ($event->banner_path)
                <div class="rounded-3 overflow-hidden mb-3" style="max-height: 220px;">
                    <img src="{{ asset('storage/' . $event->banner_path) }}" class="w-100 h-100 object-fit-cover" alt="Banner">
                </div>
            @endif

            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Thông tin tổng quan</h5>

            <ul class="list-unstyled small d-flex flex-column gap-2 mb-3">
                <li><strong class="text-muted">Đơn vị tổ chức:</strong> <strong class="text-dark">{{ $event->organizer->name }}</strong> ({{ $event->organizer->email }})</li>
                <li><strong class="text-muted">Danh mục:</strong> <span class="badge bg-light text-dark border">{{ $event->category->name }}</span></li>
                <li><strong class="text-muted">Địa điểm:</strong> {{ $event->venue->name }} ({{ $event->venue->city }})</li>
                <li><strong class="text-muted">Địa chỉ:</strong> {{ $event->venue->address }}</li>
                <li><strong class="text-muted">Thời gian:</strong> <span class="text-primary fw-semibold">{{ $event->start_time->format('H:i - d/m/Y') }}</span> đến {{ $event->end_time->format('H:i - d/m/Y') }}</li>
                <li><strong class="text-muted">Slug:</strong> <code>{{ $event->slug }}</code></li>
            </ul>

            <h6 class="fw-bold text-dark mb-2">Mô tả sự kiện:</h6>
            <div class="small text-secondary lh-base" style="white-space: pre-line;">
                {{ $event->description }}
            </div>
        </div>
    </div>

    <!-- Right Column: Ticket Types -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                <i class="bi bi-tags me-2 text-primary"></i>Danh Sách Các Loại Vé ({{ $event->ticketTypes->count() }} loại)
            </h5>

            @if ($event->ticketTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Tên loại vé</th>
                                <th>Giá bán</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Đã bán</th>
                                <th class="text-center">Còn lại</th>
                                <th>Thời gian mở bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->ticketTypes as $type)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $type->name }}</div>
                                        <small class="text-muted">Max: {{ $type->max_per_order }} vé/đơn</small>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        {{ number_format($type->price, 0, ',', '.') }} đ
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($type->quantity) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ number_format($type->sold) }}</td>
                                    <td class="text-center text-secondary fw-semibold">{{ number_format($type->remaining) }}</td>
                                    <td class="small text-muted">
                                        {{ $type->sale_start->format('d/m/Y') }} - {{ $type->sale_end->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tag fs-1 mb-2 d-block"></i>
                    <p class="mb-0">Sự kiện này chưa có loại vé nào được tạo.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
