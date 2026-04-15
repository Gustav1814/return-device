@extends('layouts.home')
@section('content')
<section class="login-section-one lost-password-section">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col-md-3"></div>
            <div class="col-md-6 text-center">
                <div class="card my-5">
                    <div class="card-body py-5 px-4">
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
                            <div class="alert alert-danger">
                                {{ session('errorMsg') }}
                            </div>
                        @endif
                        @if (session('successMsg'))
                            <div class="alert alert-success">
                                {{ session('successMsg') }}
                            </div>
                        @endif

                        <div class="login-left-box">

                            <h2>Update Password</h2>
                            {{-- <p>Please enter your email address. You will receive an email message with instructions on how to reset your password.</p> --}}

                            <?php
                                if(isset($_GET['email']) && isset($_GET['d']) && isset($_GET['token'])){
                                    if(!empty($_GET['email']) && !empty($_GET['d']) && !empty($_GET['token'] && $valid == 1)){
                            ?>
                            <div class="user-login-form">
                                <form action="{{ route('sub.update.forgotpd') }}" class="login-form mt-3" method="post">
                                    @csrf
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <input readonly value="<?php echo $_GET['email'];?>" type="email" id="email" class="form-control" aria-describedby="email" name="email" placeholder="Email Address"  maxlength="60">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <input type="password" value="{{ old('password') }}" id="password" class="form-control" aria-describedby="password" name="password" placeholder="Password" maxlength="15">
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <input type="password" value="{{ old('password_confirmation') }}" id="retypepassword" class="form-control" aria-describedby="retypepwd" name="password_confirmation" placeholder="Re-type password"  maxlength="15">
                                        </div>
                                        <input type="hidden" name="d" id="d" value="<?php echo $_GET['d'];?>"/>
                                        <input type="hidden" name="sc" id="token" value="<?php echo $_GET['token'];?>"/>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark theme-bgcolor-btn-one">Update Password <span class="ms-2"><svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <?php
                                    }else {
                                        echo '<div class="alert alert-danger">Required Parameters Are Missing.</div>';
                                    }
                                }else {
                                    echo '<p class="alert alert-danger">Required Parameters Are Missing.</p>';
                                }
                            ?>


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
