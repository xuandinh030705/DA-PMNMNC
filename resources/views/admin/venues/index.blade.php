@extends('layouts.panel')

@section('title', 'Quản Lý Địa Điểm')
@section('page_title', 'Địa Điểm Tổ Chức Sự Kiện')

@section('content')
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i>Danh Sách Địa Điểm</h5>
                <small class="text-muted">Quản lý trung tâm hội nghị, sân vận động, nhà hát trên toàn quốc</small>
            </div>
            <a href="{{ route('admin.venues.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Thêm địa điểm mới
            </a>
        </div>

        @if ($venues->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width: 60px;">#ID</th>
                            <th class="py-3">Tên địa điểm</th>
                            <th class="py-3">Địa chỉ chi tiết</th>
                            <th class="py-3">Tỉnh/Thành phố</th>
                            <th class="py-3 text-center">Sức chứa tối đa</th>
                            <th class="py-3 text-center">Số sự kiện</th>
                            <th class="text-end pe-3 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venues as $venue)
                            <tr>
                                <td class="ps-3 py-3 fw-bold text-muted">{{ $venue->id }}</td>
                                <td class="fw-bold text-dark">{{ $venue->name }}</td>
                                <td class="small text-muted">{{ $venue->address }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $venue->city }}</span>
                                </td>
                                <td class="text-center fw-semibold text-primary">
                                    {{ number_format($venue->capacity) }} chỗ
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $venue->events_count }} sự kiện
                                    </span>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.venues.edit', $venue) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa điểm này?');">
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
                {{ $venues->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                <p>Chưa có địa điểm nào được khởi tạo.</p>
            </div>
        @endif
    </div>
</div>
@endsection
