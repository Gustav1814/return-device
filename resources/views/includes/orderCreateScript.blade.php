<style>
    .mandatory {
        border-color: red !important;
        background-color: #ffe6e6;
    }

    .swal2-title {
        font-size: 22px !important;
        /* Customize title font size */
        color: #000 !important;
    }

    .swal2-confirm {
        background-color: var(--button-background) !important;
        /* Change OK button color */
        color: var(--button-text-color) !important;
        /* Change OK button text color */
        box-shadow: none !important;
        /* Remove shadow */
        border: none !important;
        /* Remove border */
        border-radius: 5px !important;
        /* Add rounded corners */
    }

    .swal2-popup {
        border: 2px solid var(--button-background) !important;
        border-radius: 15px !important;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<script>
    function showLoader() {
 document.querySelector('.loader').classList.remove('hidden');
}

function hideLoader() {
    document.querySelector('.loader').classList.add('hidden');
}
</script>
<script>
    $(document).ready(function() {
        // Toggle loader visibility
        function toggleLoader(show) {
            $('.loader').toggleClass('hidden', !show);
        }

        // Fade between steps and manage active classes
        function fadeSteps(currentStep, nextStep, isStepBack = false) {




            // STEP ONE VALIDATION - START

            console.log(currentStep);
            console.log(nextStep);
            if (currentStep == '.order-step-one' && nextStep == '.order-step-two') {
                let isValid = true;
                $('.required').each(function() {
                    console.log($(this).val());
                    if (!$(this).val()) {
                        $(this).addClass('mandatory');
                        isValid = false;
                    } else {
                        $(this).removeClass('mandatory');
                    }

                    //                    if($("#account_email").val() {

                });

                if (!isValid) {
                    event.preventDefault();
                    Swal.fire("Please fill all mandatory fields!");
                    return false;
                } else {

                    if (IsEmail($("#account_email").val()) === false && isValid == true) {
                        event.preventDefault();
                        $("#account_email").addClass('mandatory');
                        Swal.fire("Please fill proper email!");
                        return false;
                    }


                    $.ajax({
                        type: "GET",
                        url: "{{ route('order.amount') }}?type_of_equipment=" + $("#type_of_equipment")
                            .val(),
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            "_token": "{{ csrf_token() }}",
                            'Client-Id': 'remote-retrieval-app-web',
                            'Authorization': 'Basic eyJpdiI6IjRnbFVYTytLUG9RUFRKWFJuUkFWZkE9PSIsInZhbHVlIjoiNmZJblg5eUJTR1l1bzNFaTNpNmpTM1cydmllRnZ6aE5yM0hmZG93NmluTT0iLCJtYWMiOiI0MzlhNjkxNzliYjIzZmViZDc1NjY0ZWNiZTE4ZTIxYWI3OGMyYTFjMmJmNmI1OTMxMmUzYTBlZGQwYjkzY2YwIiwidGFnIjoiIn0='
                        },

                        cache: false,
                        success: function(data) {
                            {{-- if(data.status == 1 && data.message == "User created")
                    {
                        setTimeout(function() { redirectFunc(data); }, 5000);
                        Swal.fire("You have successfully signup!");
                    } --}}

                            $("#deviceAmt").val(data.amount);
                        }
                    });

                }

                // INSURANCE AMOUNT
                if ($("#ins_tick").prop('checked') == true) {
                    var insAmount = $("#ins_amount").val();
                    var insAmountPerc = (($("#ins_amount").val() * {{ env('INSURANCE_RATE') }}) / 100).toFixed(
                        2);

                    $("#insAmt").val(insAmount);
                    $("#insAmtPerc").val(insAmountPerc);
                } else {
                    $("#insAmt").val('');
                    $("#insAmtPerc").val('');
                }

                // DATA DESTRUCTION
                var checkedRadio = $("input[name='data_destruction']:checked");

                if (checkedRadio.length > 0) {
                    // If a radio button is checked, get its value
                    var dd_value = checkedRadio.val();
                    console.log("Checked value:", dd_value);
                    if (dd_value == 1) {
                        $("#ddAmt").val({{ env('DD_COMPANY') }});
                    }
                    if (dd_value == 2) {
                        $("#ddAmt").val({{ env('DD_NEW_EMP') }});
                    }



                } else {
                    $("#ddAmt").val('');
                }

                // var totalPayAmt = $("#deviceAmt").val() + $("#insAmtPerc").val() +  $("#ddAmt").val();





            }
            // STEP ONE VALIDATION - END

            // STEP TWO VALIDATION - START
            if (currentStep == '.order-step-two' && nextStep == '.order-step-three' ||
                currentStep == '.order-step-two' && nextStep == '.order-step-new-employee'
            ) {
                let isValid = true;
                $('.required2').each(function() {
                    console.log($(this).val());
                    if (!$(this).val()) {
                        $(this).addClass('mandatory');
                        isValid = false;
                    } else {
                        $(this).removeClass('mandatory');
                    }
                });

                if (!isValid) {
                    event.preventDefault();
                    Swal.fire("Please fill all mandatory fields!");
                    return false;
                } else {

                    if (IsEmail($("#employee_email").val()) === false && isValid == true) {
                        event.preventDefault();
                        $("#employee_email").addClass('mandatory');
                        Swal.fire("Please fill proper email!");
                        return false;
                    }


                    var deviceAmt = parseFloat($("#deviceAmt").val()) || 0; // Default to 0 if empty or invalid
                    var insAmtPerc = parseFloat($("#insAmtPerc").val()) ||
                    0; // Default to 0 if empty or invalid
                    var ddAmt = parseFloat($("#ddAmt").val()) || 0; // Default to 0 if empty or invalid

                    // Add the values
                    var totalPayAmt = deviceAmt + insAmtPerc + ddAmt;
                    $("#totalAmtPayTxt").val(totalPayAmt);
                    $("#totalAmtPay").html("$" + totalPayAmt);

                }



            }
            // STEP TWO VALIDATION - END


            // STEP THREE VALIDATION - START

            if (currentStep == '.order-step-new-employee' && nextStep == '.order-step-three') {
                let isValid = true;
                $('.required4').each(function() {
                    console.log($(this).val());
                    if (!$(this).val()) {
                        $(this).addClass('mandatory');
                        isValid = false;
                    } else {
                        $(this).removeClass('mandatory');
                    }

                    //                    if($("#account_email").val() {

                });

                if (!isValid) {
                    event.preventDefault();
                    Swal.fire("Please fill all mandatory fields!");
                    return false;
                } else {
                    if (IsEmail($("#new_employee_email").val()) === false && isValid == true) {
                        event.preventDefault();
                        $("#new_employee_email").addClass('mandatory');
                        Swal.fire("Please fill proper email!");
                        return false;
                    }
                }

            }
            // STEP THREE VALIDATION - END




            toggleLoader(true); // Show loader during transition
            $(currentStep).fadeOut(500, function() { // Fade out the current step
                $(nextStep).fadeIn(500, function() { // Fade in the next step
                    toggleLoader(false); // Hide loader after transition
                });
            });

            // Update progress indicators based on forward or backward movement
            updateProgressIndicator(currentStep, nextStep, isStepBack);
        }

        // Update active classes on progress boxes
        function updateProgressIndicator(currentStep, nextStep, isStepBack) {
            const progressMapping = {
                '.order-step-one': '.order-progress-content-box-one',
                '.order-step-two': '.order-progress-content-box-two',
                '.order-step-three': '.order-progress-content-box-three',
                '.order-step-new-employee': '.order-progress-content-box-new-employee' // New step mapping
            };

            if (isStepBack) {
                $(progressMapping[currentStep]).removeClass('active');
            } else {
                $(progressMapping[nextStep]).addClass('active');
            }
        }

        // Step transitions
        $('.step-one-next').on('click', function() {
            fadeSteps('.order-step-one', '.order-step-two');
        });

        $('.step-two-back').on('click', function() {
            fadeSteps('.order-step-two', '.order-step-one', true);
        });

        $('.step-two-next').on('click', function() {
            // Check if data destruction is selected
            if ($("input[name='data_destruction']:checked").val() == 2) {
                // Go to the new employee step
                fadeSteps('.order-step-two', '.order-step-new-employee');
            } else {
                // Go to step three directly
                fadeSteps('.order-step-two', '.order-step-three');
            }
        });

        $('.step-new-employee-next').on('click', function() {
            fadeSteps('.order-step-new-employee', '.order-step-three');
        });

        $('.step-three-back').on('click', function() {
            // Check if coming back from new employee step
            if ($("input[name='data_destruction']:checked").val() == 2) {
                fadeSteps('.order-step-three', '.order-step-new-employee', true);
            } else {
                fadeSteps('.order-step-three', '.order-step-two', true);
            }
        });

        $('.step-new-employee-back').on('click', function() {
            fadeSteps('.order-step-new-employee', '.order-step-two', true);
        });

        // Toggle insurance input visibility
        $('#ins_tick').on('change', function() {
            const isChecked = $(this).prop('checked');
            //$('#ins_amount').fadeToggle(isChecked).val(isChecked ? '' : '');
            if (isChecked == true) {
                $('#ins_amount').attr("style", "display:block;");
            } else {
                $('#ins_amount').attr("style", "display:none;");
            }

            if (isChecked) {
                $("#ins_amount").addClass('required');
            } else {
                $("#ins_amount").removeClass('required');
            }
        });

        // Handle order type changes
        $('select[name="order_type"]').on('change', function() {
            const orderType = $(this).val();
            const additionalServices = $('.additional-services');

            if (orderType == 1) {
                additionalServices.fadeIn();
                additionalServices.find("input[name='data_destruction']").prop('checked', false);
            } else {
                additionalServices.fadeOut();
                additionalServices.find("input[name='data_destruction']").prop('checked', false);
            }

            //Reset step 3 inputs (optional, based on your requirements)
            $('.order-step-three')
                .find('input, select')
                .val('');
        });

        // Allow only one "data destruction" option to be selected
        $("input[name='data_destruction']").on('change', function() {
            $("input[name='data_destruction']").not(this).prop('checked', false);
        });
    });

    $(".step-submit").click(function() {

        $("#discountMsg").fadeOut();
        // STEP THREE VALIDATION - START
        let isValid = true;
        if ($("#fcpn").val() == 1) {

            $("#payment_card_name,#payment_card_number,#payment_card_expiry,#payment_security_code")
                .removeClass('required3 mandatory');
        }

        if ($("#order_type").val() == "Return To Company") {
        var companySettingId = "{{ $companySettings->id }}";
        var envSettingId = "{{ env('COMPANY_SETTING_ID') }}";

        if (companySettingId == envSettingId) {
            $("#payment_card_name,#payment_card_number,#payment_card_expiry,#payment_security_code")
                .removeClass('required3 mandatory');
        }
    }

        $('.required3').each(function() {
            console.log($(this).val());
            if (!$(this).val()) {
                $(this).addClass('mandatory');
                isValid = false;
            } else {
                $(this).removeClass('mandatory');
            }
        });


        if (!isValid) {
            event.preventDefault();
            Swal.fire("Please fill all mandatory fields!");
            return false;
        } else {

            if ($("#order_type").val() == 1) {
                var orderType = "Return To Company";
            } else {
                var orderType = "Sell This Equipment";
            }

            let password = btoa(Math.random().toString(36).slice(2, 12)).slice(0, 10);
            var requestData = {
                "email": $("#account_email").val(),
                "phone": $("#single_user_phone").val(),
                "type_of_equipment": $("#type_of_equipment").val(),
                "order_type": $("#order_type").val(),
                "employee_first_name": $("#employee_first_name").val(),
                "employee_last_name": $("#employee_last_name").val(),
                "employee_phone": $("#employee_phone").val(),
                "employee_add_1": $("#employee_address1").val(),
                "employee_add_2": $("#employee_address2").val(),
                "employee_email": $("#employee_email").val(),
                "employee_city": $("#employee_city").val(),
                "employee_state": $("#employee_state").val(),
                "employee_zip": $("#employee_postal_code").val(),
                "company_name": $("#recipient_company_name").val(),
                "company_email": $("#recipient_email").val(),
                "company_phone": $("#recipient_phone").val(),
                "company_add_1": $("#recipient_address1").val(),
                "company_add_2": $("#recipient_address2").val(),
                "company_city": $("#recipient_city").val(),
                "company_state": $("#recipient_state").val(),
                "company_zip": $("#recipient_postal_code").val(),
                "comp_receip_name": $("#comp_receip_name").val(),
                "emp_custom_msg": $("#emp_custom_msg").val(),
                "password": password,
                "user_pkg": "basic",
                "billing_name": $("#payment_card_name").val(),
                "billing_cc_no": $("#payment_card_number").val(),
                "billing_cc_expiry": $("#payment_card_expiry").val(),
                "billing_cc_cvv": $("#payment_security_code").val(),
                "billing_amount": $("#totalAmtPayTxt").val()
            };

            if ($("#ins_tick").prop('checked') == true) {
                requestData.ins_tick = $("#ins_tick").val();
                requestData.ins_amount = $("#ins_amount").val();
            }
            if ($("#cpn").val() != "") {
                requestData.cpn = $("#cpn").val();
            }
            if ($("#fcpn").val() != "") {
                requestData.fcpn = $("#fcpn").val();
            }

            var checkedRadio = $("input[name='data_destruction']:checked");
            var dd = '';
            if (checkedRadio.length > 0) {
                var dd_value = checkedRadio.val();
                if (dd_value == 2) {
                    var newEmp = {
                        newemp_first_name: $("#new_employee_first_name").val(),
                        newemp_last_name: $("#new_employee_last_name").val(),
                        newemp_phone: $("#new_employee_phone").val(),
                        newemp_add_1: $("#new_employee_address1").val(),
                        newemp_add_2: $("#new_employee_address2").val(),
                        newemp_email: $("#new_employee_email").val(),
                        newemp_phone: $("#new_employee_phone").val(),
                        newemp_city: $("#new_employee_city").val(),
                        newemp_state: $("#new_employee_state").val(),
                        newemp_zip: $("#new_employee_postal_code").val(),

                    };
                    requestData.new_emp_data = JSON.stringify(newEmp);

                }
                requestData.return_add_srv = dd_value;
            }

            requestData._token = "{{ csrf_token() }}";

            if (IsEmail($("#recipient_email").val()) === false && isValid == true) {
                event.preventDefault();
                $("#recipient_email").addClass('mandatory');
                Swal.fire("Please fill proper email!");
                return false;
            }
                var companySettingId = "{{ $companySettings->id }}";
        var envSettingId = "{{ env('COMPANY_SETTING_ID') }}";
             if (companySettingId!=envSettingId && $("#order_type").val() == "Return To Company") {
            const datePattern = /^\d{4}-(0?[1-9]|1[0-2])$/;

            const value = $("#payment_card_expiry").val().trim();
            if (!datePattern.test(value) && $("#fcpn").val() != 1) {
                $("#payment_card_expiry").addClass('mandatory');
                Swal.fire("Please fill proper expiry date!");
                return false;
            }
        }




            $(".step-submit").attr("disabled", true);
            const newSVG =
                `<svg id="loading" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="16" height="16"><circle cx="25" cy="25" r="20" fill="none" stroke="#fff" stroke-width="4" stroke-dasharray="100" stroke-dashoffset="0"><animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/></circle></svg>`;
            // Replace the loading SVG with the new SVG
            $('.place-order-svg-btn').html(newSVG);
            showLoader();

            var jsonData = JSON.stringify(requestData);
            $.ajax({
                type: "POST",
                url: "{{ route('register.createorder') }}",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    "_token": "{{ csrf_token() }}",
                    'Client-Id': 'remote-retrieval-app-web',
                    'Authorization': 'Basic eyJpdiI6IjRnbFVYTytLUG9RUFRKWFJuUkFWZkE9PSIsInZhbHVlIjoiNmZJblg5eUJTR1l1bzNFaTNpNmpTM1cydmllRnZ6aE5yM0hmZG93NmluTT0iLCJtYWMiOiI0MzlhNjkxNzliYjIzZmViZDc1NjY0ZWNiZTE4ZTIxYWI3OGMyYTFjMmJmNmI1OTMxMmUzYTBlZGQwYjkzY2YwIiwidGFnIjoiIn0='
                },
                data: jsonData,
                cache: false,
                success: function(data) {
                    $(".step-submit").removeAttr("disabled");
                    if (data.status == "success") {
                         hideLoader();
                        setTimeout(function() {
                            redirectFunc(data);
                        }, 1000);
                        Swal.fire("You have successfully created order!");
                    }
                    if (data.status == "fail") {
                        hideLoader();
                        Swal.fire(data.message);
                    }
                }
            });



        }



        // STEP THREE VALIDATION - END
    });

    $(document).ready(function() {
        $("#order_type").change(function(e) {
            if ($(this).val() == "Return To Company") {
                $(".required3").attr("readonly", false);
                $("#recipient_address2").attr("readonly", false);
                $("#recipient_state").attr("disabled", false);
                @if (auth()->check())
                    getCompanyDetails();
                @endif

                @if($companySettings->id==env('COMPANY_SETTING_ID'))
                getCompanyDetailsForSpecificCompany({{ $companySettings->company_id }});
                 $(".required3").attr("readonly", true);
                $("#recipient_address2").attr("readonly", true);
                $("#recipient_state").attr("disabled", true);
                  $("#payment_card_name").attr("readonly", true);
        $("#payment_card_number").attr("readonly", true);
        $("#payment_card_expiry").attr("readonly", true);
        $("#payment_security_code").attr("readonly", true);
                @endif
            } else if ($(this).val() == "Sell This Equipment") {
                getRRDetails();
            }
        });
    });

    function redirectFunc(d) {
        location.href = "{{ route('thank.you') }}?uid=" + d.user.id + "&secret_code=" + d.user.secret_code;
    }

    @if (auth()->check())
        function getCompanyDetails() {
            $.ajax({
                type: "POST",
                url: "{{ route('getcompany_details') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "cid": " <?php echo Auth::user()->company_id; ?>"
                },
                cache: false,
                success: function(data) {
                    if (data.status == 'success') {
                        $("#recipient_company_name").val(data.details.company_name);
                        $("#recipient_email").val(data.details.company_email);
                        $("#recipient_phone").val(data.details.company_phone);
                        $("#recipient_address1").val(data.details.company_add_1);
                        $("#recipient_address2").val(data.details.company_add_2);
                        $("#recipient_city").val(data.details.company_city);
                        $("#recipient_state").val(data.details.company_state);
                        $("#recipient_postal_code").val(data.details.company_zip);
                        $("#comp_receip_name").val(data.details.receipient_name);
                    }
                }
            });
            $(".required3").attr("readonly", false);
            $("#recipient_address2").attr("readonly", false);
            $("#recipient_state").attr("disabled", false);
            $("#recipient_email").attr("readonly", true);

        }
    @endif

    function getCompanyDetailsForSpecificCompany(companyId) {
            $.ajax({
                type: "POST",
                url: "{{ route('getcompany_details') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "cid": companyId
                },
                cache: false,
                success: function(data) {
                    if (data.status == 'success') {
                        $("#recipient_company_name").val(data.details.company_name);
                        $("#recipient_email").val(data.details.company_email);
                        $("#recipient_phone").val(data.details.company_phone);
                        $("#recipient_address1").val(data.details.company_add_1);
                        $("#recipient_address2").val(data.details.company_add_2);
                        $("#recipient_city").val(data.details.company_city);
                        $("#recipient_state").val(data.details.company_state);
                        $("#recipient_postal_code").val(data.details.company_zip);
                        $("#comp_receip_name").val(data.details.receipient_name);
                    }
                }
            });
            $(".required3").attr("readonly", false);
            $("#recipient_address2").attr("readonly", false);
            $("#recipient_state").attr("disabled", false);
            $("#recipient_email").attr("readonly", true);

        }
    function getRRDetails() {

        $("#recipient_company_name").val("<?php echo env('REMOTE_COMPANY_NAME'); ?>");
        $("#recipient_email").val("<?php echo env('REMOTE_COMPANY_EMAIL'); ?>");
        $("#recipient_phone").val("<?php echo env('REMOTE_COMPANY_PHONE'); ?>");
        $("#recipient_address1").val("<?php echo env('REMOTE_COMPANY_ADD1'); ?>");
        $("#recipient_address2").val("<?php echo env('REMOTE_COMPANY_ADD2'); ?>");
        $("#recipient_city").val("<?php echo env('REMOTE_COMPANY_CITY'); ?>");
        $("#recipient_state").val("<?php echo env('REMOTE_COMPANY_STATE'); ?>");
        $("#recipient_postal_code").val("<?php echo env('REMOTE_COMPANY_ZIP'); ?>");
        $("#comp_receip_name").val("<?php echo env('REMOTE_REC_NAME'); ?>");
        $(".required3").attr("readonly", true);
        $("#recipient_address2").attr("readonly", true);
        $("#recipient_state").attr("disabled", true);

        $("#payment_card_name").attr("readonly", false);
        $("#payment_card_number").attr("readonly", false);
        $("#payment_card_expiry").attr("readonly", false);
        $("#payment_security_code").attr("readonly", false);

    }


    //Card Input Pattern
    String.prototype.toCardFormat = function() {
        return this.replace(/[^0-9]/g, "").substr(0, 16).split("").reduce(cardFormat, "");

        function cardFormat(str, l, i) {
            return str + ((!i || (i % 4)) ? "" : "-") + l;
        }
    };

    $("#payment_card_number").keyup(function() {
        $(this).val(jQuery(this).val().toCardFormat());
    });


    var payment_card_expiry = document.getElementById('payment_card_expiry');
    if (payment_card_expiry) {
        payment_card_expiry.addEventListener('input', function(e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        })
    }


    String.prototype.toDateFormat = function() {
        return this.replace(/[^0-9]/g, "") // Remove non-numeric characters
            .substr(0, 6) // Limit to first 6 characters (YYYYMM)
            .replace(/(\d{4})(\d{0,2})/, function(_, year, month) {
                return year + (month ? "-" + month : ""); // Add dash after year
            });
    };
    $("#payment_card_expiry").keyup(function() {
        $(this).val(jQuery(this).val().toDateFormat());
    });




    $(".apply_coupon_btn").click(function() {

        if ($("#coupon_apply").val() == "") {
            $("#discountMsg").fadeIn();
            $("#discountMsg").removeClass('alert-success').addClass('alert-danger');
            $("#discountMsg").html("Must fill coupon field!");
            return false;
        }


        var checkedRadio = $("input[name='data_destruction']:checked");
        var dd = '';
        var ins = '';
        if (checkedRadio.length > 0) {
            // If a radio button is checked, get its value
            var dd_value = checkedRadio.val();
            console.log("Checked value:", dd_value);
            {{-- if(dd_value == 1){ $("#ddAmt").val({{env('DD_COMPANY')}}); }
                    if(dd_value == 2){ $("#ddAmt").val({{env('DD_NEW_EMP')}}); } --}}

            dd = "&return_add_srv=" + dd_value;

        }

        if ($("#ins_tick").prop('checked') == true) {
            ins = "&insurance=" + $("#ins_amount").val();
        }


        $.ajax({
            type: "GET",
            url: "{{ route('apply.coupon.api') }}?company_name=" + $("#recipient_company_name").val() +
                "&coupon=" + $("#coupon_apply").val() +
                "&amount=" + $("#deviceAmt").val() + "&email=" + $("#account_email").val() + dd + ins,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                "_token": "{{ csrf_token() }}",
                'Client-Id': 'remote-retrieval-app-web',
                'Authorization': 'Basic eyJpdiI6IjRnbFVYTytLUG9RUFRKWFJuUkFWZkE9PSIsInZhbHVlIjoiNmZJblg5eUJTR1l1bzNFaTNpNmpTM1cydmllRnZ6aE5yM0hmZG93NmluTT0iLCJtYWMiOiI0MzlhNjkxNzliYjIzZmViZDc1NjY0ZWNiZTE4ZTIxYWI3OGMyYTFjMmJmNmI1OTMxMmUzYTBlZGQwYjkzY2YwIiwidGFnIjoiIn0='
            },

            cache: false,
            success: function(data) {
                if (data.status == 'success') {
                    $("#totalAmtPay").html("$" + data.totalAmt);
                    $("#discountMsg").fadeIn();
                    $("#discountMsg").html(data.message);
                    $("#cpn").val(data.coupon.coupon);

                    if (data.coupon.freeall == 1 && data.insurance == 0 && (typeof data.dd_amt ===
                            "undefined" || data.dd_amt == 0)) {
                        $("#payment_card_name").val("");
                        $("#payment_card_name").prop("disabled", true);
                        $("#payment_card_number").val("");
                        $("#payment_card_number").prop("disabled", true);
                        $("#payment_security_code").val("");
                        $("#payment_security_code").prop("disabled", true);
                        $("#payment_card_expiry").val("");
                        $("#payment_card_expiry").prop("disabled", true);
                        // $("#cc_year").val("");
                        // $("#cc_year").prop("disabled",true);
                        $(".step-submit").text("Order Now");
                        $("#fcpn").val(1);
                    } else {
                        //$("#cardholder_name").val("");
                        $("#payment_card_name").prop("disabled", false);
                        //$("#cc_no").val("");
                        $("#payment_card_number").prop("disabled", false);
                        //$("#cvv").val("");
                        $("#payment_security_code").prop("disabled", false);
                        //$("#cc_month").val("");
                        $("#payment_card_expiry").prop("disabled", false);
                        //$("#cc_year").val("");
                        //$("#cc_year").prop("disabled",false);
                        $(".step-submit").text("Pay Now");
                        $("#fcpn").val(0);
                    }
                }
                if (data.status == 'fail') {
                    $("#discountMsg").fadeIn();
                    $("#discountMsg").removeClass('alert-success').addClass('alert-danger');
                    $("#discountMsg").html(data.message);
                }


                //    $("#deviceAmt").val(data.amount);
            }
        });
    });

    function IsEmail(email) {
        const regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (!regex.test(email)) {
            return false;
        } else {
            return true;
        }
    }


    var user_setting_phone = $(".form-control:input[type=tel]");
    if (user_setting_phone.length > 0) {
        user_setting_phone.each(function(item, v) {
            v.addEventListener('input', function(e) {
                var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : '(' + x[1] + ')' + x[2] + (x[3] ? '-' + x[3] : '');
            })
        });
    }


    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const monitorExists = urlParams.has('monitor');

        if (monitorExists) {
            $('#type_of_equipment').val('Monitor').trigger('change');

        } else {
            $('#type_of_equipment').val('Laptop').trigger('change');
        }

        $('#type_of_equipment').change(function() {
            $(".updateDeviceTxt").text($(this).val());
        });
    });
</script>


<style>
    .insTickDiv {
        height: 85px;
    }
</style>
