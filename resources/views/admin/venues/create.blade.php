@extends('layouts.panel')

@section('title', 'Thêm Địa Điểm Mới')
@section('page_title', 'Thêm Địa Điểm Tổ Chức Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i>Điền Thông Tin Địa Điểm</h5>
                <a href="{{ route('admin.venues.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('admin.venues.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên địa điểm <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Ví dụ: Trung tâm Hội nghị Quốc gia">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện...">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="city" class="form-label fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required placeholder="Ví dụ: Hà Nội, TP. Hồ Chí Minh...">
                    </div>

                    <div class="col-md-6">
                        <label for="capacity" class="form-label fw-semibold">Sức chứa tối đa (chỗ ngồi) <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', 1000) }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.venues.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Lưu Địa Điểm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
