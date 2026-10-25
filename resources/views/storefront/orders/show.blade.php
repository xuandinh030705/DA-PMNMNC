@extends('layouts.app')

@section('title', 'Chi Tiết Đơn Hàng ' . $order->order_code)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <a href="{{ route('orders.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách đơn hàng
                </a>
                <h3 class="fw-bold mb-0">
                    Đơn Hàng <span class="ticket-code-font text-primary">{{ $order->order_code }}</span>
                </h3>
            </div>
            <div>
                <span class="badge {{ $order->statusBadgeClass() }} fs-6 px-3 py-2 shadow-sm">
                    {{ $order->statusLabel() }}
                </span>
            </div>
        </div>

        <!-- Conditional Alerts based on Status -->
        @if ($order->isPending())
            <div class="card border-warning bg-warning bg-opacity-10 mb-4 shadow-sm">
                <div class="card-body p-4 d-flex align-items-start gap-3">
                    <i class="bi bi-clock-history fs-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-dark mb-1">Đơn hàng đang chờ thanh toán</h5>
                        <p class="small text-secondary mb-2">
                            Vé của bạn đang được tạm giữ trong vòng 15 phút. Vui lòng thanh toán trước
                            <strong class="text-danger">{{ $order->expires_at?->format('H:i:s d/m/Y') }}</strong>
                            để tránh bị hủy tự động và giải phóng vé cho người khác.
                        </p>
                    </div>
                </div>
            </div>
        @elseif ($order->isPaid())
            <div class="card border-success bg-success bg-opacity-10 mb-4 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    <div>
                        <h6 class="fw-bold text-success mb-1">Thanh toán hoàn tất!</h6>
                        <small class="text-muted">
                            Đã thanh toán qua phương thức <strong>{{ strtoupper($order->payment_method ?? 'MOCK') }}</strong> vào lúc {{ $order->paid_at?->format('H:i - d/m/Y') }}.
                        </small>
                    </div>
                </div>
            </div>
        @elseif ($order->isCancelled())
            <div class="card border-danger bg-danger bg-opacity-10 mb-4 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
                    <div>
                        <h6 class="fw-bold text-danger mb-1">Đơn hàng đã bị hủy</h6>
                        <small class="text-muted">Đơn hàng này không còn hiệu lực và các vé đã được hoàn trả lại hệ thống.</small>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <!-- Order Summary & Items -->
            <div class="col-md-7">
                <div class="card card-custom border-0 shadow-sm p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-primary"></i>Chi tiết vé đã đặt</h5>

                    <div class="table-responsive mb-3">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="border-bottom text-muted small">
                                <tr>
                                    <th>Loại vé</th>
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
                                    <td colspan="3" class="text-end pt-3 fw-bold fs-6">Tổng cộng thanh toán:</td>
                                    <td class="text-end pt-3 fw-bold fs-5 text-primary">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Event Quick Information -->
                    @php $firstEvent = $order->orderItems->first()?->ticketType?->event; @endphp
                    @if ($firstEvent)
                        <div class="p-3 bg-light rounded-3 mt-auto">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt me-1 text-danger"></i> {{ $firstEvent->venue->name }}</h6>
                            <small class="text-muted d-block">{{ $firstEvent->venue->address }}, {{ $firstEvent->venue->city }}</small>
                            <small class="text-primary fw-semibold d-block mt-1">
                                <i class="bi bi-calendar-event me-1"></i> Bắt đầu: {{ $firstEvent->start_time->format('H:i - d/m/Y') }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Customer & Payment Action Column -->
            <div class="col-md-5">
                <!-- Customer Details -->
                <div class="card card-custom border-0 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Thông tin người nhận</h5>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><span class="text-muted">Họ và tên:</span> <strong class="text-dark">{{ $order->customer_name }}</strong></li>
                        <li><span class="text-muted">Email:</span> <strong class="text-dark">{{ $order->customer_email }}</strong></li>
                        <li><span class="text-muted">Số điện thoại:</span> <strong class="text-dark">{{ $order->customer_phone ?: 'Chưa cung cấp' }}</strong></li>
                        <li><span class="text-muted">Thời điểm tạo đơn:</span> <strong class="text-dark">{{ $order->created_at->format('H:i:s d/m/Y') }}</strong></li>
                    </ul>
                </div>

                <!-- Payment Form (If Pending) -->
                @if ($order->isPending())
                    <div class="card card-custom border-0 shadow-sm p-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-credit-card me-2 text-primary"></i>Thanh toán giả lập</h5>

                        <form method="POST" action="{{ route('orders.pay', $order->order_code) }}" class="mb-3">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Chọn phương thức:</label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pay_bank" value="bank_transfer" checked>
                                        <label class="form-check-label fw-semibold text-dark" for="pay_bank">
                                            <i class="bi bi-bank text-primary me-1"></i> Chuyển khoản ngân hàng (QR Fast)
                                        </label>
                                    </div>
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pay_momo" value="momo">
                                        <label class="form-check-label fw-semibold text-dark" for="pay_momo">
                                            <i class="bi bi-wallet2 text-danger me-1"></i> Ví điện tử MoMo
                                        </label>
                                    </div>
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card">
                                        <label class="form-check-label fw-semibold text-dark" for="pay_card">
                                            <i class="bi bi-credit-card text-success me-1"></i> Thẻ quốc tế Visa / MasterCard
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Xác Nhận Thanh Toán ({{ number_format($order->total_amount, 0, ',', '.') }} đ)
                            </button>
                        </form>

                        <!-- Cancel Order Button -->
                        <form method="POST" action="{{ route('orders.cancel', $order->order_code) }}" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-x-circle me-1"></i> Hủy đơn hàng này
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Generated Tickets List (When Paid) -->
        @if ($order->isPaid() && $order->tickets->count() > 0)
            <div class="card card-custom border-0 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-ticket-perforated-fill me-2 text-primary"></i>Danh Sách Vé Điện Tử ({{ $order->tickets->count() }} vé)</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> In toàn bộ vé
                    </button>
                </div>

                <div class="row g-3">
                    @foreach ($order->tickets as $ticket)
                        @php
                            $ticketItem = $ticket->orderItem;
                            $ticketEvent = $ticketItem->ticketType->event;
                        @endphp
                        <div class="col-md-6">
                            <div class="card card-ticket border border-2 rounded-3 p-3 bg-light shadow-sm position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge {{ $ticket->statusBadgeClass() }}">
                                        {{ $ticket->statusLabel() }}
                                    </span>
                                    <small class="text-muted">Mã vé điện tử</small>
                                </div>

                                <div class="p-2 bg-white rounded border text-center my-2">
                                    <span class="ticket-code-font fs-4 text-primary fw-bold letter-spacing-1">
                                        {{ $ticket->ticket_code }}
                                    </span>
                                </div>

                                <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $ticketEvent->title }}</h6>
                                <div class="small text-secondary mb-1">
                                    <strong>Loại vé:</strong> {{ $ticketItem->ticketType->name }}
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-clock me-1"></i> {{ $ticketEvent->start_time->format('H:i - d/m/Y') }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i> {{ $ticketEvent->venue->name }}
                                </div>

                                @if ($ticket->isUsed())
                                    <div class="mt-2 pt-2 border-top small text-info fw-semibold">
                                        <i class="bi bi-check-all me-1"></i> Đã soát vé lúc: {{ $ticket->checked_in_at?->format('H:i d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
