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
            <h1>Company Detail</h1>
        </div><!-- End Page Title -->
        <div class="card">
            <div class="card-body py-3">
                <div class="order-detail">

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


                       <div class="row order-detail-head">
                           <div class="col-md-12 text-md-end text-sm-start">
                               <p id="modalLblShownBtn"><span class="badge bg-primary"></span></p>
                               <p class="my-3" id="labelTrackDetails">
                                   {{-- {{ $trackDetails }} --}}
                                   {{-- {!! $trackDetails !!} --}}
                                   {{-- Laptop Box Tracking:<a target="_blank" href="#">1ZGW35400317312081</a><br>
                                   Laptop Return Tracking: <a target="_blank" href="#">1ZGW35400328255604</a> --}}
                               </p>
                           </div>
                       </div>
                       <div class="order-detail-title-bar theme-bgcolor-btn-one mb-3">
                           <div class="row">
                               <div class="col-md-3 col-12 text-md-start text-sm-start">
                                   <h6>Company Name</h6>
                                   <p id="date">
                                   {{ $data->company_name}}

                                   </p>
                               </div>
                               <div class="col-md-3 col-12">
                                   <h6>Company Domain</h6>
                                   <p><a  title="http://{{ $data->company_domain }}.{{ env('MAIN_DOMAIN') }}"  target="_blank" href="http://{{ $data->company_domain }}.{{ env('MAIN_DOMAIN') }}">{{ $data->company_domain }}
                                   </a>
                                   &nbsp;<span id="dynamicLink" class="badge bg-dark" id="type"> Edit</span>
                                   </p>
                                   <div id="inputContainer" style="display: none;">
                                    <input type="text" id="textBox" />
                                    <button id="submitButton" class="btn btn-dark btn-sm">Submit</button>
                                   </div>
                               </div>
                               <div class="col-md-3 col-12">
                                   <h6>Status</h6>
                                   <span class="badge bg-dark" id="type">
                                   {{ ucfirst( $data->status ) }}
                                   </span>
                               </div>
                               <div class="col-md-3 col-12">
                                   <h6>Action</h6>
                                   <select id="status">
                                   <option value="active">Active</option>
                                   <option value="inactive">In-Active</option>
                                   </select>

                                   <input type="button" value="Submit" id="companyStatus" class="btn btn-dark btn-sm"/>
                               </div>

                               {{-- <div class="col-md-3 col-12 text-md-end text-sm-start">
                                   <h6>Payment Details</h6>
                                   <p id="orderAmt">Order Amount: <strong>${{ $data->order_amt }}</strong></p>
                                   <p id="insAmount">{!! $insAmt !!}</p>
                                   <p id="ddAmount">{!! $PayDetCoupon !!}</p>
                                   <p id="PayDetCoupon">{!! $PayDetCoupon !!}</p>
                               </div> --}}
                           </div>
                       </div>
                       <div class="order-detail-body">
                           <div class="row">
                               <div class="col-md-12">
                                   <h5 id="headingWithEdit">Order Detail</h5>
                                   <hr />
                               </div>

                               {{-- {{ dd($data) }} --}}
                               <div class="col-md-6">
                                   <h6>Company Details</h6>
                                   <label>Company:</label> <strong>{{ $data->company_name }}</strong><br/>
                                   <label>Domain:</label> <strong>{{ $data->company_domain }}</strong><br/>
                                   <label>Receipient:</label> <strong>{{ $data->receipient_name }}</strong><br/>
                                   <label>Email:</label> <strong>{{ $data->company_email }}</strong><br/>
                                   <label>Address:</label> <strong>{{ $data->company_add_1 }}{{ $data->company_add_2 }}</strong><br/>
                                   <label>City:</label> <strong>{{ $data->company_city }}</strong><br/>
                                   <label>State:</label> <strong>{{ $data->company_state }}</strong><br/>
                                   <label>Zip:</label> <strong>{{ $data->company_zip }}</strong><br/>
                                   <label>Created At:</label> <strong>{{ \Carbon\Carbon::parse($data->created_at)->format('M d, Y') }}</strong><br/>
                               </div>
                               <div class="col-md-6">
                                   <h6>User Details</h6>
                                   <label>Name:</label> <strong>{{ $data->name }}</strong><br/>
                                   <label>Email:</label> <strong>{{ $data->email }}</strong><br/>
                                   <label>Phone:</label> <strong>{{ $data->phone }}</strong><br/>

                                   {{-- <p>{{ $data->receipient_email }}</p>
                                   <p>{{ $data->receipient_phone }}</p>
                                   --}}
                               </div>
                           </div>
                       </div>
                </div>
            </div>
        </div>
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

 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script>

    $("#companyStatus").click(function(){
    let id = "{{ $data->id }}";
    let status = $("#status").val();
    let url = `{{ route('update.company.status', ':id') }}?status=${status}`;
    url = url.replace(':id', id);

         Swal.fire({
                    title: 'Are you sure?',
                    text: "The company's status will be updated, and login access will depend on this status.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, do it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                      //let status = $("#status").val();
                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(response) {
                                location.href = "";
                            },
                            error: function(xhr, status, error) {
                            }
                        });

                    }
                });
    });

    $("#status").val("{{ $data->status }}");

    $(document).ready(function() {
    $("#dynamicLink").click(function(event) {
        event.preventDefault(); // Prevent navigation
        //let linkValue = $(this).attr("href"); // Get the link URL
        let linkValue = $(this).prev().text(); // Get the link URL
        $("#textBox").val(linkValue); // Set the input value
        $("#inputContainer").show(); // Show the input and button
        $("#dynamicLink").fadeOut();
    });

    $("#submitButton").click(function() {
let id = "{{ $data->id }}";
 let url = `{{ route('update.company.domain', ':id') }}?company_domain=${$("#textBox").val()}`;
 url = url.replace(':id', id);
         Swal.fire({
                    title: 'Are you sure?',
                    text: "The company's domain will be updated, and URL will depend on this domain.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, do it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                      //let status = $("#status").val();
                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(response) {
                                location.href = "";
                            },
                            error: function(xhr, status, error) {
                            }
                        });

                    }
                });



        $("#dynamicLink").fadeIn();
        let enteredValue = $("#textBox").val();
        $("#dynamicLink").prev().text(enteredValue);
        $("#inputContainer").fadeOut();


    });

    ///function myFunction(value) {
       // alert("Function called with value: " + value);
    //}

    $("#textBox").on("input", function () {
        $(this).val($(this).val().replace(/\s/g, '').toLowerCase());
    });
});
    </script>


@endpush
