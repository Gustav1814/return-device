<!DOCTYPE html>
@php
    $themeSet = '';
    if (isset($companySettings)) {
        $themeSet = $companySettings->theme;
    }
@endphp
<html lang="en" data-theme="{{ $themeSet }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
@php
    $favicon = $companySettings && $companySettings->favicon
    ? asset("storage/favicon/{$companySettings->favicon}")
    : asset('assets/img/dummyLogo.png');
@endphp
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}" />
    <title></title>
    <meta name="description"
        content="" />

        <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    @php
        $allowedRoutesForDBcss = [
            'dashboard',
            'orders.list',
            'users.list',
            'create.bulk.order',
            'create.singleorder',
            'user.profile',
            'company.settings',
            'suborder.edit',
            'order.detail',
            'completed.orders.list',
            'price.settings',
            'company.list',
            'company.detail',
            'order.pay',
            'admin.coupon.list',
            'admin.coupon.add',
            'admin.coupon.edit',
            'orders.filter',
            'completed.orders.filter',
            'users.search',
            'company.edit',
            'api.key',
            'api.integration',
            'admin.commission'
        ];
    @endphp
    @if (in_array(Route::currentRouteName(), $allowedRoutesForDBcss))
        {{-- <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet"> --}}
    @endif


    @php
        $btnBgColor = '#f37033';
        $btnFontColor = '#ffffff';
        if (isset($companySettings)) {
            $btnBgColor = $companySettings->btn_bg_color;
            $btnFontColor = $companySettings->btn_font_color;
        }

    @endphp
    <style>
        :root {
            --button-background: @php echo $btnBgColor
        @endphp
        ;
        --button-text-color: @php echo $btnFontColor
        @endphp
        ;
        }
    </style>
    @stack('styles')
</head>

<body class="home-body @yield('body_class')">

    @hasSection('replace_header')
        @yield('replace_header')
    @else
        @include('includes/header')
    @endif

    @yield('content')

    @hasSection('replace_footer')
        @yield('replace_footer')
    @else
        @include('includes/footer')
    @endif
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('other-scripts')

</body>

</html>
