@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Profile</h1>
        <p>Save default Settings for all of the device return you set up. You can always enter different information when placing an order.</p>
    </div><!-- End Page Title -->
    <div class="all-orders">
        <div class="card">
            <div class="card-body py-3">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('successMsg'))
                    <div class="alert alert-success">
                        {{ session('successMsg') }}
                    </div>
                @endif
        
                <h5>Profile Settings</h5>
                <form action="{{ route('submit.user.profile') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="text" name="usr_name" class="form-control" id="usr_name" placeholder="Name"
                                    value="{{ $data->name }}" maxlength="25">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" id="usr_email" placeholder="Email Address"
                                    value="{{ $data->email }}" name="usr_email" readonly disabled maxlength="30">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">+1</span>
                                <input maxlength="20" type="tel" name="usr_phone" class="form-control" id="usr_phone"
                                    placeholder="Phone*" value="{{ $data->phone }}">
                            </div>
                        </div>
                        {{-- <div class="col-md-6 col-12">
                            <a class="badge bg-light text-dark d-block p-4" href="#">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Change Password
                            </a>
                        </div> --}}
                    </div>
                    <h5>Company Information</h5>
                    <p class="mb-0"><strong>Device Return Preferences - (This will become your default address for all
                            returns)</strong></p>
                    <p>Please provide your company destination information.</p>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="text" name="comp_name" class="form-control" id="comp_name" placeholder="Name"
                                    value="{{ $data->company_name }}" maxlength="25">
                            </div>
                            <p>This will be shown to your employee in email communications. You can use the name of your
                                organization, your name, or anything else that tells your employee who is requesting the device.
                            </p>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="text" name="comp_rec_name" class="form-control" id="comp_rec_name"
                                    placeholder="Receipient Name" value="{{ $data->receipient_name }}" maxlength="25">
                            </div>
                            <p>(Please add name where will receive the return unit)</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" id="comp_email" placeholder="Email Address"
                                    value="{{ $data->company_email }}" name="comp_email" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">+1</span>
                                <input maxlength="20" type="tel" name="comp_phone" class="form-control" id="comp_phone"
                                    placeholder="Phone*" value="{{ $data->company_phone }}">
                            </div>
                        </div>
                    </div>
        
        
        
        
        
        
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="comp_add_1" id="comp_add_1"
                                    placeholder="Address Line 1" value="{{ $data->company_add_1 }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">+1</span>
                                <input type="text" class="form-control" name="comp_add_2" id="comp_add_2"
                                    placeholder="Address Line 2" value="{{ $data->company_add_2 }}" maxlength="40">
                            </div>
                        </div>
                    </div>
        
        
        
        
                    <div class="row">
                        <div class="col-md-6 col-12">
        
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="input-group mb-4">
                                        <input type="text" class="form-control" id="comp_city" name="comp_city"
                                            placeholder="City" value="{{ $data->company_city }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="input-group mb-4">
                                        <select id="comp_state" class="form-select mb-3" name="comp_state" required="">
                                            @include('includes.states_options')
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="input-group mb-4">
                                        <input type="text" class="form-control" id="comp_zip" name="comp_zip"
                                            placeholder="Zip" value="{{ $data->company_zip }}" maxlength="12">
                                    </div>
                                </div>
                            </div>
                            {{-- <h5>SMS Notification</h5>
                        <p><strong>I want to receive SMS notification for each device return</strong></p>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">+1</span>
                                    @if ($setting)
                                        @php $s =   $setting->sms_val != '' ? $setting->sms_val :   $data->phone   @endphp
                                    @else
                                        @php $s =   $data->phone  @endphp
                                    @endif
        
                                    <input type="tel" name="set_usr_phone" class="form-control" id="user_setting_phone"
                                        placeholder="Phone*" value="{{ $s }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sms_flag" id="send_sms_to_user_no"
                                        value="no" checked="">
                                    <label class="form-check-label" for="send_sms_to_user_no">No</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sms_flag" id="send_sms_to_user_yes"
                                        value="yes">
                                    <label class="form-check-label" for="send_sms_to_user_yes">Yes</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p>Please verify your phone number - We'll send you an SMS.</p>
                            </div>
                        </div> --}}
                            {{-- <h5>Email Notification</h5>
                        <p><strong>I want to receive Email Notification for each device return</strong></p>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-12">
                                <div class="input-group">
                                    @if ($setting)
                                        @php $e =   $setting->email_val != '' ? $setting->email_val :   $data->email   @endphp
                                    @else
                                        @php $e =   $data->email  @endphp
                                    @endif
        
        
                                    <input type="text" name="user_email" class="form-control" id=""
                                        placeholder="Email Address*" required="" value="{{ $e }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sms_flag" id="send_sms_to_user_no"
                                        value="no" checked="">
                                    <label class="form-check-label" for="send_sms_to_user_no">No</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sms_flag" id="send_sms_to_user_yes"
                                        value="yes">
                                    <label class="form-check-label" for="send_sms_to_user_yes">Yes</label>
                                </div>
                            </div>
                        </div> --}}
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit"
                                        class="btn btn-dark theme-bgcolor-btn-one mt-3">Update</button>
                                </div>
                            </div>
                </form>
            </div>
        </div>
    </div>
</main>

@stop

@push('other-scripts')
    <script>
        $("#comp_state").val('{{ $data->company_state }}');

        var user_setting_phone = $(".form-control:input[type=tel]");
        if (user_setting_phone.length > 0) {
            user_setting_phone.each(function(item, v) {
                v.addEventListener('input', function(e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                    e.target.value = !x[2] ? x[1] : '(' + x[1] + ')' + x[2] + (x[3] ? '-' + x[3] : '');
                })
            });
        }
    </script>
@endpush
