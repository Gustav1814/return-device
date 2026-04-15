{{-- Legacy: GET /wl-login now redirects to the React SaaS login at /saas/login. This view is unused but kept for reference. --}}
@extends('layouts.home')
@section('content')
    <section class="login-section-one ">
        <div class="container-xl">
            <div class="row align-items-center vh-100">
                <div class="col-md-3"></div>
                <div class="col-md-6 text-center">
                    <div class="card">
                        <div class="card-body py-5 px-4">
                            <div class="login-left-box">
                                <h2>Welcome Back</h2>
                                <p>Sign In into your account</p>
        
        

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('successMsg'))
                                    <div class="alert alert-success">
                                        {{ session('successMsg') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
        
        
        
                                <div class="user-login-form">
                                    <form action="{{ route('login') }}" class="login-form" method="post">
                                        @csrf
                                        <div class="row mt-4 mb-4">
                                            <div class="col-12">
                                                <input type="email" id="email" class="form-control" aria-describedby="email"
                                                    name="email" placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <input type="password" id="password" class="form-control"
                                                    aria-describedby="password" name="password" placeholder="Password">
                                            </div>
                                        </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-dark theme-bgcolor-btn-one">Sign In <span
                                                class="ms-2"><i class="bi bi-arrow-right-circle"></i></span></button>
                                    </div>
                                    </form>
                                </div>
                                <p><a class="text-dark" href="{{ route('lost.password') }}">Recover Password</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
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


{{-- <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}
