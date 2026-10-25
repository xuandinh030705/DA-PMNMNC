@extends('layouts.app')

@section('title', 'Vé Của Tôi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-ticket-detailed-fill text-primary me-2"></i>Vé Điện Tử Của Tôi</h3>
                <p class="text-muted small mb-0">Quản lý các vé sự kiện hợp lệ và sử dụng khi đến tham dự sự kiện</p>
            </div>
            @if ($tickets->count() > 0)
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> In danh sách vé
                </button>
            @endif
        </div>

        @if ($tickets->count() > 0)
            <div class="row g-4 mb-4">
                @foreach ($tickets as $ticket)
                    @php
                        $item = $ticket->orderItem;
                        $event = $item->ticketType->event;
                    @endphp
                    <div class="col-md-6 col-lg-6">
                        <div class="card card-ticket card-custom border-2 p-4 h-100 position-relative shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $ticket->statusBadgeClass() }} px-3 py-1">
                                    {{ $ticket->statusLabel() }}
                                </span>
                                <small class="text-muted">Đơn: <a href="{{ route('orders.show', $item->order->order_code) }}" class="text-decoration-none ticket-code-font">{{ $item->order->order_code }}</a></small>
                            </div>

                            <!-- Ticket Code Monospace Box -->
                            <div class="p-3 bg-light rounded-3 border text-center my-3">
                                <small class="text-muted d-block text-uppercase letter-spacing-1 mb-1">Mã Vé Điểm Danh / Check-in</small>
                                <span class="ticket-code-font fs-3 text-primary fw-bold letter-spacing-2">
                                    {{ $ticket->ticket_code }}
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-2 text-truncate-2">{{ $event->title }}</h5>

                            <div class="d-flex flex-column gap-1 small text-muted mb-3">
                                <div><strong>Hạng vé:</strong> <span class="text-dark fw-semibold">{{ $item->ticketType->name }}</span> ({{ number_format($item->unit_price, 0, ',', '.') }} đ)</div>
                                <div><i class="bi bi-clock me-1 text-primary"></i> {{ $event->start_time->format('H:i - d/m/Y') }}</div>
                                <div><i class="bi bi-geo-alt me-1 text-danger"></i> {{ $event->venue->name }} ({{ $event->venue->address }})</div>
                                <div><i class="bi bi-person me-1 text-secondary"></i> Khách tham dự: <strong class="text-dark">{{ $item->order->customer_name }}</strong></div>
                            </div>

                            @if ($ticket->isUsed())
                                <div class="p-2 rounded bg-info bg-opacity-10 text-info small fw-semibold mt-auto">
                                    <i class="bi bi-check-circle-fill me-1"></i> Đã hoàn tất check-in lúc: {{ $ticket->checked_in_at?->format('H:i - d/m/Y') }}
                                </div>
                            @else
                                <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-success"><i class="bi bi-check2-circle me-1"></i> Sẵn sàng sử dụng</small>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="window.print()">
                                        <i class="bi bi-printer me-1"></i> In thẻ vé
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="card card-custom border-0 shadow-sm p-5 text-center">
                <div class="text-muted mb-3">
                    <i class="bi bi-ticket-perforated" style="font-size: 3.5rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Bạn chưa có vé điện tử nào</h4>
                <p class="text-secondary mb-3">Sau khi đặt vé và thanh toán thành công, vé điện tử với mã check-in độc nhất sẽ xuất hiện tại đây.</p>
                <div>
                    <a href="{{ route('home') }}" class="btn btn-primary-custom px-4">
                        <i class="bi bi-compass me-1"></i> Khám Phá Sự Kiện Ngay
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
