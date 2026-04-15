@if (!empty($template))
<!doctype html>
<html lang=en>
<head>
    <meta name=viewport content="width=device-width,initial-scale=1">
    <meta http-equiv=Content-Type content="text/html; charset=UTF-8">
    <title>{{ $title }}</title>
    <style media=all>.body,body{background-color:#f4f5f6}body,p,table td{font-family: system-ui;font-size: 16px;}.btn a,.btn table td{background-color:#fff}.btn,.btn a,.content,.wrapper{box-sizing:border-box}.btn a,body{font-size:16px;margin:0}.align-center,.btn table td,.footer{text-align:center}.clear,.footer{clear:both}.btn a,.powered-by a{text-decoration:none}body{-webkit-font-smoothing:antialiased;line-height:1.3;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding:0}table{border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%}table td{font-size:16px;vertical-align:top}.body{width:100%}.container{margin:0 auto!important;max-width:600px;padding:24px 0 24px;width:600px}.btn,.footer,.main{width:100%}.content{display:block;margin:0 auto;max-width:600px;padding:0}.main{background:#fff;border:1px solid #eaebed;border-radius:16px}.wrapper{padding:24px}.footer{padding-top:24px}.footer a,.footer p,.footer span,.footer td{color:#9a9ea6;font-size:16px;text-align:center}.btn a,a{color:#0867ec}p{font-size:14px;font-weight:400;margin:0 0 16px}a{text-decoration:underline}.btn{min-width:100%!important}.btn>tbody>tr>td{padding-bottom:16px}.btn table{width:auto}.btn table td{border-radius:4px}.btn a{border:2px solid #0867ec;border-radius:4px;cursor:pointer;display:inline-block;font-weight:700;padding:12px 24px;text-transform:capitalize}.btn-primary a,.btn-primary table td{background-color:#0867ec}.btn-primary a{border-color:#0867ec;color:#fff}.last,.mb0{margin-bottom:0}.first,.mt0{margin-top:0}.align-right{text-align:right}.align-left{text-align:left}.text-link{color:#0867ec!important;text-decoration:underline!important}.preheader{color:transparent;display:none;height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;visibility:hidden;width:0}@media only screen and (max-width:640px){.btn a,.main p,.main span,.main td{font-size:16px!important}.btn a,.btn table{max-width:100%!important;width:100%!important}.btn a,.btn table,.container{width:100%!important}.wrapper{padding:8px!important}.content{padding:0!important}.container{padding:8px 0 0!important}.main{border-left-width:0!important;border-radius:0!important;border-right-width:0!important}}@media all{.btn-primary a:hover,.btn-primary table td:hover{background-color:#ec0867!important}.btn-primary a:hover{border-color:#ec0867!important}.ExternalClass{width:100%}.ExternalClass,.ExternalClass div,.ExternalClass font,.ExternalClass p,.ExternalClass span,.ExternalClass td{line-height:100%}.apple-link a{color:inherit!important;font-family:inherit!important;font-size:inherit!important;font-weight:inherit!important;line-height:inherit!important;text-decoration:none!important}#MessageViewBody a{color:inherit;text-decoration:none;font-size:inherit;font-family:inherit;font-weight:inherit;line-height:inherit}}</style>
</head>
<body>
    <table role=presentation border=0 cellpadding=0 cellspacing=0 class=body>
        <tr>
            <td>&nbsp;</td>
            <td class=container>
                <div class=content>
                    <table role=presentation border=0 cellpadding=0 cellspacing=0 class=main>
                        <tr>
                            <td class=wrapper>
                                <div style="width:100%;text-align:center;"><img
                                        src="{{env("LOGO_IMG")}}"
                                        alt="" width="200px"></div>
                                <hr />
                                @php
                                    //if($company){ $company_name      = $company->company_name ;     } else{$company_name = $mailData['receipient_name']; }
                                    //if($company){ $receipient_person = $company->receipient_person ;} else{$receipient_person = $mailData['receipient_person']; }
                                    //if($company){ $receipient_person = $company->receipient_person ;} else{$receipient_person = $mailData['receipient_person']; }
                                @endphp



                                @if ($template == 'newSignupCredentials')
                                    <p>Dear @php echo $to; @endphp,</p>
                                    <p>Welcome to Return Device – Your Partner to Retrieve Equipment from Remote Employees.</p>
                                    <p>Your registration details are given below:</p>
                                    <p>Email address: <b>@php echo $to; @endphp</b></p>
                                    <p>Password: <b>@php echo $pwd; @endphp</b></p>
                                    <p>Package: <b>@php echo $package;
                                    @endphp</b></p>
                                    <p>Please use the above credentials to sign in and change your account password.</p>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@remoteretrieval.com or 888-597-1025. We’re here to help!</p>
                                    <p>Thank you for choosing RemoteRetrieval.com We look forward to serving you and making your experience with us truly memorable.</p>
                                @endif

                                @if ($template == 'newSignupFreeCoupon')
                                    <p>Hi @php echo $to; @endphp,</p>
                                    <p>Thank you for signing up with Return Device.com. We’re excited to have you on board.</p>
                                    <p>As a token of our appreciation, we’re offering you your first order absolutely Free. Whether you need IT equipment retrieved from remote employees or any other retrieval service, this is the perfect time to experience how seamless and efficient our service is.</p>
                                    <p>Here’s how to get started:</p>
                                    <ol>
                                    <li><p>Log in to your account <a href="https://www.remoteretrieval.com/user-login/">Click Here</a>.</p></li>
                                    <li><p>Place your first order and we’ll take care of the rest – no charges, no hassle!</p></li>
                                    <li><p>Use the coupon code: <strong>N3WSEPT24</strong> on the last step to create your Free order</p></li>
                                    </ol>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@remoteretrieval.com or 888-597-1025. We’re here to help!</p>
                                @endif


                                @if ($template == 'forgotPDCredentials')
                                    <p>Dear @php echo $to; @endphp,</p>
                                    <p>Welcome to Return Device</p>
                                    <p>Please <a href="@php echo $url; @endphp">Click Here</a> to Set New Password.</p>
                                    <p>Thank you for choosing RemoteRetrieval.com We look forward to serving you and making your experience with us truly memorable.</p>
                                @endif

                                @if ($template == 'contactUs')
                                    <table width="500" border="1" style="padding:5px;">
                                     <tr><td colspan="2" style="text-align:center;"><h2>Contact Us</h2></td></tr>
                                    <tr><td width="250">Name</td><td width="250">@php echo $mailData['name'] @endphp</td></tr>
                                    <tr><td width="250">Email</td><td width="250">@php echo $mailData['email'] @endphp</td></tr>
                                    <tr><td width="250">Subject</td><td width="250">@php echo $mailData['subject'] @endphp</td></tr>
                                    <tr><td width="250">Message</td><td width="250">@php echo $mailData['message'] @endphp</td></tr>
                                    <tr><td width="250">From Page</td><td width="250">@php echo $url @endphp</td></tr>
                                    </table>
                                @endif

                                @if ($template == 'createOrderMail')

                                    <p><b>Order Details:</b></p>
                                    <p>Date of Purchase: @php echo date("M d, Y") @endphp</p>
                                    <p>Order Type:
                                    @php //echo $mailData['return_service']
                                    if($mailData['return_service'] == "Sell This Equipment"){
echo  "Recycle with Data Destruction";}else{echo  $mailData['return_service'];}
@endphp</p>
                                    <p>Item Type: @php echo $mailData['type_of_equip'] @endphp </p>
                                    <p><b>Equipment to be returned to:</b></p>
                                    <p>Name:  @php echo $mailData['receipient_name']   @endphp </p>
                                    <p>Email: @php echo $mailData['receipient_email'] @endphp</p>
                                    <p>Address: @php echo $mailData['receipient_add_1'].' '.$mailData['receipient_add_2'].' '.$mailData['receipient_city'].' '.$mailData['receipient_state'].' '.$mailData['receipient_zip'] @endphp</p>
                                    <p><b>Equipment Sender:</b></p>
                                    <p>Name: @php echo $mailData['emp_first_name'].' '.$mailData['emp_last_name'] @endphp</p>
                                    <p>Email: @php echo $mailData['emp_email'] @endphp</p>
                                    <p>Address: @php echo $mailData['emp_add_1'].' '.$mailData['emp_add_2'].' '.$mailData['emp_city'].' '.$mailData['emp_state'].' '.$mailData['emp_pcode'] @endphp</p>
                                    <p><b>Payment Details:</b></p>
                                        @if ($insurance && $insurance != 0)
                                            <p>Insurance Amount: {{ $insurance }}</p>
                                        @endif
                                        @if ($dd && $dd != 0)
                                            <p>Data Destruction: {{ $dd }}</p>
                                        @endif
                                    <p>Total Amount: @php echo $paymentAmount @endphp</p>
                                    <p>Payment Method: Card Payment</p>
                                    <p>You can track the status of your order at any time by logging into your account on our website and navigating to the "Orders" section.</p>
                                    <p>Thank you again for choosing RemoteRetrieval.com We appreciate your business and hope you enjoy your shopping experience with us.</p>

                                @endif


                                @if ($template == 'activateAccount')
                                    <p>Hi @php echo $to; @endphp,</p>
                                    <p>Thank you for signing up with ReturnDevice.com for White Label. We’re excited to have you on board.</p>
                                    <p>Here’s how to get started:</p>
                                    <ol>
                                    <li><p>Log in to your account <a href="https://@php echo $mailData['company_domain']; @endphp.returndevice.com/wl-login/">Click Here</a>.</p></li>
                                    <li>Login credentials have sent in last email </li>
                                    </ol>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@returndevice.com or 888-597-1025. We’re here to help!</p>
                                @endif




                                <p>

                                <br /> <b>Customer Support</b>
                            <br>
                             <span
                                    style="color:#000;font-weight:500;" class=apple-link>Return Device</span><br><a
                                    href=mailto:support@returndevice.com>support@returndevice.com</a>
                            </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
@endif
