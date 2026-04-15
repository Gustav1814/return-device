@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Create a Single Order</h1>
        Please fill out the form below to create a single order for the return of a laptop or monitor. You'll need to provide details of your employee address, company return address and device type.
    </div><!-- End Page Title -->
    <div class="order-process position-relative">
            <div class="loader hidden">
                <div class="spinner"></div>
            </div>
            <form action="" method="post">
                <div class="order-step-one">
                    <div class="row">
                        <div class="col-md-1">
                            <?php //include './order-now-progress-bar.php'; ?>
                            @include('includes.order-now-progress-bar')
                        </div>
                        <div class="col-md-4">
                            @include('includes.order-now-details')
                            <?php //include './order-now-details.php'; ?>
                        </div>
                        <div class="col-md-7">
                            <div class="start-content">
                                <h2>Laptop retrieval service</h2>
                                <p>@php echo $_SERVER['SERVER_NAME'] @endphp makes it easy to retrieve devices like laptops and accessories from employees.</p>
                                <h4>Account Details</h4>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input @if(Auth::check()) readonly @endif value="{{ Auth::check() ? Auth::user()->email : '' }}" type="email" class="form-control account_email required" id="account_email" aria-describedby="account_email" name="account_email" placeholder="Enter your email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">+1</span>
                                            <input type="tel" class="form-control required" minlength="13" id="single_user_phone" aria-describedby="single_user_phone" name="single_user_phone" placeholder="Phone Number" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <select class="form-select required" name="type_of_equipment" id="type_of_equipment" required>
                                                <option selected value="">Select Type of Equipment</option>
                                                <option value="Laptop">Laptop</option>
                                                <option value="Monitor">Monitor</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <select class="form-select required" name="order_type" id="order_type" required>
                                                <option selected value="">Select Return Services</option>
                                                <option value="Return To Company">Return To Company</option>
                                                <option value="Sell This Equipment">Recycle with Data Destruction</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="insurance">
                                    <h4>Insurance</h4>
                                    <div class="row align-items-center">
                                        <div class="col-md-6 col-12">
                                            <div class="input-group mb-3">
                                                <label><input type="checkbox" name="ins_tick" id="ins_tick" value="1" class="me-1 mb-2">Do you want to insure this item?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="input-group mb-3">
                                                <input type="number" class="form-control ins_amount" data-max="5" id="ins_amount" placeholder="Please add Insurance amount" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <a class="btn btn-dark theme-bgcolor-btn-one step-one-next">Send A  <span class="updateDeviceTxt"> @if(request()->has('monitor')){{ 'Monitor ' }}@else{{ 'Laptop ' }}@endif </span> Box <span class="ms-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-step-two" style="display: none;">
                    <div class="row">
                        <div class="col-md-1">
                            @include('includes.order-now-progress-bar')
                        </div>
                        <div class="col-md-11">
                            <div class="start-content">
                                <h2>Employee Address</h2>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required2" id="employee_first_name" aria-describedby="employee_first_name" name="employee_first_name" placeholder="First name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required2" id="employee_last_name" aria-describedby="employee_last_name" name="employee_last_name" placeholder="Last name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="email" class="form-control required2" id="employee_email" aria-describedby="employee_email" name="employee_email" placeholder="Employee Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">+1</span>
                                            <input type="tel" minlength="13" class="form-control required2" id="employee_phone" aria-describedby="employee_phone" name="employee_phone" placeholder="Employee Phone" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required2" id="employee_address1" aria-describedby="employee_address1" name="employee_address1" placeholder="Employee Address 1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control " id="employee_address2" aria-describedby="employee_address2" name="employee_address2" placeholder="Employee Address 2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="text" class="form-control required2" id="employee_city" aria-describedby="employee_city" name="employee_city" placeholder="City" required="">
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <select name="employee_state required2" id="employee_state" class="form-select" required="">
                                            @include('includes.states_options')
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="number" data-max="5" class="form-control zipcodeUS required2" id="employee_postal_code" aria-describedby="employee_postal_code" name="employee_postal_code" placeholder="Zip/Postal Code" required="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-12">
                                        <input type="text" class="form-control" id="emp_custom_msg" aria-describedby="emp_custom_msg" name="emp_custom_msg" placeholder="Custom Message to Employee (Optional)">
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6 text-start">
                                        <a class="btn btn-dark theme-bgcolor-btn-one step-two-back"><span class="me-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00887 12.151L0.503677 6.59567L0.508481 5.53502L6.06378 0.0297646L7.11962 1.09526L2.85045 5.32595L13.7896 5.37551L13.7828 6.87549L2.84366 6.82594L7.07432 11.0951L6.00887 12.151Z" fill="white"/></svg></span>Previous</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a class="btn btn-dark theme-bgcolor-btn-one step-two-next">Next <span class="ms-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-step-new-employee" style="display: none;">
                    <div class="row">
                        <div class="col-md-1">
                            @include('includes.order-now-progress-bar')
                        </div>
                        <div class="col-md-11">
                            <div class="start-content">
                                <h2>New Employee Address</h2>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required4" id="new_employee_first_name" aria-describedby="new_employee_first_name" name="new_employee_first_name" placeholder="New Employee First name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required4" id="new_employee_last_name" aria-describedby="new_employee_last_name" name="new_employee_last_name" placeholder="New Employee Last name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="email" class="form-control required4" id="new_employee_email" aria-describedby="new_employee_email" name="new_employee_email" placeholder="New Employee Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">+1</span>
                                            <input type="tel" minlength="13" class="form-control required4" id="new_employee_phone" aria-describedby="new_employee_phone" name="new_employee_phone" placeholder="New Employee Phone" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required4" id="new_employee_address1" aria-describedby="new_employee_address1" name="new_employee_address1" placeholder="New Employee Address 1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="new_employee_address2" aria-describedby="new_employee_address2" name="new_employee_address2" placeholder="New Employee Address 2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="text" class="form-control required4" id="new_employee_city" aria-describedby="new_employee_city" name="new_employee_city" placeholder="City" required>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <select name="new_employee_state" id="new_employee_state" class="form-select required4" required>
                                             @include('includes.states_options')
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="number" data-max="5" class="form-control zipcodeUS required4" id="new_employee_postal_code" aria-describedby="new_employee_postal_code" name="new_employee_postal_code" placeholder="Zip/Postal Code" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-12">
                                        <input type="text" class="form-control" id="new_emp_custom_msg" aria-describedby="new_emp_custom_msg" name="new_emp_custom_msg" placeholder="Custom Message to New Employee (Optional)">
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6 text-start">
                                        <a class="btn btn-dark theme-bgcolor-btn-two text-white step-new-employee-back"><span class="me-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00887 12.151L0.503677 6.59567L0.508481 5.53502L6.06378 0.0297646L7.11962 1.09526L2.85045 5.32595L13.7896 5.37551L13.7828 6.87549L2.84366 6.82594L7.07432 11.0951L6.00887 12.151Z" fill="white"/></svg></span>Previous</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a class="btn btn-dark theme-bgblack-color text-white step-new-employee-next">Next <span class="ms-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-step-three" style="display: none;">
                    <div class="row">
                        <div class="col-md-1">
                            @include('includes.order-now-progress-bar')
                        </div>
                        <div class="col-md-7">
                            <div class="start-content">
                                <h2>Return To Company</h2>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control company_name required3" id="recipient_company_name" aria-describedby="recipient_company_name" name="recipient_company_name" placeholder="Company Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required3" id="comp_receip_name" aria-describedby="comp_receip_name" name="comp_receip_name_return" placeholder="Company Recipient Name" required maxlength="25">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="email" class="form-control required3 " id="recipient_email" aria-describedby="recipient_email" name="recipient_email" placeholder="Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">+1</span>
                                            <input type="tel" minlength="13" class="form-control required3" id="recipient_phone" aria-describedby="recipient_phone" name="recipient_phone" placeholder="Phone" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required3" id="recipient_address1" aria-describedby="recipient_address1" name="recipient_address1" placeholder="Address 1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="recipient_address2" aria-describedby="recipient_address2" name="recipient_address2" placeholder="Address 2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="text" class="form-control required3" id="recipient_city" aria-describedby="recipient_city" name="recipient_city" placeholder="City" required>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <select name="recipient_state" id="recipient_state" class="form-select" required="">
                                             @include('includes.states_options')
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-4 col-12">
                                        <input type="number" data-max="5" class="form-control zipcodeUS required3" id="recipient_postal_code" aria-describedby="recipient_postal_code" name="recipient_postal_code" placeholder="Zip/Postal Code" required="">
                                    </div>
                                </div>
                                <div class="row mt-4 desktop-place-order-btn">
                                    <div class="col-6 text-start">
                                        <a class="btn btn-dark theme-bgcolor-btn-one step-three-back"><span class="me-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00887 12.151L0.503677 6.59567L0.508481 5.53502L6.06378 0.0297646L7.11962 1.09526L2.85045 5.32595L13.7896 5.37551L13.7828 6.87549L2.84366 6.82594L7.07432 11.0951L6.00887 12.151Z" fill="white"/></svg></span>Previous</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-dark theme-bgcolor-btn-one step-submit">Place Order <span class="ms-2"><svg width="14" height="16" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="end-content">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required3" placeholder="Card Name" id="payment_card_name" aria-describedby="payment_card_name" name="payment_card_name" required>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control required3" placeholder="Card Number" id="payment_card_number" aria-describedby="payment_card_number" name="payment_card_number" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <input type="text" class="form-control required3" placeholder="YYYY-MM" id="payment_card_expiry" aria-describedby="payment_card_expiry" name="payment_card_expiry" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control required3" placeholder="CVV" id="payment_security_code" aria-describedby="payment_security_code" name="payment_security_code" required>
                                    </div>
                                </div>
                                <div class="mb-3 apply_coupon_container">
                                    <div class="row">
                                        <div class="col-9">
                                            <input type="text" class="form-control coupon_apply" id="coupon_apply" aria-describedby="coupon_apply" name="coupon_apply" placeholder="Apply Coupon">
                                        </div>
                                        <div class="col-3">
                                            <a class="btn btn-dark apply_coupon_btn">Apply</a>
                                        </div>
                                    </div>

                                <div class="alert alert-success" role="alert" style="margin-top:10px;display:none;" id="discountMsg">
                                </div>

                                </div>
                                <div class="total-amount mt-5 pt-3">
                                <input type="hidden" name="deviceAmt" id="deviceAmt" value="0"/>
                                <input type="hidden" name="insAmt" id="insAmt" value="0"/>
                                <input type="hidden" name="insAmtPerc" id="insAmtPerc" value="0"/>
                                <input type="hidden" name="ddAmt" id="ddAmt" value="0"/>
                                <input type="hidden" name="totalAmtPayTxt" id="totalAmtPayTxt" value="0"/>
                                <input type="hidden" id="cpn" name="cpn"/>
                                <input type="hidden" id="fcpn" name="fcpn" value="0"/>

                                    <h6>Amount</h6>
                                    <hr>
                                    <div class="row">
                                        <div class="col-4">
                                            <h6>Total</h6>
                                        </div>
                                        <div class="col-8 text-end">
                                            <h6 class="color" id="totalAmtPay">$0</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4 mobile-place-order-btn">
                                    <div class="col-6 text-start">
                                        <a class="btn btn-dark theme-bgcolor-btn-one text-white step-three-back"><span class="me-2"><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00887 12.151L0.503677 6.59567L0.508481 5.53502L6.06378 0.0297646L7.11962 1.09526L2.85045 5.32595L13.7896 5.37551L13.7828 6.87549L2.84366 6.82594L7.07432 11.0951L6.00887 12.151Z" fill="white"/></svg></span>Previous</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-dark theme-bgcolor-btn-one step-submit">Place Order <span class="ms-2"><svg width="14" height="16" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0.034668L13.5303 5.565V6.62566L8 12.156L6.93934 11.0953L11.1893 6.84533H0.25V5.34533H11.1893L6.93934 1.09533L8 0.034668Z" fill="white"></path></svg></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
</main>
{{-- <section class="rr-order-now pb-5">
    <div class="container-xl">


    </div>
</section> --}}

@stop


@push('other-scripts')
@include('includes.orderCreateScript')
@endpush
