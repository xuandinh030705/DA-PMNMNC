@extends('layouts.panel')

@section('title', 'Tạo Sự Kiện Mới')
@section('page_title', 'Tạo Sự Kiện Mới (Bản Nháp)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Điền Thông Tin Sự Kiện</h5>
                <a href="{{ route('organizer.events.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Tiêu đề sự kiện <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Đại Nhạc Hội Mùa Hè 2026">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-semibold">Danh mục sự kiện <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="venue_id" class="form-label fw-semibold">Địa điểm tổ chức <span class="text-danger">*</span></label>
                        <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" required>
                            <option value="">-- Chọn địa điểm --</option>
                            @foreach ($venues as $venue)
                                <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }} ({{ $venue->city }} - Sức chứa: {{ number_format($venue->capacity) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="start_time" class="form-label fw-semibold">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="end_time" class="form-label fw-semibold">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="banner" class="form-label fw-semibold">Ảnh Banner Sự Kiện</label>
                    <input type="file" class="form-control @error('banner') is-invalid @enderror" id="banner" name="banner" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text small text-muted">Định dạng hỗ trợ: JPG, PNG, WEBP. Dung lượng tối đa: 2MB.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Mô tả chi tiết sự kiện <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" required placeholder="Giới thiệu nội dung sự kiện, lịch trình diễn ra, quy định tham gia...">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Lưu Bản Nháp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
