<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\User;
use App\Libraries\Services\Helper;
use App\Libraries\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Orders;
use App\Models\Systemsettings;
use App\Models\Compemployees;
use League\Csv\Reader;
use App\Models\Settings;
use App\Models\Companysettings;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions;

use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Libraries\Services\Shipping;

class LabelController extends Controller
{
    protected $helper;
    protected $mailService;

    public function __construct(MailService $mailService, Helper $helper)
    {
        $this->mailService = $mailService;
        $this->helper = $helper;
    }

    /**
     * MODULE: GENERATE RATE LIST
     * DESC:   IT WILL GENERATE RATE LIST FROM SHIPPO FOR CHOOSING PURCHASE LABEL
     */
    public function findRates(Request $request, Shipping $shipping)
    {
        $param = array();
        $type = $request->input('t');
        $order = Compemployees::where('id', $request->oid)->first();

        if ($order->return_additional_srv == null) {
            if ($type == 'rec') {
                $orderData = $this->receive_label_from_employee($order);
            } else {
                $orderData = $this->send_label_to_employee($order);
            }

        } else {
            if ($type == 'rec') {
                // IN DATADESTRUCTION, LABEL CREATE SECOND STEP
                $orderData = $this->receive_device_from_employee_datadestruction($order);
            } else if ($type == 'dest') {
                // IN DATADESTRUCTION, LABEL CREATE THIRD STEP
                $orderData = $this->return_device_after_datadestruction($order);
            } else {
                // IN DATADESTRUCTION, LABEL CREATE FIRST STEP
                $orderData = $this->send_box_to_employee_datadestruction($order);
            }

        }


        $param['from'] = $orderData['from_address'];
        $param['to'] = $orderData['to_address'];
        $param['parcel'] = $orderData['parcel'];
        // $param['insurance'] = null;
        if (isset($orderData['insurance'])) {
            $param['insurance'] = $orderData['insurance'];
        }
        $t = '';
        $fr = 'From';
        $to = 'To';
        // VALIDATE ADDRESS

        $fromAddressVal = $this->validateAddressbyShipo($param['from'], $shipping, $fr);
        $toAddressVal = $this->validateAddressbyShipo($param['to'], $shipping, $to);
        // if(isset($fromAddressVal->original) && $fromAddressVal->original['status'] == 'fail')
        // {
        //     $res = ['status' => $fromAddressVal->original['status'], 'response' => "From Address: ".$fromAddressVal->original['response']];
        //     return response()->json($res);
        // }
        // if(isset($toAddressVal->original) && $toAddressVal->original['status'] == 'fail')
        // {
        //     $res = ['status' => $toAddressVal->original['status'], 'response' => "To Address: ".$toAddressVal->original['response']];
        //     return response()->json($res);
        // }


        $response = $shipping->get_shippo_rates($param);
        $t = '';
        // print_r($response['rates']);
        // print_r($response);
        if ($response['status'] == "SUCCESS") {

            // $ratess = $response['rates']['amount'] ;
            // asort( $ratess );
            $t .= '<div class="mt-3"><div class="row">';
            foreach ($response['rates'] as $rate) {
                //print_r($rate['servicelevel']['token']);
                $bg = '';
                if ($rate['servicelevel']['token'] == "ups_ground") {
                    $bg = "background-color:#ddd";
                }
                // $t .= '<table width="100%" style="border:1px solid black;' . $bg . '">';
                // $t .= '<tr><td><img src=' . $rate['provider_image_75'] . '></td>
                // <td>' . $rate['amount'] . '' . $rate['currency'] . '</td>
                // <td>Estimated days: ' . $rate['estimated_days'] . '</td></tr>
                // <tr><td colspan="1">' . $rate['provider'] . ' (' . $rate['servicelevel']['token'] . ')</td>
                // <td colspan="2" style="text-align:right;"><button class="btn btn-primary btn-purchaselabel" data-type="' . $type . '" data-lblobj="' . $rate['object_id'] . '">
                // Purchase Label</button> </td></tr>
                // <tr><td colspan="3">' . $rate['duration_terms'] . ' </td></tr>';
                // $t .= '</table>';
                $t .= '<div class="col-md-12 mb-2" style="border:1px solid black;' . $bg . '">
                            <div class="row">
                                <div class="col-4"><img src="' . $rate['provider_image_75'] . '" alt="" /></div>
                                <div class="col-3"><p>' . $rate['amount'] . '' . $rate['currency'] . '</p></div>
                                <div class="col-5 text-end"><p>Estimated days: ' . $rate['estimated_days'] . '</p></div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p>' . $rate['provider'] . ' (' . $rate['servicelevel']['token'] . ')</p>
                                </div>
                                <div class="col-6 text-end">
                                        <button type="button" class="btn btn-dark theme-bgcolor-btn-one btn-purchaselabel" data-type="' . $type . '" data-lblobj="' . $rate['object_id'] . '">
                                            Purchase Label
                                        </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12"><p>Overnight delivery to most U.S. locations. </p></div>
                            </div>
                        </div>';

                // $t .= '<div class="label-rates-table mt-3"><table width="100%" style="border:1px solid black;' . $bg . '">';
                // $t .= '<tr>
                //                     <td><img src="' . $rate['provider_image_75'] . '"
                //                             alt="" /> </td>
                //                     <td class="text-center">
                //                         <p>' . $rate['amount'] . '' . $rate['currency'] . '</p>
                //                     </td>
                //                     <td class="text-end">
                //                         <p>Estimated days: ' . $rate['estimated_days'] . '</p>
                //                     </td>
                //                 </tr>
                //                 <tr>
                //                     <td>
                //                         <p>' . $rate['provider'] . ' (' . $rate['servicelevel']['token'] . ')</p>
                //                     </td>
                //                     <td></td>
                //                     <td class="text-end">
                //                         <button type="button" class="btn btn-dark theme-bgcolor-btn-one btn-purchaselabel" data-type="' . $type . '" data-lblobj="' . $rate['object_id'] . '">
                //                             Purchase Label
                //                         </button>
                //                     </td>
                //                 </tr>
                //                 <tr>
                //                     <td colspan="3">
                //                         <p>Overnight delivery to most U.S. locations.</p>
                //                     </td>
                //                 </tr>
                //             </table></div>';
                // $t .= '';
            }
            $t .= '</div></div>';
        } else {
            $t .= '<table width="100%" style="border:1px solid black;">';
            $t .= '<tr><td>Error:Label could not connect</td>/tr></table>';
        }

        $ratesPlain = [];
        if (($response['status'] ?? '') === 'SUCCESS' && isset($response['rates']) && is_iterable($response['rates'])) {
            foreach ($response['rates'] as $rate) {
                if ($rate instanceof \Shippo_Object) {
                    // Shippo_Object stores data in a protected $_values property accessed via ArrayAccess.
                    // json_encode / (array) cast expose mangled protected keys, not the real data.
                    // Use ->keys() to enumerate field names and ArrayAccess to read each value.
                    $plain = [];
                    foreach ($rate->keys() as $k) {
                        $v = $rate[$k];
                        if ($v instanceof \Shippo_Object) {
                            $nested = [];
                            foreach ($v->keys() as $nk) {
                                $nested[$nk] = $v[$nk];
                            }
                            $plain[$k] = $nested;
                        } else {
                            $plain[$k] = $v;
                        }
                    }
                    $ratesPlain[] = $plain;
                } elseif (is_array($rate)) {
                    $ratesPlain[] = $rate;
                }
            }
        }

        $res = ['status' => $response['status'], 'response' => $response, 'rates' => $ratesPlain, 'tbl' => $t];

        // #region agent log
        try {
            $ratesRaw = $response['rates'] ?? null;
            $ratesCount = is_countable($ratesRaw) ? count($ratesRaw) : 0;
            $firstRateType = null;
            $firstRateIsShippoObj = false;
            $firstRateKeysViaAccessor = [];
            $firstRateCastKeys = [];
            $firstRateObjectId = null;
            $responseType = is_object($response) ? get_class($response) : gettype($response);
            $ratesType = is_object($ratesRaw) ? get_class($ratesRaw) : gettype($ratesRaw);
            if ($ratesCount > 0 && $ratesRaw) {
                foreach ($ratesRaw as $one) {
                    $firstRateType = is_object($one) ? get_class($one) : gettype($one);
                    $firstRateIsShippoObj = ($one instanceof \Shippo_Object);
                    if ($firstRateIsShippoObj) {
                        $firstRateKeysViaAccessor = method_exists($one, 'keys') ? $one->keys() : [];
                        $firstRateObjectId = $one['object_id'] ?? null;
                    }
                    if (is_object($one)) {
                        $castArr = (array) $one;
                        foreach ($castArr as $ck => $cv) {
                            $firstRateCastKeys[] = $ck;
                        }
                        $firstRateCastKeys = array_slice($firstRateCastKeys, 0, 20);
                    } elseif (is_array($one)) {
                        $firstRateCastKeys = array_slice(array_keys($one), 0, 20);
                        $firstRateObjectId = $one['object_id'] ?? null;
                    }
                    break;
                }
            }
            $firstPlain = $ratesPlain[0] ?? null;
            $payload = [
                'sessionId' => 'aa8310',
                'hypothesisId' => 'H6-H8',
                'location' => 'LabelController.php:findRates',
                'message' => 'shippo deep type inspection',
                'data' => [
                    'type' => $type,
                    'response_php_type' => $responseType,
                    'rates_php_type' => $ratesType,
                    'rates_count' => $ratesCount,
                    'first_rate_php_type' => $firstRateType,
                    'first_rate_is_shippo_obj' => $firstRateIsShippoObj,
                    'first_rate_keys_via_accessor' => $firstRateKeysViaAccessor,
                    'first_rate_cast_keys' => $firstRateCastKeys,
                    'first_rate_object_id_direct' => $firstRateObjectId,
                    'plain_count' => count($ratesPlain),
                    'first_plain_keys' => is_array($firstPlain) ? array_slice(array_keys($firstPlain), 0, 25) : [],
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
                'runId' => 'post-fix-3',
            ];
            @file_put_contents(base_path('debug-aa8310.log'), json_encode($payload)."\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            @file_put_contents(base_path('debug-aa8310.log'), json_encode(['sessionId'=>'aa8310','message'=>'log_error','data'=>['err'=>$e->getMessage()],'timestamp'=>(int)round(microtime(true)*1000)])."\n", FILE_APPEND | LOCK_EX);
        }
        // #endregion

        return response()->json($res);
    }


    public function purchaseLabel(Request $request, Shipping $shipping, Helper $helper)
    {
        $param = array();
        $param['oid'] = $request->oid; // RATE OBJECT ID : rates['object_id']
        $param['sub_oid'] = $request->suborder; // SUB-ORDER ID
        $recRes = null;

        $response = $shipping->purchase_shippo_label($param);

        // #region agent log
        try {
            $msgPlain = [];
            $rawMsgs = $response['messages'] ?? [];
            if (is_iterable($rawMsgs)) {
                foreach ($rawMsgs as $m) {
                    if ($m instanceof \Shippo_Object) {
                        $mp = []; foreach ($m->keys() as $mk) { $mp[$mk] = $m[$mk]; } $msgPlain[] = $mp;
                    } elseif (is_array($m)) { $msgPlain[] = $m; }
                    else { $msgPlain[] = $m; }
                }
            }
            @file_put_contents(base_path('debug-aa8310.log'), json_encode([
                'sessionId'=>'aa8310','hypothesisId'=>'H-P1','location'=>'LabelController.php:purchaseLabel',
                'message'=>'purchase response snapshot',
                'data'=>['rate_oid'=>$param['oid'],'status'=>$response['status']??null,'messages_plain'=>$msgPlain,'object_state'=>$response['object_state']??null,'label_url'=>$response['label_url']??null,'tracking_number'=>$response['tracking_number']??null],
                'timestamp'=>(int)round(microtime(true)*1000),'runId'=>'post-fix-5',
            ])."\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            @file_put_contents(base_path('debug-aa8310.log'), json_encode(['sessionId'=>'aa8310','message'=>'purchase_log_error','data'=>['err'=>$e->getMessage()],'timestamp'=>(int)round(microtime(true)*1000)])."\n", FILE_APPEND | LOCK_EX);
        }
        // #endregion

        $t = '';
        $msgArr = [];
        if ($response['status'] == "SUCCESS") {
            $msgArr['rate_object_id'] = $response['object_id'];
            $msgArr['label_url'] = $response['label_url'];
            $msgArr['tracking_number'] = $response['tracking_number'];
            $msgArr['tracking_url_provider'] = $response['tracking_url_provider'];

            $t .= '<table class="table mt-3" width="100%">
                            <tr>
                                <td>Box Rate Object ID: </td>
                                <td> #' . $response['object_id'] . ' </td>
                            </tr>
                            <tr>
                                <td>Box Label: </td>
                                <td> <a target="_blank" href="' . $response['label_url'] . '">Label URL</a> </td>
                            </tr>
                            <tr>
                                <td>Box Tracking No: </td>
                                <td>' . $response['tracking_number'] . ' </td>
                            </tr>
                            <tr>
                                <td>Box Tracking URL: </td>
                                <td> <a target="_blank" href="' . $response['tracking_url_provider'] . '">Tracking
                                        URL</a> </td>
                            </tr>
                        </table>';
            //     $t .= '<table width="100%" style="border:1px solid black;">';
            //     $t .= '<tr><td>Box Rate Object ID: </td> <td>' . $response['object_id'] . ' </td></tr>
            // <tr><td>Box Label: </td>        <td> <a target="_blank" href="' . $response['label_url'] . '">Label URL</a> </td></tr>
            // <tr><td>Box Tracking No: </td>  <td> ' . $response['tracking_number'] . ' </td></tr>
            // <tr><td>Box Tracking URL: </td> <td> <a target="_blank" href="' . $response['tracking_url_provider'] . '">Tracking URL</a> </td></tr>';
            //     $t .= '</table>';
        } else {
            $t .= '<table width="100%" style="border:1px solid black;">';
            $t .= '<tr><td>Error:Label could not create</td></tr></table>';
        }

        if ($response['status'] == 'SUCCESS') {

            if ($request->t == "emp") {
                $orderData = ['send_flag' => 1, 'send_labelresponse' => $response];

                // SEND MAIL TO EMPLOYEE, WHEN BOX LABEL CREATES - START
                $sendEmails = compemployees::where('id', $param['sub_oid'])->first();
                $company = null;
                if ($sendEmails->return_service == "Sell This Equipment") {
                    $company = Companies::where("id", $sendEmails->company_id)->first();
                }


                $compSettingsEmail = Companysettings::where("company_id", $sendEmails->parent_comp_id)->first();
                $sendEmails->companyData=$compSettingsEmail->company;
                $sendEmails->logo=$compSettingsEmail->logo;
                //if($sendEmails->rec_flag == 1) { $recRes = json_decode($response, true); }
                $recRes = json_decode($response, true);
                $trackingNo = ($recRes) ? $recRes['tracking_number'] : '';
                $trackingUrl = ($recRes) ? $recRes['tracking_url_provider'] : '';
                $emailTemplate = "empEmailAfterLabelcreate";
                $emailTemplateSubject = "Your $sendEmails->type_of_equip Return Box is on the Way by " . $sendEmails->receipient_name;
                $filePath = storage_path('app/public/orderMsg/' . $param['sub_oid'] . '.pdf');

                // echo $compSettingsEmail->logo;
                $logo = asset("storage/logoImage/$compSettingsEmail->logo");
                // echo '<img src="data:image/png;base64,$logo " alt="" width="200px">';
                // exit();

                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" => $sendEmails->emp_email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
                    "msg" => $msgArr,
                    "mailTemplate" => "mails.email_on_status_update",
                    "mailData" => $sendEmails,
                    "company" => $company,
                    "trackingNo" => $trackingNo,
                    "trackingUrl" => $trackingUrl,
                    "pdfPath" => $filePath,
                    "logo" => $logo
                ];

                // CUSTOM MSG PDF - START
                // INSTRUCTION:pdf template come from resources/views/pdf/sample.blade.php
                $data = [
                    'name' => $sendEmails->emp_first_name . ' ' . $sendEmails->emp_last_name,
                    'customMsg' => $sendEmails->custom_msg,
                    'data' => $sendEmails,
                    'logo' => $compSettingsEmail->logo
                ];
                $pdf = Pdf::loadView('pdf.sample', $data);
                $pdf->save($filePath);
                // CUSTOM MSG PDF - END


                $this->mailService->sendMail($emailData);
                // SEND MAIL TO EMPLOYEE, WHEN BOX LABEL CREATES - END

                // SEND SMS TO EMPLOYEE , WHEN BOX LABEL CREATES - START
                $smsdata = [
                    'to' => $sendEmails->emp_phone,
                    'message' => "Your $sendEmails->type_of_equip return box is on the Way by $sendEmails->receipient_name",
                    'company_id' => $sendEmails->company_id,
                    'user_id' => $sendEmails->user_id,
                    'order_id' => $sendEmails->order_id,
                ];
                $helper->sendSms($smsdata);
                // SEND SMS TO EMPLOYEE , WHEN BOX LABEL CREATES - END


                // SEND MAIL TO COMPANY, WHEN BOX LABEL CREATES - START
                $sendEmails = compemployees::where('id', $param['sub_oid'])->first();
                $sendEmails->companyData=$compSettingsEmail->company;
                $sendEmails->logo=$compSettingsEmail->logo;
                $emailTemplate = "compEmailAfterLabelcreate";
                $orderId = $param['sub_oid'];
                $emailTemplateSubject = $sendEmails->type_of_equip . " Retrieval Box for Order #$sendEmails->order_id-$sendEmails->id - Shipped to $sendEmails->emp_first_name";
                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" => $sendEmails->receipient_email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
                    "msg" => $msgArr,
                    "mailTemplate" => "mails.email_on_status_update",
                    "mailData" => $sendEmails,
                    "company" => $company,
                    "trackingNo" => $trackingNo,
                    "trackingUrl" => $trackingUrl,
                    "logo" => $compSettingsEmail->logo
                ];

                $this->mailService->sendMail($emailData);
                // SEND MAIL TO COMPANY, WHEN BOX LABEL CREATES - END

                // SEND SMS TO COMPANY , WHEN BOX LABEL CREATES - START
                $smsdata = [
                    'to' => $sendEmails->receipient_phone,
                    'message' => $sendEmails->type_of_equip . " retrieval box for order #$sendEmails->order_id is shipped to $sendEmails->emp_first_name",
                    'company_id' => $sendEmails->company_id,
                    'user_id' => $sendEmails->user_id,
                    'order_id' => $sendEmails->order_id,
                ];
                $helper->sendSms($smsdata);
                // SEND SMS TO EMPLOYEE , WHEN BOX LABEL CREATES - END

            } else if ($request->t == "dest") {
                $orderData = ['dest_flag' => 1, 'dest_labelresponse' => $response];
            } else {
                $orderData = ['rec_flag' => 1, 'receive_labelresponse' => $response];
            }

            compemployees::where('id', $param['sub_oid'])->update($orderData);


        }

        $responsePlain = $response;
        if ($response instanceof \Shippo_Object) {
            $responsePlain = [];
            foreach ($response->keys() as $k) {
                $v = $response[$k];
                if ($v instanceof \Shippo_Object) {
                    $nested = [];
                    foreach ($v->keys() as $nk) { $nested[$nk] = $v[$nk]; }
                    $responsePlain[$k] = $nested;
                } else {
                    $responsePlain[$k] = $v;
                }
            }
        }

        $res = ['status' => $response['status'], 'response' => $responsePlain, 'tbl' => $t];
        return response()->json($res);
    }




    public function receive_label_from_employee($order)
    {
        $from_address = array(
            'name' => $order->emp_first_name . ' ' . $order->emp_last_name,
            'street1' => $order->emp_add_1,
            'street2' => $order->emp_add_2,
            'city' => $order->emp_city,
            'state' => $order->emp_state,
            'zip' => $order->emp_pcode,
            'country' => 'US',
            'phone' => $order->emp_phone,
            'email' => $order->emp_email,
        );
        if ($order->receipient_person) {
            $receipient_person = $order->receipient_person . ' - ';
        } else {
            $receipient_person = "";
        }

        $to_address = array(
           'name' => $receipient_person . ' ' . $order->receipient_name.' -'." Order# :".$order->order_id.'-'.$order->id,
            'street1' => $order->receipient_add_1,
            'street2' => $order->receipient_add_2,
            'city' => $order->receipient_city,
            'state' => $order->receipient_state,
            'zip' => $order->receipient_zip,
            'country' => 'US',
            'phone' => $order->receipient_phone,
            'email' => $order->receipient_email,
        );



        $type = 'rec';
        $parcel = $this->get_parcel_details($order, $type);

        $insurance = '';
        if ($order->insurance_active == 1) {
            $insurance = array(
                "amount" => $order->insurance_amount,
                "currency" => "USD",
                "content" => $order->type_of_equip
                //  "provider" =>  env('LABEL_CARRIER')
            );
        }


        $a = ['from_address' => $from_address, 'to_address' => $to_address, 'parcel' => $parcel];
        if ($insurance != "") {
            $a['insurance'] = $insurance;
        }
        return $a;
    }



    public function send_label_to_employee($order)
    {
        $to_address = array(
            'name' => $order->emp_first_name . ' ' . $order->emp_last_name,
            'street1' => $order->emp_add_1,
            'street2' => $order->emp_add_2,
            'city' => $order->emp_city,
            'state' => $order->emp_state,
            'zip' => $order->emp_pcode,
            'country' => 'US',
            'phone' => $order->emp_phone,
            'email' => $order->emp_email,
        );

        $company = Companies::where('id', $order->company_id)->first();
        // $receipient_name = $company->receipient_name;
        // $receipient_add_1 = $company->company_add_1;
        // $receipient_add_2 = $company->company_add_2;
        // $receipient_city = $company->company_city;
        // $receipient_state = $company->company_state;
        // $receipient_zip = $company->company_zip;
        // $receipient_phone = $company->company_phone;
        // $receipient_email = $company->company_email;
        // BOX WILL USE RR ADDRESS IN FROM FIELD - START
        $phone = $this->helper->twillioPhoneFormat(env('REMOTE_COMPANY_PHONE'));
        $from_address = array(
            'name' => env('REMOTE_COMPANY_NAME'),
            'street1' => env('REMOTE_COMPANY_ADD1'),
            'street2' => env('REMOTE_COMPANY_ADD2'),
            'city' => env('REMOTE_COMPANY_CITY'),
            'state' => env('REMOTE_COMPANY_STATE'),
            'zip' => env('REMOTE_COMPANY_ZIP'),
            'country' => 'US',
            'phone' => $phone,
            'email' => env('REMOTE_COMPANY_EMAIL'),
        );
        // if ($order->return_service == "Return To Company") {
        //     $from_address = array(
        //         'name' => $order->receipient_name,
        //         'street1' => $order->receipient_add_1,
        //         'street2' => isset($order->receipient_add_2) ? $order->receipient_add_2 : '',
        //         'city' => $order->receipient_city,
        //         'state' => $order->receipient_state,
        //         'zip' => $order->receipient_zip,
        //         'country' => 'US',
        //         'phone' => $order->receipient_phone,
        //         'email' => $order->receipient_email,
        //     );
        // } else {
        //     $from_address = array(
        //         'name' => $company->company_name,
        //         'street1' => $company->company_add_1,
        //         'street2' => isset($company->company_add_2) ? $company->company_add_2 : '',
        //         'city' => $company->company_city,
        //         'state' => $company->company_state,
        //         'zip' => $company->company_zip,
        //         'country' => 'US',
        //         'phone' => $order->company_phone,
        //         'email' => $company->company_email,
        //     );
        // }



        // $from_address = array(
        //     'name' => 'Company',
        //     'street1' => '215 Clayton St.',
        //     'street2' => '215 Clayton St.',
        //     'city' => 'San Francisco',
        //     'state' => 'CA',
        //     'zip' => '94117',
        //     'country' => 'US',
        //     'phone' => '+1 555 341 9393',
        //     'email' => 'mr-hippo@goshipppo.com',
        // );

        // $to_address = array(
        //     'name' => 'Employee',
        //     'street1' => '2920 Zoo Drive',
        //     'street2' => '215 Clayton St.',
        //     'city' => 'San Diego',
        //     'state' => 'CA',
        //     'zip' => '92101',
        //     'country' => 'US',
        //     'phone' => '+1 555 341 9393',
        //     'email' => 'ms-hippo@goshipppo.com',
        // );
        // $parcel = array(
        //     'length'=> '5',
        //     'width'=> '5',
        //     'height'=> '5',
        //     'distance_unit'=> 'in',
        //     'weight'=> '2',
        //     'mass_unit'=> 'lb',
        // );

        $type = 'send';
        $parcel = $this->get_parcel_details($order, $type);
        $a = ['from_address' => $from_address, 'to_address' => $to_address, 'parcel' => $parcel];
        return $a;
    }



    public function receive_device_from_employee_datadestruction($order)
    {
        $from_address = array(
            'name' => $order->emp_first_name . ' ' . $order->emp_last_name,
            'street1' => $order->emp_add_1,
            'street2' => $order->emp_add_2,
            'city' => $order->emp_city,
            'state' => $order->emp_state,
            'zip' => $order->emp_pcode,
            'country' => 'US',
            'phone' => $order->emp_phone,
            'email' => $order->emp_email,
        );
        $to_address = array(
            'name' => env('REMOTE_COMPANY_NAME'),
            'street1' => env('REMOTE_COMPANY_ADD1'),
            'street2' => env('REMOTE_COMPANY_ADD2'),
            'city' => env('REMOTE_COMPANY_CITY'),
            'state' => env('REMOTE_COMPANY_STATE'),
            'zip' => env('REMOTE_COMPANY_ZIP'),
            'country' => 'US',
            'phone' => env('REMOTE_COMPANY_PHONE'),
            'email' => env('REMOTE_COMPANY_EMAIL')
        );



        $type = 'rec';
        $parcel = $this->get_parcel_details($order, $type);

        $insurance = '';
        if ($order->insurance_active == 1) {
            $insurance = array(
                "amount" => $order->insurance_amount,
                "currency" => "USD",
                "content" => $order->type_of_equip
                //  "provider" =>  env('LABEL_CARRIER')
            );
        }


        $a = ['from_address' => $from_address, 'to_address' => $to_address, 'parcel' => $parcel];
        if ($insurance != "") {
            $a['insurance'] = $insurance;
        }
        return $a;
    }



    /**
     * MODULE: LABEL CREATE FOR SENDING BOX
     * DESC:   DATA DESTRUCTION SERVICE , LABEL CREATE FOR SENDING BOX TO EMPLOYEE
     */
    public function return_device_after_datadestruction($order)
    {
        if ($order->return_additional_srv == 1) {
            // WHEN DD CHOOSE RETURN TO COMPANY THEN MUST FILL COMPANY DETAILS IN CREATE ORDER FORM.
            // WE WILL GET COMPANY DETAILS FROM THERE.
            $to_address = array(
                'name' => $order->receipient_name,
                'street1' => $order->receipient_add_1,
                'street2' => $order->receipient_add_2,
                'city' => $order->receipient_city,
                'state' => $order->receipient_state,
                'zip' => $order->receipient_zip,
                'country' => 'US',
                'phone' => $order->receipient_phone,
                'email' => $order->receipient_email
            );
        } else if ($order->return_additional_srv == 2) {
            $newED = json_decode($order->new_emp_data, true);
            $to_address = array(
                'name' => $newED['newemp_first_name'] . ' ' . $newED['newemp_last_name'],
                'street1' => $newED['newemp_add_1'],
                'street2' => $newED['newemp_add_2'],
                'city' => $newED['newemp_city'],
                'state' => $newED['newemp_state'],
                'zip' => $newED['newemp_zip'],
                'country' => 'US',
                'phone' => $newED['newemp_phone'],
                'email' => $newED['newemp_email']
            );
        }

        $from_address = array(
            'name' => env('REMOTE_COMPANY_NAME'),
            'street1' => env('REMOTE_COMPANY_ADD1'),
            'street2' => env('REMOTE_COMPANY_ADD2'),
            'city' => env('REMOTE_COMPANY_CITY'),
            'state' => env('REMOTE_COMPANY_STATE'),
            'zip' => env('REMOTE_COMPANY_ZIP'),
            'country' => 'US',
            'phone' => env('REMOTE_COMPANY_PHONE'),
            'email' => env('REMOTE_COMPANY_EMAIL')
        );

        $type = 'rec'; // USE THIE TYPE IN DD, BCS NOW WE RETURN BACK DEVICE TO COMPANY/NEW EMP.
        $parcel = $this->get_parcel_details($order, $type);
        $insurance = '';
        if ($order->insurance_active == 1) {
            $insurance = array(
                "amount" => $order->insurance_amount,
                "currency" => "USD",
                "content" => $order->type_of_equip
            );
        }
        $a = ['from_address' => $from_address, 'to_address' => $to_address, 'parcel' => $parcel];
        if ($insurance != "") {
            $a['insurance'] = $insurance;
        }
        return $a;
    }


    /**
     * MODULE: LABEL CREATE FOR SENDING BOX
     * DESC:   DATA DESTRUCTION SERVICE , LABEL CREATE FOR SENDING BOX TO EMPLOYEE
     */
    public function send_box_to_employee_datadestruction($order)
    {
        $to_address = array(
            'name' => $order->emp_first_name . ' ' . $order->emp_last_name,
            'street1' => $order->emp_add_1,
            'street2' => $order->emp_add_2,
            'city' => $order->emp_city,
            'state' => $order->emp_state,
            'zip' => $order->emp_pcode,
            'country' => 'US',
            'phone' => $order->emp_phone,
            'email' => $order->emp_email,
        );

        $from_address = array(
            'name' => env('REMOTE_COMPANY_NAME'),
            'street1' => env('REMOTE_COMPANY_ADD1'),
            'street2' => env('REMOTE_COMPANY_ADD2'),
            'city' => env('REMOTE_COMPANY_CITY'),
            'state' => env('REMOTE_COMPANY_STATE'),
            'zip' => env('REMOTE_COMPANY_ZIP'),
            'country' => 'US',
            'phone' => env('REMOTE_COMPANY_PHONE'),
            'email' => env('REMOTE_COMPANY_EMAIL')
        );

        $type = 'send';
        $parcel = $this->get_parcel_details($order, $type);
        $a = ['from_address' => $from_address, 'to_address' => $to_address, 'parcel' => $parcel];
        return $a;
    }


    /**
     * MODULE: VALIDATE ADDRESS
     * DESC:   IF ADDRESS IS INVALID THEN ERROR WILL GENERATE
     */
    public function validateAddressbyShipo($param, $shipping, $type)
    {
        $t = '';
        try {
            $addresObj = $shipping->get_address_obj($param);
            $addressObj = json_decode($addresObj, true);

            if (isset($addresObj->is_complete) && $addresObj->is_complete == 1 && !isset($addressObj['__all__'][0])) {
                $address = $shipping->validate_shippo_address($addresObj->object_id);
                // exit();
            } else {

                // $addressObj = json_decode($addresObj, true);
                // print_r($addressObj['validation_results']);
                if (isset($addressObj['validation_results']['messages'][0]['text'])) {
                    $eRes = $addressObj['validation_results']['messages'][0]['text'];
                } else {
                    $eRes = "Invalid Response!";
                }

                if (isset($addressObj['__all__'])) {
                    $res = $addressObj['__all__'];
                } else {
                    $res = $eRes;
                }
                $t .= '<table width="100%" style="border:1px solid black;">';
                $t .= '<tr><td>Error(' . $type . '):' . $eRes . '</td></tr></table>';
                $res = [
                    'status' => 'fail',
                    'response' => $res,
                    'msg' => 'Invalid Address'
                    ,
                    'tbl' => $t
                ];
                // print_r($res);
                return response()->json($res);
            }

            // if(!is_null($address->validation_results->validation_results))
            // {
            //     $t .= '<table width="100%" style="border:1px solid black;">';
            //     $t .= '<tr><td>Error('.$type.'):'.$address->validation_results->validation_results->messages .'</td></tr></table>';
            //     $res = ['status' => 'fail', 'response' => $addresObj,
            //     'msg' => $address->validation_results->validation_results->messages , 'tbl' => $t];
            //     return response()->json($res);
            // }
        } catch (\Exception $e) {
            // print_r($e);
            $eRes = json_decode($e->httpBody, true);
            if (isset($eRes['__all__'][0])) {
                $errorVal = $eRes['__all__'][0];
            } else {
                $errorVal = 'Invalid Address';
            }
            $t .= '<table width="100%" style="border:1px solid black;">';
            $t .= '<tr><td>Error(' . $type . '):' . $errorVal . '</td></tr></table>';
            $res = ['status' => 'fail', 'response' => $errorVal, 'msg' => "Invalid Address", 'tbl' => $t];
            return response()->json($res);
        }

    }


    public function get_parcel_details($order, $type)
    {
        $parcel = [
            'shippo_parcel_desktop_device' => [
                "length" => "19",
                "width" => "21",
                "height" => "9",
                "distance_unit" => "in",
                "weight" => "20",
                "mass_unit" => "lb",
            ],
            'shippo_parcel_laptop_device' => [
                "length" => "19",
                "width" => "15",
                "height" => "5",
                "distance_unit" => "in",
                "weight" => "7",
                "mass_unit" => "lb",
            ],
            'shippo_parcel_monitor_device' => [
                "length" => "16",
                "width" => "20",
                "height" => "3",
                "distance_unit" => "in",
                "weight" => "15",
                "mass_unit" => "lb",
            ],
            'shippo_parcel_desktop_box' => [
                "length" => "19",
                "width" => "21",
                "height" => "9",
                "distance_unit" => "in",
                "weight" => "4",
                "mass_unit" => "lb",
            ],
            'shippo_parcel_laptop_box' => [
                "length" => "19",
                "width" => "15",
                "height" => "5",
                "distance_unit" => "in",
                "weight" => "2",
                "mass_unit" => "lb",
            ],
            'shippo_parcel_monitor_box' => [
                "length" => "16",
                "width" => "20",
                "height" => "3",
                "distance_unit" => "in",
                "weight" => "2",
                "mass_unit" => "lb",
            ],

        ];

        if ($order->type_of_equip == "Laptop" && $type == "send") {
            $p = $parcel['shippo_parcel_laptop_box'];
        }
        if ($order->type_of_equip == "Laptop" && $type == "rec") {
            $p = $parcel['shippo_parcel_laptop_device'];
        }
        if ($order->type_of_equip == "Monitor" && $type == "send") {
            $p = $parcel['shippo_parcel_monitor_box'];
        }
        if ($order->type_of_equip == "Monitor" && $type == "rec") {
            $p = $parcel['shippo_parcel_monitor_device'];
        }

        return $p;
    }


}
