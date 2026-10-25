@extends('layouts.panel')

@section('title', 'Bảng Điều Khiển Quản Trị')
@section('page_title', 'Tổng Quan Hệ Thống Quản Trị')

@section('content')
<!-- KPI Cards Row -->
<div class="row g-3 mb-4">
    <!-- Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-3">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Doanh thu sàn</small>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h4>
                <small class="text-success"><i class="bi bi-receipt"></i> {{ number_format($totalPaidOrders) }} đơn đã thanh toán</small>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-3">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Người dùng</small>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalUsers) }}</h3>
                <small class="text-muted">{{ $organizerCount }} BTC | {{ $customerCount }} Khách</small>
            </div>
        </div>
    </div>

    <!-- Total Events -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-3">
                <i class="bi bi-calendar2-week"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Sự kiện toàn sàn</small>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalEvents) }}</h3>
                <small class="text-success">{{ $publishedEvents }} công khai | {{ $draftEvents }} nháp</small>
            </div>
        </div>
    </div>

    <!-- Pending Events (Need Action) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-3">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <small class="text-muted fw-semibold d-block">Sự kiện chờ duyệt</small>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingEvents) }}</h3>
                <small class="text-danger fw-semibold">Cần quản trị xử lý</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Pending Review Events Section -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>Sự Kiện Chờ Phê Duyệt
                </h5>
                <a href="{{ route('admin.events.index') }}?status=pending" class="btn btn-sm btn-outline-secondary">
                    Xem tất cả
                </a>
            </div>

            <div class="card-body p-0">
                @if ($reviewEvents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4 py-3">Sự kiện</th>
                                    <th class="py-3">Ban tổ chức</th>
                                    <th class="py-3">Thời gian</th>
                                    <th class="text-end pe-4 py-3">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviewEvents as $ev)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <a href="{{ route('admin.events.show', $ev) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $ev->title }}
                                            </a>
                                            <div class="small text-muted">{{ $ev->category->name }} &bull; {{ $ev->venue->city }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $ev->organizer->name }}</div>
                                            <small class="text-muted">{{ $ev->organizer->email }}</small>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $ev->start_time->format('H:i d/m/Y') }}
                                        </td>
                                        <td class="text-end pe-4 text-nowrap">
                                            <form method="POST" action="{{ route('admin.events.approve', $ev) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt sự kiện này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Duyệt sự kiện">
                                                    <i class="bi bi-check-lg"></i> Duyệt
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.events.reject', $ev) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn từ chối sự kiện này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark" title="Từ chối">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success mb-2 d-block"></i>
                        <p class="mb-0">Tuyệt vời! Hiện không có sự kiện nào đang chờ phê duyệt.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Selling Events Section -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-trophy text-warning me-2"></i>Top Sự Kiện Bán Chạy Nhất
                </h5>
            </div>

            <div class="card-body p-3">
                @if ($topEvents->count() > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach ($topEvents as $index => $topEvent)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="text-truncate" style="max-width: 220px;">
                                        <a href="{{ route('admin.events.show', $topEvent) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block">
                                            {{ $topEvent->title }}
                                        </a>
                                        <small class="text-muted">{{ $topEvent->category->name }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        {{ number_format($topEvent->total_sold ?? 0) }} vé
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center my-4">Chưa có dữ liệu bán vé.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
