@extends('layouts.app')

@section('title', 'Đăng Ký Tài Khoản')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-7 col-lg-6">
        <div class="card card-custom border-0 shadow-sm p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bi bi-person-plus-fill fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1">Tạo Tài Khoản Mới</h3>
                <p class="text-muted small">Khám phá và đặt vé hàng ngàn sự kiện hấp dẫn</p>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Nguyễn Văn A">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0912345678">
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Tối thiểu 8 ký tự">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" id="password_confirmation" name="password_confirmation" required placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6">
                    <i class="bi bi-person-check me-1"></i> Hoàn Tất Đăng Ký
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center small text-muted">
                Đã có tài khoản? <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>
@endsection
