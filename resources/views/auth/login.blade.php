@extends('layouts.app')

@section('title', 'Đăng Nhập Tài Khoản')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom border-0 shadow-sm p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bi bi-box-arrow-in-right fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1">Đăng Nhập</h3>
                <p class="text-muted small">Chào mừng bạn trở lại với EventTicket</p>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label fw-semibold mb-0">Mật khẩu <span class="text-danger">*</span></label>
                    </div>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Nhập mật khẩu">
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-secondary" for="remember">Ghi nhớ đăng nhập trên thiết bị này</label>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng Nhập
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center small text-muted">
                Chưa có tài khoản? <a href="{{ route('register') }}" class="fw-bold text-primary text-decoration-none">Đăng ký ngay</a>
            </div>

            {{-- Demo Accounts Quick Helper --}}
            <div class="mt-4 p-3 rounded-3 bg-light border small">
                <div class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary me-1"></i> Tài khoản trải nghiệm (Mật khẩu: <code>password</code>):</div>
                <div class="d-flex flex-column gap-1">
                    <div><span class="badge bg-danger">Admin:</span> <code>admin@example.com</code></div>
                    <div><span class="badge bg-warning text-dark">Organizer:</span> <code>organizer1@example.com</code></div>
                    <div><span class="badge bg-primary">Customer:</span> <code>customer1@example.com</code></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
