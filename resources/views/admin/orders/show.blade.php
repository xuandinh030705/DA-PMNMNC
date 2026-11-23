@extends('layouts.panel')

@section('title', 'Đơn Hàng: ' . $order->order_code)
@section('page_title', 'Chi Tiết Đơn Hàng ' . $order->order_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách đơn hàng
        </a>
        <h4 class="fw-bold mb-0 text-dark">
            Mã Đơn: <span class="ticket-code-font text-primary">{{ $order->order_code }}</span>
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
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Thông tin người mua</h5>
            <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                <li><span class="text-muted">Tài khoản:</span> <strong class="text-dark">{{ $order->user->name }}</strong> (ID: {{ $order->user_id }})</li>
                <li><span class="text-muted">Tên nhận vé:</span> <strong class="text-dark">{{ $order->customer_name }}</strong></li>
                <li><span class="text-muted">Email:</span> <strong class="text-dark">{{ $order->customer_email }}</strong></li>
                <li><span class="text-muted">Số điện thoại:</span> <strong class="text-dark">{{ $order->customer_phone ?: 'Chưa cập nhật' }}</strong></li>
                <li><span class="text-muted">Ngày đặt:</span> <strong class="text-dark">{{ $order->created_at->format('H:i:s - d/m/Y') }}</strong></li>
                @if ($order->isPaid())
                    <li><span class="text-muted">Thời điểm thanh toán:</span> <strong class="text-success">{{ $order->paid_at?->format('H:i:s - d/m/Y') }}</strong></li>
                    <li><span class="text-muted">Phương thức:</span> <span class="badge bg-light text-dark border">{{ strtoupper($order->payment_method ?? 'MOCK') }}</span></li>
                @elseif ($order->isPending())
                    <li><span class="text-muted">Hết hạn giữ chỗ:</span> <strong class="text-danger">{{ $order->expires_at?->format('H:i:s - d/m/Y') }}</strong></li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Items and Issued Tickets -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-cart me-2 text-primary"></i>Các loại vé trong đơn</h5>

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
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <span class="fw-bold text-dark">{{ $item->ticketType->name }}</span>
                                        @if ($item->ticketType->zone)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $item->ticketType->zone }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->ticketType->description)
                                        <div class="small text-secondary mt-1">
                                            <i class="bi bi-info-circle me-1 text-primary"></i>{{ $item->ticketType->description }}
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">
                                        {{ $item->ticketType->event->title }} ({{ $item->ticketType->event->venue->name }})
                                        @if ($item->ticketType->event->isCancelled())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">Sự kiện đã hủy</span>
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center py-3 fw-semibold">x{{ $item->quantity }}</td>
                                <td class="text-end py-3 text-muted text-nowrap">{{ number_format($item->unit_price, 0, ',', '.') }} đ</td>
                                <td class="text-end py-3 fw-bold text-dark text-nowrap">{{ number_format($item->subtotal, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end pt-3 fw-bold fs-6">Tổng giá trị đơn hàng:</td>
                            <td class="text-end pt-3 fw-bold fs-5 text-primary text-nowrap">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($order->isPaid() && $order->tickets->count() > 0)
                <h6 class="fw-bold text-dark mb-3 pt-3 border-top"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Mã vé điện tử đã phát hành ({{ $order->tickets->count() }} vé)</h6>
                <div class="row g-3">
                    @foreach ($order->tickets as $ticket)
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="ticket-code-font fw-bold text-primary">{{ $ticket->ticket_code }}</span>
                                        <div class="small text-muted">{{ $ticket->orderItem->ticketType->name }}</div>
                                        @if ($ticket->orderItem->ticketType->zone)
                                            <div class="mt-1">
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                                    <i class="bi bi-geo-alt me-1"></i>{{ $ticket->orderItem->ticketType->zone }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="badge {{ $ticket->statusBadgeClass() }}">
                                        {{ $ticket->statusLabel() }}
                                    </span>
                                </div>

                                @if ($ticket->isUsed())
                                    <div class="mt-2 pt-2 border-top small text-muted">
                                        @if ($ticket->checked_in_at)
                                            <div>
                                                <i class="bi bi-clock-history me-1 text-info"></i>
                                                Soát vé lúc: <strong class="text-dark">{{ $ticket->checked_in_at->format('H:i - d/m/Y') }}</strong>
                                            </div>
                                        @endif
                                        <div>
                                            <i class="bi bi-person-check me-1 text-success"></i>
                                            Nhân viên soát vé:
                                            @if ($ticket->checkedInBy)
                                                <strong class="text-dark">{{ $ticket->checkedInBy->name }}</strong>
                                                <span class="text-muted">({{ $ticket->checkedInBy->email }})</span>
                                            @else
                                                <span class="fst-italic">Không xác định</span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($ticket->isCancelled())
                                    <div class="mt-2 pt-2 border-top small text-danger">
                                        <i class="bi bi-x-circle me-1"></i>Vé đã bị vô hiệu hóa, không còn giá trị check-in.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
