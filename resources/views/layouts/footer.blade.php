<footer class="site-footer site-footer--sub">
    <div class="container footer-shell px-3 px-md-4 px-lg-5">
        <div class="row g-4 py-5 py-lg-5 align-items-start">
            <div class="col-12 col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <h5 class="footer-heading mb-0">Marketplace</h5>
                </div>
                <p class="footer-text mb-3">
                    A marketplace for handcrafted products from local makers. Browse approved listings, message artisans, and track your orders in one place.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark border-2 btn-sm px-3">Browse products</a>
                    <a href="{{ route('register.artisan') }}" class="btn btn-primary btn-sm px-3">Become a seller</a>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <h5 class="footer-heading mb-3">Explore</h5>
                <ul class="list-unstyled footer-links mb-0">
                    <li class="mb-2"><a href="{{ route('home') }}">Home</a></li>
                    <li class="mb-2"><a href="{{ route('products.index') }}">Shop</a></li>
                    <li class="mb-2"><a href="{{ route('artisans.index') }}">Artisans</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <h5 class="footer-heading mb-3">Account</h5>
                <ul class="list-unstyled footer-links mb-0">
                    @guest
                        <li class="mb-2">
                            <button type="button"
                                    class="btn btn-link p-0 footer-links__btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#likhaAuthPanel"
                                    aria-controls="likhaAuthPanel">
                                Log in / Create account
                            </button>
                        </li>
                    @else
                        @if(auth()->user()->isCustomer())
                            <li class="mb-2"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                            <li class="mb-2"><a href="{{ route('customer.orders.index') }}">My orders</a></li>
                        @elseif(auth()->user()->isArtisan())
                            <li class="mb-2"><a href="{{ route('artisan.dashboard') }}">Dashboard</a></li>
                            <li class="mb-2"><a href="{{ route('artisan.orders.index') }}">Incoming orders</a></li>
                            <li class="mb-2"><a href="{{ route('artisan.earnings.index') }}">After delivery</a></li>
                            <li class="mb-2"><a href="{{ route('artisan.ledger.index') }}">Settlement ledger</a></li>
                            <li class="mb-2"><a href="{{ route('artisan.products.index') }}">My products</a></li>
                        @elseif(auth()->user()->isRider())
                            <li class="mb-2"><a href="{{ route('rider.dashboard') }}">Dashboard</a></li>
                            <li class="mb-2"><a href="{{ route('rider.deliveries.index') }}">Deliveries</a></li>
                        @endif
                        @if(! auth()->user()->isRider())
                            <li class="mb-2"><a href="{{ route('account.edit') }}">Shipping address</a></li>
                        @endif
                    @endguest
                </ul>
            </div>

            <div class="col-12 col-md-4 col-lg-4">
                <h5 class="footer-heading mb-3">Support</h5>
                <p class="footer-text small mb-2">
                    Need help? Reach us anytime.
                </p>
                <ul class="list-unstyled footer-links mb-0">
                    <li class="mb-2">
                        <a href="mailto:support@likha.local">support@likha.local</a>
                    </li>
                    <li class="mb-2">
                        <span class="text-muted small">Guihulngan City, {{ config('guihulngan.province') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center py-4">
            <p class="mb-0 small text-muted">
                &copy; {{ date('Y') }} Marketplace. All rights reserved.
            </p>
            <p class="mb-0 small text-muted">
                Built for local makers and customers.
            </p>
        </div>
    </div>
</footer>
