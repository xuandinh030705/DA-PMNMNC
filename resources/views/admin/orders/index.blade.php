@extends('layouts.panel')

@section('title', 'Quản Lý Đơn Hàng')
@section('page_title', 'Danh Sách Đơn Hàng Toàn Hệ Thống')

@section('content')
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Mã đơn hàng, tên khách hàng, email, SĐT...">
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái đơn hàng</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ thanh toán (Pending)</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Đã thanh toán (Paid)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex justify-content-md-end">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-filter"></i> Lọc đơn hàng
                    </button>
                </div>
            </div>
        </form>

        @if ($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Mã đơn hàng</th>
                            <th class="py-3">Khách hàng</th>
                            <th class="py-3">Sự kiện liên quan</th>
                            <th class="py-3">Tổng giá trị</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="py-3">Ngày tạo đơn</th>
                            <th class="text-end pe-3 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $firstEvent = $order->orderItems->first()?->ticketType?->event;
                            @endphp
                            <tr>
                                <td class="ps-3 py-3">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" class="fw-bold ticket-code-font text-primary text-decoration-none">
                                        {{ $order->order_code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->customer_email }}</small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 220px;">
                                        {{ $firstEvent?->title ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">{{ $firstEvent?->venue->city }}</small>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </td>
                                <td>
                                    <span class="badge {{ $order->statusBadgeClass() }} px-2 py-1">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $order->created_at->format('H:i - d/m/Y') }}
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt fs-1 mb-2 d-block"></i>
                <p>Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</p>
            </div>
        @endif
    </div>
</div>
@endsection
