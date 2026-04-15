@extends('layouts.home')
@section('content')
    <section class="rr-banner rr-thankyou-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="rr-banner-content">

                        <h1>Application Logs</h1>
                        <pre>{{ $logs }}</pre>
                    </div>

                </div>
            </div>
        </div>
    </section>
@stop
@push('other-scripts')
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }

        pre {
            background: #1e1e1e;
            color: #dcdcdc;
            padding: 20px;
            border-radius: 5px;
            overflow: auto;
            max-height: 80vh;
        }
    </style>
@endpush
