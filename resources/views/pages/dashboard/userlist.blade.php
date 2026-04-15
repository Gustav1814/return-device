@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Users</h1>
    </div><!-- End Page Title -->
    <div class="all-orders">
        <div class="card">
            <div class="card-body py-3">
                <div class="filter">
                    <form action="{{ route('users.search') }}" method="GET" class="search-filter admin-search-filter">
                        @csrf
                        <div class="row py-3 align-items-center">
                            <div class="col-md-3" id="custom-search">
                                <input type="text" name="search" placeholder="Search..." class="form-control" aria-describedby="search" value="{{ $searchTerm ?? '' }}">
                            </div>
                            <div class="col-md-3" id="custom-submit">
                                <input type="hidden" name="orders_status" value="">
                                <button type="submit" class="btn btn-dark theme-bgcolor-btn-one">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">User ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Company</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Created At</th>
                                {{-- <th scope="col">Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @if ($data->isEmpty())
                                <p>No users found.</p>
                            @else

                                @foreach ($data as $usr)
                                    <tr>
                                        <td scope="row">{{ $usr->id }}</td>
                                        <td>{{ $usr->name }}</td>
                                        <td>{{ $usr->email }} </td>
                                        <td>{{ $usr->company_name }} </td>
                                        <td>{{ $usr->phone }} </td>
                                        <td>
                                         {{ \Carbon\Carbon::parse($usr->created_at)->format('M d, Y') }}
                                         </td>
                                        {{-- <td>
                                            <div class="d-flex">
                                                <a href="#" class="badge theme-bgcolor-btn-one p-2 me-1"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                        <path
                                                            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                        <path fill-rule="evenodd"
                                                            d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                    </svg></a>
                                                <a href="#" class="badge theme-bgcolor-btn-one p-2 me-1"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                                        <path
                                                            d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                                    </svg></a>
                                                <a href="#" class="badge theme-bgcolor-btn-one p-2"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                                        <path
                                                            d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                                    </svg></a>
                                            </div>
                                        </td> --}}
                                    </tr>
                                @endforeach

                            @endif

                        </tbody>
                    </table>
                </div>


                <div class="d-flex justify-content-end">
                    {{ $data->links() }}
                </div>

            </div>
        </div>
    </div>
</main>

@stop


@push('other-scripts')
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
@endpush
