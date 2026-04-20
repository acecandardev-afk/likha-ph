<nav class="navbar navbar-expand-lg navbar-light nav-editorial">
    <div class="container-fluid px-3 px-lg-5">
        <a class="navbar-brand nav-editorial__brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img
                src="{{ asset('likha-ph-logo.png') }}"
                alt="{{ config('app.name', 'Likha PH') }} logo"
                width="34"
                height="34"
                class="nav-editorial__logo-img flex-shrink-0"
                loading="eager"
                decoding="async"
                style="object-fit:contain;"
            >
            <span class="nav-editorial__site-name">{{ config('app.name', 'Likha PH') }}</span>
        </a>
        <button class="navbar-toggler nav-editorial__toggler border-0 rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav nav-editorial__center mx-lg-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link nav-editorial__link px-lg-3 {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-editorial__link px-lg-3 {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-editorial__link px-lg-3 {{ request()->routeIs('artisans.*') ? 'active' : '' }}" href="{{ route('artisans.index') }}">Artisans</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center flex-row flex-wrap gap-1 gap-lg-0">
                @php
                    $cartLink = auth()->check()
                        ? route('customer.cart.index')
                        : route('login', ['intended' => '/customer/cart']);
                @endphp

                <li class="nav-item">
                    <a class="nav-link nav-editorial__icon px-2 px-lg-3 d-flex align-items-center position-relative"
                       href="{{ $cartLink }}"
                       aria-label="Shopping cart"
                       title="Cart">
                        <i class="bi bi-cart3 fs-5"></i>
                        @if(($uiCartCount ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark cart-badge border border-2 border-white">
                                {{ $uiCartCount }}
                            </span>
                        @endif
                    </a>
                </li>

                @auth
                    <li class="nav-item">
                        <a class="nav-link nav-editorial__icon px-2 px-lg-3 d-flex align-items-center position-relative"
                           href="{{ route('notifications.index') }}"
                           aria-label="Notifications"
                           title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            @if(($uiUnreadNotificationsCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark cart-badge border border-2 border-white">
                                    {{ $uiUnreadNotificationsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endauth

                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <button type="button"
                                    class="btn btn-link nav-link nav-editorial__icon px-2 px-lg-3 text-decoration-none"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#likhaAuthPanel"
                                    data-auth-tab="login"
                                    aria-label="Sign in"
                                    title="Account">
                                <i class="bi bi-person fs-5"></i>
                            </button>
                        </li>
                    @endif
                    @if (Route::has('register'))
                        <li class="nav-item d-none d-sm-block ms-lg-1">
                            <a class="nav-link nav-editorial__muted small px-2" href="{{ route('register.artisan') }}">Sell</a>
                        </li>
                        <li class="nav-item">
                            <button type="button"
                                    class="btn btn-nav-editorial ms-lg-2"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#likhaAuthPanel"
                                    data-auth-tab="register">
                                Sign up
                            </button>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link nav-editorial__link dropdown-toggle px-2 px-lg-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-lg-1"></i><span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 rounded-0">
                            @if(auth()->user()->isAdmin())
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('admin.sales.index') }}"><i class="bi bi-receipt-cutoff me-2"></i> Sales</a></li>
                            @elseif(auth()->user()->isArtisan())
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.products.index') }}"><i class="bi bi-box-seam me-2"></i> My Products</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.orders.index') }}"><i class="bi bi-receipt me-2"></i> Incoming orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}"><i class="bi bi-bag me-2"></i> My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.profile.edit') }}"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
                            @elseif(auth()->user()->isCustomer())
                                <li><a class="dropdown-item py-2" href="{{ route('customer.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}"><i class="bi bi-receipt me-2"></i> My Orders</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('chats.index') }}"><i class="bi bi-chat-dots me-2"></i> Chats</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('account.edit') }}"><i class="bi bi-geo-alt me-2"></i> Shipping address</a></li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> Log out
                                </a>
                            </li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
