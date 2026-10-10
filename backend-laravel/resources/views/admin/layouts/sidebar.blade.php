@php
    $links = [
        ['route' => 'admin.index', 'match' => 'admin.index', 'icon' => 'bi-grid-1x2', 'label' => 'dashboard'],
        ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'bi-bag-check', 'label' => 'orders'],
        ['route' => 'admin.pictures.index', 'match' => 'admin.pictures.*', 'icon' => 'bi-images', 'label' => 'pictures'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'bi-tags', 'label' => 'categories'],
        ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'icon' => 'bi-star', 'label' => 'reviews'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'bi-people', 'label' => 'users'],
    ];
@endphp
<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar">
    <div class="sidebar-brand">
        <span class="brand-logo"><i class="bi bi-image"></i></span>
        <div>
            <div class="fw-bold">Picture Shop</div>
            <small>{{ __('admin.panel') }}</small>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas"
            data-bs-target="#adminSidebar" aria-label="Close"></button>
    </div>
    <nav class="sidebar-nav">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}"
                class="sidebar-link {{ request()->routeIs($link['match']) ? 'active' : '' }}">
                <i class="bi {{ $link['icon'] }}"></i>
                {{ __('admin.nav.' . $link['label']) }}
            </a>
        @endforeach
    </nav>
    <div class="sidebar-footer">
        <a href="{{ config('app.frontend_url') }}" target="_blank" class="sidebar-link">
            <i class="bi bi-box-arrow-up-right"></i> {{ __('admin.open_storefront') }}
        </a>
    </div>
</aside>
