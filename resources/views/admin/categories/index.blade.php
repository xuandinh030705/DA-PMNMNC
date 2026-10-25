@extends('layouts.panel')

@section('title', 'Quản Lý Danh Mục')
@section('page_title', 'Danh Mục Sự Kiện')

@section('content')
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-tags me-2 text-primary"></i>Danh Sách Danh Mục</h5>
                <small class="text-muted">Quản lý các chủ đề sự kiện trên toàn hệ thống</small>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Thêm danh mục mới
            </a>
        </div>

        @if ($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width: 60px;">#ID</th>
                            <th class="py-3">Tên danh mục</th>
                            <th class="py-3">Đường dẫn (Slug)</th>
                            <th class="py-3">Mô tả</th>
                            <th class="py-3 text-center">Số sự kiện</th>
                            <th class="text-end pe-3 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td class="ps-3 py-3 fw-bold text-muted">{{ $cat->id }}</td>
                                <td class="fw-bold text-dark">{{ $cat->name }}</td>
                                <td><code>{{ $cat->slug }}</code></td>
                                <td class="small text-muted text-truncate" style="max-width: 280px;">
                                    {{ $cat->description ?: 'Chưa có mô tả' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $cat->events_count }} sự kiện
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $categories->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                <p>Chưa có danh mục nào được khởi tạo.</p>
            </div>
        @endif
    </div>
</div>
@endsection
