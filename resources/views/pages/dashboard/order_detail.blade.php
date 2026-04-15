@extends('layouts.home')
@section('content')
    <style>
        .table {
            color: var(--text-color);
        }

        .label-rates-table {
            border: 1px var(--text-color) solid;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .spinner-label {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .btn:hover svg {
            animation: spin 1s linear infinite;
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Order Detail #{{ $data->id }}</h1>
            <p>We'll ship the box within 4 business days.</p>
        </div><!-- End Page Title -->
        <div class="card">
            <div class="card-body py-3">
                <div class="order-detail">
                    <div class="row order-detail-head">
                        <div class="col-md-12 text-md-end text-sm-start">
                            <p id="modalLblShownBtn"><span class="badge bg-primary">In Progress</span></p>
                            <p class="my-3" id="labelTrackDetails">
                                {{-- {{ $trackDetails }} --}}
                                {!! $trackDetails !!}
                                {{-- Laptop Box Tracking:<a target="_blank" href="#">1ZGW35400317312081</a><br>
                                Laptop Return Tracking: <a target="_blank" href="#">1ZGW35400328255604</a> --}}
                            </p>
                        </div>
                    </div>
                    <div class="order-detail-title-bar theme-bgcolor-btn-one mb-3">
                        <div class="row">
                            <div class="col-md-3 col-12 text-md-start text-sm-start">
                                <h6>Order Placed</h6>
                                <p id="date">{{ $data->created_at->format('m-d-Y') }}</p>
                            </div>
                            <div class="col-md-3 col-12">
                                <h6>Order ID</h6>
                                <p id="orderid">#{{ $data->id }}</p>
                            </div>
                            <div class="col-md-3 col-12">
                                <h6>Type of Equipment</h6>
                                <span class="badge bg-dark" id="type">{{ $data->type_of_equip }}</span>
                            </div>
                            <div class="col-md-3 col-12 text-md-end text-sm-start">
                                <h6>Payment Details</h6>
                                <p id="orderAmt">Order Amount: <strong>${{ $data->order_amt }}</strong></p>
                                <p id="insAmount">{!! $insAmt !!}</p>
                                <p id="ddAmount">{!! $ddSrv !!}</p>
                                <p id="PayDetCoupon">{!! $PayDetCoupon !!}</p>
                            </div>
                        </div>
                    </div>
                    <div class="order-detail-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 id="headingWithEdit">Order Detail</h5>
                                <hr />
                            </div>
                            <div class="col-md-6">
                                <h6>Employee Details</h6>
                                <p id="emp_name">{{ $data->emp_first_name }} {{ $data->emp_last_name }} </p>
                                <p id="emp_address">{{ $data->emp_add_1 }} {{ $data->emp_add_2 }}</p>
                                <p id="emp_state">{{ $data->emp_city }}, {{ $data->emp_state }} {{ $data->emp_pcode }}</p>
                                <p id="emp_zip"></p>
                                <p id="emp_email">{{ $data->emp_email }}</p>
                                <p id="emp_phone">{{ $data->emp_phone }}</p>
                                <h6>Return Services: <span class="badge bg-primary"
                                        id="service">
                                        {{-- {{ $data->return_service }} --}}

                                         @if($data->return_service == 'Sell This Equipment')
                                                    Recycle with Data Destruction
                                                @else
                                                    {{ $data->return_service }}
                                                @endif

                                        </span></h6>
                            </div>
                            <div class="col-md-6">
                                <h6>Return Details</h6>
                                <p id="receipient_name">{{ $data->receipient_name }}</p>
                                <p id="receipient_address">{{ $data->receipient_add_1 }} {{ $data->receipient_add_2 }}</p>
                                <p id="receipient_state">{{ $data->receipient_city }}, {{ $data->receipient_state }}
                                    {{ $data->receipient_zip }}</p>
                                <p id="receipient_zip"></p>
                                <p id="receipient_email">{{ $data->receipient_email }}</p>
                                <p id="receipient_phone">{{ $data->receipient_phone }}</p>
                                <h6>Company Recipient Name: <span class="badge bg-success" id="receipient_person">
                                        {{ $data->receipient_person }}
                                    </span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LABEL PROCESS - START -->
        {{-- @if ($data->send_flag == 1 && $data->rec_flag == 1 && $data->order_status == 'completed') --}}
        @if ($data->order_status == 'completed' && Auth::user()->role == 'SUPER_ADMIN')
            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body py-3">
                            <h5 id="headingWithEdit">Box Label </h5>
                            <p>Create box label for sending to employee</p>
                            @if ($data->send_flag == 1)
                                <table width="100%" style="border:1px solid #ddd;">
                                    <tr>
                                        <td>Box Rate Object ID: </td>
                                        <td> {{ $sendRes['object_id'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>Box Label: </td>
                                        <td> <a target="_blank" href="{{ $sendRes['label_url'] }}">Label URL</a> </td>
                                    </tr>
                                    <tr>
                                        <td>Box Tracking No: </td>
                                        <td>{{ $sendRes['tracking_number'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>Box Tracking URL: </td>
                                        <td> <a target="_blank" href="{{ $sendRes['tracking_url_provider'] }}">Tracking
                                                URL</a> </td>
                                    </tr>
                                </table>
                            @else
                                <div class="shipping-container" id="labelEmp">
                                    <button type="button" class="btn btn-dark theme-bgcolor-btn-one createLabelEmp"
                                        data-type="emp" style="width: 100%;">
                                        Create Box Label
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body py-3">
                            <h5 id="headingWithEdit">Device Label
                                @if ($data->insurance_active == 1 && $data->insurance_amount != null)
                                    <div class="badge bg-success" style="float:right;">
                                        Device Label Insured
                                    </div>
                                @endif
                            </h5>

                            <p>Create device label for receiving from employee</p>
                            @if ($data->rec_flag == 1)
                                <table width="100%" style="border:1px solid #ddd;">
                                    <tr>
                                        <td>Box Rate Object ID: </td>
                                        <td> {{ $recRes['object_id'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>Box Label: </td>
                                        <td> <a target="_blank" href="{{ $recRes['label_url'] }}">Label URL</a> </td>
                                    </tr>
                                    <tr>
                                        <td>Box Tracking No: </td>
                                        <td>{{ $recRes['tracking_number'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>Box Tracking URL: </td>
                                        <td> <a target="_blank" href="{{ $recRes['tracking_url_provider'] }}">Tracking
                                                URL</a> </td>
                                    </tr>
                                </table>
                            @else
                                <div class="shipping-container" id="labelRec">
                                    <button type="button" class="btn btn-dark theme-bgcolor-btn-one createLabelEmp"
                                        style="width: 100%;" data-type="rec">
                                        Create Device Label
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{--
            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="shadow p-4">
                        <h5 id="headingWithEdit">Box Label</h5>
                        <p>Create box label for sending to employee</p>
                        <button type="button" class="btn btn-lg theme-bgcolor-btn-one" style="width: 100%;">
                            Create Box Label
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="spinner-label ms-4"
                                width="20" height="20">
                                <path
                                    d="M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" />
                            </svg>
                        </button>
                        <div class="label-rates-table mt-3">
                            <table width="100%">
                                <tr>
                                    <td><img src="https://www.usps.com/global-elements/header/images/utility-header/logo-sb.svg"
                                            alt="" /> </td>
                                    <td class="text-center">
                                        <p>13.87USD</p>
                                    </td>
                                    <td class="text-end">
                                        <p>Estimated days: 2</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p>USPS (usps_priority)</p>
                                    </td>
                                    <td></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-lg theme-bgcolor-btn-one">
                                            Purchase Label
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                class="spinner-label ms-4" width="20" height="20">
                                                <path
                                                    d="M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <p>Overnight delivery to most U.S. locations.</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="label-rates-table mt-3">
                            <table width="100%">
                                <tr>
                                    <td><img src="https://www.usps.com/global-elements/header/images/utility-header/logo-sb.svg"
                                            alt="" /> </td>
                                    <td class="text-center">
                                        <p>13.87USD</p>
                                    </td>
                                    <td class="text-end">
                                        <p>Estimated days: 2</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p>USPS (usps_priority)</p>
                                    </td>
                                    <td></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-lg theme-bgcolor-btn-one">
                                            Purchase Label
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                class="spinner-label ms-4" width="20" height="20">
                                                <path
                                                    d="M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <p>Overnight delivery to most U.S. locations.</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="shadow p-4">
                        <h5 id="headingWithEdit">Device Label<div class="badge bg-success" style="float:right;">Device
                                Label
                                Insured</div>
                        </h5>
                        <p>Create device label for receiving from employee</p>
                        <button type="button" class="btn btn-lg theme-bgcolor-btn-one" style="width: 100%;">
                            Create Device Label
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="spinner-label ms-4"
                                width="20" height="20">
                                <path
                                    d="M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" />
                            </svg>
                        </button>
                        <table class="table mt-3" width="100%">
                            <tr>
                                <td>Box Rate Object ID: </td>
                                <td> #12131</td>
                            </tr>
                            <tr>
                                <td>Box Label: </td>
                                <td> <a target="_blank" href="#">Label URL</a> </td>
                            </tr>
                            <tr>
                                <td>Box Tracking No: </td>
                                <td>122wegffgdfgdf</td>
                            </tr>
                            <tr>
                                <td>Box Tracking URL: </td>
                                <td> <a target="_blank" href="">Tracking
                                        URL</a> </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            --}}
        @endif



        <!-- LABEL PROCESS - END -->
    </main>

@stop

@push('other-scripts')
    <style>
        .order-detail-title-bar {
            border-radius: 1rem;
        }

        .order-detail-title-bar p {
            margin-bottom: 0.1rem;
        }

        .order-detail p {
            margin-bottom: 0.5rem;
        }
    </style>

    <script>
        $(".createLabelEmp").click(function() {
            window.d = $(this).attr("data-type");
            window.txt = $(this).text();
            window.this = $(this);
            $('#labelEmpErr').fadeOut();
            $('#labelRecErr').fadeOut();
            var loadingImg =
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="spinner-label ms-4" width = "20" height = "20" > \
                                                                                                                            <path d = "M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" > \
                                                                                                                            </svg>';
            $(this).html(window.txt + loadingImg);

            $.ajax({
                type: "GET",
                url: "{{ route('orderslabel.create') }}",
                data: "oid=" + {{ $data->id }} + "&t=" + $(this).attr("data-type"),
                cache: false,
                success: function(data) {
                    if (data.status == "SUCCESS") {
                        $(window.this).html('');
                        $(window.this).html(window.txt);
                        if (window.d == "emp") {
                            $('#labelEmp').html(data.tbl);

                        } else {
                            $('#labelRec').html(data.tbl);
                        }
                    } else {
                        $(window.this).html('');
                        $(window.this).html(window.txt);
                        if (window.d == "emp") {
                            $('#labelEmpErr').empty();
                            $('#labelEmpErr').fadeIn();
                            $('#labelEmpErr').html(data.response);
                        } else {
                            $('#labelRecErr').empty();
                            $('#labelRecErr').fadeIn();
                            $('#labelRecErr').html(data.response);
                        }
                    }

                }
            });

        });



        $(document).on('click', '.btn-purchaselabel', function() {
            window.d = $(this).attr("data-type");


            window.txt = $(this).text();
            var loadingImg =
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="spinner-label ms-4" width = "20" height = "20" > \
                                                                                                                            <path d = "M142.9 142.9c-17.5 17.5-30.1 38-37.8 59.8c-5.9 16.7-24.2 25.4-40.8 19.5s-25.4-24.2-19.5-40.8C55.6 150.7 73.2 122 97.6 97.6c87.2-87.2 228.3-87.5 315.8-1L455 55c6.9-6.9 17.2-8.9 26.2-5.2s14.8 12.5 14.8 22.2l0 128c0 13.3-10.7 24-24 24l-8.4 0c0 0 0 0 0 0L344 224c-9.7 0-18.5-5.8-22.2-14.8s-1.7-19.3 5.2-26.2l41.1-41.1c-62.6-61.5-163.1-61.2-225.3 1zM16 312c0-13.3 10.7-24 24-24l7.6 0 .7 0L168 288c9.7 0 18.5 5.8 22.2 14.8s1.7 19.3-5.2 26.2l-41.1 41.1c62.6 61.5 163.1 61.2 225.3-1c17.5-17.5 30.1-38 37.8-59.8c5.9-16.7 24.2-25.4 40.8-19.5s25.4 24.2 19.5 40.8c-10.8 30.6-28.4 59.3-52.9 83.8c-87.2 87.2-228.3 87.5-315.8 1L57 457c-6.9 6.9-17.2 8.9-26.2 5.2S16 449.7 16 440l0-119.6 0-.7 0-7.6z" > \
                                                                                                                            </svg>';
            $(this).html(window.txt + loadingImg);




            $.ajax({
                type: "GET",
                url: "{{ route('orderslabel.purchase') }}",
                data: "oid=" + $(this).attr("data-lblobj") + "&t=" + $(this).attr("data-type") +
                    "&suborder=" + <?php echo $data->id; ?>,
                cache: false,
                success: function(data) {
                    if (data.status == "SUCCESS") {
                        if (window.d == "emp") {
                            $('#labelEmp').html('');
                            $('#labelEmp').html(data.tbl);
                        } else if (window.d == "dest") {
                            $('#labelDest').html('');
                            $('#labelDest').html(data.tbl);
                        } else {
                            $('#labelRec').html('');
                            $('#labelRec').html(data.tbl);
                        }
                    } else {
                        if (window.d == "emp") {
                            $('#labelEmp').html('');
                            $('#labelEmp').html(data.tbl);
                        } else if (window.d == "dest") {
                            $('#labelDest').html('');
                            $('#labelDest').html(data.tbl);
                        } else {
                            $('#labelRec').html('');
                            $('#labelRec').html(data.tbl);
                        }
                    }
                }
            });
        });

        $("#dataDestLabelCreate").click(function() {
            if ($(this).prop("checked")) {
                $("#lblCreateDataDest").fadeIn();
            } else {
                $("#lblCreateDataDest").fadeOut();
            }
        });


        $(".createLabelDest").click(function() {
            window.txt = $(this).text();
            window.this = $(this);
            window.d = $(this).attr("data-type");
            $(this).html(window.txt + ' <i class="fa fa-refresh fa-spin"></i>');

            $.ajax({
                type: "GET",
                url: "{{ route('orderslabel.create') }}",
                data: "oid=" + {{ $data->id }} + "&t=" + $(this).attr("data-type"),
                cache: false,
                success: function(data) {
                    if (data.status == "SUCCESS") {
                        $(window.this).html('');
                        $(window.this).html(window.txt);
                        $('#labelDest').html(data.tbl);
                    } else {
                        $(window.this).html('');
                        $(window.this).html(window.txt);
                        $('#labelDestErr').empty();
                        $('#labelDestErr').fadeIn();
                        $('#labelDestErr').html(data.response);
                    }

                }
            });
        });
    </script>
@endpush
