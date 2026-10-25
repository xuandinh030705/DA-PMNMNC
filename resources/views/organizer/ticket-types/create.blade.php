@extends('layouts.panel')

@section('title', 'Thêm Loại Vé Mới')
@section('page_title', 'Thêm Loại Vé: ' . $event->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-tag me-2 text-primary"></i>Thông Tin Loại Vé Mới</h5>
                <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại sự kiện
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('organizer.events.ticket-types.store', $event) }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên loại vé <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ví dụ: Vé VIP, Vé Tiêu Chuẩn, Early Bird...">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-semibold">Giá vé (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="1000" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', 0) }}" required>
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-semibold">Tổng số lượng phát hành <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 100) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sale_start" class="form-label fw-semibold">Thời gian mở bán <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('sale_start') is-invalid @enderror" id="sale_start" name="sale_start" value="{{ old('sale_start', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="sale_end" class="form-label fw-semibold">Thời gian kết thúc bán <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('sale_end') is-invalid @enderror" id="sale_end" name="sale_end" value="{{ old('sale_end', $event->start_time->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="max_per_order" class="form-label fw-semibold">Số lượng vé tối đa trên mỗi đơn <span class="text-danger">*</span></label>
                    <input type="number" min="1" max="50" class="form-control @error('max_per_order') is-invalid @enderror" id="max_per_order" name="max_per_order" value="{{ old('max_per_order', 10) }}" required>
                    <div class="form-text small text-muted">Giới hạn để ngăn chặn đầu cơ gom vé (mặc định: 10 vé/đơn).</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Lưu Loại Vé
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
