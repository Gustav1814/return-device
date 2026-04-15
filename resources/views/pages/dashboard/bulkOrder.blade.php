@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <div class="alert alert-success" role="alert" id="alertBox" style="display:none;">
            <strong>Success:</strong> Order has created
        </div>
        <h1>Start Bulk Returns</h1>
        <p>Please upload a CSV file with details of your employee(s), return address(s) and the type of equipment to start bulk return in just one go!</p>
    </div><!-- End Page Title -->
    <div class="all-orders">
        <div class="card">
            <div class="card-body py-3">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-0">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create Bulk Orders using CSV File</h1>
                            </div>

                            <div class="col-lg-12 mb-4" id="emp_update_msg" style="display:none;">
                                <div class="card bg-success text-white shadow">
                                    <div class="card-body">
                                        Data has successfully updated
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                            </div>
                            <hr style="border:1px solid #ddd;">
                            <form action="" class="user" name="order_csv" id="order_csv" enctype="multipart/form-data">
                                <div class="alert alert-dark" role="alert">Download sample CSV file <a href="/download-filecsv" title="Sample CSV"><strong>here</strong></a> - Use the column headings to enter the correct information row wise.
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="col-sm-3 mb-3 mb-sm-0">
                                        <input type="file" name="csvFile" id="csvFile" accept=".csv">
                                    </div>
                                    <div class="col-sm-9">
                                        <button class="btn btn-dark theme-bgcolor-btn-one step-one-next uploadCSV">Upload</button>
                                    </div>
                                </div>
                                <hr>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@stop



@push('other-scripts')
    <script>

function redirectFunc() {
            location.href = "{{ route('orders.list') }}";
        }
$(document).ready(function() {
    $('#order_csv').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = new FormData(this);
        var file = $('#csvFile')[0].files[0];

        if (file) {
            formData.append('csvFile', file);
            formData.append('_token',"{{ csrf_token() }}");
          //  formData.append('oid', 10);
          //  formData.append('selected', selectedValues);

            $.ajax({
                type: "POST", // Use POST for file upload
                url: "{{ route('submit.order.bycsv') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                $("#alertBox").fadeIn();
                $("#alertBox").addClass('alert-success').removeClass('alert-danger');
                $("#alertBox").html('');
                $("#alertBox").html('<strong>CSV processed successfully: </strong> '+response.message);
                setTimeout(redirectFunc,3000);
                },
                error: function(xhr, status, error) {
                $("#alertBox").fadeIn();
                $("#alertBox").addClass('alert-danger').removeClass('alert-success');
                $("#alertBox").html('<strong>CSV must have missing fields: </strong> '+xhr.responseJSON.message);

                }
            });
        } else {
            alert("Please select a file.");
        }
    });
});


    </script>
@endpush
