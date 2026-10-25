@extends('layouts.panel')

@section('title', 'Quản Lý Người Dùng')
@section('page_title', 'Quản Lý Tài Khoản Người Dùng')

@section('content')
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <!-- Filter & Search Bar -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Tên, email, số điện thoại...">
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select" name="role" onchange="this.form.submit()">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        <option value="organizer" {{ request('role') === 'organizer' ? 'selected' : '' }}>Ban tổ chức (Organizer)</option>
                        <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Khách hàng (Customer)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex justify-content-md-end">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-filter"></i> Lọc
                    </button>
                </div>
            </div>
        </form>

        @if ($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width: 60px;">#ID</th>
                            <th class="py-3">Họ và tên</th>
                            <th class="py-3">Địa chỉ Email</th>
                            <th class="py-3">Số điện thoại</th>
                            <th class="py-3">Vai trò</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="text-end pe-3 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="ps-3 py-3 fw-bold text-muted">{{ $user->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    @if ($user->id === Auth::id())
                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">Chính bạn</span>
                                    @endif
                                </td>
                                <td><code>{{ $user->email }}</code></td>
                                <td class="small">{{ $user->phone ?: 'Chưa cập nhật' }}</td>
                                <td>
                                    <span class="badge badge-role badge-role-{{ $user->role }}">
                                        {{ $user->role === 'admin' ? 'Quản Trị Viên' : ($user->role === 'organizer' ? 'Ban Tổ Chức' : 'Khách Hàng') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle-fill me-1"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-lock-fill me-1"></i> Đã bị khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa vai trò">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>

                                    @if ($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn {{ $user->is_active ? 'khóa' : 'mở khóa' }} tài khoản này?');">
                                            @csrf
                                            @if ($user->is_active)
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Khóa tài khoản">
                                                    <i class="bi bi-lock"></i> Khóa
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mở khóa tài khoản">
                                                    <i class="bi bi-unlock"></i> Mở
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 mb-2 d-block"></i>
                <p>Không tìm thấy người dùng nào phù hợp.</p>
            </div>
        @endif
    </div>
</div>
@endsection
