@props(['active' => 'shipping'])

<nav class="nav nav-pills nav-fill border-bottom pb-3 mb-4">
    @if(auth()->user()->isArtisan())
        <a class="nav-link {{ $active === 'profile' ? 'active' : '' }}" href="{{ route('artisan.profile.edit') }}">
            <i class="bi bi-person-circle me-1"></i> Profile
        </a>
    @endif
    <a class="nav-link {{ $active === 'shipping' ? 'active' : '' }}" href="{{ route('account.edit') }}">
        <i class="bi bi-geo-alt me-1"></i> Shipping address
    </a>
    @if(auth()->user()->isArtisan())
        <a class="nav-link {{ $active === 'customer-orders' ? 'active' : '' }}" href="{{ route('artisan.orders.index') }}">
            <i class="bi bi-receipt me-1"></i> Customer's Orders
        </a>
        <a class="nav-link {{ $active === 'my-orders' ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">
            <i class="bi bi-bag me-1"></i> My Orders
        </a>
    @elseif(auth()->user()->isCustomer())
        <a class="nav-link {{ $active === 'my-orders' ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">
            <i class="bi bi-bag me-1"></i> My Orders
        </a>
    @endif
</nav>
