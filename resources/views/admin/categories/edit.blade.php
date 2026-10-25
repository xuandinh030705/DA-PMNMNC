@extends('layouts.panel')

@section('title', 'Sửa Danh Mục: ' . $category->name)
@section('page_title', 'Sửa Danh Mục Sự Kiện')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-pencil me-2 text-primary"></i>Chỉnh Sửa Danh Mục</h5>
                    <small class="text-muted">Slug hiện tại: <code>{{ $category->slug }}</code></small>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Mô tả ngắn</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Cập Nhật Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
