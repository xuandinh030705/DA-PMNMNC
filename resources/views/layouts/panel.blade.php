<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bảng Điều Khiển') - EventTicket Management</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        #sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #94a3b8;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .sidebar-nav {
            list-style: none;
            padding: 1rem 0.75rem;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-header {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: #64748b;
            padding: 0.75rem 0.75rem 0.25rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
        }

        .sidebar-link:hover {
            color: #fff;
            background-color: var(--sidebar-hover);
        }

        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .sidebar-link i {
            font-size: 1.15rem;
        }

        #page-content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
        }

        .main-container {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .card-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .card-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            background: #ffffff;
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
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
            }
            #sidebar.active {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <a href="{{ route('home') }}" class="sidebar-brand">
                <div class="bg-primary text-white rounded-3 p-1 px-2 d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-ticket-perforated-fill fs-5"></i>
                </div>
                <span>EventTicket</span>
            </a>

            <div class="px-3 py-2 border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 700;">
                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="text-truncate lh-1">
                    <div class="text-white small fw-bold text-truncate">{{ Auth::user()->name }}</div>
                    <small class="badge {{ Auth::user()->isAdmin() ? 'bg-danger' : 'bg-warning text-dark' }} mt-1" style="font-size: 0.65rem;">
                        {{ Auth::user()->isAdmin() ? 'QUẢN TRỊ VIÊN' : 'BAN TỔ CHỨC' }}
                    </small>
                </div>
            </div>

            <ul class="sidebar-nav">
                @if (Auth::user()->isAdmin())
                    <li class="nav-header">Quản trị hệ thống</li>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Thống Kê
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.events.index') }}" class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event"></i> Phê Duyệt Sự Kiện
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i> Quản Lý Danh Mục
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.venues.index') }}" class="sidebar-link {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}">
                            <i class="bi bi-geo-alt"></i> Quản Lý Địa Điểm
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Quản Lý Người Dùng
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Quản Lý Đơn Hàng
                        </a>
                    </li>
                @elseif (Auth::user()->isOrganizer())
                    <li class="nav-header">Kênh Ban Tổ Chức</li>
                    <li>
                        <a href="{{ route('organizer.dashboard') }}" class="sidebar-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Thống Kê
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('organizer.events.index') }}" class="sidebar-link {{ request()->routeIs('organizer.events.index') || request()->routeIs('organizer.events.edit') || request()->routeIs('organizer.events.ticket-types.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Sự Kiện Của Tôi
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('organizer.events.create') }}" class="sidebar-link {{ request()->routeIs('organizer.events.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i> Tạo Sự Kiện Mới
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('organizer.orders.index') }}" class="sidebar-link {{ request()->routeIs('organizer.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-cart-check"></i> Đơn Hàng Bán Vé
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('organizer.checkin.form') }}" class="sidebar-link {{ request()->routeIs('organizer.checkin.*') ? 'active' : '' }}">
                            <i class="bi bi-qr-code-scan"></i> Soát Vé (Check-in)
                        </a>
                    </li>
                @endif

                <li class="nav-header">Hệ thống</li>
                <li>
                    <a href="{{ route('home') }}" class="sidebar-link">
                        <i class="bi bi-shop"></i> Về Trang Bán Vé
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i> Hồ Sơ Cá Nhân
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                        @csrf
                        <button type="submit" class="sidebar-link w-100 text-start border-0 bg-transparent text-danger">
                            <i class="bi bi-box-arrow-right"></i> Đăng Xuất
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <header class="top-navbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" id="sidebarToggle">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Bảng Quản Trị')</h5>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Xem trang bán vé">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Xem Website
                    </a>
                </div>
            </header>

            <main class="main-container">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    @stack('scripts')
</body>
</html>
