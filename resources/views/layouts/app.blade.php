<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Home')</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=DM+Sans:400,500,600,700|Playfair+Display:600,700,800|Nunito:400,600&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="likha-app {{ auth()->check() && auth()->user()->isAdmin() ? 'admin-has-sidebar' : '' }}">
    <div id="app">
        @include('partials.likha-flash-stack')
        @include('layouts.navigation')
        @auth
            @if(auth()->user()->isAdmin())
                @include('layouts.admin.nav')
            @endif
        @endauth

        <main class="flex-grow-1 @yield('main_class', 'py-4 py-md-5')">
            @auth
                @if($uiApplicationBanner ?? null)
                    <div class="container-fluid px-3 px-lg-5 likha-flash-region">
                        <div class="alert alert-info alert-dismissible fade show likha-flash-alert likha-app-banner" role="alert">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold">{{ $uiApplicationBanner->title }}</div>
                                    @if($uiApplicationBanner->body)
                                        <div class="small text-body-secondary mt-1">{{ $uiApplicationBanner->body }}</div>
                                    @endif
                                    <a
                                        href="{{ $uiApplicationBanner->action_url ?? route('notifications.index') }}"
                                        class="btn btn-sm btn-primary mt-2"
                                    >
                                        View
                                    </a>
                                </div>
                                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            @yield('content')
        </main>

        @unless(request()->routeIs('admin.*'))
            @include('layouts.footer')
        @endunless
    </div>

    @unless(request()->routeIs('login', 'register', 'register.artisan'))
        @include('auth.partials.auth-offcanvas')
    @endunless

    @stack('scripts')

    @if($errors->any() && !request()->routeIs('login', 'register', 'register.artisan'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('likhaAuthPanel');
                if (!el || !window.bootstrap) return;
                var tab = @json(old('name') ? 'register' : 'login');
                var oc = bootstrap.Offcanvas.getOrCreateInstance(el);
                oc.show();
                setTimeout(function () {
                    window.dispatchEvent(new CustomEvent('likha-auth-tab', { detail: { tab: tab } }));
                }, 100);
            });
        </script>
    @endif
</body>
</html>