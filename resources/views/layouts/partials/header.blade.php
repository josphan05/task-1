<header class="header header-sticky p-0 mb-4">
    <div class="container-fluid px-4">
        <button class="header-toggler d-lg-none" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()" style="margin-inline-start: -14px;">
            <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>

        <a class="header-brand d-lg-none" href="{{ route('dashboard') }}">
            <i class="bi bi-hexagon-fill" style="font-size: 1.5rem;"></i>
        </a>

        <ul class="header-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-bell" style="font-size: 1.25rem;"></i>
                    <span class="badge bg-danger ms-1">5</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-envelope" style="font-size: 1.25rem;"></i>
                </a>
            </li>
        </ul>

        <ul class="header-nav">
            <li class="nav-item dropdown">
                <a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-md">
                        <img class="avatar-img" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=321fdb&color=fff" alt="{{ auth()->user()->name }}">
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </div>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-bell me-2"></i> Thông báo
                        <span class="badge badge-sm bg-info ms-2">42</span>
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-envelope me-2"></i> Tin nhắn
                        <span class="badge badge-sm bg-success ms-2">3</span>
                    </a>
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">
                        <div class="fw-semibold">Cài đặt</div>
                    </div>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person me-2"></i> Hồ sơ
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-gear me-2"></i> Cài đặt
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</header>
