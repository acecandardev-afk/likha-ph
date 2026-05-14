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
        <a class="nav-link {{ $active === 'incoming-orders' ? 'active' : '' }}" href="{{ route('artisan.orders.index') }}">
            Incoming orders
        </a>
        <a class="nav-link {{ $active === 'returns' ? 'active' : '' }}" href="{{ route('artisan.returns.index') }}">
            Item returns
        </a>
    @elseif(auth()->user()->isCustomer())
        <a class="nav-link {{ $active === 'my-orders' ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">
            Orders
        </a>
        <a class="nav-link {{ $active === 'returns' ? 'active' : '' }}" href="{{ route('customer.returns.index') }}">
            Returns
        </a>
    @endif
</nav>
