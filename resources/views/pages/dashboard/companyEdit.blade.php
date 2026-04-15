@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>
        Edit Company
        {{-- #{{ $data->id }} --}}
        </h1>
    </div><!-- End Page Title -->
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
            @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
            @endif
            <div class="col-lg-12 mb-4" id="emp_update_msg" style="display:none;">
                <div class="alert alert-success">
                    Employee data has updated!
                </div>
            </div>
            <div class="alert alert-danger" style="display:none;" id="errorDiv"></div>
            <form class="user" method="post" action="{{ route('company.edit.sub',['id'=>request()->id]) }}" name="emp_details" id="emp_details">
                @csrf

                {{-- <h5>Type of Equipment</h5>
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
                </div> --}}

                {{-- <h5>Company Details</h5> --}}
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_name" placeholder="Company"
                                value="{{ $c->company_name }}" name="company_name" maxlength="75">
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="domain" placeholder="Domain"
                                value="{{ $c->domain }}" name="domain" maxlength="75">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_domain" placeholder="Domain"
                                value="{{ $c->company_domain }}" name="company_domain" maxlength="75" readonly>
                        </div>
                    </div>
                      <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="receipient_name" placeholder="Receipient"
                                value="{{ $c->receipient_name }}" name="receipient_name" maxlength="75">
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_email" placeholder="Company Email"
                                value="{{ $c->company_email }}" name="company_email" maxlength="75">
                        </div>
                    </div>
                      <div class="col-md-6 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_add_1" placeholder="Address 1"
                                value="{{ $c->company_add_1 }}" name="company_add_1" maxlength="75">
                        </div>
                    </div>
                </div>




                <div class="row">
                    <div class="mb-3 col-md-4 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_add_2" placeholder="Address 2"
                                value="{{ $c->company_add_2 }}" name="company_add_2" maxlength="75">
                        </div>
                    </div>
                    <div class="mb-3 col-md-4 col-12">
                        <input type="text" class="form-control" id="company_city" placeholder="City"
                            value="{{ $c->company_city }}" name="company_city" maxlength="45">
                    </div>

                    <div class="mb-3 col-md-4 col-12">
                        <select id="company_state" class="form-select mb-3" name="company_state">
                            @include('includes.states_options')
                        </select>
                    </div>

                </div>


                  <div class="row">
                    <div class="mb-3 col-md-4 col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="company_zip" placeholder="Zip"
                                value="{{ $c->company_zip }}" name="company_zip" maxlength="75">
                        </div>
                    </div>
                     <div class="mb-3 col-md-4 col-12">
                       <div class="input-group mb-3">
                                            <span class="input-group-text">+1</span>
                                            <input value="{{ $c->company_phone }}" type="tel" class="form-control required" minlength="13" id="company_phone" aria-describedby="single_user_phone" name="company_phone" placeholder="Phone Number">
                       </div>
                    </div>

                   <div class="mb-3 col-md-4 col-12">
                        <select id="status" name="status" class="form-select mb-3">
                        <option value="active">Active</option>
                        <option value="inactive">In-Active</option>
                        </select>
                    </div>

                </div>




                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-dark theme-bgcolor-btn-one mt-3 employeeSub">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>


@stop

@push('other-scripts')
    <script>
$("#company_state").val("{{ $c->company_state }}");
$("#status").val("{{ $u->status }}");


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
