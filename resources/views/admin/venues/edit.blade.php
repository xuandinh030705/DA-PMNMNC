@extends('layouts.panel')

@section('title', 'Sửa Địa Điểm: ' . $venue->name)
@section('page_title', 'Sửa Địa Điểm Tổ Chức Sự Kiện')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-pencil me-2 text-primary"></i>Chỉnh Sửa Địa Điểm</h5>
                    <small class="text-muted">{{ $venue->city }}</small>
                </div>
                <a href="{{ route('admin.venues.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('admin.venues.update', $venue) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên địa điểm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $venue->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $venue->address) }}" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="city" class="form-label fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $venue->city) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="capacity" class="form-label fw-semibold">Sức chứa tối đa (chỗ ngồi) <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $venue->capacity) }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.venues.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Cập Nhật Địa Điểm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
