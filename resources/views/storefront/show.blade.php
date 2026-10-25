@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><span class="text-secondary">{{ $event->category->name }}</span></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;">{{ $event->title }}</li>
        </ol>
    </nav>
</div>

<div class="row g-4">
    <!-- Left Column: Event Information -->
    <div class="col-lg-8">
        <div class="card card-custom border-0 shadow-sm overflow-hidden mb-4">
            <!-- Event Banner Image -->
            <div class="position-relative" style="height: 320px; background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%);">
                @if ($event->banner_path)
                    <img src="{{ asset('storage/' . $event->banner_path) }}" class="w-100 h-100 object-fit-cover" alt="{{ $event->title }}">
                @else
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white text-opacity-75">
                        <i class="bi bi-calendar2-event fs-1 mb-2"></i>
                        <span class="fs-5 fw-bold">{{ $event->title }}</span>
                    </div>
                @endif
                <span class="position-absolute top-0 start-0 m-3 badge bg-primary px-3 py-2 rounded-pill fs-6 shadow-sm">
                    {{ $event->category->name }}
                </span>
            </div>

            <div class="card-body p-4">
                <h2 class="fw-bold text-dark mb-3">{{ $event->title }}</h2>

                <!-- Key Event Metrics -->
                <div class="row g-3 p-3 bg-light rounded-3 mb-4">
                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <div class="bg-primary text-white p-2 rounded-3 d-inline-flex">
                            <i class="bi bi-clock-fill fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Thời gian diễn ra</small>
                            <span class="fw-semibold text-dark">
                                {{ $event->start_time->format('H:i - d/m/Y') }} <br>
                                <small class="text-muted">Đến {{ $event->end_time->format('H:i - d/m/Y') }}</small>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <div class="bg-danger text-white p-2 rounded-3 d-inline-flex">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Địa điểm tổ chức</small>
                            <span class="fw-semibold text-dark">{{ $event->venue->name }}</span>
                            <small class="text-muted d-block">{{ $event->venue->address }}, {{ $event->venue->city }}</small>
                        </div>
                    </div>
                </div>

                <!-- Event Description -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Giới thiệu sự kiện</h5>
                <div class="text-secondary lh-lg mb-4" style="white-space: pre-line;">
                    {{ $event->description }}
                </div>

                <!-- Venue Information -->
                <div class="p-3 border rounded-3 bg-light-subtle">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-building me-2 text-primary"></i>Thông tin địa điểm: {{ $event->venue->name }}</h6>
                    <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1">
                        <li><strong>Địa chỉ:</strong> {{ $event->venue->address }}, {{ $event->venue->city }}</li>
                        <li><strong>Sức chứa tối đa:</strong> {{ number_format($event->venue->capacity) }} khán giả</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Ticket Selection Form -->
    <div class="col-lg-4">
        <div class="card card-custom border-0 shadow-sm sticky-top" style="top: 90px;">
            <div class="card-header bg-primary text-white p-3 border-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2"></i>Chọn Loại Vé</h5>
            </div>

            <div class="card-body p-3 p-md-4">
                @include('partials.form-errors')

                @if ($event->start_time <= now())
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Sự kiện đã bắt đầu hoặc kết thúc, hiện đã đóng bán vé.
                    </div>
                @elseif ($event->ticketTypes->isEmpty())
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> Ban tổ chức chưa công bố loại vé cho sự kiện này.
                    </div>
                @else
                    <form method="POST" action="{{ route('checkout.store', $event->slug) }}" id="ticketOrderForm">
                        @csrf

                        <div class="d-flex flex-column gap-3 mb-4">
                            @foreach ($event->ticketTypes as $type)
                                @php
                                    $now = now();
                                    $inSalePeriod = $now->gte($type->sale_start) && $now->lte($type->sale_end);
                                    $availableQty = min($type->max_per_order, $type->remaining);
                                    $isAvailable = $inSalePeriod && $availableQty > 0;
                                @endphp

                                <div class="p-3 border rounded-3 {{ $isAvailable ? 'bg-white' : 'bg-light opacity-75' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $type->name }}</h6>
                                            <div class="fw-bold text-primary fs-5">
                                                {{ number_format($type->price, 0, ',', '.') }} đ
                                            </div>
                                        </div>
                                        @if (! $inSalePeriod)
                                            <span class="badge bg-secondary">Hết hạn bán</span>
                                        @elseif ($type->remaining <= 0)
                                            <span class="badge bg-danger">Hết vé</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                Còn {{ $type->remaining }} vé
                                            </span>
                                        @endif
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <div>Giới hạn: tối đa {{ $type->max_per_order }} vé/đơn</div>
                                        <div>Mở bán đến: {{ $type->sale_end->format('H:i d/m/Y') }}</div>
                                    </div>

                                    @if ($isAvailable)
                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                            <label class="small fw-semibold text-secondary">Số lượng:</label>
                                            <div style="width: 110px;">
                                                <input type="number"
                                                       name="tickets[{{ $type->id }}]"
                                                       class="form-control form-control-sm text-center ticket-qty-input"
                                                       data-price="{{ $type->price }}"
                                                       value="{{ old('tickets.' . $type->id, 0) }}"
                                                       min="0"
                                                       max="{{ $availableQty }}">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Customer Info Form -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-bounding-box me-2 text-primary"></i>Thông tin người nhận vé</h6>

                        @auth
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="customer_name" value="{{ old('customer_name', Auth::user()->name) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Email nhận vé <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" name="customer_email" value="{{ old('customer_email', Auth::user()->email) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Số điện thoại</label>
                                <input type="text" class="form-control form-control-sm" name="customer_phone" value="{{ old('customer_phone', Auth::user()->phone) }}">
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                <i class="bi bi-bag-check me-1"></i> Đặt Vé & Giữ Chỗ (15 Phút)
                            </button>
                        @else
                            <div class="alert alert-warning small mb-3">
                                <i class="bi bi-lock me-1"></i> Vui lòng <strong>đăng nhập</strong> tài khoản để tiến hành đặt vé.
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng Nhập Để Mua Vé
                            </a>
                        @endauth
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
