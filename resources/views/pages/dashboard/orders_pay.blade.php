@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="container">
        <div class="tab-title">
            <!-- Page Heading -->
            <div class="mb-4">
                <h1 class="h2 fw-bold">Payment Details</h1>
            </div>
        </div>




        <!-- ORDER DETAILS - START -->
        <div class="all-orders">
            <div class="card">
                <div class="filter">
                    {{-- <form action="" method="get" class="search-filter admin-search-filter">
                        <div class="row py-3 align-items-center">
                            <div class="col-md-3">
                                <select class="form-select search_by" aria-label="Search By" name="search_by"
                                    id="search_by">
                                    <option value="" selected>Search By</option>
                                    <option value="Device Type">Device Type</option>
                                    <option value="Return Service">Return Service</option>
                                    <option value="Date">Date</option>
                                    <option value="Custom Search">Custom Search</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="device-type">
                                <select class="form-select" aria-label="" name="device_type">
                                    <option value="" selected>Device Type</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Monitor">Monitor</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="return-service">
                                <select class="form-select" aria-label="" name="return_service">
                                    <option value="" selected>Return Service</option>
                                    <option value="Return To Company">Return To Company</option>
                                    <option value="Sell This Equipment">Sell This Equipment</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="custom-date">
                                <input type="date" name="date" class="form-control" aria-describedby="date">
                            </div>
                            <div class="col-md-3" id="custom-search">
                                <input type="text" name="search" placeholder="Search..." class="form-control"
                                    aria-describedby="search">
                            </div>
                            <div class="col-md-3" id="custom-submit">
                                <input type="hidden" name="orders_status" value="">
                                <button type="submit" class="btn btn-lg theme-bgcolor-btn-one">Filter</button>
                            </div>
                        </div>
                    </form> --}}
                </div>
                <div class="card-body">

                    <h5 class="mb-4"><strong>Bulk Order List  </strong></h5>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-payment-pending" role="tabpanel"
                            aria-labelledby="pills-payment-pending-tab">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Device Type</th>
                                            <th scope="col">Return Service</th>
                                            <th scope="col">Company Name</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- {{ dd($data) }} --}}
                                        @foreach ($data as $order)
                                            <tr>
                                                <td scope="row">{{ $order->id }}</td>
                                                <td>{{ $order->type_of_equip }}</td>
                                                <td>{{ $order->return_service }}</td>
                                                <td>{{ $order->receipient_name }}</td>
                                                <td><span class="badge bg-dark">${{ $order->order_amt }}</span></td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a target="_blank" href="{{ route('suborder.edit', $order->id) }}"
                                                            class="badge theme-bgcolor-btn-one p-2 me-1"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor"
                                                                class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                                <path fill-rule="evenodd"
                                                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                            </svg></a>
                                                        <a target="_blank" href="{{ route('order.detail', $order->id) }}"
                                                            class="badge theme-bgcolor-btn-one p-2 me-1"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-eye"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                                <path
                                                                    d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                            </svg></a>
                                                        <a href="#"
                                                            class="badge theme-bgcolor-btn-one p-2 deleteOrder"
                                                            data-del="{{ $order->id }}"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-trash3"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                                            </svg></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                {{ $data->links() }}
                            </div>





                            <div class="p-1">
                                <form class="user" name="pay_submit" id="pay_submit">
                                    @csrf
                                    <div class="user_payment_box">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="mb-4"><strong>Card Information</strong></h5>
                                                <!-- SMS alert msg - start -->
                                                <div class="setting-alerts-sms" id="pay_update_msg" style="display:none;">
                                                    <div class="alert alert-success alert-dismissible fade show"
                                                        role="alert">
                                                        <strong>
                                                            <button type="button" data-div="setting-alerts-sms"
                                                                class="btn-close" data-bs-dismiss="alert"
                                                                aria-label="Close">
                                                            </button>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <!-- SMS alert msg - end -->
                                                <div class="form-group row">
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" id="cardholder_name"
                                                            placeholder="Card Name" value="" name="cardholder_name"
                                                            maxlength="15">
                                                    </div>
                                                    <div class="col-md-6 mb-3 mb-sm-0">
                                                       
                                                        <input type="text" class="form-control" id="cc_no"
                                                            placeholder="Card Number" value="" name="cc_no"
                                                            maxlength="16">
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="form-group row">
                                                    <div class="col-md-4">
                                                        
                                                        <select class="form-select mb-3" name="cc_month" id="cc_month">
                                                            <option value="">Month</option>
                                                            @for ($i = 1; $i <= 12; $i++)
                                                                <option value="{{ $i }}">{{ $i }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                       
                                                        <select class="form-select mb-3" name="cc_year" id="cc_year">
                                                            <option value="">Year</option>
                                                            @for ($i = date('y'); $i <= date('y') + 6; $i++)
                                                                <option value="{{ $i }}">{{ $i }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                       
                                                        <input type="text" class="form-control" id="cvv"
                                                            placeholder="CVV" value="" name="cvv"
                                                            maxlength="15">
                                                    </div>
                                                </div>
                                                <hr />
                                                <div class="form-group row">
                                                    <div class="col-sm-12">
                                                        <h5 class="mb-1"><strong>Apply Discount</strong></h5>
                                                        <!-- SMS alert msg - start -->
                                                        <div id="discount_msg" style="display:none;">
                                                            <div class="alert alert-success alert-dismissible fade show"
                                                                role="alert">
                                                                <strong>
                                                                    <button type="button" data-div="setting-alerts-sms"
                                                                        class="btn-close" data-bs-dismiss="alert"
                                                                        aria-label="Close">
                                                                    </button>
                                                                </strong>
                                                            </div>
                                                        </div>
                                                        <!-- SMS alert msg - end -->

                                                        <input placeholder="Coupon" class="form-control" type="text" name="coupon"
                                                            id="coupon" style="width:60%; display: inline-block;vertical-align: middle;" maxlength="12"><input
                                                            id="couponApply" type="button" value="Apply" class="btn btn-primary" />
                                                    </div>
                                                    {{-- <div class="col-sm-4 mb-3 mb-sm-0">
                                    <label>City</label>
                                    <input type="text" class="form-control" id="comp_city" name="comp_city" placeholder="City"
                                        value="" maxlength="20">
                                </div>
                                <div class="col-sm-4 mb-3 mb-sm-0">
                                    <label>State</label>
                                    <select id="comp_state" class="form-select mb-3" name="comp_state" required="">
                                       @include('includes.states_options')
                                    </select>
                                </div>

                                <div class="col-sm-4">
                                    <label>Zip Code</label>
                                    <input type="text" class="form-control" id="comp_zip" name="comp_zip" placeholder="Zip"
                                        value="" maxlength="15">
                                </div> --}}
                                                </div>
                                            </div>
                                            <div class="col-md-4 ps-4">
                                                <h5 class="mb-4"><strong>Order Details</strong></h5>
                                                <table class="table payment-table mb-3">
                                                    <tbody>
                                                        {{-- <tr>
                                        <td><label>Amount</label></td>
                                        <td class="text-end"><p><strong><span>$ </span>{{ $totalAmt }}</strong></p></td>
                                    </tr> --}}
                                                        <tr>
                                                            <td><label>Number of Devices:</label></td>
                                                            <td class="text-end">
                                                                <p><strong>{{ $employeesCount }}</strong></p>
                                                            </td>
                                                        </tr>
                                                        {{-- <tr>
                                        <td><label>Amount per Device:</label></td>
                                        <td class="text-end"><p><strong>{{ $orderAmt }}</strong></p></td>
                                    </tr> --}}
                                                        @if ($ins_amount != null && $ins_amount != 0)
                                                            <tr>
                                                                <td><label>Insurance</label></td>
                                                                <td class="text-end">
                                                                    <p><strong>{{ $ins_amount }}</strong></p>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        {{-- @if (($DD_amt != null && $DD_amt != 0) || $dd_service != null) --}}
                                                        @if ($DD_amt != null && $DD_amt != 0)
                                                            <tr>
                                                                <td><label>Data Destruction</label></td>
                                                                <td class="text-end">
                                                                    <p><strong>${{ $DD_amt }}</strong></p>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <tr>
                                                            <td><label>Discount</label></td>
                                                            <td class="text-end">
                                                                <p><strong id="amtDiscount">$0</strong></p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label>Total Amount:</label></td>
                                                            <td class="text-end">
                                                                <p style="color:#1c4da0;"><strong
                                                                        id="amtTotal">${{ $totalAmt }}</strong>
                                                                </p>
                                                            </td>
                                                        </tr>


                                                        <!-- COUPON WORK - START -->
                                                        {{-- <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>

                                     <tr>
                                        <td><label>Discount:</label></td>
                                        <td class="text-end"><p style="color:#1c4da0;"><strong>10</strong></p></td>
                                    </tr>
                                    <tr>
                                        <td><label>Total after Discount:</label></td>
                                        <td class="text-end"><p style="color:#1c4da0;"><strong>124</strong></p></td>
                                    </tr> --}}


                                                        <!-- COUPON WORK - END -->










                                                    </tbody>
                                                </table>
                                                <div class="row">
                                                    <div class="col-sm-12 text-end">
                                                        <button class="btn btn-primary btn-user pay">Pay Now</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="oid" name="oid"
                                        value="{{ app('request')->input('oid') }}" />
                                    <input type="hidden" id="cpn" name="cpn" />
                                    <input type="hidden" id="fcpn" name="fcpn" value="0" />
                                </form>
                            </div>


                        </div>



                    </div>



                </div>
            </div>
        </div>
    </div>
    <!-- ORDER DETAILS - END -->




</main>



@stop



@push('other-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="{{ asset('assets/js/loadingoverlay.min.js') }}"></script>
    <script>
        function redirectFunc() {
            location.href = "{{ route('thank.you') }}";
        }
        $(".pay").click(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: "{{ route('pay.submit') }}",
                data: $("#pay_submit").serialize(),
                cache: false,
                beforeSend: function(xhr) {
                    jQuery('.pay').LoadingOverlay("show");
                },
                success: function(data) {
                    if (data.status == "success") {
                        $("#pay_update_msg").fadeIn();
                        $("#pay_update_msg .alert").addClass('alert-success').removeClass(
                            'alert-danger');
                        $("#pay_update_msg .alert").text(data.message);

                        setTimeout(redirectFunc, 2000);
                        jQuery('.pay').LoadingOverlay("hide");

                    } else {
                        $("#pay_update_msg").fadeIn();
                        $("#pay_update_msg .alert").addClass('alert-danger').removeClass(
                            'alert-success');
                        $("#pay_update_msg .alert").text(data.message);
                        jQuery('.pay').LoadingOverlay("hide");
                    }

                }
            });
        });

        $("#couponApply").click(function() {
            if ($("#coupon").val() == "") {
                $("#discount_msg").fadeIn();
                $("#discount_msg .alert").addClass('alert-danger').removeClass('alert-success');
                $("#discount_msg .alert").text("Must fill coupon!");
                return false;
            }


            $("#cpn").val('');
            $.ajax({
                type: "POST",
                url: "{{ route('apply.coupon') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "coupon": $("#coupon").val(),
                    "oid": $("#oid").val()
                },
                cache: false,
                beforeSend: function(xhr) {
                    jQuery('#couponApply').LoadingOverlay("show");
                },
                success: function(data) {
                    //console.log(data);
                    if (data.status == "success") {
                        $("#cpn").val(data.coupon.coupon);
                        $("#amtDiscount").html("$" + data.discount);
                        $("#amtTotal").html(data.totalAmt);
                        $("#discount_msg").fadeIn();
                        $("#discount_msg .alert").addClass('alert-success').removeClass(
                            'alert-danger');
                        $("#discount_msg .alert").text(data.message);
                        jQuery('#couponApply').LoadingOverlay("hide");

                        $("#coupon").prop("readonly", true);
                        if (data.coupon.freeall == 1 && data.insurance == 0 && data.dd_amt == 0) {
                            $("#cardholder_name").val("");
                            $("#cardholder_name").prop("disabled", true);
                            $("#cc_no").val("");
                            $("#cc_no").prop("disabled", true);
                            $("#cvv").val("");
                            $("#cvv").prop("disabled", true);
                            $("#cc_month").val("");
                            $("#cc_month").prop("disabled", true);
                            $("#cc_year").val("");
                            $("#cc_year").prop("disabled", true);
                            $(".pay").text("Order Now");
                            $("#fcpn").val(1);
                        } else {
                            //$("#cardholder_name").val("");
                            $("#cardholder_name").prop("disabled", false);
                            //$("#cc_no").val("");
                            $("#cc_no").prop("disabled", false);
                            //$("#cvv").val("");
                            $("#cvv").prop("disabled", false);
                            //$("#cc_month").val("");
                            $("#cc_month").prop("disabled", false);
                            //$("#cc_year").val("");
                            $("#cc_year").prop("disabled", false);
                            $(".pay").text("Pay Now");
                            $("#fcpn").val(0);
                        }
                        //setTimeout(redirectFunc, 2000);

                    } else {
                        $("#discount_msg").fadeIn();
                        $("#discount_msg .alert").addClass('alert-danger').removeClass(
                            'alert-success');
                        $("#discount_msg .alert").text(data.message);
                        jQuery('#couponApply').LoadingOverlay("hide");
                    }


                }
            });

        });

        $(".deleteOrder").click(function() {
            console.log($(this).attr("data-del"));
            var d = $(this).attr("data-del");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, do it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    //suborder.delete
                    // Action when confirmed
                    {{-- Swal.fire(
                            'Confirmed!',
                            'Your action has been completed.',
                            'success'
                        ); --}}
                    $.ajax({
                        {{-- url: "{{ route('suborder.delete', d) }}", --}}
                        url: "{{ route('suborder.delete', ':d') }}".replace(':d',
                            d), // R
                        type: 'GET',
                        success: function(response) {
                            location.href = "";
                            // console.log(response);
                            //alert('Data retrieved successfully!');
                        },
                        error: function(xhr, status, error) {
                            //console.error(error);
                            //alert('Failed to fetch data: ' + xhr.responseText);
                        }
                    });

                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Action when canceled
                    {{-- Swal.fire(
                        'Cancelled',
                        'Your action has been canceled.',
                        'error'
                    ); --}}
                }
            });
        })
    </script>
@endpush
