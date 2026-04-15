@if (in_array(Route::currentRouteName(), $allowedRoutesForDBcss))

    <!-- ======= Header ======= -->
    <header class="header fixed-top">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between">
                    @if (isset($companySettings))
                     @if ($_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN'))
                        <a class="logo d-flex align-items-center" href="{{ route('home.index') }}">
                            @if ($companySettings->logo != null)
                                <img src="{{ asset("storage/logoImage/$companySettings->logo") }}?v={{ time() }}"
                                    alt="">
                                {{-- <span class="d-none d-lg-block">Device42</span> --}}
                            @else
                                <img src="{{ asset('assets/img/dummyLogo.png') }}?v={{ time() }}"
                                    alt="">
                                {{-- <span class="d-none d-lg-block">Device42</span> --}}
                            @endif
                        </a>
                    @endif
                    @endif
                    <i class="bi bi-list toggle-sidebar-btn"></i>
                </div><!-- End Logo -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0"></ul>
                    <div class="d-lg-flex d-md-inline-flex align-items-center">
                        <div class="header-phone me-3 text-center">

                            {{-- <a href="tel:+18885971025" class="text-dark"><span class="me-1"><i
                                        class="bi bi-phone"></i></span><strong class="ms-1">(+1)
                                    888-597-1025</strong></a> --}}

                        </div>


                        @if (Auth::check())
                            <div class="position-relative">
                                <div class="mt-1 ms-md-2 mt-md-0 login-user-dropdown">
                                    <a class="btn btn-dark dropdown-toggle w-100" href="#"
                                        id="dropdownMenu" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        {{-- {{ ucfirst(substr(Auth::user()->name, 0, 1)) }} --}}
                                        Welcome, {{ Auth::user()->name }}
                                    </a>
                                    <ul
                                        class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('user.profile') }}">
                                                <i class="bi bi-person"></i>
                                                <span>Profile</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="#">
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <li onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="bi bi-box-arrow-right"></i>
                                                        <span>{{ __('Sign Out') }}</span>
                                                    </li>
                                                </form>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a class="nav-link {{ request()->is('saas/dashboard') ? '' : 'collapsed' }}" href="{{ route('saas.dashboard') }}">
                    <i class="bi bi-grid"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('orders.list') ? '' : 'collapsed' }}" href="{{ route('orders.list') }}">
                    <i class="bi bi-arrow-repeat"></i>
                    In Progress Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::is('completed.orders.list') ? '' : 'collapsed' }}" href="{{ route('completed.orders.list') }}">
                    <i class="bi bi-check-circle"></i>
                    Completed Orders
                </a>
            </li>

            @if (isset($companySettings) && $companySettings->company_id != env('RR_COMPANY_ID'))
               <li class="nav-item">
                <a class="nav-link {{ Route::is('completed.orders.list') ? '' : 'collapsed' }}" href="{{ route('create.singleorder') }}">
                    <i class="bi bi-plus-circle"></i>
                        Create Single Order
                </a>
            </li>

                <li class="nav-item">
                    <a class="nav-link {{ Route::is('create.bulk.order') ? '' : 'collapsed' }}" href="{{ route('create.bulk.order') }}">
                        <i class="bi bi-upload"></i>
                        Create Order (by CSV)
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ Route::is('users.list') ? '' : 'collapsed' }}" href="{{ route('users.list') }}">
                    <i class="bi bi-people"></i>
                    Users
                </a>
            </li>

            @if (isset($companySettings) && $companySettings->company_id == env('RR_COMPANY_ID'))
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('company.list') ? '' : 'collapsed' }}" href="{{ route('company.list') }}">
                        <i class="bi bi-building"></i>
                        Companies
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.coupon.list') ? '' : 'collapsed' }}" href="{{ route('admin.coupon.list') }}">
                        <i class="bi bi-ticket-perforated"></i>
                        Coupon
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.commission') ? '' : 'collapsed' }}" href="{{ route('admin.commission') }}">
                        <i class="bi bi-cash-stack"></i>
                        Commission
                    </a>
                </li>
            @endif

            @if (isset($companySettings) && $companySettings->company_id != env('RR_COMPANY_ID'))
                <li class="nav-item">
                <a class="nav-link {{ Route::is('admin.coupon.list') ? '' : 'collapsed' }}" href="{{ route('company.settings') }}">
                <i class="bi bi-gear"></i>
                Theme Settings
                </a>
                </li>
                 <li class="nav-item">
                <a class="nav-link {{ Route::is('api.key') ? '' : 'collapsed' }}" href="{{ route('api.key') }}">
                <i class="bi bi-gear"></i>
                API
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link {{ Route::is('api.integration') ? '' : 'collapsed' }}" href="{{ route('api.integration') }}">
                <i class="bi bi-gear"></i>
                API Integration Instructions
                </a>
                </li>
            @endif
                <li class="nav-item">
                <a class="nav-link {{ Route::is('price.settings') ? '' : 'collapsed' }}" href="{{ route('price.settings') }}">
                <i class="bi bi-currency-dollar"></i>
                Price Settings
                </a>
                </li>
        </ul>

    </aside><!-- End Sidebar-->
@else

    <header class="header py-1" style="height: auto;">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    @if (isset($companySettings))
                     @if ($_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN'))
                        <a class="logo d-flex align-items-center" href="{{ route('home.index') }}">
                            @if ($companySettings->logo != null)
                                <img src="{{ asset("storage/logoImage/$companySettings->logo") }}?v={{ time() }}"
                                    alt="">
                                {{-- <span class="d-none d-lg-block">Device42</span> --}}
                            @else
                                <img src="{{ asset('assets/img/dummyLogo.png') }}?v={{ time() }}"
                                    alt="">
                                {{-- <span class="d-none d-lg-block">Device42</span> --}}
                            @endif
                        </a>
                    @endif
                    @endif
                </div><!-- End Logo -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0"></ul>
                    <div class="d-lg-flex d-md-inline-flex align-items-center">
                        <div class="header-phone me-3 text-center">

                            {{-- <a href="tel:+18885971025" class="text-dark"><span class="me-1"><i
                                        class="bi bi-phone"></i></span><strong class="ms-1">(+1)
                                    888-597-1025</strong></a> --}}

                        </div>

                        <div class="header-btn-group">
                             @if ($_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN'))

                            @if (!request()->routeIs('create.singleorder.notauth'))
                            <a href="{{ route('create.singleorder.notauth') }}"
                                class="btn btn-dark w-100">Create Order <span class="ms-1"><i
                                        class="bi bi-arrow-right-circle"></i></span></a>
                            @endif
                            @endif
                        </div>
                        @if (Auth::check())
                            <div class="position-relative">
                                <div class="mt-1 ms-md-2 mt-md-0 login-user-dropdown">
                                    <a class="btn btn-dark dropdown-toggle w-100" href="#"
                                        id="dropdownMenu" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        {{ ucfirst(substr(Auth::user()->name, 0, 1)) }}
                                    </a>
                                    <ul
                                        class="dropdown-menu dropdown-menu-start dropdown-menu-arrow profile">
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('saas.dashboard') }}">
                                                <i class="bi bi-grid"></i>
                                                <span>Dashboard</span>
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('company.settings') }}">
                                                <i class="bi bi-gear"></i>
                                                <span>Theme Settings</span>
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('price.settings') }}">
                                                <i class="bi bi-currency-dollar"></i>
                                                <span>Price Settings</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center" href="#">
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <li onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="bi bi-box-arrow-right"></i>
                                                        <span>{{ __('Sign Out') }}</span>
                                                    </li>
                                                </form>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
    </header>

@endif
