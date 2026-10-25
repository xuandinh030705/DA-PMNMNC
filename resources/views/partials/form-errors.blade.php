@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-x-octagon-fill fs-5 me-2"></i>
            <strong class="fs-6">Đã xảy ra lỗi nhập liệu. Vui lòng kiểm tra lại:</strong>
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif
