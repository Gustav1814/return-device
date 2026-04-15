@extends('layouts.home')
{{-- @section('meta')
    <title>{{ $metaData['title'] }}</title>
	<meta name="description" content="{{ $metaData['description'] }}" />

    <link rel="canonical" href="{{ $metaData['canonicalUrl'] }}" />
	<meta property="og:locale" content="{{ $metaData['og']['locale'] }}" />
	<meta property="og:type" content="{{ $metaData['og']['type'] }}" />
	<meta property="og:title" content="{{ $metaData['og']['title'] }}" />
	<meta property="og:url" content="{{ $metaData['canonicalUrl'] }}" />
	<meta property="og:site_name" content="{{ $metaData['og']['site_name'] }}" />
	<meta property="og:image" content="{{ $metaData['og']['image']['url'] }}" />
	<meta property="og:image:width" content="{{ $metaData['og']['image']['width'] }}" />
	<meta property="og:image:height" content="{{ $metaData['og']['image']['height'] }}" />
	<meta property="og:image:type" content="image/png" />
	<meta name="twitter:card" content="summary_large_image" />

<script type="application/ld+json">
    {!! json_encode([
        '@context' => $pageData['context'],
        '@graph' => [
            $pageData['webPage'],
            $pageData['breadcrumbList'],
            $pageData['webSite'],
            $pageData['organization'],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection --}}


@section('content')
<section class="login-section-one lost-password-section">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-md-3"></div>
            <div class="col-md-6 text-center">
                <div class="card my-5">
                    <div class="card-body py-5 px-4">
                        <div class="login-left-box">

                            <h2>Lost Password</h2>
                            <p>Please enter your email address. You will receive an email message with instructions on how to reset your password.</p>
                            <div class="user-login-form">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('errorMsg'))
                                    <div class="alert alert-danger" >
                                        {{ session('errorMsg') }}
                                    </div>
                                @endif
                                @if (session('successMsg'))
                                    <div class="alert alert-success">
                                        {{ session('successMsg') }}
                                    </div>
                                @endif


                                <form action="{{ route('validate.email.forgotpd') }}" class="login-form mt-3" method="post">
                                    @csrf
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <input type="email" id="email" class="form-control" aria-describedby="email" name="email" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark theme-bgcolor-btn-one">Get New Password <span class="ms-2"><svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
            {{-- <div class="col-md-6">
            <div class="login-right-box"></div>
            </div> --}}
        </div>
    </div>
</section>
@stop
@push('other-scripts')
<style>
    form.login-form input {
        padding: 7px 10px;
    }
</style>
@endpush
