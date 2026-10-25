@extends('layouts.panel')

@section('title', 'Soát Vé Tại Cổng (Check-in)')
@section('page_title', 'Hệ Thống Soát Vé Điện Tử Tại Cổng')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Checkin Input Form Card -->
        <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5 mb-4 text-center">
            <div class="mb-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px;">
                    <i class="bi bi-qr-code-scan fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Soát Vé Vào Cửa</h3>
                <p class="text-muted small">Nhập hoặc quét mã vé dạng <code>TKT-XXXXXXXXXX</code> để kiểm tra tính hợp lệ</p>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('organizer.checkin.store') }}" class="mb-3">
                @csrf

                <div class="mb-4">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-upc-scan text-muted"></i></span>
                        <input type="text"
                               name="ticket_code"
                               id="ticket_code"
                               class="form-control ticket-code-font text-center text-uppercase fs-4 @error('ticket_code') is-invalid @enderror"
                               placeholder="TKT-ABC123XYZ"
                               value="{{ old('ticket_code') }}"
                               required
                               autofocus
                               autocomplete="off">
                    </div>
                    <div class="form-text text-muted mt-2">Hệ thống sẽ tự động đối soát đơn hàng, trạng thái sử dụng và quyền sở hữu sự kiện.</div>
                </div>

                <div class="d-grid gap-2 col-md-8 mx-auto">
                    <button type="submit" class="btn btn-primary py-3 fs-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Check-in
                    </button>
                </div>
            </form>
        </div>

        <!-- Checkin Result Card if Success -->
        @if (session('success_ticket'))
            @php
                $ticket = session('success_ticket');
                $item = $ticket->orderItem;
                $event = $item->ticketType->event;
            @endphp
            <div class="card border-success border-2 shadow-sm rounded-3 p-4 mb-4 bg-success bg-opacity-10">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-success fs-1">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="badge bg-success fs-6 px-3 py-1 mb-2">HỢP LỆ & CHECK-IN THÀNH CÔNG</span>
                        <h4 class="fw-bold text-dark mb-1">{{ $event->title }}</h4>

                        <div class="p-3 bg-white rounded-3 border my-3">
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Mã vé:</span>
                                    <span class="ticket-code-font fs-5 text-primary fw-bold">{{ $ticket->ticket_code }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Loại vé:</span>
                                    <strong class="text-dark fs-6">{{ $item->ticketType->name }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Người tham dự:</span>
                                    <strong class="text-dark">{{ $item->order->customer_name }}</strong> ({{ $item->order->customer_phone ?: 'N/A' }})
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Thời điểm soát vé:</span>
                                    <span class="text-success fw-bold">{{ $ticket->checked_in_at?->format('H:i:s - d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted">Đơn hàng liên quan: <code>{{ $item->order->order_code }}</code> - Đã thanh toán</small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
