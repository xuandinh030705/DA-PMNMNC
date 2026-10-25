@extends('layouts.panel')

@section('title', 'Sửa Người Dùng: ' . $user->name)
@section('page_title', 'Chỉnh Sửa Thông Tin Người Dùng')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-person-gear me-2 text-primary"></i>Cập Nhật Người Dùng</h5>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @include('partials.form-errors')

            @if ($user->id === Auth::id())
                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i> Bạn đang chỉnh sửa tài khoản quản trị của chính mình. Hệ thống sẽ chặn các hành động tự khóa hoặc tự hạ quyền quản trị.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ Email</label>
                    <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled title="Email là định danh tài khoản, không thể thay đổi">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="09########">
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold">Vai trò người dùng <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Khách hàng (Customer)</option>
                        <option value="organizer" {{ old('role', $user->role) === 'organizer' ? 'selected' : '' }}>Ban tổ chức (Organizer)</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Trạng thái tài khoản <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label text-success fw-semibold" for="status_active">
                                <i class="bi bi-check-circle me-1"></i> Hoạt động bình thường
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_active" id="status_locked" value="0" {{ !old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label text-danger fw-semibold" for="status_locked">
                                <i class="bi bi-lock me-1"></i> Tạm khóa tài khoản
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Lưu Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
