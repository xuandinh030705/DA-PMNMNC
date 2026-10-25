@extends('layouts.panel')

@section('title', 'Kênh Ban Tổ Chức - Dashboard')
@section('page_title', 'Tổng Quan Hoạt Động Sự Kiện')

@section('content')
<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-3">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Tổng số sự kiện</small>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalEvents) }}</h3>
                <small class="text-success"><i class="bi bi-check2"></i> {{ $publishedEvents }} đang mở bán</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-3">
                <i class="bi bi-ticket-perforated"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Vé đã bán / giữ chỗ</small>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalTicketsSold) }}</h3>
                <small class="text-muted">Tổng các loại vé</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-3">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Doanh thu tạm tính</small>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h4>
                <small class="text-muted">Từ đơn đã thanh toán</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-3">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Soát vé trực tiếp</small>
                <div class="mt-1">
                    <a href="{{ route('organizer.checkin.form') }}" class="btn btn-sm btn-outline-info fw-bold">
                        Vào Cổng Check-in <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Events -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i>Sự Kiện Gần Đây</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('organizer.events.create') }}" class="btn btn-primary btn-sm rounded-2">
                <i class="bi bi-plus-circle me-1"></i> Tạo sự kiện mới
            </a>
            <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm rounded-2">
                Xem tất cả
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        @if ($recentEvents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Sự kiện</th>
                            <th class="py-3">Danh mục</th>
                            <th class="py-3">Địa điểm</th>
                            <th class="py-3">Thời gian diễn ra</th>
                            <th class="py-3">Loại vé</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="text-end pe-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentEvents as $event)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">{{ $event->title }}</div>
                                    <small class="text-muted">{{ $event->slug }}</small>
                                </td>
                                <td>{{ $event->category->name }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 180px;">{{ $event->venue->name }}</div>
                                    <small class="text-muted">{{ $event->venue->city }}</small>
                                </td>
                                <td class="small">{{ $event->start_time->format('H:i d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $event->ticket_types_count }} loại vé
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $event->statusBadgeClass() }} px-2 py-1">
                                        {{ $event->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center p-5 text-muted">
                <i class="bi bi-calendar-x fs-1 mb-2 d-block"></i>
                <p class="mb-3">Bạn chưa có sự kiện nào được tạo.</p>
                <a href="{{ route('organizer.events.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tạo sự kiện đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
