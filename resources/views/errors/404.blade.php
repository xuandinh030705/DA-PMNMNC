@extends('layouts.app')

@section('title', '404 - Không Tìm Thấy Trang')

@section('content')
<div class="row justify-content-center py-5 text-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom border-0 p-5 shadow-sm">
            <div class="text-warning mb-3">
                <i class="bi bi-question-circle" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-2">404</h1>
            <h4 class="fw-semibold text-secondary mb-3">Không Tìm Thấy Trang</h4>
            <p class="text-muted mb-4">
                {{ $exception->getMessage() ?: 'Trang hoặc sự kiện bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc chưa được công khai.' }}
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
