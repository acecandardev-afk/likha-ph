@php
    $adminLinks = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'admin.products.pending', 'label' => 'Pending products', 'icon' => 'bi-hourglass-split'],
        ['route' => 'admin.products.approved', 'label' => 'Approved products', 'icon' => 'bi-check2-circle'],
        ['route' => 'admin.products.rejected', 'label' => 'Rejected products', 'icon' => 'bi-x-circle'],
        ['route' => 'admin.payments.pending', 'label' => 'Pending payments', 'icon' => 'bi-credit-card'],
        ['route' => 'admin.payments.verified', 'label' => 'Verified payments', 'icon' => 'bi-patch-check'],
        ['route' => 'admin.users.artisans', 'label' => 'Artisans', 'icon' => 'bi-person-workspace'],
        ['route' => 'admin.users.customers', 'label' => 'Customers', 'icon' => 'bi-people'],
        ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => 'bi-tags'],
        ['route' => 'admin.sales.index', 'label' => 'Sales', 'icon' => 'bi-receipt-cutoff'],
    ];
@endphp

<div class="admin-nav-shell border-bottom">
    <div class="container py-2 d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminNavOffcanvas" aria-controls="adminNavOffcanvas">
            <i class="bi bi-list me-1"></i> Admin menu
        </button>
        <div class="d-none d-lg-flex flex-wrap gap-2">
            @foreach($adminLinks as $link)
                <a href="{{ route($link['route']) }}" class="btn btn-sm {{ request()->routeIs($link['route']) ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi {{ $link['icon'] }} me-1"></i>{{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="adminNavOffcanvas" aria-labelledby="adminNavOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminNavOffcanvasLabel">Admin menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-2">
        <div class="list-group list-group-flush">
            @foreach($adminLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="list-group-item list-group-item-action rounded-3 mb-1 {{ request()->routeIs($link['route']) ? 'active' : '' }}">
                    <i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
