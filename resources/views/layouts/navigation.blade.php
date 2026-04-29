<nav class="navbar navbar-expand-lg navbar-light nav-editorial">
    <div class="container-fluid px-3 px-lg-5">
        <a class="navbar-brand nav-editorial__brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <span class="nav-editorial__site-name">Home</span>
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
                    $showCartNav = ! auth()->check() || ! auth()->user()->isRider();
                    $cartLink = auth()->check()
                        ? route('customer.cart.index')
                        : route('login', ['intended' => '/customer/cart']);
                @endphp

                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <button
                                type="button"
                                class="btn btn-link nav-link nav-editorial__icon px-2 px-lg-3 text-decoration-none admin-sidebar-trigger"
                                aria-label="Toggle admin menu"
                                title="Admin menu"
                            >
                                <i class="bi bi-list fs-5"></i>
                            </button>
                        </li>
                    @endif
                @endauth

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
                            <a class="nav-link nav-editorial__muted small px-2" href="{{ route('register.artisan') }}">Become a seller</a>
                        </li>
                        <li class="nav-item">
                            <button type="button"
                                    class="btn btn-nav-editorial ms-lg-2"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#likhaAuthPanel"
                                    data-auth-tab="register">
                                Create account
                            </button>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link nav-editorial__link dropdown-toggle px-2 px-lg-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 rounded-0">
                            @if(auth()->user()->isAdmin())
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('admin.deliveries.index') }}">Deliveries</a></li>
                            @elseif(auth()->user()->isArtisan())
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.products.index') }}">Products</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.orders.index') }}">Incoming orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.earnings.index') }}">After delivery</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.ledger.index') }}">Payment records</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('artisan.profile.edit') }}">Profile</a></li>
                            @elseif(auth()->user()->isCustomer())
                                <li><a class="dropdown-item py-2" href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}">Orders</a></li>
                            @elseif(auth()->user()->isRider())
                                <li><a class="dropdown-item py-2" href="{{ route('rider.dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('rider.cod-settlement') }}">Pay-on-delivery summary</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('rider.deliveries.index') }}">Deliveries</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('chats.index') }}">Messages</a></li>
                            @if(! auth()->user()->isRider())
                                <li><a class="dropdown-item py-2" href="{{ route('account.edit') }}">Delivery address</a></li>
                            @endif
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Log out
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
