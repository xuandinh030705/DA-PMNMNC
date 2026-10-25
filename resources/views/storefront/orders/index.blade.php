@extends('layouts.app')

@section('title', 'Đơn Hàng Của Tôi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-bag-check-fill text-primary me-2"></i>Đơn Hàng Của Tôi</h3>
                <p class="text-muted small mb-0">Theo dõi trạng thái thanh toán và thông tin các đơn đặt vé</p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Đặt thêm vé mới
            </a>
        </div>

        @if ($orders->count() > 0)
            <div class="card card-custom border-0 shadow-sm overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Mã đơn hàng</th>
                                <th class="py-3">Sự kiện</th>
                                <th class="py-3">Tổng tiền</th>
                                <th class="py-3">Trạng thái</th>
                                <th class="py-3">Ngày đặt</th>
                                <th class="text-end pe-3 py-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $firstItem = $order->orderItems->first();
                                    $event = $firstItem?->ticketType?->event;
                                @endphp
                                <tr>
                                    <td class="ps-3 py-3">
                                        <a href="{{ route('orders.show', $order->order_code) }}" class="fw-bold ticket-code-font text-primary text-decoration-none">
                                            {{ $order->order_code }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($event)
                                            <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                                {{ $event->title }}
                                            </div>
                                            <small class="text-muted">{{ $firstItem->ticketType->name }} (x{{ $firstItem->quantity }})</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
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
                                        <a href="{{ route('orders.show', $order->order_code) }}" class="btn btn-sm btn-outline-secondary">
                                            Chi tiết <i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @else
            <div class="card card-custom border-0 shadow-sm p-5 text-center">
                <div class="text-muted mb-3">
                    <i class="bi bi-cart-x" style="font-size: 3.5rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Bạn chưa có đơn đặt vé nào</h4>
                <p class="text-secondary mb-3">Hãy dạo quanh các sự kiện đặc sắc và chọn cho mình những tấm vé ưng ý ngay nhé!</p>
                <div>
                    <a href="{{ route('home') }}" class="btn btn-primary-custom px-4">
                        <i class="bi bi-search me-1"></i> Khám Phá Sự Kiện Ngay
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
