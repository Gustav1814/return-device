@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Coupon</h1>
    </div><!-- End Page Title -->
    <div class="card">
        <div class="card-body py-3">
            @if (Session::has('successMsg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{-- <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> --}}
                {{ Session::get('successMsg') }}
            </div>
            @endif
            @if (Session::has('fail'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{-- <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> --}}
                {{ Session::get('fail') }}
            </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="post" class="user" name="frm_coupon" id="frm_coupon" action="{{route('admin.coupon.editsub',request("id")    )}}">
                @csrf

                <div class="form-group row">
                    <div class="col-sm-12">
                        <label>Coupon name</label>
                        <input type="text" class="form-control" id="coupon_name"
                            placeholder="Coupon Name" value="{{$coupon->coupon}}" name="coupon_name" maxlength="10">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12">
                        <input type="checkbox" name="freeCpn" id="freeCpn" @checked($coupon->freeall == 1)>
                        <label>Make 100% free this coupon?</label>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12">
                        <label>Coupon Type</label>
                        <select id="coupon_type" name="coupon_type" class="form-control" @readonly($coupon->freeall == 1)>
                            <option value="">Select Below </option>
                            <option value="amount">Amount </option>
                            <option value="percentage">Percentage </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12">
                        <label>Coupon Apply For</label>
                        <select id="coupon_apply_for" name="coupon_apply_for" class="form-control" @readonly($coupon->freeall == 1)>
                            <option value="">Select Below </option>
                            <option value="total">Order Total </option>
                            <option value="per-order">Per Order </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12 mb-3 mb-sm-0">
                        <label>Value of Amount/Percentage</label>
                        <input type="number" class="form-control" id="amt_perc"
                            placeholder="Amount/Percentage" value="{{$coupon->amt_or_perc}}" name="amt_perc" maxlength="12" @readonly($coupon->freeall == 1)>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12 mb-3 mb-sm-0">
                        <label>Active/Inactive</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Select Below </option>
                            <option value="1">Active </option>
                            <option value="0">InActive </option>
                        </select>
                    </div>
                </div>
                <hr/>
                <button type="button" class="btn btn-dark theme-bgcolor-btn-one couponSubmit" style="">
                    Save
                </button>
            </form>
        </div>
    </div>
</main>

@stop


@push('other-scripts')
<script>
$(".couponSubmit").click(function(e){
    // var err = 0;
    // if($("#coupon_name").val() == ""){ alert("Must fill Coupon Name"); err = 1;}
    // if($("#coupon_type").val() == ""){ alert("Must select  Coupon Type"); err = 1;}
    // if($("#amt_perc").val() == ""){ alert("Must fill  Coupon Amount/percentage"); err = 1;}
    // if(err == 0)
    // {


    // }
    $("#frm_coupon").submit();


    e.preventDefault;

})

$(document).ready(function() {
    $("#coupon_type").val("{{$coupon->type}}")
    $("#status").val("{{$coupon->status}}")
    $("#coupon_apply_for").val("{{$coupon->coupon_apply_for}}")

});

   $('#coupon_name').on('keypress', function(e) {
            if (e.which == 32){
                console.log('Space Detected');
                return false;
            }
        });

    $("#freeCpn").click(function(e){
    if ($("#freeCpn").prop("checked") === true)
    {
        $("#coupon_type").val("percentage");
        $("#coupon_type").prop("readonly",true);
        $("#coupon_apply_for").val("total");
        $("#coupon_apply_for").prop("readonly",true);
        $("#amt_perc").val(100);
        $("#amt_perc").prop("readonly",true);


    }else{
        $("#coupon_type").val("");
        $("#coupon_type").prop("readonly",false);
        $("#coupon_apply_for").val("");
        $("#coupon_apply_for").prop("readonly",false);
        $("#amt_perc").val("");
        $("#amt_perc").prop("readonly",false);
    }

    });
</script>
@endpush
