@extends('layouts.app')

@section('title', 'Khám Phá & Đặt Vé Sự Kiện')

@section('content')
<!-- Hero Section -->
<div class="p-4 p-md-5 mb-4 rounded-4 text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);">
    <div class="row align-items-center position-relative" style="z-index: 2;">
        <div class="col-lg-8">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold mb-3">
                <i class="bi bi-stars me-1"></i> Nền Tảng Đặt Vé Số 1
            </span>
            <h1 class="display-5 fw-bold mb-3">Trải Nghiệm Sự Kiện Đỉnh Cao Cùng EventTicket</h1>
            <p class="lead text-light text-opacity-75 mb-4">
                Hòa mình vào không khí sôi động của hàng trăm sự kiện âm nhạc, hội thảo công nghệ, thể thao và văn hóa nghệ thuật hấp dẫn mỗi ngày.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="#events-list" class="btn btn-light fw-bold px-4 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-compass me-1 text-primary"></i> Khám Phá Ngay
                </a>
            </div>
        </div>
        <div class="col-lg-4 d-none d-lg-block text-center">
            <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 backdrop-blur">
                <i class="bi bi-ticket-perforated text-warning" style="font-size: 6rem;"></i>
                <div class="fw-bold fs-5 mt-2">Vé Điện Tử Tức Thì</div>
                <small class="text-light text-opacity-75">Thanh toán an toàn - Giữ chỗ bảo đảm</small>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom border-0 shadow-sm mb-4" id="events-list">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('home') }}">
            <div class="row g-2 align-items-center">
                <!-- Search by keyword -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ khóa tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Tên sự kiện...">
                    </div>
                </div>

                <!-- Filter by Category -->
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Danh mục</label>
                    <select class="form-select" name="category">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by City -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Thành phố</label>
                    <select class="form-select" name="city">
                        <option value="">Tất cả thành phố</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Date From -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                </div>

                <!-- Filter by Date To -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="col-lg-1 col-md-12 d-flex flex-column justify-content-end">
                    <label class="form-label small fw-semibold text-muted mb-1 d-none d-lg-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary-custom w-100" title="Tìm kiếm">
                        <i class="bi bi-filter"></i> Lọc
                    </button>
                </div>
            </div>

            <!-- Sorting Bar -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị <strong>{{ $events->total() }}</strong> sự kiện phù hợp
                    @if (request()->hasAny(['q', 'category', 'city', 'from', 'to', 'sort']))
                        <a href="{{ route('home') }}" class="ms-2 text-danger text-decoration-none">
                            <i class="bi bi-x-circle me-1"></i> Xóa bộ lọc
                        </a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-semibold text-nowrap mb-0">Sắp xếp theo:</label>
                    <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()" style="width: auto;">
                        <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Thời gian: Sớm nhất</option>
                        <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Thời gian: Muộn nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá vé: Thấp đến cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá vé: Cao đến thấp</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Event Listing Grid -->
@if ($events->count() > 0)
    <div class="row g-4 mb-4">
        @foreach ($events as $event)
            <div class="col-md-6 col-lg-4">
                <div class="card card-custom h-100 d-flex flex-column border-0">
                    <!-- Event Banner Image or Gradient Visual -->
                    <div class="position-relative overflow-hidden" style="height: 180px; background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);">
                        @if ($event->banner_path)
                            <img src="{{ asset('storage/' . $event->banner_path) }}" class="w-100 h-100 object-fit-cover" alt="{{ $event->title }}">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white text-opacity-75">
                                <i class="bi bi-calendar-event fs-1"></i>
                            </div>
                        @endif
                        <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 backdrop-blur px-2 py-1 rounded-3">
                            {{ $event->category->name }}
                        </span>
                    </div>

                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-grow-1">
                        <h5 class="fw-bold mb-2">
                            <a href="{{ route('events.show', $event->slug) }}" class="text-dark text-decoration-none hover-primary text-truncate-2">
                                {{ $event->title }}
                            </a>
                        </h5>

                        <div class="small text-muted mb-2">
                            <i class="bi bi-clock me-1 text-primary"></i>
                            {{ $event->start_time->format('H:i - d/m/Y') }}
                        </div>

                        <div class="small text-muted mb-3 text-truncate">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>
                            {{ $event->venue->name }} ({{ $event->venue->city }})
                        </div>

                        <p class="small text-secondary mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $event->description }}
                        </p>

                        <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Giá từ</small>
                                <span class="fs-5 fw-bold text-primary">
                                    {{ $event->min_price !== null ? number_format($event->min_price, 0, ',', '.') . ' đ' : 'Liên hệ' }}
                                </span>
                            </div>
                            <a href="{{ route('events.show', $event->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                                Đặt vé <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $events->links() }}
    </div>
@else
    <div class="card card-custom border-0 shadow-sm p-5 text-center my-4">
        <div class="text-muted mb-3">
            <i class="bi bi-search" style="font-size: 3.5rem;"></i>
        </div>
        <h4 class="fw-bold mb-2">Không tìm thấy sự kiện nào</h4>
        <p class="text-secondary mb-3">Rất tiếc, hiện tại không có sự kiện nào khớp với tiêu chí tìm kiếm của bạn.</p>
        <div>
            <a href="{{ route('home') }}" class="btn btn-primary-custom px-4">
                <i class="bi bi-arrow-clockwise me-1"></i> Đặt lại bộ lọc
            </a>
        </div>
    </div>
@endif
@endsection
