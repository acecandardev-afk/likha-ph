@props(['active' => 'shipping'])

<nav class="nav nav-pills nav-fill border-bottom pb-3 mb-4">
    @if(auth()->user()->isArtisan())
        <a class="nav-link {{ $active === 'profile' ? 'active' : '' }}" href="{{ route('artisan.profile.edit') }}">
            Profile
        </a>
    @endif
    <a class="nav-link {{ $active === 'shipping' ? 'active' : '' }}" href="{{ route('account.edit') }}">
        Delivery address
    </a>
    @if(auth()->user()->isArtisan())
        <a class="nav-link {{ $active === 'customer-orders' ? 'active' : '' }}" href="{{ route('artisan.orders.index') }}">
            Incoming orders
        </a>
    @elseif(auth()->user()->isCustomer())
        <a class="nav-link {{ $active === 'my-orders' ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">
            Orders
        </a>
    @endif
</nav>
