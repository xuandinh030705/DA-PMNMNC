@extends('layouts.app')

@section('title', '419 - Phiên Làm Việc Hết Hạn')

@section('content')
<div class="row justify-content-center py-5 text-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom border-0 p-5 shadow-sm">
            <div class="text-info mb-3">
                <i class="bi bi-clock-history" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-2">419</h1>
            <h4 class="fw-semibold text-secondary mb-3">Phiên Làm Việc Đã Hết Hạn</h4>
            <p class="text-muted mb-4">
                Trang đã ở trạng thái chờ quá lâu. Vui lòng tải lại trang và thực hiện lại thao tác.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="javascript:location.reload()" class="btn btn-primary-custom">
                    <i class="bi bi-arrow-clockwise me-1"></i> Tải Lại Trang
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house-door me-1"></i> Về Trang Chủ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
