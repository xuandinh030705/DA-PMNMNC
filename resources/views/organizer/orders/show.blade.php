@extends('layouts.panel')

@section('title', 'Đơn Hàng ' . $order->order_code)
@section('page_title', 'Chi Tiết Đơn Hàng ' . $order->order_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('organizer.orders.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách đơn hàng
        </a>
        <h4 class="fw-bold mb-0 text-dark">
            Đơn Hàng: <span class="ticket-code-font text-primary">{{ $order->order_code }}</span>
        </h4>
    </div>
    <span class="badge {{ $order->statusBadgeClass() }} fs-6 px-3 py-2">
        {{ $order->statusLabel() }}
    </span>
</div>

<div class="row g-4 mb-4">
    <!-- Customer & Payment Details -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Khách hàng</h5>
            <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                <li><span class="text-muted">Họ và tên:</span> <strong class="text-dark">{{ $order->customer_name }}</strong></li>
                <li><span class="text-muted">Email:</span> <strong class="text-dark">{{ $order->customer_email }}</strong></li>
                <li><span class="text-muted">Số điện thoại:</span> <strong class="text-dark">{{ $order->customer_phone ?: 'Chưa cung cấp' }}</strong></li>
                <li><span class="text-muted">Ngày đặt hàng:</span> <strong class="text-dark">{{ $order->created_at->format('H:i - d/m/Y') }}</strong></li>
                @if ($order->isPaid())
                    <li><span class="text-muted">Thời điểm thanh toán:</span> <strong class="text-success">{{ $order->paid_at?->format('H:i - d/m/Y') }}</strong></li>
                    <li><span class="text-muted">Hình thức:</span> <span class="badge bg-light text-dark border">{{ strtoupper($order->payment_method ?? 'MOCK') }}</span></li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Items and Tickets -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Chi tiết các loại vé</h5>

            <div class="table-responsive mb-3">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="border-bottom small text-muted">
                        <tr>
                            <th>Loại vé / Sự kiện</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $item)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="fw-bold text-dark">{{ $item->ticketType->name }}</div>
                                    <small class="text-muted">{{ $item->ticketType->event->title }}</small>
                                </td>
                                <td class="text-center py-3 fw-semibold">x{{ $item->quantity }}</td>
                                <td class="text-end py-3 text-muted">{{ number_format($item->unit_price, 0, ',', '.') }} đ</td>
                                <td class="text-end py-3 fw-bold text-dark">{{ number_format($item->subtotal, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end pt-3 fw-bold fs-6">Tổng giá trị đơn hàng:</td>
                            <td class="text-end pt-3 fw-bold fs-5 text-primary">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Tickets generated -->
            @if ($order->isPaid() && $order->tickets->count() > 0)
                <h6 class="fw-bold text-dark mb-3 pt-3 border-top"><i class="bi bi-qr-code me-2 text-primary"></i>Danh sách mã vé đã phát hành</h6>
                <div class="row g-2">
                    @foreach ($order->tickets as $ticket)
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="ticket-code-font fw-bold text-primary">{{ $ticket->ticket_code }}</span>
                                    <div class="small text-muted">{{ $ticket->orderItem->ticketType->name }}</div>
                                </div>
                                <span class="badge {{ $ticket->statusBadgeClass() }}">
                                    {{ $ticket->statusLabel() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
