@extends('layouts.home')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Generate API key</h1>
            <p class="text-muted small pt-2 ps-1">Generate API access key to authenticate api calls. For more information,
                please refer to our API documentation <a style="color: var(--primary-color);"><strong>here</strong></a></p>
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
                 <div class="row">
                                <div class="col-lg-12">
                                    <div class="p-0">


                                        <form class="user" name="api_frm" id="api_frm">
                                            @csrf
                                            <div class="alert alert-primary" role="alert">
                                                Please use the key in all API calls
                                            </div>

                                            <div class="alert alert-success" role="alert" id="alertBox"
                                                style="display:none;">
                                                <strong>Success:</strong> API Key has generated!
                                            </div>



                                            <div class="row me-12">
                                                <div class="col-md-12">
                                                    <h4 class="h6 text-gray-800 mb-0">API Key</h4>

                                                    <div class="row align-items-center" id="apiKeyDiv" @if(!$user->api_key) style="display:none;" @endif>
                                                        <div class="col-11">
                                                             <input type="text" name="api_key" class="form-control" id="api_key"
                                                    placeholder="API Key" required="" value="{{$user->api_key}}">
                                                        </div>
                                                        <div class="col-1">
                                                            <button type="button" id="copyButton" class="btn btn-primary"
                                                                title="Copy API Key">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
</svg></button>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <button class="btn btn-primary custom-dashboard-btn mt-2 generateKey">
                                                Generate
                                            </button>

                                        </form>
                                    </div>
                                </div>
                            </div>
            </div>
        </div>

    </main><!-- End #main -->
@stop

@push('other-scripts')
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>

     <script>

        $(document).ready(function() {
            $('.generateKey').on('click', function(event) {
                event.preventDefault(); // Prevent the default form submission
            var c = confirm("Are you sure to generate API key?")
            if(c == true)
            {
                 $.ajax({
                        type: "POST", // Use POST for file upload
                        url: "{{ route('generate.api.key') }}",
                        data: $("#api_frm").serialize(),
                        cache: false,
                        success: function(response) {
                            $("#alertBox").fadeIn();
                            $("#alertBox").addClass('alert-success').removeClass(
                            'alert-danger');
                            $("#alertBox").html('');
                            $("#alertBox").html(response.message);
                            $("#apiKeyDiv").fadeIn();
                            $("#api_key").val(response.apiKey);
                            $("#copyButton").fadeIn();
                        },
                        error: function(xhr, status, error) {
                            $("#alertBox").fadeIn();
                            $("#alertBox").addClass('alert-danger').removeClass(
                            'alert-success');
                            $("#alertBox").html(response.message);

                        }
                    });
            }


            });

        });


        $(document).ready(function(){
            $("#copyButton").click(function(){
                if (window.isSecureContext && navigator.clipboard) {
                    console.log(11);
                    var copyText = $("#api_key");
                    copyText.select();
                    copyText[0].setSelectionRange(0, 99999); // For mobile devices
                    var content = copyText.val();
                    var r =  navigator.clipboard.writeText(content);
                    alert("Copied the key: " + copyText.val());
                }else{
                    console.log(22);
                    var justForCopy = document.getElementById('api_key');
                    justForCopy.select();
                    var r = document.execCommand("copy");
                    alert("Copied the key: " + justForCopy.value);
                }
            });
        });
    </script>
@endpush
