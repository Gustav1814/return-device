@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Coupons <a href="{{route('admin.coupon.add')}}" class="badge bg-dark theme-bgcolor-btn-one"><i class="bi bi-plus-circle"></i></a></h1>
    </div><!-- End Page Title -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <div class="table-responsive">
                <div id="dataTable_wrapper">
                    <div class="row">
                        <div class="col-sm-12">

                            <table class="table fold-table" id=" " width="100%"
                                                    cellspacing="0" role="grid"  style="width: 100%;">
                                                    <thead>
                                        <tr>
                                            <th title="ID">ID</th>
                                            <th title="Coupon">Coupon</th>
                                            <th title="Coupon Type">Coupon Type</th>
                                            <th title="Coupon Type">Discount Type</th>
                                            <th title="Amount/Percentage">Amount/Percentage</th>
                                            <th title="Status">Status</th>
                                            <th title="Action">Action</th>
                                            {{-- <th title="Address Line 2">Action</th> --}}
                                        </tr>
                                    </thead>

                                    @foreach ($data as $e)
                                        <tr class="view">
                                            <td>{{ $e->id }}</td>
                                            <td>{{ $e->coupon }}</td>
                                            <td>@php echo ucfirst($e->type)@endphp</td>
                                            <td>@if($e->coupon_apply_for == "total")On total amount @else On per order amount @endif </td>
                                            <td>@if($e->type == "amount") $  @endif {{ $e->amt_or_perc }}@if($e->type == "percentage") %  @endif @if($e->freeall == 1) Free  @endif</td>
                                            <td>@if ($e->status == 1) Active @else In-Active @endif</td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{route('admin.coupon.edit',$e->id)}}" class="badge bg-dark theme-bgcolor-btn-one p-2 me-1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"></path><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"></path></svg></a>
                                                </div>
                                            {{-- <a title="Delete Coupon" href="{{route('admin.coupon.delete',$e->id)}}" class="btn btn-sm btn-danger me-1 coupon-delete" style="background-color:#e74a3b !important; " data-confirm="Are you sure you want to delete?"><i class="fa-solid fa-trash"></i></i></a> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ $data->links() }}
</main>


@stop


@push('other-scripts')
    <script src="{{ asset('theme/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('theme/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('theme/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('theme/js/demo/datatables-demo.js') }}"></script>

    <script>
        $(".orderConfirm").click(function() {
            location.href = "{{ route('order.pay') }}";
        });
        $(document).ready(function() {
            $('.coupon-delete').click(function() {
                if (!confirm('Are you sure you want to delete this coupon?')) {
                    return false
                }else{
                    load($(this).attr('href'));
                }
            });
        });
    </script>
@endpush
