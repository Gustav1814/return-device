@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Order #{{ $data->id }}</h1>
    </div><!-- End Page Title -->
    <div class="card">
        <div class="card-body py-3">
            @if (session('successMsg'))
                <div class="alert alert-success">
                    {{ session('successMsg') }}
                </div>
            @endif

            @if (session('errorMsg'))
                <div class="alert alert-danger">
                    {{ session('errorMsg') }}
                </div>
            @endif
            <div class="col-lg-12 mb-4" id="emp_update_msg" style="display:none;">
                <div class="alert alert-success">
                    Employee data has updated!
                </div>
            </div>
            <div class="alert alert-danger" style="display:none;" id="errorDiv"></div>
            <form class="user" name="emp_details" id="emp_details">
                @csrf

                <h5>Type of Equipment</h5>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <select id="equipment_type" class="form-select mb-3" name="equipment_type" required="">
                                <option value="Laptop">Laptop</option>
                                <option value="Monitor">Monitor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                    </div>
                </div>

                <h5>Employee Address</h5>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="emp_firstname" placeholder="First Name"
                                value="{{ $data->emp_first_name }}" name="emp_firstname" maxlength="15">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="emp_lastname" placeholder="Last Name"
                                value="{{ $data->emp_last_name }}" name="emp_lastname" maxlength="15">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="emp_email" placeholder="Email"
                                value="{{ $data->emp_email }}" name="emp_email" maxlength="25">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <span class="input-group-text">+1</span>
                            <input type="tel" class="form-control" id="emp_phone" placeholder="Phone"
                                value="{{ $data->emp_phone }}" name="emp_phone" maxlength="15">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="emp_add1" placeholder="Address 1"
                                value="{{ $data->emp_add_1 }}" name="emp_add1" maxlength="45">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="emp_add2" placeholder="Address 2"
                                value="{{ $data->emp_add_2 }}" name="emp_add2" maxlength="45">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-4 col-12">
                        <input type="text" class="form-control" id="emp_city" placeholder="City"
                            value="{{ $data->emp_city }}" name="emp_city" maxlength="15">
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <select id="emp_state" class="form-select mb-3" name="emp_state" required="">
                            @include('includes.states_options')
                        </select>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <input type="text" class="form-control" id="emp_pcode" placeholder="Zip/Postal code"
                            value="{{ $data->emp_pcode }}" name="emp_pcode" maxlength="20">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-12">
                        <textarea class="form-control" name="custom_msg" id="custom_msg">{{ $data->custom_msg }}</textarea>
                        <p id="charCount"></p>
                    </div>
                </div>
                <h5>Return To Company</h5>




                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <select name="return_srv" id="return_srv" class="form-select" style="">
                                <option value=""> Select below</option>
                                <option value="1"> Return To Company</option>
                                <option value="2"> Recycle with Data Destruction</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">

                    </div>
                </div>







                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="comp_name" placeholder="Company Name"
                                value="{{ $data->receipient_name }}" name="comp_name" maxlength="45">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="comp_email" placeholder="Company Email"
                                value="{{ $data->receipient_email }}" name="comp_email" maxlength="45">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="comp_rec_person"
                                placeholder="Company Receipient Name" value="{{ $data->receipient_person }}"
                                name="comp_rec_person" maxlength="25">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <span class="input-group-text">+1</span>
                            <input type="tel" class="form-control" id="comp_phone" placeholder="Company Phone"
                                value="{{ $data->receipient_phone }}" name="comp_phone" maxlength="15">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="comp_add_1" placeholder="Company Address 1"
                                value="{{ $data->receipient_add_1 }}" name="comp_add_1" maxlength="45">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="comp_add_2" placeholder="Company Address 2"
                                value="{{ $data->receipient_add_2 }}" name="comp_add_2" maxlength="45">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-4 col-12">
                        <input type="text" class="form-control" id="comp_city" placeholder="Company City"
                            value="{{ $data->receipient_city }}" name="comp_city" maxlength="45">
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <select id="comp_state" class="form-select mb-3" name="comp_state" required="">
                            @include('includes.states_options')
                        </select>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <input type="text" class="form-control" id="comp_zip" placeholder="Zip/Postal Code"
                            value="{{ $data->receipient_zip }}" name="comp_zip" maxlength="45">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-dark theme-bgcolor-btn-one mt-3 employeeSub">Update Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>


@stop

@push('other-scripts')
    <script>
        //function redirectFunc() { location.href= "{{ route('suborder.edit', ['sid' => $data->id]) }}"; }
        function redirectFunc() {
            location.href = "";
        }

        function IsEmail(email) {
            const regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!regex.test(email)) {
                return false;
            } else {
                return true;
            }
        }

        $(".employeeSub").click(function(e) {
            e.preventDefault();
            $(".alert-success").fadeOut();
            $(".alert-danger").fadeOut();
            console.log($(this).attr("data-empbtn"));
            var btnType = $(this).attr("data-empbtn");
            var i = 0;
            var m = 'Required Fields: ';

            if ($("#emp_firstname").val() == "") {
                m += "First Name,";
                i = 1;
            }
            if ($("#emp_lastname").val() == "") {
                m += "Last Name,";
                i = 1;
            }
            if ($("#emp_email").val() == "") {
                m += "Email,";
                i = 1;
            }
            if (IsEmail($("#emp_email").val()) === false) {
                m += "Employee Email,";
                i = 1;
            }
            if ($("#emp_phone").val() == "") {
                m += "Phone,";
                i = 1;
            }
            if ($("#emp_add1").val() == "") {
                m += "Adress 1,";
                i = 1;
            }
            if ($("#emp_city").val() == "") {
                m += "City,";
                i = 1;
            }
            if ($("#emp_state").val() == "") {
                m += "State,";
                i = 1;
            }
            if ($("#emp_pcode").val() == "") {
                m += "Zip/Postcode,";
                i = 1;
            }

            if ($("#comp_name").val() == "") {
                m += "Employee Zip/Postcode,";
                i = 1;
            }
            if ($("#comp_email").val() == "") {
                m += "Company Email,";
                i = 1;
            }
            if (IsEmail($("#comp_email").val()) === false) {
                m += "Company Email,";
                i = 1;
            }
            if ($("#comp_phone").val() == "") {
                m += "Company Phone,";
                i = 1;
            }
            if ($("#comp_add_1").val() == "") {
                m += "Company Address 1,";
                i = 1;
            }
            if ($("#comp_city").val() == "") {
                m += "Company City,";
                i = 1;
            }
            if ($("#comp_state").val() == "") {
                m += "Company State,";
                i = 1;
            }
            if ($("#comp_zip").val() == "") {
                m += "Company Zip,";
                i = 1;
            }
            if ($("#comp_rec_person").val() == "") {
                m += "Company Receipient Name,";
                i = 1;
            }

            if ($('input[name="data_destruction"][value="2"]').prop('checked') == true) {
                if ($("#new_emp_firstname").val() == "") {
                    m += "New Employee First Name,";
                    i = 1;
                }
                if ($("#new_emp_lastname").val() == "") {
                    m += "New Employee Last Name,";
                    i = 1;
                }
                if ($("#new_emp_email").val() == "") {
                    m += "New Employee Email,";
                    i = 1;
                }
                if (IsEmail($("#new_emp_email").val()) === false) {
                    m += "New Employee Email,";
                    i = 1;
                }
                if ($("#new_emp_phone").val() == "") {
                    m += "New Employee Phone,";
                    i = 1;
                }
                if ($("#new_emp_add1").val() == "") {
                    m += "New Employee Address 1,";
                    i = 1;
                }
                if ($("#new_emp_city").val() == "") {
                    m += "New Employee City,";
                    i = 1;
                }
                if ($("#new_emp_state").val() == "") {
                    m += "New Employee State,";
                    i = 1;
                }
                if ($("#new_emp_pcode").val() == "") {
                    m += "New Employee Zip/Postcode,";
                    i = 1;
                }
            } // END OF DD , RETURN TO NEW EMP


            $(".employeeSub").attr('disabled', 'disabled');
            if (i == 1) {
                $(".employeeSub").removeAttr('disabled');
                $("#errorDiv").fadeIn();
                $("#errorDiv").html(m);
                window.scrollTo(0, 0);
                return false;
            }

            //console.log( $(this).attr("data-empbtn") );
            var formData = $(this).serialize();
            $.ajax({
                type: "POST",
                url: "{{ route('suborder.edit', $data->id) }}",
                data: $("#emp_details").serialize(),
                cache: false,
                success: function(data) {
                    $(".employeeSub").removeAttr('disabled');
                    if (data.status == "success") {
                        var appMsg = '';
                        // if($(this).attr("data-empbtn") == 1){ appMsg = "<br/>You can add next employee.";}
                        $("#emp_update_msg").fadeIn();
                        $("#emp_details")[0].reset();
                        $("#emp_update_msg .card .card-body").append(appMsg);
                        window.scrollTo(0, 0);

                        setTimeout(redirectFunc, 1000);

                    } else {
                        $("#emp_update_msg").fadeIn();
                        $("#emp_update_msg .card").addClass('bg-danger').removeClass('bg-success');
                        $("#emp_update_msg .card .card-body").text(data.message);
                        window.scrollTo(0, 0);

                    }

                }
            });
        });


        $("#return_srv").change(function(e) {
            $("input[name='data_destruction']").prop("checked", false);
            e.preventDefault();
            $("#comp_name").val("");
            $("#comp_email").val("");
            $("#comp_phone").val("");
            $("#comp_add_1").val("");
            $("#comp_add_2").val("");
            $("#comp_city").val("");
            $("#comp_state").val("");
            $("#comp_zip").val("");
            $("#comp_rec_person").val("");

            if ($(this).val() != "") {
                $(".return_details").fadeIn();
                if ($(this).val() == 1) {
                    getCompanyDetails();


                    $("#additional_srv").delay(100).fadeIn({
                        duration: 800,
                        start: function() {
                            // Show the loading spinner at the start of fadeIn

                            $("#loading").show();
                            $(".col-sm-12").hide();

                        },
                        complete: function() {
                            // Hide the loading spinner once fadeIn is complete
                            $("#loading").hide();
                            $(".col-sm-12").show();
                        }
                    });



                    // $("#additional_srv").fadeIn(); // data destruction box
                    $(".return_details").fadeIn();
                } else if ($(this).val() == 2) {
                    $("#comp_name").val("<?php echo env('REMOTE_COMPANY_NAME'); ?>");
                    $("#comp_email").val("<?php echo env('REMOTE_COMPANY_EMAIL'); ?>");
                    $("#comp_phone").val("<?php echo env('REMOTE_COMPANY_PHONE'); ?>");
                    $("#comp_add_1").val("<?php echo env('REMOTE_COMPANY_ADD1'); ?>");
                    $("#comp_add_2").val("<?php echo env('REMOTE_COMPANY_ADD2'); ?>");
                    $("#comp_city").val("<?php echo env('REMOTE_COMPANY_CITY'); ?>");
                    $("#comp_state").val("<?php echo env('REMOTE_COMPANY_STATE'); ?>");
                    $("#comp_zip").val("<?php echo env('REMOTE_COMPANY_ZIP'); ?>");
                    $("#comp_rec_person").val("<?php echo env('REMOTE_REC_NAME'); ?>");
                    $("#additional_srv").fadeOut(); // data destruction box
                    $("input[name='data_destruction']").prop("checked", false);

                    $("#divNewEmpData").fadeOut();
                }
                $(".employeeSub").fadeIn();
            } else {
                $(".return_details").fadeOut();
                $("#comp_name").val("");
                $(".employeeSub").fadeOut();

                $("#additional_srv").fadeOut();
                $("#divNewEmpData").fadeOut();
            }
        });


        function getCompanyDetails() {
            $.ajax({
                type: "POST",
                url: "{{ route('getcompany_details') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "cid": " <?php echo $data->parent_comp_id; ?>"
                },
                cache: false,
                success: function(data) {
                    if (data.status == 'success') {
                        $("#comp_name").val(data.details.company_name);
                        $("#comp_email").val(data.details.company_email);
                        $("#comp_phone").val(data.details.company_phone);
                        $("#comp_add_1").val(data.details.company_add_1);
                        $("#comp_add_2").val(data.details.company_add_2);
                        $("#comp_city").val(data.details.company_city);
                        $("#comp_state").val(data.details.company_state);
                        $("#comp_zip").val(data.details.company_zip);
                        $("#comp_rec_person").val(data.details.receipient_name);

                    }
                }
            });
        }



        $("#equipment_type").val("{{ $data->type_of_equip }}");
        $("#comp_state").val("{{ $data->receipient_state }}");
        $("#emp_state").val("{{ $data->emp_state }}");
        $("#new_emp_state").val("{{ $newEmpData['newemp_state'] ?? '' }}");

        @if ($data->return_service == 'Return To Company')
            $("#return_srv").val(1);
            $("#additional_srv").fadeIn(); // data destruction box
            @if ($data->return_additional_srv != null)
                @if ($data->return_additional_srv == 1)
                    $('input[name="data_destruction"][value="1"]').prop('checked', true);
                @elseif ($data->return_additional_srv == 2)
                    $('input[name="data_destruction"][value="2"]').prop('checked', true);
                    $("#newEmpBox").fadeIn();
                @endif
            @endif
        @else
            $("#return_srv").val(2);
        @endif

        var user_setting_phone = $(".form-control:input[type=tel]");
        if (user_setting_phone.length > 0) {
            user_setting_phone.each(function(item, v) {
                v.addEventListener('input', function(e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                    e.target.value = !x[2] ? x[1] : '(' + x[1] + ')' + x[2] + (x[3] ? '-' + x[3] : '');
                })
            });
        }

        $("#ins_tick").click(function() {
            if ($("#ins_tick").prop('checked') == true) {
                $("#ins_amount").fadeIn();
            } else {
                $("#ins_amount").val("");
                $("#ins_amount").fadeOut();
            }
        })

        $("input[name='data_destruction']").on('change', function() {
            $("input[name='data_destruction']").not(this).prop('checked', false);
            if ($(this).val() == 2 && $(this).prop("checked")) {
                //$("#newEmpBox").fadeIn();

                $("#newEmpBox").delay(100).fadeIn({
                    duration: 800,
                    start: function() {
                        // Show the loading spinner at the start of fadeIn

                        $("#loadingNewEmp").show();
                        $("#divNewEmpData").hide();

                    },
                    complete: function() {
                        // Hide the loading spinner once fadeIn is complete
                        $("#loadingNewEmp").hide();
                        $("#divNewEmpData").show();
                    }
                });




            } else {
                $("#newEmpBox").fadeOut();
            }
        });

        const textarea = document.getElementById('custom_msg');
        const charCountDisplay = document.getElementById('charCount');
        const maxChars = 1000;

        textarea.addEventListener('input', function() {
            const currentLength = textarea.value.length;
            if (currentLength > maxChars) {
                textarea.value = textarea.value.substring(0, maxChars); // Trim the extra characters
            }
            charCountDisplay.textContent = `${textarea.value.length}/${maxChars} characters`;
        });

        $("#equipment_type").val("{{ $data->type_of_equip }}");
    </script>
@endpush
