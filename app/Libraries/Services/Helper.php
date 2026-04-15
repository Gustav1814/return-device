<?php
namespace App\Libraries\Services;

// use Shippo;
// use Shippo_Address;
// use Shippo_Shipment;
// use Shippo_Transaction;
// use Shippo_Batch;
// use Shippo_Track;
// use Illuminate\Http\Request;

use App\Models\Companies;
use App\Models\Orders;
use App\Models\Systemsettings;
use App\Models\Coupon;
use App\Models\Transactions;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;
use Twilio\Rest\Client;
use App\Models\Smstrack;
use Exception;
use App\Models\User;
use App\Models\Emailonstatus;
use App\Models\Compemployees;
use DateTime;

class Helper
{

    public function __construct()
    {
    }

    /**
     *  MODULE: LABEL GENERATE
     *  DESCRIPTION: MAKE ADDRESSES FROM EMPLOYEE TO RECEIPIENT
     */
    public function data_for_device_label($order)
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
            'name' => $order->receipient_name,
            'street1' => $order->receipient_add_1,
            'street2' => $order->receipient_add_2,
            'city' => $order->receipient_city,
            'state' => $order->receipient_state,
            'zip' => $order->receipient_zip,
            'country' => 'US',
            'phone' => $order->receipient_phone,
            'email' => $order->receipient_email,
        );

        $a = ['from_address' => $from_address, 'to_address' => $to_address];
        return $a;
    }

    /**
     *  MODULE: LABEL GENERATE
     *  DESCRIPTION: MAKE ADDRESSES FROM COMPANY TO EMPLOYEE
     */
    public function data_for_employee_label($order)
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

        $from_address = array(
            'name' => $company->company_name,
            'street1' => $company->company_add_1,
            'street2' => $company->company_add_2,
            'city' => $company->company_city,
            'state' => $company->company_state,
            'zip' => $company->company_zip,
            'country' => 'US',
            'phone' => $company->company_phone,
            'email' => $company->company_email,
        );

        $a = ['from_address' => $from_address, 'to_address' => $to_address];
        return $a;
    }

    /**
     *  MODULE: ADDRESS VALIDATE
     *  DESCRIPTION: VALIDATE ADDRESS BY SHIPPO
     */
    public function validateAddressbyShipo($param, $shipping)
    {
        $t = '';
        try {
            $addresObj = $shipping->get_address_obj($param);
            $addressObj = json_decode($addresObj, true);
            //if ($addressObj['is_complete'] == 1) {
            if (isset($addresObj->is_complete) && $addresObj->is_complete == 1 && !isset($addressObj['__all__'][0])) {
                $address = $shipping->validate_shippo_address($addressObj['object_id']);
                if ($address['validation_results']['is_valid'] == 1) {
                    $res = ['status' => 'success', 'response' => 'OK', 'msg' => 'Valid Address'];
                } else {
                    // $r = json_decode($address['validation_results']['messages'],true);
                    $res = ['status' => 'fail', 'response' => $address['validation_results']['messages']['text'], 'msg' => 'Invalid Address'];
                }

            } else {

                if (isset($addressObj['validation_results']['messages'][0]['text'])) {
                    $eRes = $addressObj['validation_results']['messages'][0]['text'];
                } else {
                    $eRes = "Invalid Response!";
                }

                if (isset($addressObj['__all__'])) {
                    $res = $addressObj['__all__'][0];
                } else {
                    $res = $eRes;
                }

                $res = ['status' => 'fail', 'response' => $res, 'msg' => 'Invalid Address'];
            }
            return $res;
        } catch (\Exception $e) {

            $eRes = json_decode($e->httpBody, true);
            if (isset($eRes['__all__'][0])) {
                $errorVal = $eRes['__all__'][0];
            } else {
                $errorVal = 'Invalid Address';
            }
            $res = [
                'status' => 'fail',
                'response' => $errorVal,
                'msg' => 'Invalid Address'
            ];
            return $res;
        }

    }

    /**
     *  MODULE: LABEL GENERATE
     *  DESCRIPTION: GET PARCEL DETAILS ACCORDING TO TYPE
     */
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

    public function get_tracking_info($param)
    {
        $shipping = $param['shipping'];
        $objectId = $param['objectId'];
        $lblCarrier = $param['lblCarrier'];
        $trackingInfo = $shipping->get_trackingInfo($objectId, $lblCarrier);
        return $trackingInfo;
    }


    /**
     * MODULE: GET STATE SHORT NAME BY FULL NAME
     * DESCRIPTION: GET STATE AS SHORT NAME
     */
    public function getState($s)
    {
        $states = array(
            'Alabama' => 'AL',
            'Alaska' => 'AK',
            'Arizona' => 'AZ',
            'Arkansas' => 'AR',
            'California' => 'CA',
            'Colorado' => 'CO',
            'Connecticut' => 'CT',
            'Delaware' => 'DE',
            'District Of Columbia' => 'DC',
            'Florida' => 'FL',
            'Georgia' => 'GA',
            'Hawaii' => 'HI',
            'Idaho' => 'ID',
            'Illinois' => 'IL',
            'Indiana' => 'IN',
            'Iowa' => 'IA',
            'Kansas' => 'KS',
            'Kentucky' => 'KY',
            'Louisiana' => 'LA',
            'Maine' => 'ME',
            'Maryland' => 'MD',
            'Massachusetts' => 'MA',
            'Michigan' => 'MI',
            'Minnesota' => 'MN',
            'Mississippi' => 'MS',
            'Missouri' => 'MO',
            'Montana' => 'MT',
            'Nebraska' => 'NE',
            'Nevada' => 'NV',
            'New Hampshire' => 'NH',
            'New Jersey' => 'NJ',
            'New Mexico' => 'NM',
            'New York' => 'NY',
            'North Carolina' => 'NC',
            'North Dakota' => 'ND',
            'Ohio' => 'OH',
            'Oklahoma' => 'OK',
            'Oregon' => 'OR',
            'Pennsylvania' => 'PA',
            'Rhode Island' => 'RI',
            'South Carolina' => 'SC',
            'South Dakota' => 'SD',
            'Tennessee' => 'TN',
            'Texas' => 'TX',
            'Utah' => 'UT',
            'Vermont' => 'VT',
            'Virginia' => 'VA',
            'Washington' => 'WA',
            'West Virginia' => 'WV',
            'Wisconsin' => 'WI',
            'Wyoming' => 'WY',
            'Armed Forces (AA)' => 'AA',
            'Armed Forces (AE)' => 'AE',
            'Armed Forces (AP)' => 'AP'
        );

        return $states[$s];
    }

    public function get_insurance_amt($order, $type)
    {
        $insAmount = null;
        if ($type == "rec" && $order->insurance_active == 1) {
            $insAmount = $order->insurance_amount;
        }
        $insAmount = round(($insAmount * env("INSURANCE_RATE")) / 100, 2);
        return $insAmount;
    }



    public function get_discount($param)
    {
        $coupon = $param['coupon'];
        $orderId = $param['orderId'];
        $ins_amount = 0;
        $data = Orders::join('compemployees', 'compemployees.order_id', '=', 'orders.id')
            ->where('compemployees.parent_comp_id', Auth::user()->company_id)
            ->where('orders.status', 'pending')
            ->where('orders.id', $orderId)
            ->where('compemployees.soft_del', 0)
            ->get(['orders.*', 'compemployees.*']);

        $ins_amount = $this->getInsuranceAmount($data); // GET INSURANCE AMOUNT
        $ins_amountH = $ins_amount;

        // $settings = Systemsettings::latest('id')->first();
        // if ($settings) {
        //     $orderAmount = $settings->order_amount;
        // } else {
        //     $orderAmount = env('ORDER_AMT');
        // }
        $orderAmount = 0;
        $dd_amt = 0;
        $ddSrvStatus = 0;
        $dd_Srvamt = '';
        foreach ($data as $dt) {
            $orderAmount += $this->getDeviceAmount($dt->type_of_equip);

            if ($dt->return_additional_srv != null) {
                $ddSrvStatus = 1;
                if ($dt->return_additional_srv == 1) {
                    $dd_amt += env('DD_COMPANY');
                    $dd_Srvamt = env('DD_COMPANY');
                } else if ($dt->return_additional_srv == 2) {
                    $dd_amt += env('DD_NEW_EMP');
                    $dd_Srvamt = env('DD_NEW_EMP');
                }
            }
        }
        $coupon = Coupon::where("coupon", $coupon)->where("status", 1)->first();
        if (is_null($coupon)) {
            $d = ['status' => "fail", 'message' => 'Invalid Coupon'];
            return response()->json($d);
        }


        // SAME COMPANY USER CAN USE FULL FREE COUPON ONLY ONE TIME - START

        $comp_valid = $this->getCouponValiditybyCompany($data, $coupon);
        if ($comp_valid) {
            return response()->json($comp_valid);
        }
        // SAME COMPANY  CAN USE FULL FREE COUPON ONLY ONE TIME - END


        // USER CAN USE FULL FREE COUPON ONLY ONE TIME - START
        $cpnParams['userId'] = Auth::user()->id;
        $cpnParams['coupon'] = $coupon;

        $user_valid = $this->validateFullFeeCouponforOneTimeOnly($cpnParams);
        if ($user_valid) {
            return response()->json($user_valid);
        }
        // return response()->json($d);
        // USER CAN USE FULL FREE COUPON ONLY ONE TIME - START

        // USER CAN USE FULL FREE COUPON ONLY ONE TIME - START
        // if (Transactions::where("user_id", Auth::user()->id)->exists()) {
        //     $transactions = Transactions::where("user_id", Auth::user()->id)->get();
        //     foreach ($transactions as $transaction) {
        //         $transResponse = json_decode($transaction->trans_response, true);
        //         if (isset($transResponse['coupon'])) {
        //             $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
        //             if ($check_free_coupon_exit == 1 && $coupon->freeall == 1) {
        //                 $d = ['status' => "fail", 'message' => 'As a new user, you can create only one order using a 100% discount coupon.'];
        //                 return response()->json($d);
        //             }
        //         }
        //     }
        // }
        // USER CAN USE FULL FREE COUPON ONLY ONE TIME - START



        // IF COUPON IS 100% FREE
        // if($coupon->freeall == 1){ $ins_amount = 0; }


        if ($coupon->type == "amount") {   // AMOUNT DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                // $totalAmt = $orderAmount * count($data);
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $coupon->amt_or_perc has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                // $totalAmt = $orderAmount * count($data);
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc * count($data);
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
                // echo "total".$totalAmt;
            }

        } else {
            // PERCENTAGE DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                // $totalAmt = $orderAmount * count($data);
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc / 100;
                $discount = round($totalAmt * $discount, 2);
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                $discount = $orderAmount * ($coupon->amt_or_perc / 100);
                // $totalAmt = $orderAmount * count($data);
                $totalAmt = $orderAmount;
                // $discount = $discount * count($data);
                //    $discount = $discount * count($data);
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            }
        }

        // IF COUPON IS 100% FREE
        $message2 = '';
        if ($coupon->freeall == 1 && $ins_amountH == 0) {
            $message2 = ' You can create a free order by clicking on the "Order Now" button.';
            $message = "The discount of $discount has been applied. " . $message2;
        } else if ($coupon->freeall == 1 && $ins_amountH != 0) {
            $message2 = ' But insurance amount of ' . $ins_amountH . ' will be charged.';
            $message = "The discount of $discount has been applied. " . $message2;
        } else {
            //$message2 = ' An insurance amount of ' . $ins_amountH . ' will be charged even when a 100% discount coupon is applied.';
            $message = "The discount of $discount has been applied. " . $message2;
        }

        if ($ddSrvStatus == 1) {
            $message .= ' The data destruction service will be charged at an amount of $' . $dd_Srvamt;
        }

        $d = [
            'status' => "success",
            'message' => $message,
            'orderID' => $orderId,
            'coupon' => $coupon,
            'discount' => $discount,
            'totalAmt' => $totalAmt,
            'insurance' => $ins_amount,
            'dd_amt' => $dd_amt
        ];
        return response()->json($d);


    }

    /**
     * MODULE: PAYMENT
     * DESCRIPTION: GET TOTAL INSURANCE AMOUNT
     */
    public function getInsuranceAmount($data)
    {
        $ins_amount = 0;
        foreach ($data as $d) {

            $ins_amount += $d->insurance_amount;
        }
        $ins_amount = round(($ins_amount * env("INSURANCE_RATE")) / 100, 2);
        return $ins_amount;
    }

    public function validateFullFeeCouponforOneTimeOnly($cpnParams)
    {
        if (Transactions::where("user_id", $cpnParams['userId'])->exists()) {
            $transactions = Transactions::where("user_id", $cpnParams['userId'])->get();
            foreach ($transactions as $transaction) {
                $transResponse = json_decode($transaction->trans_response, true);
                if (isset($transResponse['coupon'])) {
                    $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
                    if ($check_free_coupon_exit == 1 && $cpnParams['coupon']->freeall == 1) {
                        $d = ['status' => "fail", 'message' => 'The 100% free coupon can be used only once!'];
                        return $d;
                    }
                }
            }
        }
    }

    public function getCouponValiditybyCompany($data, $coupon)
    {
        foreach ($data as $d) {
            if ($d['return_service'] == "Sell This Equipment") {
                $company = Companies::where('id', Auth::user()->company_id)->first();
                $companyName = $company->company_name;
            } else {
                $companyName = $d['receipient_name'];
            }
            $d = $this->chkCouponValidity($companyName, $coupon);

            if ($d) {
                if ($d['status'] == "fail") {
                    return $d;
                }
            }

        }
    }

    public function chkCouponValidity($companyName, $coupon)
    {
        $company = Companies::where('company_name', $companyName)->get();
        foreach ($company as $uid) {
            // echo $uid['user_id'];
            $cpnParams['userId'] = $uid['user_id'];
            $cpnParams['coupon'] = $coupon;

            $d = $this->validateFullFeeCouponforOneTimeOnly($cpnParams);
            if ($d) {
                if ($d['status'] == "fail") {
                    return $d;
                }
            }
        }
    }

    public function getDeviceAmount($equipment)
    {
        $settings = app('companySettings');
        $orderAmount =
            $equipmentType = strtolower($equipment);
        $settings = Systemsettings::where('equipment_type', ucfirst($equipmentType))
            ->where("company_id", $settings->company_id)
            ->first();
        if ($settings) {
            $orderAmount = $settings->order_amount;
        } else {
            $orderAmount = env('ORDER_AMT');
        }
        return $orderAmount;
    }
    public function getDeviceAmountAPI($equipment,$company_id)
    {
        $orderAmount =
            $equipmentType = strtolower($equipment);
        $settings = Systemsettings::where('equipment_type', ucfirst($equipmentType))
            ->where("company_id", $company_id)
            ->first();
        if ($settings) {
            $orderAmount = $settings->order_amount;
        } else {
            $orderAmount = env('ORDER_AMT');
        }
        return $orderAmount;
    }

     public function getThreeDaysPassed($getOrder)
    {

        $threeDaysPassed = 0;
        if (Emailonstatus::where('suborder_id', $getOrder->id)->exists()) {
            $Emailonstatus = Emailonstatus::where('suborder_id', $getOrder->id)->first();
            if ($Emailonstatus->box_del_emp == 1 && $Emailonstatus->device_del_start == 0) {
                $date1 = new DateTime($Emailonstatus->box_del_emp_dt);
                $date2 = new DateTime(date('Y-m-d'));
                $interval = $date1->diff($date2);
                if ($interval->days > 3) {
                    $threeDaysPassed = 1;
                }
            }
        }
        return $threeDaysPassed;
    }

    public function sendSms($smsdata)
    {
        if (!empty($smsdata['company_id'])) {
            $company_settings = Settings::where('company_id', $smsdata['company_id'])->first();
            // if (!isset($company_settings) || $company_settings->sms_flag == 1) {

            // DISABLE TEMPERORY CODE
            // if (isset($company_settings)) {
            //     try {
            //         if ($company_settings->sms_flag == 1) {
            //             if (env('TWILLIO_TEST') == true) {
            //                 $to = env('TWILLIO_TEST_NUMBER');
            //             } else {
            //                 if ($company_settings->sms_val) {
            //                     $to = $company_settings->sms_val;
            //                 } else {
            //                     $to = $smsdata['to'];
            //                 }
            //                 $to = $this->twillioPhoneFormat($to);
            //             }
            //             $sid = env('TWILIO_SID');
            //             $token = env('TWILIO_TOKEN');
            //             $twilio = new Client($sid, $token);
            //             $response = $twilio->messages->create(
            //                 $to, // to
            //                 [
            //                     'from' => env('TWILIO_FROM'),
            //                     'body' => $smsdata['message']
            //                 ]
            //             );
            //             $smsdata['to'] = $to;
            //             $this->smsTrack($smsdata);
            //         }




            //     } catch (Exception $e) {
            //         // Log the error
            //     }
            // }

        }

    }

    public function smsTrack($data)
    {
        $data = [
            'user_id' => $data['user_id'],
            'company_id' => $data['company_id'],
            'order_id' => $data['order_id'],
            'to' => $data['to'],
            'message' => $data['message']
        ];
        //   Smstrack::create($data);
    }

    public function twillioPhoneFormat($phone)
    {
        $pattern = '~[()-]~';
        $phone = preg_replace($pattern, '', $phone);
        $phone = str_replace('+1', '', $phone);
        $phone = str_replace(" ", '', $phone);
        return "+1". $phone;
    }


    public function getLabelDetailinPopup($getOrder, $threeDaysPassed)
    {
        $company = "Company";
        // IF FOR NORMAL ORDERS AND ELSE WILL USE FOR DATA DESTRUCTION ORDERS
        if ($getOrder->return_additional_srv == null || $getOrder->return_additional_srv == '') {
            $labelStatus = "In Progress";
            if ($getOrder->return_service == "Sell This Equipment") {
                $company = "Return Device: ";
            }

            if ($getOrder->send_label_status == 'TRANSIT' && $getOrder->receive_label_status == 'PRE_TRANSIT') {
                $labelStatus = $getOrder->type_of_equip . " box to Employee: Shipped";
            }
            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'PRE_TRANSIT' &&
                $threeDaysPassed == 0
            ) {
                $labelStatus = $getOrder->type_of_equip . " box to Employee: Delivered";
            }
            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'PRE_TRANSIT'
                && $threeDaysPassed == 1
            ) {
                // after 3 days working is need to implement
                $labelStatus = "Return " . $getOrder->type_of_equip . " box to $company: Not Shipped";
            }
            if ($getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'TRANSIT') {
                $labelStatus = "Return " . $getOrder->type_of_equip . " box to $company: Shipped";
            }
            if ($getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'DELIVERED') {
                $labelStatus = "Return " . $getOrder->type_of_equip . " box to $company: Delivered";
            }
        } else {
            // DATA DESTRUCTION CONDITION
            $labelStatus = "In Progress";
            $DD_company = "Return Device ";
            if ($getOrder->return_additional_srv == 1) {
                $company = "Company ";
            } else {
                $company = "New Employee ";
            }

            if ($getOrder->send_label_status == 'TRANSIT' && $getOrder->receive_label_status == 'PRE_TRANSIT') {
                $labelStatus = $getOrder->type_of_equip . " box to Employee: Shipped";
            }
            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'PRE_TRANSIT' &&
                $threeDaysPassed == 0
            ) {
                $labelStatus = $getOrder->type_of_equip . " box to Employee: Delivered";
            }
            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'PRE_TRANSIT'
                && $threeDaysPassed == 1
            ) {
                // after 3 days working is need to implement
                $labelStatus = "Send " . $getOrder->type_of_equip . " box to $DD_company : Not Shipped";
            }
            if ($getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'TRANSIT') {
                $labelStatus = "Send " . $getOrder->type_of_equip . " box to $DD_company: Shipped";
            }
            if ($getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'DELIVERED') {
                $labelStatus = "Send " . $getOrder->type_of_equip . " box to $DD_company: Delivered";
            }

            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'DELIVERED'
                && $getOrder->dest_label_status == 'TRANSIT'
            ) {
                $labelStatus = "Return " . $getOrder->type_of_equip . " box to $company: Shipped";
            }

            if (
                $getOrder->send_label_status == 'DELIVERED' && $getOrder->receive_label_status == 'DELIVERED'
                && $getOrder->dest_label_status == 'DELIVERED'
            ) {
                $labelStatus = "Return " . $getOrder->type_of_equip . " box to $company: Delivered";
            }
        }


        return $labelStatus;
    }

    public function getDataDestructionAmount($ddSrv)
    {
        $dd_amt = '';
        if ($ddSrv != null) {
            if ($ddSrv == 1) {
                $dd_amt = env('DD_COMPANY');
            } else if ($ddSrv == 2) {
                $dd_amt = env('DD_NEW_EMP');
            }
        }
        return $dd_amt;
    }


    public static function makeRequest(string $url, string $method = 'GET', array $params = [], array $headers = []): array
    {
        // $curl = curl_init();

        // // Set request method and parameters
        // if (strtoupper($method) === 'POST') {
        //     curl_setopt($curl, CURLOPT_POST, true);
        //     curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        // } else {
        //     $url .= '?' . http_build_query($params);
        // }

        // // Set headers
        // $defaultHeaders = [
        //     'Content-Type: application/x-www-form-urlencoded',
        // ];
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        // // Set URL and other options
        // curl_setopt($curl, CURLOPT_URL, $url);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing (not recommended for production).

        // print_r($curl);
        // exit();
        // // Execute the request
        // $response = $response = curl_exec($curl);
        // $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // // Handle errors
        // if ($response === false) {
        //     $error = curl_error($curl);
        //     curl_close($curl);

        //     return [
        //         'status' => false,
        //         'http_code' => $httpStatusCode,
        //         'error' => $error,
        //     ];
        // }

        // curl_close($curl);

        // // Return the response
        // return [
        //     'status' => true,
        //     'http_code' => $httpStatusCode,
        //     'content' => json_decode($response, true) ?? $response, // Try decoding JSON response
        // ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $defaultHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function generateSchema($param)
    {
        $currentPg = $param['currentPg'];
        $currentLink = $param['currentLink'];
        //$webpageName = $param['webpageName'];
        $webpageName = $param['webpageName'];
        $webpageDatePublished = $param['webpageDatePublished'];
        $webpageDateModified = $param['webpageDateModified'];
        $breadcrumbListListTwoName = $param['breadcrumbListListTwoName'];
        $webSiteName = $param['webSiteName'];
        $webSiteDescription = $param['webSiteDescription'];
        $webSitealternateName = $param['webSitealternateName'];
        $organizationName = $param['organizationName'];
        $organizationAlternateName = $param['organizationAlternateName'];
        $organizationCaption = $param['organizationCaption'];

        if (isset($param['ogImage'])) {
            $ogImage = $param['ogImage'];
        }
        if (isset($param['ogImageWidth'])) {
            $ogImageWidth = $param['ogImageWidth'];
        }
        if (isset($param['ogImageHeight'])) {
            $ogImageHeight = $param['ogImageHeight'];
        }
        //if(isset($param['ogImageType'])){$ogImageType = $param['ogImageType'];}

        $pageData = [
            'context' => 'https://schema.org',
            'webPage' => [
                '@type' => 'WebPage',
                '@id' => url($currentPg),
                'url' => url($currentPg),
                'name' => $webpageName,
                'isPartOf' => [
                    '@id' => "https://$currentLink/#website",
                ],
                'breadcrumb' => [
                    '@id' => url($currentPg) . "/#breadcrumb", //url("$currentPg/#breadcrumb"),
                ],
                'inLanguage' => 'en-US',
                'potentialAction' => [
                    [
                        '@type' => 'ReadAction',
                        'target' => [url($currentPg)],
                    ],
                ],
            ],
            'breadcrumbList' => [
                '@type' => 'BreadcrumbList',
                '@id' => url($currentPg) . "/#breadcrumb",
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => url("/"),
                    ]
                ],
            ],
            'webSite' => [
                '@type' => 'WebSite',
                '@id' => "https://$currentLink/#website",
                'url' => "https://$currentLink/",
                'name' => $webSiteName,
                'description' => $webSiteDescription,
                'publisher' => [
                    '@id' => "https://$currentLink/#organization",
                ],
                'alternateName' => $webSitealternateName,
                'potentialAction' => [
                    [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => "https://$currentLink/?s={search_term_string}",
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                'inLanguage' => 'en-US',
            ],
            'organization' => [
                '@type' => 'Organization',
                '@id' => "https://$currentLink/#organization",
                'name' => $organizationName,
                'alternateName' => $organizationAlternateName,
                'url' => "https://$currentLink/",
                'logo' => [
                    '@type' => 'ImageObject',
                    '@id' => "https://$currentLink/#/schema/logo/image/",
                    'url' => $ogImage ?? asset('theme/img/rr-icon.png'),
                    'contentUrl' => $ogImage ?? asset('theme/img/rr-icon.png'),
                    'width' => $ogImageWidth ?? 512,
                    'height' => $ogImageHeight ?? 512,
                    'caption' => $organizationCaption,
                ],
                'image' => [
                    '@id' => "https://$currentLink/#/schema/logo/image/",
                ],
            ],
        ];


        if ($webpageDatePublished != "") {
            $pageData['webPage']['datePublished'] = $webpageDatePublished;
        }
        if ($webpageDateModified != "") {
            $pageData['webPage']['dateModified'] = $webpageDateModified;
        }

        if ($breadcrumbListListTwoName != "") {
            $bc['@type'] = 'ListItem';
            $bc['position'] = 2;
            $bc['name'] = $breadcrumbListListTwoName;
            $bc['item'] = url("$currentPg/");
            $pageData['breadcrumbList']['itemListElement'][1] = $bc;
        }

        return $pageData;
    }

    public function generateMetaTags($param)
    {
        $title = $param['title'];
        $description = $param['description'];
        $canonicalUrl = $param['canonicalUrl'];
        $ogTitle = $param['ogTitle'];
        $ogSiteName = $param['ogSiteName'];
        if (isset($param['ogModifiedTime'])) {
            $ogModifiedTime = $param['ogModifiedTime'];
        }

        $ogTitle = $param['ogTitle'];
        $ogType = $param['ogType'];
        $ogLocale = $param['ogLocale'];
        $ogDescription = $param['ogDescription'];

        if (isset($param['ogImage'])) {
            $ogImage = $param['ogImage'];
        }
        if (isset($param['ogImageWidth'])) {
            $ogImageWidth = $param['ogImageWidth'];
        }
        if (isset($param['ogImageHeight'])) {
            $ogImageHeight = $param['ogImageHeight'];
        }
        if (isset($param['ogImageType'])) {
            $ogImageType = $param['ogImageType'];
        }

        $metadata = [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'og' => [
                'locale' => $ogLocale,
                'type' => $ogType,
                'title' => $ogTitle,
                'description' => $ogDescription,
                'url' => $canonicalUrl,
                'site_name' => $ogSiteName,
                //'modified_time' => $ogModifiedTime,
                'image' => [
                    'url' => $ogImage ?? asset('theme/img/rr-icon.png'),
                    'width' => $ogImageWidth ?? '512',
                    'height' => $ogImageHeight ?? '512',
                    'type' => $ogImageType ?? 'image/png',
                ],
            ],
            'twitter' => [
                'card' => 'summary_large_image',
            ],
        ];

        if (isset($ogModifiedTime)) {
            $metadata['og']['modified_time'] = $ogModifiedTime;
        } else {
            $metadata['og']['modified_time'] = '2024-06-19T15:19:07+00:00';
        }

        return $metadata;
    }


    public function validateFrmRequest($param)
    {
        $res = '';
        $request = $param['request'];
        $origin = $request->header('Origin');
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST']; // or $_SERVER['SERVER_NAME']
        $allowedOrigin = $protocol . '://' . $domain;
        if (trim($origin) !== trim($allowedOrigin)) {
            $res = [
                'status' => 'error',
                'message' => 'Cross-origin request is not allowed'
            ];
        } else {
            $res = [
                'status' => 'success',
                'message' => 'Cross-origin request is ok'
            ];
        }
        return $res;
    }


    public function getCurrentCompany()
    {
        $subDomain = explode(env('CURR_DOMAIN'), $_SERVER['SERVER_NAME'])[0];
        $company = Companies::where("company_domain", $subDomain)->first();
        return $company;
    }

    // public function getAdminSettings()
    // {
    //     $allowStatus = "no";
    //     $subDomain = explode(env('CURR_DOMAIN'), $_SERVER['SERVER_NAME'])[0];
    //     if (Auth::check()) {
    //         if (Auth::user()->role == "SUPER_ADMIN") {
    //             $allowStatus = "yes";
    //         }
    //     }

    //     $query = User::join('companies', 'users.company_id', '=', 'companies.id')
    //         ->select('users.*', 'companies.company_name as company_name')
    //         //->where('company_domain',$subDomain)
    //         ->where('status', "active");
    //     // ->first();
    // }


    /**
     * MODULE: PAYMENT
     * DESCRIPTION: GET TOTAL INSURANCE AMOUNT
     */
    public function getInsuranceAmountSingleorder($d)
    {
        $ins_amount = 0;
        if (isset($d->insurance_amount))
            $ins_amount += $d->insurance_amount;

        $ins_amount = round(($ins_amount * env("INSURANCE_RATE")) / 100, 2);
        return $ins_amount;
    }
    public function getCommissionAndCostAmount($companyId)
    {
        $orderAmount=Compemployees::where('company_id',$companyId)->where('soft_del', '=', 0)->where('send_flag',1)->where('rec_flag',1)->select('type_of_equip')->get()->toArray();
        $deviceAmount=0;
        $commissionAmount=0;
        foreach ($orderAmount as $value) {
            $priceSettings = Systemsettings::where('company_id', env('RR_COMPANY_ID'))->where('equipment_type',$value['type_of_equip'])->first();
            $deviceAmount+=$priceSettings->order_amount;
            $priceSettingsCompany = Systemsettings::where('company_id', $companyId)->where('equipment_type',$value['type_of_equip'])->first();
            $amount=$priceSettingsCompany->order_amount-$priceSettings->order_amount;
            $commissionAmount+=$amount;
        }
        return [$deviceAmount,$commissionAmount];
    }


} // END OF CLASS
