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
                            @php  
                           $logoFilename = $mailData['logo'];
$logoUrl = asset("storage/logoImage/$logoFilename");
     @endphp
                                 <div style="width:100%;text-align:center;"><img
                                        src="{{ $logoUrl }}"
                                        alt="" width="200px"></div>
                                <hr />
                                @php
                                    if(isset($mailData['companyData'])){ $company_name      = $mailData['companyData']['company_name'] ;     } else{$company_name = $mailData['receipient_name']; }
                                    if(isset($mailData['companyData'])){ $receipient_person =$mailData['companyData']['receipient_name']  ;} else{$receipient_person = $mailData['receipient_person']; }
                                    //if($company){ $receipient_person = $company->receipient_person ;} else{$receipient_person = $mailData['receipient_person']; }
                                @endphp
                                @if ($template == 'boxDeliveredToEmployee')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp @php echo ($mailData['emp_last_name'])?$mailData['emp_last_name']:''; @endphp</strong>,</p>
                                     <p>The @php echo $mailData['type_of_equip']; @endphp retrieval box for <strong>@php echo $mailData['receipient_name']; @endphp</strong> has been delivered to
                                     <strong>@php echo $mailData['emp_add_1'] .' '. $mailData['emp_add_2'] .''.$mailData['emp_city'] .' '. $mailData['emp_state'] .' '. $mailData['emp_pcode'] @endphp </strong>.</p>
                                    <p>We kindly ask that you ship your @php echo $mailData['type_of_equip']; @endphp as soon as possible. </p>
<p><strong> How to Return Your @php echo $mailData['type_of_equip']; @endphp:</strong></p>
{{-- <p>Follow the simple instructions inside the box (also available <a href="https://www.remoteretrieval.com/faqs/"><strong>here</strong></a>) to securely pack and ship your @php echo $mailData['type_of_equip']; @endphp along with any requested accessories.</p> --}}

<p><strong> Can't find the box?</strong></p>
<p>Please check the tracking status for more details on the delivery location.</p>
<p>Tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong></p>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@returndevice.com or call us at 888-597-1025. We're here to help!</p>
                                @endif

                                @if ($template == 'boxDeliveredToEmployee_companyemail')
                                    <p>Hi <strong>@php echo $receipient_person; @endphp</strong>,</p>
                                    <p>We wanted to let you know that the @php echo $mailData['type_of_equip']; @endphp retrieval box for order #<strong>@php echo $mailData['order_id'] @endphp</strong> has been delivered to <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.
</p>
                                    <p>You can track the package using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong> </p>
                                    <p>We'll send you another update once the @php echo $mailData['type_of_equip']; @endphp has been successfully shipped by <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.</p>
<p> If your employee delays returning their @php echo $mailData['type_of_equip']; @endphp, we will send them reminders at 5, 10, and 20-day intervals. You will also receive an email notification each time we send a reminder to your employee.
</p>

                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif





                                @if ($template == 'deviceDeliveryStartToCompany_employeeemail')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                    <p>Thank you for sending the @php echo $mailData['type_of_equip']; @endphp for <strong>@php echo $company_name; @endphp </strong></p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>We'll send you another update once the box has been successfully delivered to you.</p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveryStartToCompany_companyemail')
                                    <p>Hi <strong>@php echo $company_name; @endphp </strong>,</p>
                                    <p>We wanted to let you know that <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong> has shipped the @php echo $mailData['type_of_equip']; @endphp.</p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>We'll send you another update once the box has been successfully delivered to you.</p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif

                                @if ($template == 'deviceDeliveredToCompany_companyemail')
                                    <p>Hi <strong>@php echo $company_name; @endphp</strong>,</p>
                                    <p>Good news! Tracking shows that the @php echo $mailData['type_of_equip']; @endphp has been successfully retrieved and delivered to your
                                        <strong>@php echo $mailData['receipient_add_1'] .' '. $mailData['receipient_add_2'] .' '.$mailData['receipient_city'] .' '. $mailData['receipient_state'] .' '. $mailData['receipient_zip'] @endphp </strong>
                                    </p>
                                    <p>In case you have not received the @php echo $mailData['type_of_equip']; @endphp, you can track the package using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong> </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveredToCompany_employeeemail')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                    <p>Good news! Tracking shows that the @php echo $mailData['type_of_equip']; @endphp has been successfully retrieved and delivered to your
                                        <strong>@php echo $mailData['receipient_add_1'] .' '. $mailData['receipient_add_2'] .' '.$mailData['receipient_city'] .' '. $mailData['receipient_state'] .' '. $mailData['receipient_zip'] @endphp </strong></p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong> </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We’re happy to help!</p>
                                @endif

                                @if ($template == 'empEmailboxDeliveredToEmployeeAsReminder')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                    <p>We've noticed that you have not yet shipped your <strong>@php echo $mailData['receipient_name']; @endphp</strong> @php echo $mailData['type_of_equip']; @endphp using the box we provided.</p>
                                    <p>We would greatly appreciate it if you could return the @php echo $mailData['type_of_equip']; @endphp by the next business day.</p>
                                    <h3>How to Return Your @php echo $mailData['type_of_equip']; @endphp: </h3>

                                    {{-- <p>Please follow the easy instructions inside the box (also available <strong><a href="https://www.remoteretrieval.com/faqs/">here</a></strong>) to securely pack and ship your @php echo $mailData['type_of_equip']; @endphp along with any requested accessories.</p> --}}
                                    <p>If you have already shipped your @php echo $mailData['type_of_equip']; @endphp, feel free to disregard this email. Please note that we will continue to send reminders until we see tracking for the @php echo $mailData['type_of_equip']; @endphp return shipment. You can track its progress using this tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help! </p>
                                    <p>Thank you for your attention to this matter!</p>

                                @endif
                                @if ($template == 'compEmailboxDeliveredToEmployeeAsReminder')
                                    <p>Hi <strong>@php echo $mailData['receipient_name']; @endphp</strong>,</p>
                                    <p>According to our return tracking information, the @php echo $mailData['type_of_equip']; @endphp for order #<strong>@php echo $mailData['order_id'] @endphp</strong> has not yet been shipped. </p>
                                    <p>We've sent <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong> another reminder regarding the return. If the @php echo $mailData['type_of_equip']; @endphp shipment does not start tracking within the next 5 days, we will send a final reminder to your employee.</p>
                                    <p>Has <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong> informed you that they have already shipped the @php echo $mailData['type_of_equip']; @endphp? Please note that tracking may take 24-48 hours to update once the package is sent.</p>
                                    <p>You can keep track of the shipment using this tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong> . </p>
                                    <p>If you have any comments or questions, please feel free to reply to this email or contact us at support@returndevice.com or call us at 888-597-1025. We’re happy to help!</p>
                                @endif


                                @if ($template == 'empEmailAfterLabelcreate')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                @if($mailData['return_service'] == "Sell This Equipment")
                                    <p>Thank you for placing an order on Return Device to recycle the @php echo $mailData['type_of_equip']; @endphp with data destruction.</p>
                                    <p>We've shipped you a box containing a prepaid return shipping label, packing materials, and clear instructions to make the laptop recycle process quick and hassle-free.</p>
                                @else
                                    {{-- <p><strong>@php echo $mailData['receipient_name']; @endphp</strong> has partnered with Remote Retrieval to assist in the return of your work @php echo $mailData['type_of_equip']; @endphp. </p> --}}
                                    <p><b>{{ $mailData['receipient_name'] }}</b> has shipped you a box containing a prepaid return shipping label, packing materials, and clear instructions to make the return process quick and hassle-free.</p>
                                @endif
                                    <p>Please expect the box to arrive within the next few days. You can track the shipment using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong>. </p>
                                    <p> Please note that tracking updates may take 24-48 hours to appear.</p>
                                    <p>If you have any questions or need assistance, feel free to reach out to {{ $mailData['receipient_name'] }}</p>
                                    {{-- us at support@returndevice.com or call us at 888-597-1025. We're here to help! --}}
                                @endif
                                @if ($template == 'compEmailAfterLabelcreate')
                                    <p>Hi <strong>@php echo $mailData['receipient_name']; @endphp</strong>,</p>
                                    <p>We wanted to let you know that the @php echo $mailData['type_of_equip']; @endphp retrieval box for order #<strong>@php echo $mailData['order_id'] @endphp-@php echo $mailData['id'] @endphp</strong> has been shipped to <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>. </p>
                                    <p>You can track the package using the tracking number:  <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>We'll send you another update once the box has been successfully delivered.</p>
                                    <p>If you have any comments or questions, please feel free to reply to this email or contact us at support@returndevice.com. We're happy to help!</p>
                                @endif

@if ($template == 'compPaymentFailedOrder')
                                    <p>Hi <strong>@php echo $mailData['receipient_name']; @endphp</strong>,</p>
                                    <p>I hope you're doing well.</p>
                                    <p>This is to inform you that a new order #{{$mailData['order_id']}}-{{$mailData['id']}} has been created under your white label account. However, please note that the payment for this order has not yet been processed.</p>
                                    <p>Kindly review and ensure the payment is completed so we can proceed with fulfillment. If you need any assistance or have questions regarding the order details, feel free to reach out.</p>
                                    <p>Thank you,</p>
                                @endif

                                {{-- @if ($template == 'boxDeliveredToEmployeeDD')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp @php echo ($mailData['emp_last_name'])?$mailData['emp_last_name']:''; @endphp</strong>,</p>
                                    <p>The @php echo $mailData['type_of_equip']; @endphp retrieval box for <strong>@php echo $mailData['receipient_name']; @endphp</strong> has been delivered to
                                    <strong>@php echo $mailData['emp_add_1'] .' '. $mailData['emp_add_2'] .''.$mailData['emp_city'] .' '. $mailData['emp_state'] .' '. $mailData['emp_pcode'] @endphp </strong>.</p>
                                    <p>We kindly ask that you ship your @php echo $mailData['type_of_equip']; @endphp as soon as possible. </p>
                                    <p><strong> How to Return Your @php echo $mailData['type_of_equip']; @endphp:</strong></p>
                                    <p>Follow the simple instructions inside the box (also available <a href="https://www.remoteretrieval.com/faqs/"><strong>here</strong></a>) to securely pack and ship your @php echo $mailData['type_of_equip']; @endphp along with any requested accessories.</p>

                                    <p><strong> Can't find the box?</strong></p>
                                    <p>Please check the tracking status for more details on the delivery location.</p>
                                    <p>Tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong></p>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@returndevice.com or call us at 888-597-1025. We're here to help!</p>
                                @endif
                                @if ($template == 'boxDeliveredToEmployee_companyemailDD')
                                    <p>Hi <strong>@php echo $receipient_person; @endphp</strong>,</p>
                                    <p>We wanted to let you know that the @php echo $mailData['type_of_equip']; @endphp retrieval box for order #<strong>@php echo $mailData['order_id'] @endphp</strong> has been delivered to <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.
                                    </p>
                                    <p>You can track the package using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong> </p>
                                    <p>We'll send you another update once the @php echo $mailData['type_of_equip']; @endphp has been successfully shipped by <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.</p>
                                    <p> If your employee delays returning their @php echo $mailData['type_of_equip']; @endphp, we will send them reminders at 5, 10, and 20-day intervals. You will also receive an email notification each time we send a reminder to your employee.
                                    </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveryStartToCompany_employeeemailDD')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                    <p>Thank you for sending the @php echo $mailData['type_of_equip']; @endphp for <strong>@php echo $company_name; @endphp </strong></p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>We'll send you another update once the box has been successfully delivered to you.</p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveredToCompany_employeeemailDD')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>,</p>
                                    <p>Good news! Tracking shows that the @php echo $mailData['type_of_equip']; @endphp has been successfully retrieved and delivered to your
                                        <strong>@php echo $mailData['receipient_add_1'] .' '. $mailData['receipient_add_2'] .' '.$mailData['receipient_city'] .' '. $mailData['receipient_state'] .' '. $mailData['receipient_zip'] @endphp </strong></p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong> </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We’re happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveredToCompany_companyemailDD')
                                    <p>Hi <strong>@php echo $company_name; @endphp</strong>,</p>
                                    <p>Good news! Tracking shows that the @php echo $mailData['type_of_equip']; @endphp has been successfully retrieved and delivered to your
                                        <strong>@php echo $mailData['receipient_add_1'] .' '. $mailData['receipient_add_2'] .' '.$mailData['receipient_city'] .' '. $mailData['receipient_state'] .' '. $mailData['receipient_zip'] @endphp </strong>
                                    </p>
                                    <p>In case you have not received the @php echo $mailData['type_of_equip']; @endphp, you can track the package using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong> </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif
                                @if ($template == 'deviceDeliveryStartToCompany_companyemailDD')
                                    <p>Hi <strong>@php echo $company_name; @endphp </strong>,</p>
                                    <p>We wanted to let you know that <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong> has shipped the @php echo $mailData['type_of_equip']; @endphp.</p>
                                    <p>You can track the package using the tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>'  @endphp</strong></p>
                                    <p>We'll send you another update once the box has been successfully delivered to you.</p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif

                                @if ($template == 'companyEmail_After_DD')
                                    <p>Hi <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp @php echo ($mailData['emp_last_name'])?$mailData['emp_last_name']:''; @endphp</strong>,</p>
                                    <p>The @php echo $mailData['type_of_equip']; @endphp retrieval box for <strong>@php echo $mailData['receipient_name']; @endphp</strong> has been delivered to
                                    <strong>@php echo $mailData['emp_add_1'] .' '. $mailData['emp_add_2'] .''.$mailData['emp_city'] .' '. $mailData['emp_state'] .' '. $mailData['emp_pcode'] @endphp </strong>.</p>
                                    <p>We kindly ask that you ship your @php echo $mailData['type_of_equip']; @endphp as soon as possible. </p>
                                    <p><strong> How to Return Your @php echo $mailData['type_of_equip']; @endphp:</strong></p>
                                    <p>Follow the simple instructions inside the box (also available <a href="https://www.remoteretrieval.com/faqs/"><strong>here</strong></a>) to securely pack and ship your @php echo $mailData['type_of_equip']; @endphp along with any requested accessories.</p>

                                    <p><strong> Can't find the box?</strong></p>
                                    <p>Please check the tracking status for more details on the delivery location.</p>
                                    <p>Tracking number:<strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong></p>
                                    <p>If you have any questions or need assistance, feel free to reach out to us at support@returndevice.com or call us at 888-597-1025. We're here to help!</p>
                                @endif
                                @if ($template == 'empEmail_After_DD')
                                    <p>Hi <strong>@php echo $receipient_person; @endphp</strong>,</p>
                                    <p>We wanted to let you know that the @php echo $mailData['type_of_equip']; @endphp retrieval box for order #<strong>@php echo $mailData['order_id'] @endphp</strong> has been delivered to <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.
                                    </p>
                                    <p>You can track the package using the tracking number: <strong>@php echo '<a href="'.$trackingUrl.'">'.$trackingNo.'</a>' @endphp</strong> </p>
                                    <p>We'll send you another update once the @php echo $mailData['type_of_equip']; @endphp has been successfully shipped by <strong>@php echo ($mailData['emp_first_name'])?$mailData['emp_first_name']:''; @endphp</strong>.</p>
                                    <p> If your employee delays returning their @php echo $mailData['type_of_equip']; @endphp, we will send them reminders at 5, 10, and 20-day intervals. You will also receive an email notification each time we send a reminder to your employee.
                                    </p>
                                    <p>If you have any questions or need any assistance, don't hesitate to contact us at support@returndevice.com or call us at 888-597-1025. We're happy to help!</p>
                                @endif --}}



                                <p>
                                
                                <br /> <b>Customer Support</b>
                            <br>
                             <span
                                    style="color:#000;font-weight:500;" class=apple-link>Return Device</span><br><a
                                    href=mailto:support@returndevice.com>support@returndevice.com</a>
                            </p>
                                <p>
                                {{-- <span><img
                                    src="{{env("LOGO_IMG")}}"
                                    alt="" width="100px"></span><br /> --}}
                                   
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
