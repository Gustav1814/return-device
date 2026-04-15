<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\Services\Helper;
use App\Libraries\Services\MailService;
use App\Libraries\Services\Paypal;
use App\Models\Companies;
use App\Models\Compemployees;
use App\Models\Emailonstatus;
use App\Models\Orders;
use App\Models\Systemsettings;
use App\Models\Transactions;
use App\Models\User;
use DateTime;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    protected $mailService;

    protected $helper;

    public function __construct(MailService $mailService, Helper $helper)
    {
        $this->mailService = $mailService;
        $this->helper = $helper;
    }

    /**
     * MODULE: API FOR GETTING ORDER DETAILS,
     * DESCRIPTION: GET DETAILS,IF ORDER ID EXIST THEN SPECIFIC ORDER DATA FETCH OTHERWISE ALL RELATED DATA WILL FETCH
     */
    public function deviceReturns(Request $request)
    {
        $oid = $request->oid;
        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }

        if (is_null($token)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                'response_code' => 401,
            ], 401);
        }

        $userChk = User::where('api_key', $token)->first();
        if (is_null($userChk)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                'response_code' => 401,
            ], 401);
        } else {

            if ($oid) {
                $userChk->company_id;
                if (strpos($oid, '-') !== false) {
                    $oids = explode('-', $oid);
                    $oid = $oids[1];
                }
                $data = Orders::join('compemployees', 'compemployees.order_id', '=', 'orders.id')->where('compemployees.id', $oid)->where('compemployees.company_id', $userChk->company_id)->get(['orders.status', 'compemployees.*']);
                if (! isset($data[0])) {
                    return response()->json([
                        'message' => 'Data not found!',
                        'status' => 'Fail',
                        'response_code' => 400,
                    ], 400);
                }
            } else {
                $data = Orders::join('compemployees', 'compemployees.order_id', '=', 'orders.id')->where('compemployees.company_id', $userChk->company_id)->select('orders.status', 'compemployees.*')->orderBy('compemployees.id', 'desc')->paginate(10);
                if (! isset($data[0])) {
                    return response()->json([
                        'message' => 'Data not found!',
                        'status' => 'Fail',
                        'response_code' => 400,
                    ], 400);
                }
            }

            $data = $this->getOrders($data);

            return response()->json($data);
        }
    }

    /**
     * MODULE: API FOR GETTING ORDER DETAILS,
     * DESCRIPTION: DATA FOR CREATING RESPONSE OF ORDER DETAILS
     */
    public function getOrders($data)
    {
        // print_r($data);
        $orderData = [];
        // $orderData['next'] = $data->nextPageUrl();
        // $orderData['previous'] = $data->previousPageUrl();
        $orderData['next'] = method_exists($data, 'nextPageUrl') ? $data->nextPageUrl() : null;
        $orderData['previous'] = method_exists($data, 'previousPageUrl') ? $data->previousPageUrl() : null;

        foreach ($data as $i => $val) {
            $labelStatus = 'In Progress';
            $threeDaysPassed = $this->helper->getThreeDaysPassed($val);
            if ($val->status == 'pending') {
                $labelStatus = 'Proceed to Payment';
            } else {
                $labelStatus = $this->helper->getLabelDetailinPopup($val, $threeDaysPassed);
            }

            // ORDER TYPE - START
            if ($val->return_service == 'Return To Company' && $val->return_additional_srv == null) {
                $returnSrvSt = 'Return To Company';
            } elseif ($val->return_service != 'Return To Company' && $val->return_additional_srv == null) {
                $returnSrvSt = 'Recycle with Data Destruction';
            } elseif ($val->return_additional_srv == 1) {
                $returnSrvSt = 'Destruction-Return to Company';
            } elseif ($val->return_additional_srv == 2) {
                $returnSrvSt = 'Destruction-Send to New Employee';
            }
            // ORDER TYPE - END
            $orderData['results'][$i]['order_id'] = $val->order_id.'-'.$val->id;
            $orderData['results'][$i]['payment_status'] = ucfirst($val->status);
            $orderData['results'][$i]['order_status'] = $labelStatus;
            $orderData['results'][$i]['return_service'] = $returnSrvSt;
            $orderData['results'][$i]['order_amt'] = $val->order_amt;
            $orderData['results'][$i]['insurance_amount'] = $this->helper->getInsuranceAmount($data);
            $orderData['results'][$i]['custom_msg'] = $val->custom_msg;
            $dateC = new DateTime($val->created_at);
            $dateC = $dateC->format('m-d-Y');

            $orderData['results'][$i]['order_date'] = $dateC;

            // EMPLOYEE INFO
            $orderData['results'][$i]['employee_info']['email'] = $val->emp_email;
            $orderData['results'][$i]['employee_info']['name'] = $val->emp_first_name.' '.$val->emp_last_name;
            $orderData['results'][$i]['employee_info']['address_line_1'] = $val->emp_add_1;
            $orderData['results'][$i]['employee_info']['address_line_2'] = $val->emp_add_2;
            $orderData['results'][$i]['employee_info']['city'] = $val->emp_city;
            $orderData['results'][$i]['employee_info']['state'] = $val->emp_state;
            $orderData['results'][$i]['employee_info']['zip'] = $val->emp_pcode;
            $orderData['results'][$i]['employee_info']['phone'] = $val->emp_phone;

            // COMPANY INFO
            $orderData['results'][$i]['company_info']['email'] = $val->receipient_email;
            $orderData['results'][$i]['company_info']['name'] = $val->receipient_name;
            $orderData['results'][$i]['company_info']['address_line_1'] = $val->receipient_add_1;
            $orderData['results'][$i]['company_info']['address_line_2'] = $val->receipient_add_2;
            $orderData['results'][$i]['company_info']['city'] = $val->receipient_city;
            $orderData['results'][$i]['company_info']['state'] = $val->receipient_state;
            $orderData['results'][$i]['company_info']['zip'] = $val->receipient_zip;
            $orderData['results'][$i]['company_info']['phone'] = $val->receipient_phone;

            $orderData['results'][$i]['company_info']['receipient_name'] = $val->receipient_person;

            // SHIPMENT INFO
            if ($val->send_label_status == 0) {
                $sendStatus = '---';
            } else {
                $sendStatus = $val->send_label_status;
            }
            if ($val->receive_label_status == 0) {
                $recStatus = '---';
            } else {
                $recStatus = $val->receive_label_status;
            }

            $orderData['results'][$i]['shipments']['device_type'] = ($val->type_of_equip == 'Monitor_27' || $val->type_of_equip == 'Monitor') ? 'Monitor'.$this->getMonitorSize($val->type_of_equip) : (($val->type_of_equip == 'Laptop_14' || $val->type_of_equip == 'Laptop') ? 'Laptop'.$this->getLaptopSize($val->type_of_equip) : $val->type_of_equip);
            $orderData['results'][$i]['shipments']['send_status'] = $sendStatus;
            $orderData['results'][$i]['shipments']['return_status'] = $recStatus;

            // LABEL WORK - START
            $getOrder = $val;
            // FOR FINDING THREE DAYS PASSED TO DELIVER BOX - START
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
            // FOR FINDING THREE DAYS PASSED TO DELIVER BOX - END
            $labelStatus = 'In Progress';
            if ($getOrder->status == 'pending') {
                $labelStatus = 'Proceed to Payment';
            } else {
                $labelStatus = $this->helper->getLabelDetailinPopup($getOrder, $threeDaysPassed);
            }
            // LABEL WORK - END
            $orderData['results'][$i]['shipments']['tracking_status'] = $labelStatus;

        }

        return $orderData;
    }

    /**
     * MODULE: API FOR CREATE ORDER
     * DESCRIPTION: CREATE ORDER THROUGH API
     */
    public function createOrder(Request $request, Helper $helper)
    {

        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }
        // if ($_SERVER['SERVER_NAME'] == '54.202.232.27') {
        //     $token = 'r9QN7meBd9XlfdOIQQ0iVO8MJlb8rZM7G1eq89aVEjC6cS7p2AQPeQuGMM2C05Y4aHhfDQ8lJl8POuNaGiby6hn39CLjQKxRWRCIFPX1fSeYbs1s4SjZvBZb7122OGhdiyL8yqPp2Yssbphe3gWKTZ';
        // }
        if (is_null($token)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                'response_code' => 401,
            ], 401);
        }

        $user = User::where('api_key', $token)->first();
        if (is_null($user)) {
            return response()->json(
                [
                    'accessToken' => null,
                    'user' => null,
                    'message' => 'You are not authorized to access API!',
                    'status' => 'Fail',
                    'response_code' => 400,
                ],
                400
            );
        }

        // IF 25 PENDING ORDERS EXIST THEN THIS CHECK WILL APPLY - START
        $chkPendingOrders = Orders::join('compemployees', 'orders.id', '=', 'compemployees.order_id')
            ->where('orders.status', 'pending')
            ->where('compemployees.soft_del', 0)
            ->where('compemployees.company_id', $user->company_id)
            ->count();
        if ($chkPendingOrders >= 25) {
            return response()->json(
                [
                    'message' => 'Limit is 25 pending orders. Please delete existing ones before adding more',
                    'status' => 'Fail',
                    'response_code' => 429,
                ],
                429
            );
        }
        // IF 25 PENDING ORDERS EXIST THEN THIS CHECK WILL APPLY - START

        foreach ($request->orders as $order) {
            if (
                (! isset($order['type_of_equipment']) || $order['type_of_equipment'] == '')
                || (! isset($order['order_type']) || $order['order_type'] == '')
                || (! isset($order['employee_info']['phone']) || $order['employee_info']['phone'] == '')
                || (! isset($order['employee_info']['name']) || $order['employee_info']['name'] == '')
                || (! isset($order['employee_info']['email']) || $order['employee_info']['email'] == '')
                || (! isset($order['employee_info']['address_line_1']) || $order['employee_info']['address_line_1'] == '')
                || (! isset($order['employee_info']['address_city']) || $order['employee_info']['address_city'] == '')
                || (! isset($order['employee_info']['address_state']) || $order['employee_info']['address_state'] == '')
                || (! isset($order['employee_info']['address_zip']) || $order['employee_info']['address_zip'] == '')

                || (! isset($order['company_info']['return_person_name']) || $order['company_info']['return_person_name'] == '')
                || (! isset($order['company_info']['return_company_name']) || $order['company_info']['return_company_name'] == '')
                || (! isset($order['company_info']['email']) || $order['company_info']['email'] == '')
                || (! isset($order['company_info']['phone']) || $order['company_info']['phone'] == '')
                || (! isset($order['company_info']['return_address_line_1']) || $order['company_info']['return_address_line_1'] == '')
                || (! isset($order['company_info']['return_address_city']) || $order['company_info']['return_address_city'] == '')
                || (! isset($order['company_info']['return_address_state']) || $order['company_info']['return_address_state'] == '')
                || (! isset($order['company_info']['return_address_zip']) || $order['company_info']['return_address_zip'] == '')
            ) {
                return response()->json(
                    [
                        'message' => 'Missing field!',
                        'status' => 'Fail',
                        'response_code' => 400,
                    ],
                    400
                );

            }

            // VALIDATE ORDER TYPE IF SENDING ADDITIONAL SERVICE - START
            if ((isset($order['return_add_srv']) && ($order['return_add_srv'] == 2 || $order['return_add_srv'] == 1))) {
                if (strtolower($order['order_type']) != 'return to company') {
                    return response()->json(
                        [
                            'message' => 'Must select order type as "Return To Company" for using any Additional Service',
                            'status' => 'Fail',
                            'response_code' => 400,
                        ],
                        400
                    );
                }
            }
            // VALIDATE ORDER TYPE IF SENDING ADDITIONAL SERVICE - END

            if (
                (isset($order['return_add_srv']) && $order['return_add_srv'] == 2)
            ) {
                if (! isset($order['new_employee_info']['phone']) || $order['new_employee_info']['phone'] == ''
                || ! isset($order['new_employee_info']['first_name']) || $order['new_employee_info']['last_name'] == ''
                || ! isset($order['new_employee_info']['email']) || $order['new_employee_info']['email'] == ''
                || ! isset($order['new_employee_info']['address_line_1']) || $order['new_employee_info']['address_line_1'] == ''
                || ! isset($order['new_employee_info']['address_city']) || $order['new_employee_info']['address_city'] == ''
                || ! isset($order['new_employee_info']['address_state']) || $order['new_employee_info']['address_state'] == ''
                || ! isset($order['new_employee_info']['address_zip']) || $order['new_employee_info']['address_zip'] == '') {
                    return response()->json(
                        [
                            'message' => 'Missing fields of new employee data!',
                            'status' => 'Fail',
                            'response_code' => 400,
                        ],
                        400
                    );
                }

            } // END OF IF CONDITION ($order['return_add_srv'])

            // INSURANCE AMOUNT VALIDATE - START
            if (isset($order['ins_active']) && $order['ins_active'] == 1) {
                if (isset($order['ins_amount']) && is_numeric($order['ins_amount']) && $order['ins_amount'] != 0) {
                } else {
                    return response()->json(
                        [
                            'message' => 'Must fill proper amount of insurance field',
                            'status' => 'Fail',
                            'response_code' => 400,
                        ],
                        400
                    );
                }
            }
            // $empCountry    = trim($order['employee_info']['address_country']);
            // $compCountry    = trim($order['company_info']['return_address_country']);
            // if($empCountry!=$compCountry)
            // {
            //     if(!isset($order['company_info']['ein_number'])  || $order['company_info']['ein_number'] == ""){
            //          return response()->json(
            //             [
            //                 'message' => 'Company EIN Number is required for International Shipping',
            //                 'status' => 'Fail',
            //                 'response_code' => 400
            //             ],
            //             400
            //         );
            //     }
            // }
            // if(isset($order['new_employee_info']['address_country'])  || $order['new_employee_info']['address_country'] != ""){
            //     $newEmpCountry    = trim($order['new_employee_info']['address_country']);
            //         if ($newEmpCountry != $compCountry) {
            //             if (!isset($order['company_info']['ein_number']) || $order['company_info']['ein_number'] == "") {
            //                 return response()->json(
            //                     [
            //                         'message' => 'Company EIN Number is required for International Shipping',
            //                         'status' => 'Fail',
            //                         'response_code' => 400
            //                     ],
            //                     400
            //                 );
            //             }
            //         }
            // }
            // INSURANCE AMOUNT VALIDATE - END

            // VALIDATED API REQUEST - START
            $validateRequest = $this->validateAPIrequest($order);
            if ($validateRequest['error'] == 'yes') {
                return response()->json(
                    [
                        'message' => $validateRequest['msg'],
                        'status' => 'Fail',
                        'response_code' => 400,
                    ],
                    400
                );
            }
            // VALIDATED API REQUEST - END

        } // END FOREACH

        // CREATE ORDER - START
        $orderData = ['company_id' => $user->company_id, 'status' => 'pending'];
        $order = Orders::create($orderData);
        // CREATE ORDER - END

        $company = Orders::where('id', $user->company_id)->first();

        $subOrderids = '';
        foreach ($request->orders as $emp) {

            // CREATE SUB ORDER - START
            $employeParam = [
                'validatedData' => $emp,
                'request' => $request,
                'user' => $user,
                'company' => $company,
                'order' => $order,
                'helper' => $helper,
            ];

            $employeeData = $this->getSuborderData($employeParam);
            $suborder = Compemployees::create($employeeData);
            // CREATE SUB ORDER - END

            $subOrderids .= $order->id.'-'.$suborder->id.',';
        }

        // NEW CODE - END

        return response()->json(
            [
                'order' => $subOrderids,
                'message' => 'Order has created!',
                'status' => 'Success',
                'response_code' => 200,
            ],
            200
        );
        // }
    }

    /**
     * MODULE: CREATE ORDER THROUGH API
     * DESCRIPTION: MAKE ORDER DATA FOR CREATING ORDER
     */
    public function getSuborderData($employeParam)
    {
        $validatedData = $employeParam['validatedData'];
        $request = $employeParam['request'];
        $user = $employeParam['user'];
        $company = $employeParam['company'];
        $order = $employeParam['order'];
        $helper = $employeParam['helper'];
        $employeeState = $validatedData['employee_info']['address_state'];
        $compState = $validatedData['company_info']['return_address_state'];
        $defaultLaptop = isset($validatedData['default_laptop']) ? $validatedData['default_laptop'] : true;
        // if (strlen($validatedData['employee_info']['address_state']) != 2) {
        //     $employeeState = $validatedData['employee_info']['address_state'];
        // } else {
        //     $employeeState = $validatedData['employee_info']['address_state'];
        // }
        // if (strlen($validatedData['company_info']['return_address_state']) != 2) {
        //     $compState = $helper->getState($validatedData['company_info']['return_address_state']);
        // } else {
        //     $compState = $validatedData['company_info']['return_address_state'];
        // }
        $values = [$validatedData['employee_info']['address_country'], $validatedData['company_info']['return_address_country']];
        if (isset($validatedData['return_add_srv']) && $validatedData['return_add_srv'] != null) {
            if ($validatedData['return_add_srv'] == 2) {
                $values[2] = $validatedData['new_employee_info']['address_country'];
            }
        }
        $world = true;
        if (in_array('US', $values) && in_array('CA', $values)) {
            $world = false;
        }
        $typeOfEquipment = 'Laptop';
        if (strtolower($validatedData['type_of_equipment']) == 'laptop' && $defaultLaptop == true) {
            $typeOfEquipment = 'Laptop';
        }
        if (strtolower($validatedData['type_of_equipment']) == 'laptop' && $defaultLaptop == false) {
            $typeOfEquipment = 'Laptop_14';
        }
        if (strtolower($validatedData['type_of_equipment']) == 'monitor') {
            $typeOfEquipment = 'Monitor';
        }
        if (strtolower($validatedData['type_of_equipment']) == 'monitor_27') {
            $typeOfEquipment = 'Monitor_27';
        }
        if (strtolower($validatedData['type_of_equipment']) == 'tablet') {
            $typeOfEquipment = 'Tablet';
        }
        if (strtolower($validatedData['type_of_equipment']) == 'cell phone') {
            $typeOfEquipment = 'Cell Phone';
        }
        $orderType = 'Return To Company';
        if (strtolower($validatedData['order_type']) == 'return to company') {
            $orderType = 'Return To Company';
        }
        if (strtolower($validatedData['order_type']) == 'sell this equipment' || strtolower($validatedData['order_type']) == 'recycle with data destruction') {
            $orderType = 'Sell This Equipment';
        }

        if (preg_match('/\s/', $validatedData['employee_info']['name'])) {
            $nameParts = explode(' ', trim($validatedData['employee_info']['name']));
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        } else {
            $firstName = $validatedData['employee_info']['name'];
            $lastName = '';
        }
        $orderAmount = $helper->getDeviceAmountAPI($typeOfEquipment, $user->company_id);
        $emp_address_2 = isset($validatedData['employee_info']['address_line_2']) ? $validatedData['employee_info']['address_line_2'] : '';
        $comp_address_2 = isset($validatedData['company_info']['return_address_line_2']) ? $validatedData['company_info']['return_address_line_2'] : '';
        $cust_msg = isset($validatedData['custom_msg']) ? $validatedData['custom_msg'] : '';

        $employeeData = [
            'emp_first_name' => $firstName,
            'emp_last_name' => $lastName,
            'emp_email' => $validatedData['employee_info']['email'],
            'emp_phone' => $validatedData['employee_info']['phone'],
            'emp_add_1' => $validatedData['employee_info']['address_line_1'],
            'emp_add_2' => $emp_address_2,
            'emp_city' => $validatedData['employee_info']['address_city'],
            'emp_state' => $employeeState,
            'emp_pcode' => $validatedData['employee_info']['address_zip'],
            'emp_country' => $validatedData['employee_info']['address_country'] ?? 'US',
            'return_service' => $orderType,
            'type_of_equip' => $typeOfEquipment,
            'company_id' => ($company) ? $company->id : $user->company_id,
            'parent_comp_id' => $user->company_id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'receipient_name' => $validatedData['company_info']['return_company_name'],
            'receipient_person' => $validatedData['company_info']['return_person_name'],
            'receipient_email' => $validatedData['company_info']['email'],
            'receipient_phone' => $validatedData['company_info']['phone'],
            'receipient_add_1' => $validatedData['company_info']['return_address_line_1'],
            'receipient_add_2' => $comp_address_2,
            'receipient_city' => $validatedData['company_info']['return_address_city'],
            'receipient_state' => $compState,
            'receipient_zip' => $validatedData['company_info']['return_address_zip'],
            'receipient_country' => $validatedData['company_info']['return_address_country'] ?? 'US',
            'send_flag' => 0,
            'rec_flag' => 0,
            'source' => 'API',
            'insurance_active' => isset($validatedData['ins_active']) ? $validatedData['ins_active'] : 0,
            'insurance_amount' => isset($validatedData['ins_amount']) ? $validatedData['ins_amount'] : null,
            'order_amt' => $orderAmount,
            'custom_msg' => $cust_msg,
            'ein_number' => isset($validatedData['company_info']['ein_number']) ? $validatedData['company_info']['ein_number'] : '',
        ];

        // NEW EMPLOYEE DATE DD ADDITIONAL SERVICE - START
        if (isset($validatedData['return_add_srv']) && $validatedData['return_add_srv'] != null) {
            $employeeData['return_additional_srv'] = $validatedData['return_add_srv'];
            if ($validatedData['return_add_srv'] == 2) {

                $newEmp['newemp_first_name'] = $validatedData['new_employee_info']['first_name'];
                $newEmp['newemp_last_name'] = $validatedData['new_employee_info']['last_name'];
                $newEmp['newemp_email'] = $validatedData['new_employee_info']['email'];
                $newEmp['newemp_phone'] = $validatedData['new_employee_info']['phone'];
                $newEmp['newemp_add_1'] = $validatedData['new_employee_info']['address_line_1'];
                $newEmp['newemp_add_2'] = $validatedData['new_employee_info']['address_line_2'];
                $newEmp['newemp_city'] = $validatedData['new_employee_info']['address_city'];
                $newEmp['newemp_state'] = $validatedData['new_employee_info']['address_state'];
                $newEmp['newemp_zip'] = $validatedData['new_employee_info']['address_zip'];
                $newEmp['new_emp_country'] = $validatedData['new_employee_info']['address_country'];
                if (isset($validatedData['new_employee_info']['newemp_msg'])) {
                    $newEmp['newemp_msg'] = $validatedData['new_employee_info']['newemp_msg'];
                }

                $newEmployeeState = $newEmp['newemp_state'];
                if (strlen($newEmployeeState) != 2) {
                    $newEmployeeState = $helper->getState($newEmployeeState);
                    $newEmp['newemp_state'] = $newEmployeeState;
                }
                $newEmp = preg_replace('/\s+/', ' ', $newEmp);
                $employeeData['new_emp_data'] = json_encode($newEmp);
            }
        }
        // NEW EMPLOYEE DATE DD ADDITIONAL SERVICE - END

        // SET SRC API - START
        if (isset($validatedData['service_name'])) {
            $employeeData['source'] = ucfirst(strtolower($validatedData['service_name']));
        } else {
            $employeeData['source'] = 'API';
        }
        // SET SRC API - END

        return $employeeData;
    }

    /**
     * MODULE: API FOR AUTHENTICATE
     * DESCRIPTION: AUTHENTICATE API KEY PROCESS
     */
    public function checkValidRequest(Request $request)
    {
        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }
        if (is_null($token)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                'response_code' => 401,
            ], 401);
        } else {

            $userChk = User::where('api_key', $token)->first();
            $phone = preg_replace('/\D/', '', $userChk->phone);

            return response()->json([
                'message' => 'Valid Key!',
                'email' => $userChk->email,
                'phone' => $phone,
                'status' => 'Success',
                'response_code' => 200,
            ]);
        }
    }

    public function validateUser($token)
    {
        $userChk = User::where('api_key', $token)->first();
        if (is_null($userChk)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                'response_code' => 401,
            ], 401);
        } else {
            return response()->json([
                'message' => 'Valid Key!',
                'status' => 'Success',
                'user' => $userChk,
                'response_code' => 200,
            ], 200);
        }
    }

    public function getCompanyDetails(Request $request)
    {
        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }

        $userChk = $this->validateUser($token);
        if ($userChk->original['status'] != 'Success') {
            return response()->json($userChk->original);
        } else {
            $data = Companies::where('id', $userChk->original['user']->company_id)
                ->select(
                    'companies.company_name',
                    'companies.company_email',
                    'companies.company_add_1 as address_1',
                    'companies.company_add_2 as address_2',
                    'companies.company_city as city',
                    'companies.company_state as state',
                    'companies.company_zip as zip',
                    DB::raw('DATE_FORMAT(companies.created_at, "%d-%b-%Y") as created_date')
                )->first();

            if (is_null($data)) {
                return response()->json(['message' => 'Data not found!', 'status' => 'Fail', 'response_code' => 400], 400);
            }

            return response()->json($data);
        }
    }

    public function getAllOrders(Request $request)
    {
        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }

        $perPage = env('API_PER_PAGE');
        $userChk = $this->validateUser($token);
        if ($userChk->original['status'] != 'Success') {
            return response()->json($userChk->original);
        } else {

            $data = Orders::join('compemployees', 'orders.id', '=', 'compemployees.order_id')
                ->select('orders.status', 'compemployees.*')
                // ->where('orders.status', 'completed')
                ->orderBy('compemployees.id', 'desc')
                ->where('compemployees.soft_del', 0)
                ->where('compemployees.company_id', $userChk->original['user']->company_id)
                // ->where('orders.status','pending')
                // ->where('compemployees.send_flag',1)
                // ->where('compemployees.rec_flag',1)
                ->cursorPaginate($perPage);

            if (! isset($data[0])) {
                return response()->json(['message' => 'Data not found!', 'status' => 'Fail', 'response_code' => 400]);
            }

            $data = $this->getOrders($data);

            return response()->json($data);
        }
    }

    // GET DEVICE LIST WITH PRICES
    public function getDevicePrices(Request $request)
    {
        $key = 'Authorization';
        $header = $request->header($key, '');
        $header = $request->header('Authorization');
        $token = '';
        if (is_null($header)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                "code" => 401,
                "custom_code" => 401
            ], 401);
        }
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }
        $user = User::where('api_key', $token)->first();
        if (is_null($user)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                "code" => 401,
                "custom_code" => 401
            ], 401);
        }
        $query = Systemsettings::query();
        if ($request->has('device') && $request->get('device')) {
            $query->whereRaw('LOWER(equipment_type) = ?', [$request->get('device')]);
        }

        $query->where('equipment_type', '!=', 'Laptop_14');
        $query->where('company_id', $user->company_id);
        $settings = $query->get();
        $jsonData = [];
        $shipping = false;
        if ($request->has('country') && $request->get('country')) {
            $country = $request->get('country');
            $shipping = $country == 'US' ? false : true;
        }
        foreach ($settings as $detail) {
            $jsonData[] = [
                'equipment_type' => $detail->equipment_type,
                'order_amount' => $shipping ? $detail->order_amount_ca : $detail->order_amount,
                'option_lbl' => $detail->equipment_type . ' ($' . ($shipping ? $detail->order_amount_ca : $detail->order_amount) . ')'
            ];
        }
        echo json_encode($jsonData);
    }

    public function validateAPIrequest($order)
    {
        $equipmentList = Systemsettings::select('equipment_type')->get();
        $equipmentArr = [];
        foreach ($equipmentList as $equipment) {
            array_push($equipmentArr, strtolower($equipment->equipment_type));
        }

        if (! in_array(strtolower($order['type_of_equipment']), $equipmentArr)) {
            return ['error' => 'yes', 'msg' => 'Must fill valid type of equipment!'];
        }
        $orderTypeArr = ['return to company', 'recycle with data destruction', 'sell this equipment'];
        if (! in_array(strtolower($order['order_type']), $orderTypeArr)) {
            return ['error' => 'yes', 'msg' => 'Must fill valid order type!'];
        }
        $empCountry = trim($order['employee_info']['address_country']);
        $empPhone = trim($order['employee_info']['phone']);
        $empPhoneLen = strlen($empPhone);
        if ($empPhoneLen < 5 || $empPhoneLen > 18) {
            return ['error' => 'yes', 'msg' => 'Do not enter fewer than 5 characters or more than 18 characters for the employee phone!'];
        }

        $empEmail = trim($order['employee_info']['email']);
        $empEmailLen = strlen($empEmail);
        if ($empEmailLen < 5 || $empEmailLen > 60) {
            return ['error' => 'yes', 'msg' => 'Do not enter fewer than 5 characters or more than 60 characters for the employee email!'];
        }

        $empName = trim($order['employee_info']['name']);
        $empNameLen = strlen($empName);
        if ($empNameLen < 5 || $empNameLen > 59) {
            return ['error' => 'yes', 'msg' => 'Do not enter fewer than 5 characters or more than 60 characters in employee name!'];
        }
        // if (!preg_match('/^[a-zA-Z0-9\s\-\.\(\)]+$/', $empName)) {
        //     return ["error" => "yes", "msg" => "Employee name contains invalid characters! Only letters, numbers, space, dash (-), dot (.) and brackets ( ) are allowed."];
        // }
        $empAdd1 = trim($order['employee_info']['address_line_1']);
        $empAdd1Len = strlen($empAdd1);
        if ($empAdd1Len < 3 || $empAdd1Len > 99) {
            return ['error' => 'yes', 'msg' => 'Do not enter fewer than 3 characters or more than 99 characters in employee Address Line 1!'];
        }

        $empAdd2 = trim($order['employee_info']['address_line_2']);
        $empAdd2Len = strlen($empAdd2);
        if ($empAdd2Len > 99) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 100 characters in employee Address Line 2!'];
        }

        $empCity = trim($order['employee_info']['address_city']);
        $empCityLen = strlen($empCity);
        if ($empCityLen > 39) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 40 characters in employee city!'];
        }

        $empState = trim($order['employee_info']['address_state']);
        $empStateLen = strlen($empState);
        if ($empStateLen > 2 && $empCountry == 'US') {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 2 characters in employee state!'];
        }

        $empZip = trim($order['employee_info']['address_zip']);
        $empZipLen = strlen($empZip);
        if ($empZipLen > 12) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 12 characters in employee zip!'];
        }
        $compCountry = trim($order['company_info']['return_address_country']);
        $compEmail = trim($order['company_info']['email']);
        $compEmailLen = strlen($compEmail);
        if ($compEmailLen > 119) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 120 characters in company email!'];
        }

        $compPhone = trim($order['company_info']['phone']);
        $compPhoneLen = strlen($compPhone);
        if ($compPhoneLen > 18) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 18 characters in company phone!'];
        }

        $compName = trim($order['company_info']['return_company_name']);
        $compNameLen = strlen($compName);
        if ($compNameLen > 119) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 120 characters in company name!'];
        }

        $compAdd1 = trim($order['company_info']['return_address_line_1']);
        $compAdd1Len = strlen($compAdd1);
        if ($compAdd1Len > 199) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 200 characters in company address 1!'];
        }

        $compAdd2 = trim($order['company_info']['return_address_line_2']);
        $compAdd2Len = strlen($compAdd2);
        if ($compAdd2Len > 199) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 200 characters in company address 2!'];
        }

        $compCity = trim($order['company_info']['return_address_city']);
        $compCityLen = strlen($compCity);
        if ($compCityLen > 49) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 50 characters in company city!'];
        }

        $compState = trim($order['company_info']['return_address_state']);
        $compStateLen = strlen($compState);
        if ($compStateLen > 2 && $compCountry == 'US') {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 2 characters in company state!'];
        }

        $compZip = trim($order['company_info']['return_address_zip']);
        $compZipLen = strlen($compZip);
        if ($compZipLen > 12) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 12 characters in company zip!'];
        }

        $compReceipName = trim($order['company_info']['return_person_name']);
        $compReceipNameLen = strlen($compReceipName);
        if ($compReceipNameLen > 24) {
            return ['error' => 'yes', 'msg' => 'Do not enter more than 25 characters in company recipient name!'];
        }

        //  if (!preg_match('/^[a-zA-Z0-9\s\-\.\(\)]+$/', $compReceipName)) {
        //     return ["error" => "yes", "msg" => "Company receipient name contains invalid characters! Only letters, numbers, space, dash (-), dot (.) and brackets ( ) are allowed."];
        // }
        return ['error' => 'no'];

    }

    public function getMonitorSize($device)
    {
        if ($device == 'Monitor_27') {
            return ' (24" to 27")';
        } elseif ($device == 'Monitor') {
            return ' (17" to 23")';
        } else {
            return '';
        }
    }

    public function getLaptopSize($device)
    {
        if ($device == 'Laptop_14') {
            return ' (11" to 14")';
        } else {
            return '';
        }
    }
} // END OF CLASS
