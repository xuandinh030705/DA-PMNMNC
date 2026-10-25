@extends(Auth::user()->isCustomer() ? 'layouts.app' : 'layouts.panel')

@section('title', 'Hồ Sơ Cá Nhân')
@section('page_title', 'Hồ Sơ Cá Nhân')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom border-0 shadow-sm p-4">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fs-3 fw-bold shadow-sm" style="width: 64px; height: 64px;">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge badge-role badge-role-{{ $user->role }}">
                            {{ $user->role === 'admin' ? 'Quản Trị Viên' : ($user->role === 'organizer' ? 'Ban Tổ Chức' : 'Khách Hàng') }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle-fill me-1"></i> Hoạt động
                        </span>
                    </div>
                </div>
            </div>

            @include('partials.form-errors')

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Thông tin cơ bản</h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                        <input type="email" class="form-control bg-light" id="email" value="{{ $user->email }}" disabled title="Email là định danh tài khoản, không thể thay đổi">
                        <div class="form-text small text-muted">Email là định danh duy nhất của tài khoản.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0912345678">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-shield-lock-fill me-2 text-primary"></i>Đổi mật khẩu</h5>
                <p class="text-muted small mb-3">Nếu không có nhu cầu đổi mật khẩu, vui lòng để trống các ô bên dưới.</p>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="current_password" class="form-label fw-semibold">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi mật khẩu mới">
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Tối thiểu 8 ký tự">
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu mới">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-save me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
