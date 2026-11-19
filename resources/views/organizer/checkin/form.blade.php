@extends('layouts.panel')

@section('title', 'Soát Vé Tại Cổng (Check-in)')
@section('page_title', 'Hệ Thống Soát Vé Điện Tử Tại Cổng')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Checkin Input Form Card -->
        <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5 mb-4">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px;">
                    <i class="bi bi-qr-code-scan fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Soát Vé Vào Cửa</h3>
                <p class="text-muted small mb-0">Quét mã QR bằng camera hoặc nhập mã vé dạng <code>TKT-XXXXXXXXXX</code> để kiểm tra tính hợp lệ</p>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('organizer.checkin.store') }}" id="checkin-form">
                @csrf

                <div class="mb-3">
                    <label for="ticket_code" class="form-label fw-semibold text-start d-block">
                        <i class="bi bi-upc-scan me-1 text-primary"></i>Mã vé / Nội dung mã QR
                    </label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-upc-scan text-muted"></i></span>
                        <input type="text"
                               name="ticket_code"
                               id="ticket_code"
                               class="form-control ticket-code-font text-center text-uppercase fs-5 @error('ticket_code') is-invalid @enderror"
                               placeholder="TKT-ABC123XYZ"
                               value="{{ old('ticket_code') }}"
                               required
                               autofocus
                               autocomplete="off">
                    </div>
                    <div class="form-text text-muted mt-2 text-start">
                        Hỗ trợ cả mã nhập tay <code>TKT-...</code> và chuỗi JSON của mã QR (có kiểm tra chữ ký HMAC-SHA256).
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 my-3">
                    <hr class="flex-grow-1 my-0">
                    <small class="text-muted text-uppercase" style="letter-spacing: 1px;">hoặc</small>
                    <hr class="flex-grow-1 my-0">
                </div>

                <div class="border rounded-3 bg-light p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-camera-video me-1 text-primary"></i>Quét mã QR bằng Camera</h6>
                            <p class="text-muted small mb-0">Dùng webcam / camera điện thoại để soát vé nhanh tại cổng.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-start-camera" class="btn btn-outline-primary">
                                <i class="bi bi-camera-video-fill me-1"></i> Bật camera
                            </button>
                            <button type="button" id="btn-stop-camera" class="btn btn-outline-danger d-none">
                                <i class="bi bi-stop-circle me-1"></i> Tắt camera
                            </button>
                        </div>
                    </div>

                    <div id="camera-alert" class="alert alert-warning small mt-3 mb-0 d-none" role="alert"></div>

                    <div id="qr-reader" class="mt-3 rounded-3 overflow-hidden bg-white d-none"></div>

                    <div class="form-text text-muted mt-2">
                        Trình duyệt chỉ cấp quyền camera khi chạy qua <code>http://localhost:8000</code> hoặc kết nối HTTPS.
                        Nếu camera không khả dụng, hãy nhập mã vé bằng tay ở ô phía trên.
                    </div>
                </div>

                <div class="d-grid gap-2 col-md-8 mx-auto mt-4">
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
                                    @if ($item->ticketType->zone)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border ms-1">{{ $item->ticketType->zone }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Người tham dự:</span>
                                    <strong class="text-dark">{{ $item->order->customer_name }}</strong> ({{ $item->order->customer_phone ?: 'N/A' }})
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Thời điểm soát vé:</span>
                                    <span class="text-success fw-bold">{{ $ticket->checked_in_at?->format('H:i:s - d/m/Y') }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Nhân viên soát vé:</span>
                                    <strong class="text-dark">{{ $ticket->checkedInBy?->name ?? 'Không xác định' }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Chữ ký QR:</span>
                                    @if ($ticket->hasValidQrSignature())
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="bi bi-shield-check me-1"></i>HMAC-SHA256 hợp lệ
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Chưa xác thực QR
                                        </span>
                                    @endif
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

@push('scripts')
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('checkin-form');
        const input = document.getElementById('ticket_code');
        const startButton = document.getElementById('btn-start-camera');
        const stopButton = document.getElementById('btn-stop-camera');
        const reader = document.getElementById('qr-reader');
        const alertBox = document.getElementById('camera-alert');

        let scanner = null;
        let isHandlingResult = false;

        const showAlert = function (message) {
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        };

        const hideAlert = function () {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        };

        const stopCamera = async function () {
            if (scanner === null) {
                return;
            }

            try {
                await scanner.stop();
                scanner.clear();
            } catch (error) {
                // Camera may already be stopped by the browser.
            }

            scanner = null;
            reader.classList.add('d-none');
            startButton.disabled = false;
            startButton.classList.remove('d-none');
            stopButton.classList.add('d-none');
        };

        const onScanSuccess = function (decodedText) {
            if (isHandlingResult) {
                return;
            }

            isHandlingResult = true;
            input.value = decodedText;

            stopCamera().then(function () {
                form.requestSubmit();
            });
        };

        startButton.addEventListener('click', async function () {
            if (scanner !== null) {
                return;
            }

            hideAlert();
            startButton.disabled = true;

            if (typeof Html5Qrcode === 'undefined') {
                startButton.disabled = false;
                showAlert('Không tải được thư viện quét QR (public/js/html5-qrcode.min.js). Vui lòng nhập mã vé bằng tay.');
                return;
            }

            reader.classList.remove('d-none');
            scanner = new Html5Qrcode('qr-reader');

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    onScanSuccess,
                    function () {
                        // Ignore per-frame decode failures while the camera is warming up.
                    }
                );

                startButton.classList.add('d-none');
                stopButton.classList.remove('d-none');
            } catch (error) {
                reader.classList.add('d-none');
                await stopCamera();
                showAlert('Không mở được camera: ' + (error && error.message ? error.message : error) + '. Hãy nhập mã vé bằng tay.');
            }
        });

        stopButton.addEventListener('click', function () {
            stopCamera();
        });
    });
</script>
@endpush
