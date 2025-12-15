<aside class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <span class="sidebar-brand-full">
                <i class="bi bi-hexagon-fill me-2" style="font-size: 1.5rem;"></i>
                <span class="fw-bold">Core Laravel</span>
            </span>
            <span class="sidebar-brand-narrow">
                <i class="bi bi-hexagon-fill" style="font-size: 1.5rem;"></i>
            </span>
        </div>
        <button class="btn-close d-lg-none" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark" aria-label="Close"></button>
    </div>

    <ul class="sidebar-nav" data-coreui="navigation" data-compact="">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="nav-icon bi bi-speedometer2"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <li class="nav-title">Quản lý</li>

        <!-- Users -->
        <li class="nav-group {{ request()->routeIs('users.*') ? 'show' : '' }}">
            <a class="nav-link nav-group-toggle" href="#">
                <i class="nav-icon bi bi-people"></i>
                <span class="nav-text">Người dùng</span>
            </a>
            <ul class="nav-group-items compact">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                        <span class="nav-text">Danh sách</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}" href="{{ route('users.create') }}">
                        <span class="nav-icon"><span class="nav-icon-bullet"></span></span>
                        <span class="nav-text">Thêm mới</span>
                    </a>
                </li>
            </ul>
        </li>
        <!-- Telegram -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('telegram.*') ? 'active' : '' }}" href="{{ route('telegram.index') }}">
                <i class="nav-icon bi bi-telegram"></i>
                <span class="nav-text">Gửi Telegram</span>
            </a>
        </li>
        {{-- <li class="nav-title">Hệ thống</li>

        <!-- Settings -->
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="nav-icon bi bi-gear"></i>
                <span class="nav-text">Cài đặt</span>
            </a>
        </li> --}}
    </ul>

    <div class="sidebar-footer border-top d-none d-md-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
</aside>
