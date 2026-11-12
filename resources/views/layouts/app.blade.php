<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ Thống Quản Lý Bán Vé Sự Kiện') - EventTicket</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Theme Styles -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #06b6d4;
            --dark-surface: #0f172a;
            --card-radius: 14px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
        }

        .brand-logo {
            font-weight: 800;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.45rem;
            letter-spacing: -0.5px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        }

        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            overflow: hidden;
            background: #ffffff;
        }

        .card-custom:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .badge-role {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .badge-role-admin { background-color: #fee2e2; color: #dc2626; }
        .badge-role-organizer { background-color: #fef3c7; color: #d97706; }
        .badge-role-customer { background-color: #e0e7ff; color: #4338ca; }

        .ticket-code-font {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        /* Print Styles for Tickets */
        @media print {
            .navbar, .footer, .btn, .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
            .card-ticket {
                border: 2px dashed #000 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar Header -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-custom py-2 py-lg-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <div class="bg-primary text-white rounded-3 p-1 px-2 d-inline-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-ticket-perforated-fill fs-5"></i>
                    </div>
                    <span class="brand-logo">EventTicket</span>
                </a>

                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ request()->routeIs('home') ? 'active text-primary' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-house-door me-1"></i> Trang chủ
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-primary-custom" href="{{ route('register') }}">
                                    <i class="bi bi-person-plus me-1"></i> Đăng ký
                                </a>
                            </li>
                        @else
                            {{-- Role Badges & Quick Links --}}
                            @if (Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="btn btn-sm btn-outline-danger fw-bold" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-shield-lock me-1"></i> Trang Quản Trị
                                    </a>
                                </li>
                            @elseif (Auth::user()->isOrganizer())
                                <li class="nav-item">
                                    <a class="btn btn-sm btn-outline-warning fw-bold text-dark" href="{{ route('organizer.dashboard') }}">
                                        <i class="bi bi-speedometer2 me-1"></i> Kênh Ban Tổ Chức
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold {{ request()->routeIs('orders.*') ? 'active text-primary' : '' }}" href="{{ route('orders.index') }}">
                                        <i class="bi bi-bag-check me-1"></i> Đơn hàng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold {{ request()->routeIs('tickets.*') ? 'active text-primary' : '' }}" href="{{ route('tickets.index') }}">
                                        <i class="bi bi-ticket-detailed me-1"></i> Vé của tôi
                                    </a>
                                </li>
                            @endif

                            {{-- User Dropdown Menu --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 rounded-3 bg-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700; font-size: 0.85rem;">
                                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <div class="d-none d-md-block text-start lh-1">
                                        <span class="d-block fw-semibold text-truncate" style="max-width: 130px;">{{ Auth::user()->name }}</span>
                                        <small class="badge badge-role badge-role-{{ Auth::user()->role }} mt-1">
                                            {{ Auth::user()->role === 'admin' ? 'Quản trị' : (Auth::user()->role === 'organizer' ? 'Ban tổ chức' : 'Khách hàng') }}
                                        </small>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                                    <li class="px-3 py-2 d-md-none border-bottom">
                                        <div class="fw-bold">{{ Auth::user()->name }}</div>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                            <i class="bi bi-person-gear me-2 text-muted"></i> Hồ sơ cá nhân
                                        </a>
                                    </li>
                                    @if (Auth::user()->isCustomer())
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('orders.index') }}">
                                                <i class="bi bi-receipt me-2 text-muted"></i> Lịch sử đơn hàng
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tickets.index') }}">
                                                <i class="bi bi-ticket-perforated me-2 text-muted"></i> Vé đã mua
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow-1 py-4">
        <div class="container">
            @include('partials.alerts')
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-auto border-top border-secondary-subtle">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-primary text-white rounded-3 p-1 px-2 d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-ticket-perforated-fill fs-5"></i>
                        </div>
                        <span class="fs-4 fw-bold text-white">EventTicket</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        Nền tảng quản lý và phân phối vé sự kiện hàng đầu Việt Nam. Kết nối cảm xúc, đem đến trải nghiệm đặt vé nhanh chóng, tiện lợi và an toàn.
                    </p>
                    <div class="d-flex gap-3 text-secondary">
                        <span class="badge bg-secondary"><i class="bi bi-shield-check me-1"></i> An toàn 100%</span>
                        <span class="badge bg-secondary"><i class="bi bi-lightning-charge me-1"></i> Vé tức thì</span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold text-light mb-3">Khám phá</h6>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('home') }}" class="text-decoration-none text-secondary hover-text-white">Trang chủ</a></li>
                        <li><a href="{{ route('home') }}?sort=date_asc" class="text-decoration-none text-secondary">Sự kiện sắp diễn ra</a></li>
                        <li><a href="{{ route('home') }}?sort=price_asc" class="text-decoration-none text-secondary">Giá tốt nhất</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold text-light mb-3">Hỗ trợ khách hàng</h6>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li><i class="bi bi-envelope me-2"></i> support@eventticket.vn</li>
                        <li><i class="bi bi-telephone me-2"></i> 1900 6868 (8:00 - 21:00)</li>
                        <li><i class="bi bi-geo-alt me-2"></i> Hà Nội & TP. Hồ Chí Minh</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-uppercase fw-bold text-light mb-3">Ban tổ chức sự kiện</h6>
                    <p class="text-secondary small mb-3">
                        Bạn muốn tổ chức và phân phối vé sự kiện tiếp theo của mình? Đăng ký tài khoản và bắt đầu bán vé ngay hôm nay!
                    </p>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            Tham gia ngay <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endguest
                </div>
            </div>

            <hr class="border-secondary mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
                <div>&copy; {{ date('Y') }} EventTicket - Hệ Thống Quản Lý Bán Vé Sự Kiện. Đồ án môn học thực hiện bởi Nhóm phát triển.</div>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-dark border border-secondary text-secondary">PHP 8.3 & Laravel 13</span>
                    <span class="badge bg-dark border border-secondary text-secondary">Bootstrap 5.3</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
