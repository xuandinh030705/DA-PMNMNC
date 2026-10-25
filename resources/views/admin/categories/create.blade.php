@extends('layouts.panel')

@section('title', 'Thêm Danh Mục Mới')
@section('page_title', 'Thêm Danh Mục Sự Kiện Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-tag me-2 text-primary"></i>Điền Thông Tin Danh Mục</h5>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Ví dụ: Âm nhạc & Hòa nhạc">
                    <div class="form-text small text-muted">Đường dẫn thân thiện (slug) sẽ được tự động khởi tạo từ tên danh mục.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Mô tả ngắn</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Mô tả phạm vi các sự kiện thuộc danh mục này...">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Lưu Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
