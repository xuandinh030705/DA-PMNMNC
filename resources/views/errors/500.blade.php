@extends('layouts.app')

@section('title', '500 - Lỗi Máy Chủ')

@section('content')
<div class="row justify-content-center py-5 text-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom border-0 p-5 shadow-sm">
            <div class="text-danger mb-3">
                <i class="bi bi-exclamation-triangle" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-2">500</h1>
            <h4 class="fw-semibold text-secondary mb-3">Đã Xảy Ra Lỗi Máy Chủ</h4>
            <p class="text-muted mb-4">
                Hệ thống đang gặp sự cố tạm thời trong quá trình xử lý yêu cầu của bạn. Đội ngũ kỹ thuật đã được thông báo.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-primary-custom">
                    <i class="bi bi-house-door me-1"></i> Về Trang Chủ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
