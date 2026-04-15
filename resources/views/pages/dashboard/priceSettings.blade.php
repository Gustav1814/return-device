@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pricing</h1>
        <p>You can set the price you want to charge for a single order.
        @if ($_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN'))
        The price cannot be lower than ${{ $defSettings[0]->order_amount }} per laptop retrieval and ${{ $defSettings[1]->order_amount }} per monitor retrieval.
        @endif
        </p>

    </div><!-- End Page Title -->
    <div class="card">
        <div class="card-body py-3">
            @if (session('success'))
                <div class="alert alert-success">
                    {!! session('success') !!}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {!! session('error') !!}
                </div>
            @endif
            <h5>Set New Price</h5>
            <form action="{{ route('price.settings.sub') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    @php $j = 0; @endphp

                    @foreach ($defSettings as $price)
                        {{-- @php $i = '' @endphp --}}
                        <div class="col-md-3 col-12">
                            <div class="my-3">
                                <div class="form-check ps-0">
                                @php if($price->equipment_type == "Laptop"){ $device = "Per Laptop Retrieval"; } @endphp
                                @php if($price->equipment_type == "Monitor"){ $device = "Per Monitor Retrieval"; } @endphp
                                    <label class="form-check-label" for="theme_color">{{ $device }}</label>
                                    {{-- <select id="device_{{ $price->equipment_type }}"
                                        name="device_{{ $price->equipment_type }}" class="form-select">


                                        @php
                                            $startAmt = 1;
                                            $endAmt = 200;
                                            $ord = 1;
                                            if (isset($compSettings)) {
                                                $ord = $compSettings[$j]->order_amount;
                                            }
                                        @endphp

                                        @for ($i = $startAmt; $i <= $endAmt; $i++)
                                            <option @if ($i == $ord) selected @endif
                                                value="{{ $i }}">{{ $i }}</option>
                                        @endfor

                                    </select> --}}
                                    <input type="text" value="{{ $compSettings[$j]->order_amount }}" id="device_{{ $price->equipment_type }}"
                                        name="device_{{ $price->equipment_type }}" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        @php
                            // exit();
                        @endphp
                        @php $j = $j + 1;  @endphp
                    @endforeach

                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark theme-bgcolor-btn-one mt-3" type="submit">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

@stop

@push('other-scripts')
    <style>
        .upload-logo-btn {
            font-size: 0.95rem;
            font-weight: 400;
        }

        .logo-display img {
            width: 100%;
            height: 50px;
            object-fit: contain;
            object-position: left center;
        }
    </style>



    <script>
        const light_icon =
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-brightness-high" viewBox="0 0 16 16"><path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>';
        const dark_icon =
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286"/><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/></svg>';
        const themeToggleButton = document.getElementById("theme-toggle");
        const currentTheme = localStorage.getItem("theme") || "light";
        // Apply saved theme
        // document.documentElement.setAttribute("data-theme", currentTheme);
        // themeToggleButton.innerHTML = currentTheme === "dark" ? light_icon : dark_icon;
        // themeToggleButton.addEventListener("click", () => {
        //     const newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        //     document.documentElement.setAttribute("data-theme", newTheme);
        //     localStorage.setItem("theme", newTheme);
        //     themeToggleButton.innerHTML = newTheme === "dark" ? light_icon : dark_icon;
        // });

        $(".themeSet").click(function() {
            console.log($(this).val());
            const newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", $(this).val());
            localStorage.setItem("theme", $(this).val());
        });

        {{-- alert({{ $compSettings[0]->order_amount }});
        $("#device_Laptop").val({{ $compSettings[0]->order_amount }});
        $("#device_Monitor").val({{ $compSettings[0]->order_amount }}); --}}
    </script>
@endpush
