@php
    $links = [
        ['route' => 'admin.index', 'match' => 'admin.index', 'icon' => 'bi-grid-1x2', 'label' => 'Dashboard'],
        ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'bi-bag-check', 'label' => 'Orders'],
        ['route' => 'admin.pictures.index', 'match' => 'admin.pictures.*', 'icon' => 'bi-images', 'label' => 'Pictures'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'bi-tags', 'label' => 'Categories'],
        ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'icon' => 'bi-star', 'label' => 'Reviews'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'bi-people', 'label' => 'Users'],
    ];
    $pending = \App\Models\Review::where('approved', 0)->count();
@endphp
<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar">
    <div class="sidebar-brand">
        <span class="brand-logo"><i class="bi bi-image"></i></span>
        <div>
            <div class="fw-bold">Picture Shop</div>
            <small>Admin panel</small>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas"
            data-bs-target="#adminSidebar" aria-label="Close"></button>
    </div>
    <nav class="sidebar-nav">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}"
                class="sidebar-link {{ request()->routeIs($link['match']) ? 'active' : '' }}">
                <i class="bi {{ $link['icon'] }}"></i>
                {{ $link['label'] }}
                @if ($link['label'] === 'Reviews' && $pending)
                    <span class="badge rounded-pill bg-warning text-dark ms-auto">{{ $pending }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    <div class="sidebar-footer">
        <a href="{{ config('app.frontend_url') }}" target="_blank" class="sidebar-link">
            <i class="bi bi-box-arrow-up-right"></i> Open storefront
        </a>
    </div>
</aside>
