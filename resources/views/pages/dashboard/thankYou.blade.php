@extends('layouts.home')
@section('content')
    <section class="rr-banner rr-thankyou-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="card my-5">
                        <div class="card-body py-5 px-4">
                            <div class="rr-banner-content">
                                <h1><span>Thank You</span> For Your Order.</h1>
                            </div>
                            <div class="btn-group mt-5" style="width:310px">
                                {{-- <a href="{{ route('dashboard') }}" class="btn btn-lg theme-bgcolor-btn-one text-white me-4">Please
                                    proceed to your account <span class="ms-2"><svg width="14" height="13"
                                            viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z"
                                                fill="white"></path>
                                        </svg></span></a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop
