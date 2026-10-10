<header class="admin-topbar">
    <button class="btn btn-icon btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"
        aria-label="Open menu">
        <i class="bi bi-list"></i>
    </button>
    <div>
        <h1 class="topbar-title">@yield('title')</h1>
        @hasSection('subtitle')
            <div class="topbar-subtitle">@yield('subtitle')</div>
        @endif
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @yield('actions')
        <div class="dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <span class="avatar">{{ strtoupper(substr(auth()->guard('admin')->user()->name, 0, 1)) }}</span>
                <span class="d-none d-sm-inline fw-semibold">{{ auth()->guard('admin')->user()->name }}</span>
                <i class="bi bi-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2 small text-muted">{{ auth()->guard('admin')->user()->email }}</li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign
                            out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
