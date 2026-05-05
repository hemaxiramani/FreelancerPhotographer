<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Freelancer Photographers Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --brand: #0F6B5E; --brand-light: #e0f2ef; --brand-dark: #0a4f45; --sidebar-w: 260px; }
        body { background: #f5f6fa; font-family: 'Segoe UI', system-ui, sans-serif; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--brand-dark);
            position: fixed; top: 0; left: 0; z-index: 1040;
            transition: transform .3s ease;
            display: flex; flex-direction: column;
        }
        .sidebar .brand {
            padding: 16px 20px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar .brand img { width: 36px; height: 36px; border-radius: 8px; }
        .sidebar .brand-text { color: #fff; font-size: 15px; font-weight: 700; line-height: 1.2; }
        .sidebar .brand-text small { font-size: 11px; font-weight: 400; opacity: .7; display: block; }
        .sidebar .nav-link {
            color: rgba(255,255,255,.7); padding: 11px 20px; font-size: 14px;
            border-left: 3px solid transparent; transition: all .15s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.08); border-left-color: #fff; }
        .sidebar .nav-link i { width: 22px; margin-right: 10px; font-size: 15px; }
        .sidebar-footer { margin-top: auto; padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.1); }
        .sidebar-footer .text-muted-light { color: rgba(255,255,255,.4); font-size: 11px; }

        /* Main content */
        .main-content { margin-left: var(--sidebar-w); padding: 0; min-height: 100vh; transition: margin-left .3s ease; }
        .topbar {
            background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1020;
        }
        .topbar h5 { margin: 0; font-size: 16px; font-weight: 600; }
        .topbar .page-icon { color: var(--brand); margin-right: 8px; }
        .content-area { padding: 24px; }

        /* Sidebar toggle button (mobile) */
        .sidebar-toggle {
            display: none; background: none; border: none; font-size: 22px;
            color: #333; padding: 4px 8px; cursor: pointer;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,.5); z-index: 1035; cursor: pointer;
        }

        /* Cards & UI */
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb; }
        .stat-card .stat-num { font-size: 28px; font-weight: 700; color: var(--brand); }
        .stat-card .stat-label { font-size: 13px; color: #6b7280; margin-top: 4px; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-accepted { background: #d1fae5; color: #065f46; }
        .badge-declined { background: #fee2e2; color: #991b1b; }
        .badge-invalidated { background: #e5e7eb; color: #4b5563; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-blocked { background: #fee2e2; color: #991b1b; }
        .btn-brand { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover { background: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; }
        .card-header { background: #fff; border-bottom: 1px solid #e5e7eb; font-weight: 600; }
        .table th { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; white-space: nowrap; }
        .table td { font-size: 14px; }
        .sort-link { color: #6b7280; text-decoration: none; }
        .sort-link:hover { color: var(--brand); }
        .sort-link .bi { font-size: 10px; }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .sidebar-toggle { display: inline-block; }
            .main-content { margin-left: 0; }
        }
        @media (max-width: 767.98px) {
            .content-area { padding: 16px; }
            .topbar { padding: 10px 16px; }
            .table-responsive { font-size: 13px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('logo-square.png') }}" alt="Logo">
            <div class="brand-text">
                Freelancer Photographers
                <small>Admin Panel</small>
            </div>
        </div>
        <nav class="nav flex-column mt-2">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('admin.photographers*') ? 'active' : '' }}" href="{{ route('admin.photographers') }}">
                <i class="bi bi-people-fill"></i> Photographers
            </a>
            <a class="nav-link {{ request()->routeIs('admin.hire-requests*') ? 'active' : '' }}" href="{{ route('admin.hire-requests') }}">
                <i class="bi bi-send-fill"></i> Hire Requests
            </a>
            <a class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}" href="{{ route('admin.notifications') }}">
                <i class="bi bi-bell-fill"></i> Notifications
            </a>
            <a class="nav-link {{ request()->routeIs('admin.locations*') ? 'active' : '' }}" href="{{ route('admin.locations') }}">
                <i class="bi bi-geo-alt-fill"></i> Locations
            </a>
            <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories') }}">
                <i class="bi bi-tag-fill"></i> Categories
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="text-muted-light">© {{ date('Y') }} Freelancer Photographers</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-2" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h5><i class="page-icon bi @yield('icon', 'bi-grid-1x2-fill')"></i> @yield('title', 'Dashboard')</h5>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span></button>
            </form>
        </div>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        // Close sidebar on nav click (mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) toggleSidebar();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
