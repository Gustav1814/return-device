@extends('layouts.home')
@section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Settings</h1>
        <p>Save default Settings for all of the device return you set up. You can always enter different information when placing an order.</p>
    </div><!-- End Page Title -->
    <div class="card">
        <div class="card-body py-3">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
         <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
@endif

            <h5>Theme Settings</h5>
            <form action="{{ route('company.settings.sub') }}" method="post" enctype="multipart/form-data">
                @csrf

                {{-- <div class="row">
                    <div class="col-md-12 col-12">
                        <label for="theme_color">Select Your Color Theme</label>
                        <div class="input-group my-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input themeSet" type="radio" name="theme_color" id="theme_color"
                                    value="light" @if ($companySettings->theme == 'light') checked @endif>
                                <label class="form-check-label" for="theme_color">Light</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input themeSet" type="radio" name="theme_color" id="theme_color"
                                    value="dark" @if ($companySettings->theme == 'dark') checked @endif>
                                <label class="form-check-label" for="theme_color">Dark</label>
                            </div>
                        </div>
                    </div>
                </div> --}}


                <div class="row">
                    @php $btnBgColor = ($companySettings->btn_bg_color)?$companySettings->btn_bg_color:'#f37033' @endphp
                    <div class="col-md-4 col-12">
                        <div class="mb-3">
                            <label for="button_background_color">Button Background Color</label>
                            <input type="color" class="d-block p-0" id="button_background_color"
                                name="button_background_color" value="{{ $btnBgColor }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        @php $btnFontColor = ($companySettings->btn_font_color)?$companySettings->btn_font_color:'#ffffff' @endphp
                        <div class="mb-3">
                            <label for="button_text_color">Button Text Color</label>
                            <input type="color" class="d-block p-0" id="button_text_color" name="button_text_color"
                                value="{{ $btnFontColor }}">
                        </div>
                    </div>
                    {{-- <div class="col-md-4 col-12">
                        <div class="mb-3">
                            @php $textColor = ($companySettings->theme_font_color)?$companySettings->theme_font_color:'#222222' @endphp
                            <label for="text_color">Theme Text Color</label>
                            <input type="color" class="d-block p-0" id="text_color" name="text_color"
                                value="{{ $textColor }}">
                        </div>
                    </div> --}}
                </div>
                <div class="form-group row align-items-center mt-4">
                    <div class="col-md-3 mb-4">
                        <label for="button_background_color">Theme Logo</label>
                        <input type="file" name="theme_logo" id="theme_logo">
                    </div>
                    <div class="col-md-7">
                        <div class="logo-display">
                        @if($companySettings->logo != null)
                                <img
                                        src="{{ asset("storage/logoImage/$companySettings->logo") }}?v={{ time() }}"
                                        alt="">
                                @else
                                <img

                                        src="{{ asset('assets/img/dummyLogo.png') }}?v={{ time() }}"
                                        alt="">
                                @endif


                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
                <div class="row">


                    <div class="col-12">
                        <div class="alert alert-info">
                        <ul><li>The image size should not exceed 2 MB</li>
                        <li>The maximum allowed dimensions are 300px in width and 100px in height</li>
                        <li>Allowed image formats: JPEG, PNG, JPG, and GIF</li></ul>
                        </div>
                    </div>



                </div>
                <div class="form-group row align-items-center mt-4">
                    <div class="col-md-3 mb-4">
                        <label for="button_background_color">Theme Favicon</label>
                        <input type="file" name="theme_fav" id="theme_fav">
                    </div>
                    <div class="col-md-7">
                        <div class="logo-display">
                        @if($companySettings->favicon != null)
                                <img
                                        src="{{ asset("storage/favicon/$companySettings->favicon") }}?v={{ time() }}"
                                        alt="">
                                @else
                                <img

                                        src="{{ asset('assets/img/dummyLogo.png') }}?v={{ time() }}"
                                        alt="">
                                @endif


                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
                  <div class="row">


                    <div class="col-12">
                        <div class="alert alert-info">
                        <ul><li>The image size should not exceed 2 MB</li>
                        <li>The maximum allowed dimensions are 100px in width and 100px in height</li>
                        <li>Allowed image formats: JPEG, PNG, JPG, and GIF</li></ul>
                        </div>
                    </div>



                </div>
                <div class="row">
                     <div class="col-12">
                        <button type="submit" class="btn btn-dark theme-bgcolor-btn-one mt-3"
                            type="submit">Update</button>
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
    </script>
@endpush
