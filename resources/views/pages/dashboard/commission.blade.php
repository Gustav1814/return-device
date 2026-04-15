@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Companies</h1>
    </div><!-- End Page Title -->
    <div class="all-orders">
        <div class="card">
            <div class="card-body py-3">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif


                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-payment-pending" role="tabpanel"
                        aria-labelledby="pills-payment-pending-tab">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Company ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Domain</th>
                                        <th scope="col">Cost</th>
                                        <th scope="col">Commission</th>
                                        {{-- <th scope="col">Company Name</th>
                                        <th scope="col">Payment Status</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- {{ dd($data) }} --}}
                                    @foreach ($data as $order)
                                    @php
                                        $commission=$helper->getCommissionAndCostAmount($order->id);
                                    @endphp
                                        <tr>
                                            <td scope="row">{{ $order->id }}</td>
                                            <td>{{ $order->company_name }}</td>
                                            <td>{{ $order->company_domain }}</td>
                                            <td>${{ $commission[0]}}</td>
                                           <td>${{ $commission[1]}}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {{ $data->links() }}
                        </div>


                    </div>



                </div>
            </div>
        </div>
    </div>
</main>

@stop


@push('other-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">


    <style>
        .nav-link,
        .nav-link:focus,
        .nav-link:hover {
            color: var(--theme-black-color);
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            background-color: var(--theme-color-two);
            border-radius: 0px;
        }

        .form-control,
        .form-select {
            box-shadow: none !important;
            border-color: #ccc !important;
            /* border-radius: 0px; */
            padding: 7px;
            height: auto;
        }

        .all-orders .page-link {
            color: #000;
            box-shadow: none !important;
        }

        .all-orders .page-item.active .page-link {
            background-color: var(--theme-color-two);
            border-color: var(--theme-color-two);
        }


    </style>
@endpush
