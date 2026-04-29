@php
    $adminLinks = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'admin.analytics.index', 'label' => 'Shop insights', 'icon' => 'bi-graph-up-arrow'],
        ['route' => 'admin.audit-logs.index', 'label' => 'Activity log', 'icon' => 'bi-journal-text'],
        ['route' => 'admin.ledger.index', 'label' => 'Payment records', 'icon' => 'bi-journal-richtext'],
        ['route' => 'admin.cod-treasury.index', 'label' => 'Cash vs rider reports', 'icon' => 'bi-cash-stack'],
        ['route' => 'admin.financial-disputes.index', 'label' => 'Financial disputes', 'icon' => 'bi-exclamation-octagon'],
        ['route' => 'admin.products.pending', 'label' => 'Product review', 'icon' => 'bi-hourglass-split', 'badge_key' => 'products'],
        ['route' => 'admin.products.approved', 'label' => 'Approved products', 'icon' => 'bi-check2-circle'],
        ['route' => 'admin.products.rejected', 'label' => 'Rejected products', 'icon' => 'bi-x-circle'],
        ['route' => 'admin.payments.pending', 'label' => 'Payment review', 'icon' => 'bi-credit-card', 'badge_key' => 'payments'],
        ['route' => 'admin.payments.verified', 'label' => 'Recorded payments', 'icon' => 'bi-patch-check'],
        ['route' => 'admin.users.artisans', 'label' => 'Artisans', 'icon' => 'bi-person-workspace', 'badge_key' => 'artisans'],
        ['route' => 'admin.users.customers', 'label' => 'Customers', 'icon' => 'bi-people'],
        ['route' => 'admin.riders.index', 'label' => 'Riders', 'icon' => 'bi-bicycle'],
        ['route' => 'admin.deliveries.index', 'label' => 'Deliveries', 'icon' => 'bi-truck', 'badge_key' => 'deliveries'],
        ['route' => 'admin.delivery-reports.index', 'label' => 'Delivery reports', 'icon' => 'bi-flag', 'badge_key' => 'reports'],
        ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => 'bi-tags'],
        ['route' => 'admin.vouchers.index', 'label' => 'Promo vouchers', 'icon' => 'bi-ticket-perforated'],
        ['route' => 'admin.sales.index', 'label' => 'Sales', 'icon' => 'bi-receipt-cutoff'],
    ];
@endphp

<div class="admin-sidebar d-none d-lg-flex flex-column">
    <div class="admin-sidebar__head d-flex align-items-center justify-content-between">
        <h6 class="mb-0">Admin</h6>
        <button class="btn btn-sm btn-outline-secondary admin-sidebar-toggle" type="button" aria-label="Toggle admin sidebar">
            <i class="bi bi-layout-sidebar-inset-reverse"></i>
        </button>
    </div>
    <div class="admin-sidebar__links">
        @foreach($adminLinks as $link)
            <a href="{{ route($link['route']) }}"
               class="admin-sidebar__link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
               title="{{ $link['label'] }}">
                <i class="bi {{ $link['icon'] }} me-2"></i>
                <span>{{ $link['label'] }}</span>
                @if(!empty($link['badge_key']) && (($adminPendingCounts[$link['badge_key']] ?? 0) > 0))
                    <span class="badge rounded-pill bg-danger ms-auto">{{ $adminPendingCounts[$link['badge_key']] }}</span>
                @endif
            </a>
        @endforeach
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
                    @if(!empty($link['badge_key']) && (($adminPendingCounts[$link['badge_key']] ?? 0) > 0))
                        <span class="badge rounded-pill bg-danger float-end">{{ $adminPendingCounts[$link['badge_key']] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const key = 'admin-sidebar-collapsed';
    const sidebarToggle = document.querySelector('.admin-sidebar-toggle');
    const headerToggle = document.querySelector('.admin-sidebar-trigger');
    const offcanvasEl = document.getElementById('adminNavOffcanvas');

    const collapsed = localStorage.getItem(key) === '1';
    if (collapsed && sidebarToggle) body.classList.add('admin-sidebar-collapsed');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            body.classList.toggle('admin-sidebar-collapsed');
            localStorage.setItem(key, body.classList.contains('admin-sidebar-collapsed') ? '1' : '0');
        });
    }

    if (!headerToggle) return;

    headerToggle.addEventListener('click', function () {
        if (window.innerWidth >= 992) {
            body.classList.toggle('admin-sidebar-collapsed');
            localStorage.setItem(key, body.classList.contains('admin-sidebar-collapsed') ? '1' : '0');
            return;
        }

        if (offcanvasEl && window.bootstrap) {
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
            offcanvas.show();
        }
    });
});
</script>
@endpush
