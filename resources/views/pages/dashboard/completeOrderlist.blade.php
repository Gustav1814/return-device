@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Completed Orders</h1>
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
                <div class="alert alert-danger" id="errorMessage" style=" display: none;"></div>

                <div class="filter">
                    <form id="filterForm" action="{{ route('completed.orders.filter') }}" method="GET" class="search-filter admin-search-filter">
                        @csrf
                        <div class="row py-3 align-items-center">
                            <div class="col-md-3">
                                <select class="form-select search_by" aria-label="Search By" name="search_by" id="search_by">
                                    <option value="" selected>Search By</option>
                                    <option value="Device Type" {{ old('search_by') == 'Device Type' ? 'selected' : '' }}>Device Type</option>
                                    <option value="Return Service" {{ old('search_by') == 'Return Service' ? 'selected' : '' }}>Return Service</option>
                                    <option value="Date" {{ old('search_by') == 'Date' ? 'selected' : '' }}>Date</option>
                                    <option value="Custom Search" {{ old('search_by') == 'Custom Search' ? 'selected' : '' }}>Custom Search</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="device-type">
                                <select class="form-select" name="device_type" id="device_type">
                                    <option value="" selected>Device Type</option>
                                    <option value="Laptop" {{ old('device_type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                    <option value="Monitor" {{ old('device_type') == 'Monitor' ? 'selected' : '' }}>Monitor</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="return-service">
                                <select class="form-select" name="return_service" id="return_service">
                                    <option value="" selected>Return Service</option>
                                    <option value="Return To Company" {{ old('return_service') == 'Return To Company' ? 'selected' : '' }}>Return To Company</option>
                                    <option value="Sell This Equipment" {{ old('return_service') == 'Sell This Equipment' ? 'selected' : '' }}>Sell This Equipment</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="custom-date">
                                <input type="date" name="date" id="date" class="form-control" value="{{ old('date') }}">
                            </div>
                            <div class="col-md-3" id="custom-search">
                                <input type="text" name="search" id="search" placeholder="Search..." class="form-control" value="{{ old('search') }}">
                            </div>
                            <div class="col-md-3" id="custom-submit">
                                <button type="submit" class="btn btn-dark theme-bgcolor-btn-one">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                {{-- <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-payment-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-payment-pending" type="button" role="tab" aria-controls="pills-payment-pending" aria-selected="true">Payment Pending Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-label-pending-tab" data-bs-toggle="pill" data-bs-target="#pills-label-pending" type="button" role="tab" aria-controls="pills-label-pending" aria-selected="false">Label Pending Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-in-progress-tab" data-bs-toggle="pill" data-bs-target="#pills-in-progress" type="button" role="tab" aria-controls="pills-in-progress" aria-selected="false">In Progress Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-completed-tab" data-bs-toggle="pill" data-bs-target="#pills-completed" type="button" role="tab" aria-controls="pills-completed" aria-selected="false">Completed Orders</button>
                </li>
            </ul> --}}
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
                                        <th scope="col">Order Status</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $order)
                                        <tr>
                                            <td>{{ $order->item_id }}</td>
                                            <td>{{ $order->equip_type }}</td>
                                            <td>
                                            {{-- {{ $order->return_srv }} --}}
                                            @if($order->return_srv == 'Sell This Equipment')
                                                    Recycle with Data Destruction
                                                @else
                                                    {{ $order->return_srv }}
                                                @endif
                                                </td>
                                            <td>{{ $order->company_name }}</td>
                                            <td><span class="badge bg-dark">Completed</span></td>
                                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                                            <td>
                                            @if($order->box_label != 1 && $order->device_label != 1)
                                                <a href="{{ route('suborder.edit', $order->item_id) }}" class="badge bg-dark theme-bgcolor-btn-one"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="16"
                                                    height="16" fill="currentColor"
                                                    class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                    <path
                                                        d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                    <path fill-rule="evenodd"
                                                        d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                </svg></a>
                                            @endif
                                                <a href="{{ route('order.detail', $order->item_id) }}" class="badge bg-dark theme-bgcolor-btn-one"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="16"
                                                    height="16" fill="currentColor" class="bi bi-eye"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                    <path
                                                        d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                </svg></a>
                                                @if($order->box_label != 1 && $order->device_label != 1)
                                                <a href="#" class="badge bg-dark theme-bgcolor-btn-one deleteOrder" data-del="{{ $order->item_id }}"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="16"
                                                    height="16" fill="currentColor" class="bi bi-trash3"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                                </svg></a>
                                                @endif
                                            </td>
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
    <script>
        $(document).ready(function() {
            // Initially hide all optional fields
            $('#device-type, #return-service, #custom-date, #custom-search').hide();

            // Listen for changes in the "search_by" dropdown
            $('#search_by').on('change', function() {
                var selectedValue = $(this).val();

                // Hide all fields initially
                $('#device-type, #return-service, #custom-date, #custom-search').hide();

                // Show the relevant fields based on the selected value
                if (selectedValue === 'Device Type') {
                    $('#device-type').show();
                } else if (selectedValue === 'Return Service') {
                    $('#return-service').show();
                } else if (selectedValue === 'Date') {
                    $('#custom-date').show();
                } else if (selectedValue === 'Custom Search') {
                    $('#custom-search').show();
                }
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
                        Swal.fire(
                            'Cancelled',
                            'Your action has been canceled.',
                            'error'
                        );
                    }
                });
            })

        });








$(document).ready(function () {
        $('#filterForm').on('submit', function (e) {
            const searchBy = $('#search_by').val();
            const deviceType = $('#device_type').val();
            if (searchBy == "" ) {
                e.preventDefault(); // Prevent form submission
                $('#errorMessage').show(); // Show error message
                $("#errorMessage").text("Must select search by options!");
            } else {
                if(searchBy == "Device Type" && deviceType == "")
                {
                    e.preventDefault(); // Prevent form submission
                    $('#errorMessage').show(); // Show error message
                    $("#errorMessage").text("Must select device type : Laptop or Monitor!");
                    return false;
                }
                else if(searchBy == "Return Service" && $("#return_service").val() == "")
                {
                    e.preventDefault(); // Prevent form submission
                    $('#errorMessage').show(); // Show error message
                    $("#errorMessage").text("Must select return service : Return to Company or Sell This Equipment!");
                    return false;
                }
                else if(searchBy == "Date" && $("#date").val() == "")
                {
                    e.preventDefault(); // Prevent form submission
                    $('#errorMessage').show(); // Show error message
                    $("#errorMessage").text("Must choose date!");
                    return false;
                }
else if(searchBy == "Custom Search" && $("#search").val() == "")
                {
                    e.preventDefault(); // Prevent form submission
                    $('#errorMessage').show(); // Show error message
                    $("#errorMessage").text("Must fill search field!");
                    return false;
                }
                $('#errorMessage').hide(); // Hide error message
            }
        });

    });

    </script>

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

        /* .all-orders .card {
            border: none;
            box-shadow: 0px 0px 2px 0px #000;
            border-radius: 0px !important;
        }

        .all-orders .card ul.pagination li.page-item.active {
            background-color: var(--button-background);
        } */
    </style>


    @if (!empty($search_details))
            @php
                $search_by = (!empty($search_details['search_by'])) ? $search_details['search_by'] : '' ;
                $device_type = (!empty($search_details['device_type'])) ? $search_details['device_type'] : '' ;
                $return_service = (!empty($search_details['return_service'])) ? $search_details['return_service'] : '' ;
                $date = (!empty($search_details['date'])) ? $search_details['date'] : '' ;
                $search = (!empty($search_details['search'])) ? $search_details['search'] : '' ;
            @endphp
            <script>
                $(document).ready(function() {

                    // Get the selected value
                    var selectedValue = '{{$search_by}}';
                    $('#search_by').val(selectedValue);
                    // Show the corresponding div based on the selected value
                    if (selectedValue == 'Device Type') {

                        $('#device-type, #custom-submit').show();
                        $('#return-service, #custom-date, #custom-search').hide();
                        $('#device-type select').val('{{$device_type}}');

                    } else if (selectedValue == 'Return Service') {

                        $('#return-service, #custom-submit').show();
                        $('#device-type, #custom-date, #custom-search').hide();
                        $('#return-service select').val('{{$return_service}}');

                    } else if (selectedValue == 'Date') {

                        $('#custom-date, #custom-submit').show();
                        $('#device-type, #return-service, #custom-search').hide();
                        $('#custom-date input').val('{{$date}}');

                    } else if (selectedValue == 'Custom Search') {

                        $('#custom-search, #custom-submit').show();
                        $('#device-type, #return-service, #custom-date').hide();
                        $('#custom-search input').val('{{$search}}');

                    }else{
                        $('#device-type, #return-service, #custom-date, #custom-search, #custom-submit').hide();
                    }

                });
            </script>
        @endif
@endpush
