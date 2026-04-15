<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Companies;
use App\Models\Compemployees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Services\Helper;
use App\Libraries\Services\Paypal;
use App\Models\Coupon;
use App\Models\Transactions;
use App\Models\Orders;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Models\Companysettings;
use App\Models\Systemsettings;
use App\Libraries\Services\MailService;

class UserController extends Controller
{

    protected $helper;
    protected $paypal;
    protected $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    // public function __construct(Helper $helper, Paypal $paypal)
    // {
    //     $this->helper = $helper;
    //     $this->paypal = $paypal;
    // }


    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:15',
            'phone' => 'required',
            'company_name' => 'required|string',
            'company_domain' => 'required',
            // 'company_add1' => 'required|string',
            // 'company_city' => 'required|string',
            // 'company_state' => 'required|string',
            // 'company_zip' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        // User first (no company_id until company row exists — works on empty DB without seed data).
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => null,
            'parent_comp_id' => 0,
            'role' => 'ADMIN',
            'status' => 'active',
            'secret_code' => Str::random(100),
            'phone' => $request->phone,
        ]);

        $company = Companies::create([
            'company_name' => $request->company_name,
            'domain' => $request->company_domain,
            'company_domain' => explode('.', (string) $request->company_domain)[0],
            'parent_company' => '0',
            'user_id' => $user->id,
            'receipient_name' => $request->name,
            'company_email' => $request->email,
        ]);

        Companysettings::create([
            'company_id' => $company->id,
            'theme' => 'light',
            'btn_bg_color' => '#f37033',
            'btn_font_color' => '#ffffff',
        ]);

        foreach (
            [
                ['equipment_type' => 'Laptop', 'order_amount' => 77],
                ['equipment_type' => 'Monitor', 'order_amount' => 99],
            ] as $priceRow
        ) {
            Systemsettings::create([
                'company_id' => $company->id,
                'equipment_type' => $priceRow['equipment_type'],
                'order_amount' => $priceRow['order_amount'],
            ]);
        }

        $user->forceFill(['company_id' => $company->id])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user->fresh(),
        ], 200);
    }



    public function createSingleOrder(Request $request)
    {



        // $origin = $request->header('Origin');
        // $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        // $domain = $_SERVER['HTTP_HOST']; // or $_SERVER['SERVER_NAME']
        // $allowedOrigin = $protocol . '://' . $domain;
        // if (trim($origin) !== trim($allowedOrigin)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Cross-origin request is not allowed'
        //     ], 403); // 403 Forbidden status
        // }




        if ($request->fcpn == 1) {

            $validatedData = $request->validate([
                'email' => 'required',
                'phone' => 'required',
                'type_of_equipment' => 'required',
                'order_type' => 'required',
                'employee_first_name' => 'required',
                'employee_last_name' => 'required',
                'employee_email' => 'required',
                'employee_phone' => 'required',
                'employee_add_1' => 'required',
                'employee_city' => 'required',
                'employee_state' => 'required',
                'employee_zip' => 'required',
                'company_name' => 'required',
                'company_email' => 'required',
                'company_phone' => 'required',
                'company_add_1' => 'required',
                'company_city' => 'required',
                'company_state' => 'required',
                'company_zip' => 'required',
                'user_pkg' => 'required',
                'password' => ['min:8'],
                'comp_receip_name' => 'required'
            ]);
        } else {

            $validatedData = $request->validate([
                'email' => 'required',
                'phone' => 'required',
                'type_of_equipment' => 'required',
                'order_type' => 'required',
                'employee_first_name' => 'required',
                'employee_last_name' => 'required',
                'employee_email' => 'required',
                'employee_phone' => 'required',
                'employee_add_1' => 'required',
                'employee_city' => 'required',
                'employee_state' => 'required',
                'employee_zip' => 'required',
                'company_name' => 'required',
                'company_email' => 'required',
                'company_phone' => 'required',
                'company_add_1' => 'required',
                'company_city' => 'required',
                'company_state' => 'required',
                'company_zip' => 'required',
                'user_pkg' => 'required',
                'password' => ['min:8'],
                'billing_name' => 'required',
                'billing_cc_no' => 'required',
                'billing_cc_expiry' => 'required',
                'billing_cc_cvv' => 'required',
                'billing_amount' => 'required',
                'comp_receip_name' => 'required'
            ]);
        }





        if ($request->emp_custom_msg) {
            if (strlen($request->emp_custom_msg) > 1000) {
                return response()->json(
                    [
                        'message' => 'Not allowed more than 1000 characters in custom message of employee',
                        'status' => 0
                    ]
                );
            }
        }


        if ($request->fcpn != 1) {

            if (
                $request->email == "" || $request->phone == "" || $request->type_of_equipment == "" || $request->order_type == ""
                || $request->employee_first_name == ""
                || $request->employee_last_name == "" || $request->employee_email == "" || $request->employee_phone == "" || $request->employee_add_1 == ""
                || $request->employee_city == ""
                || $request->employee_state == "" || $request->employee_zip == "" || $request->company_name == "" || $request->company_email == ""
                || $request->company_phone == ""
                || $request->company_add_1 == "" || $request->company_city == "" || $request->company_state == ""
                || $request->company_zip == "" || $request->user_pkg == ""
                || $request->password == ""
                || $request->billing_name == "" || $request->billing_cc_no == ""
                || $request->billing_cc_expiry == "" || $request->billing_cc_cvv == ""
                || $request->billing_amount == ""
            ) {
                return response()->json(
                    [
                        'accessToken' => null,
                        'user' => null,
                        'message' => 'Missing field!',
                        'status' => 0
                    ]
                );

            }

            // $pwd = Str::random(10);
            // $request->password = $pwd;
            //////////////// START CREATE ORDER,USER AFTER SUCCESSFUL TRANSACTION

            // MAKE CC EXPIRY DATE A/C TO REQUIREMENT - START
            $cardExpiry = $request->billing_cc_expiry;
            $cardExpiry = explode("-", $cardExpiry);
            $expiryYear = substr($cardExpiry[0], 2, 2);
            if (strlen($cardExpiry[1]) == 1) {
                $cardExpireMonth = '0' . $cardExpiry[1];
            } else {
                $cardExpireMonth = $cardExpiry[1];
            }
            // MAKE CC EXPIRY DATE A/C TO REQUIREMENT - END

            // REMOVE DASH OF CC , A/C TO REQUIREMENT - START
            $ccNo = $request->billing_cc_no;
            $ccNo = str_replace("-", "", $ccNo);
            // REMOVE DASH OF CC , A/C TO REQUIREMENT - END
            if ($request->billing_amount == 0) {
                $d = [
                    'status' => "fail",
                    'message' => 'Invalid amount',
                    'amount' => $request->billing_amount
                ];
                return response()->json($d);
            }
        }




        // RETURN TO COMPANY - DATA DESTRUCTION PART - START
        $dd_amt = 0;
        if (isset($request->return_add_srv) && $request->return_add_srv != null) {
            $dd_amt = env('DD_COMPANY');
            if ($request->return_add_srv == 2) {
                $d = json_decode($request->new_emp_data, true);
                //print_r($d);
                if (
                    $d['newemp_first_name'] == "" || $d['newemp_last_name'] == ""
                    || $d['newemp_phone'] == "" || $d['newemp_add_1'] == ""
                    || $d['newemp_email'] == ""
                    || $d['newemp_phone'] == "" || $d['newemp_city'] == ""
                    || $d['newemp_state'] == "" || $d['newemp_zip'] == ""
                ) {

                    $d = [
                        'status' => "fail",
                        'message' => 'Must fill all details of new employee form',
                        'code' => 403
                    ];
                    return response()->json($d);
                }
                $dd_amt = env('DD_NEW_EMP');
            }
        }
        // RETURN TO COMPANY - DATA DESTRUCTION PART - END



        $amount = $this->helper->getDeviceAmount($validatedData['type_of_equipment']);
        if (isset($request->ins_amount)) {
            $insAmount = $request->ins_amount;
            $insAmount = round(($insAmount * env("INSURANCE_RATE")) / 100, 2);
        } else {
            $insAmount = 0;
        }
        $paymentAmount = $amount + $insAmount + $dd_amt;


        // COUPON - START
        $couponExist = 0;
        $validCpn = 1;

        // VALIDATE COUPON ON BASIS OF EMAIL - START
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Transactions::where("user_id", $user->id)->exists()) {
                $transactions = Transactions::where("user_id", $user->id)->get();
                foreach ($transactions as $transaction) {
                    $transResponse = json_decode($transaction->trans_response, true);
                    if (isset($transResponse['coupon'])) {
                        $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
                        if ($check_free_coupon_exit == 1) {
                            $validCpn = 0;
                        }
                    }
                }
            }
        }
        // VALIDATE COUPON ON BASIS OF EMAIL - END

        // VALIDATE COUPON ON BASIS OF COMPANY - START
        // if ($request->order_type != "Sell This Equipment") {
        if ($request->company_name != "Remote Retrieval") {
            $company = Companies::where('company_name', $request->company_name)->get();
            foreach ($company as $uid) {
                $cpnParams['userId'] = $uid['user_id'];
                $cpnParams['coupon'] = $request->cpn;
                if (Transactions::where("user_id", $uid['user_id'])->exists()) {
                    $transactions = Transactions::where("user_id", $uid['user_id'])->get();
                    foreach ($transactions as $transaction) {
                        $transResponse = json_decode($transaction->trans_response, true);
                        if (isset($transResponse['coupon'])) {
                            $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
                            // if ($check_free_coupon_exit == 1 && $cpnParams['coupon']->freeall == 1) {
                            if ($check_free_coupon_exit == 1) {
                                $validCpn = 0;
                            }
                        }
                    }
                }
            }
        }
        // VALIDATE COUPON ON BASIS OF COMPANY - END


        $discountObj = '';
        if (Coupon::where("coupon", $request->cpn)->exists() && $validCpn == 1) {
            $couponExist = 1;
            if ($request->cpn) {
                $param['coupon'] = $request->cpn;
                $param['amount'] = $paymentAmount;
                $param['insurance'] = $insAmount;
                $param['dd_amt'] = $dd_amt;

                // COUPON FOR FREE ORDER - START
                if ($request->fcpn == 1) {
                    $param['discount'] = $paymentAmount;
                    $param['ins_amount'] = $insAmount;
                    $param['dd_amt'] = $dd_amt;
                    $param['orderCnt'] = 1;
                    $param['email'] = $request->email;
                    $param['validatedData'] = $validatedData;
                    $param['request'] = $request;
                    $param['helper'] = $this->helper;
                    $coupon = Coupon::where("coupon", $request->cpn)
                        ->where("status", 1)->first();
                    if ($coupon->freeall == 1 && $insAmount == 0 && $dd_amt == 0) {
                        $r = $this->createFreeOrder($param);
                        return response()->json($r);
                    }
                }
                // COUPON FOR FREE ORDER - END
                $discountObj = $this->get_discount_forAPIPayment($param); // GET DISCOUNT

                $ins_amount = $discountObj['insurance'];
                $paymentAmount = $discountObj['totalAmt'];
                $discount = $discountObj['discount'];
                $dd_amt = $discountObj['dd_amt'];
                $amountTotal = round($paymentAmount + $discount, 2);
            }
        }

        // return response()->json($discountObj);
        // COUPON - END

        $params = [
            'USER' => env('PAYFLOW_USER'),
            'VENDOR' => env('PAYFLOW_VENDOR'),
            'PARTNER' => env('PAYFLOW_PARTNER'),
            'PWD' => env('PAYFLOW_PD'),
            'TRXTYPE' => 'S', // Sale transaction
            'TENDER' => 'C', // Card payment
            'AMT' => $paymentAmount,
            'CURRENCY' => env('PAYFLOW_CURRENCY'),
            'ACCT' => $ccNo,
            'EXPDATE' => $cardExpireMonth . $expiryYear, // MMYY format
            // 'CVV2'          => $request->billing_cc_no,
            'CVV2' => $request->billing_cc_cvv,
            'CITY' => 'Houston',
            'STATE' => 'TX',
            'ZIP' => '73301',
            'COMMENT1' => "RR WP single order",
            'apiEndpoint' => env('PAYFLOW_API_URL'),
        ];

        if ($request->cpn && $couponExist == 1) {
            $params['COMMENT2'] = "$discount Discount from total $amountTotal";
        }
        //$user       =   User::where('email', $validatedData['email'])->first();

        $response = $this->paypal->payment($params);


        // Process response
        if ($response !== false) {
            $rs = 1;
            parse_str($response, $parsedResponse);

            // TRANSACTION FAIL OR SUCCESS, MUST CREATE ORDER
            //if ($rs == 1)
            if (isset($parsedResponse['RESULT']) && $parsedResponse['RESULT'] == 0) {

                // TRANSACTION FAIL OR SUCCESS, MUST CREATE ORDER
                //if ($rs == 1)
                if ($parsedResponse['RESPMSG'] == 'Approved') {
                    $user = User::where('email', $validatedData['email'])->first();
                    //if (is_null($user))
                    // USER ALREADY REGISTER OR NOT, MUST GO IN THIS BLOCK
                    if ($rs == 1) {

                        // CREATE USER - START
                        $newUser = 0;
                        if (is_null($user)) {
                            $userData = $this->getUserData($validatedData);
                            $user = User::create($userData);
                            $token = $user->createToken("auth_token")->accessToken;
                            $newUser = 1;
                        } else {
                            $token = $user->createToken("auth_token")->accessToken;
                        }

                        // $loginCr['email'] = $validatedData['email'];
                        // $loginCr['password'] = $validatedData['password'];
                        // Auth::attempt($loginCr);
                        // CREATE USER - END


                        // CREATE COMPANY - START

                        if ($user->company_id == 1) {
                            $companyData = $this->getCompanyData($validatedData, $user);
                            $company = Companies::create($companyData);
                            User::where('email', $request->email)->update([
                                'company_id' => $company->id
                            ]);
                        } else {
                            $company = Companies::where("id", $user->company_id)->first();
                        }
                        // CREATE COMPANY - END

                        // CREATE ORDER - START
                        if (isset($parsedResponse['RESULT']) && $parsedResponse['RESULT'] == 0) {
                            $orderStatus = 'completed';
                            $transactionStatus = 'success';
                        } else {
                            $orderStatus = 'pending';
                            $transactionStatus = 'fail';
                        }
                        $orderData = ["company_id" => $company->id, "status" => $orderStatus];
                        $order = Orders::create($orderData);
                        // CREATE ORDER - END


                        // CREATE SUB ORDER - START
                        $employeParam = [
                            'validatedData' => $validatedData,
                            'request' => $request,
                            'user' => $user,
                            'company' => $company,
                            'order' => $order,
                            'helper' => $this->helper
                        ];
                        $employeeData = $this->getSuborderData($employeParam);
                        $suborder = Compemployees::create($employeeData);
                        // CREATE SUB ORDER - END

                        if ($insAmount != 0 && !empty($request->cpn && $couponExist == 1)) {
                            $trans_response = [
                                'response' => $response,
                                'discount' => $discount,
                                'coupon' => $request->cpn,
                                'ins_amount' => $insAmount,
                            ];
                        } else if ($insAmount == 0 && !empty($request->cpn && $couponExist == 1)) {
                            $trans_response = [
                                'response' => 'Free Order',
                                'discount' => $discount,
                                'coupon' => $request->cpn,
                            ];
                        } else {
                            $trans_response = $response;
                        }

                        // CREATE TRANSACTION RECORD - START
                        $orderData = [
                            'order_id' => $order->id,
                            'company_id' => $company->id,
                            'user_id' => $user->id,
                            'trans_response' => json_encode($trans_response),
                            'status' => $transactionStatus,
                            'amount' => $paymentAmount,
                        ];
                        Transactions::create($orderData);

                        // CREATE TRANSACTION RECORD - END


                        // SEND EMAIL - START



                        // if ($newUser == 1) {

                        //     $emailData = [
                        //         "emailTemplate" => 'newSignupCredentials',
                        //         "subject" => 'Welcome to RemoteRetrieval.com - Sign up Information',
                        //         "to" => $request->email,
                        //         "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                        //         "cc" => "",
                        //         "fromEmail" => env('MAIL_USERNAME'),
                        //         "fromName" => 'Remote Retrieval',
                        //         "title" => 'Welcome to RemoteRetrieval.com - Sign up Information',
                        //         "template" => "newSignupCredentials",
                        //         "mailData" => $suborder,
                        //         "pwd" => $request->password,
                        //         "package" => 'basic',
                        //         "mailTemplate" => 'mails.send_to_user'
                        //     ];
                        //     $this->mailService->sendMail($emailData);


                        //     $emailData = [
                        //         "emailTemplate" => 'newSignupFreeCoupon',
                        //         "subject" => 'First Laptop Retrieval Order Free on us.',
                        //         "to" => $request->email,
                        //         "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                        //         "cc" => "",
                        //         "fromEmail" => env('MAIL_USERNAME'),
                        //         "fromName" => 'Remote Retrieval',
                        //         "title" => 'First Laptop Retrieval Order Free on us.',
                        //         "template" => "newSignupFreeCoupon",
                        //         "mailData" => $suborder,
                        //         "company"  => $company,
                        //         "pwd" => $request->password,
                        //         "package" => 'basic',
                        //         "mailTemplate" => 'mails.send_to_user'
                        //     ];
                        //     $this->mailService->sendMail($emailData);
                        // }
// MAIL CREATE ORDER

                        // $emailData = [
                        //     "emailTemplate" => 'createOrderMail',
                        //     "subject" => "Order Confirmation and Details - #$order->id",
                        //     "to" => $request->email,
                        //     "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                        //     "cc" => "",
                        //     "fromEmail" => env('MAIL_USERNAME'),
                        //     "fromName" => 'Remote Retrieval',
                        //     "title" => 'Order Confirmation and Details',
                        //     "template"      => "createOrderMail",
                        //     "mailData"      => $suborder,
                        //     "insurance"     => $insAmount ?? '',
                        //     "dd"            => $dd_amt ?? '',
                        //     "paymentAmount" => $paymentAmount,
                        //     "package" => 'basic',
                        //     "mailTemplate" => 'mails.send_to_user'
                        // ];
                        // $this->mailService->sendMail($emailData);





                        // SEND EMAIL - END
// SET THANKYOU TOKEN
                        Session::put('thanksToken', Str::random(20));


                        return response()->json(
                            [
                                'orderId' => $order->id,
                                'accessToken' => $token,
                                'user' => $user,
                                'message' => 'Order and User have created',
                                'status' => "success"
                            ]
                        );
                    } else {  // ELSE OF "if (is_null($user)) "
                        return response()->json(
                            [
                                'token' => null,
                                'user' => null,
                                'message' => "User already exist!",
                                'status' => "fail"
                            ]
                        );
                    }
                } else { // ELSE OF "if ($parsedResponse['RESPMSG'] == 'Approved')"
                    // CREATE TRANSACTION RECORD - START
                    $orderData = [
                        'order_id' => 0,
                        'company_id' => 0,
                        'user_id' => 0,
                        'trans_response' => json_encode($response),
                        'status' => 'fail',
                        'amount' => $request->billing_amount
                    ];
                    Transactions::create($orderData);
                    // CREATE TRANSACTION RECORD - END
                    $d = ['status' => "fail", 'message' => $parsedResponse['RESPMSG']];
                    return response()->json($d);
                }

            } else { // ELSE OF "// if (isset($parsedResponse['RESULT']) && $parsedResponse['RESULT'] == 0)"
                $d = ['status' => "fail", 'message' => $parsedResponse['RESPMSG']];
                return response()->json($d);
            }
        } else {  // ELSE OF "if ($response !== false) "
            $d = ['status' => "fail", 'message' => 'Error occurred while processing payment'];
            return response()->json($d);
        }





    }



    /**
     * MODULE: CREATE USER,ORDER AFTER TAKING SUCCESSFUL PAYMENT
     * DESCRIPTION: MAKE SUB-ORDER DATA FOR CREATING SUB-ORDER
     */
    public function getSuborderData($employeParam)
    {
        $validatedData = $employeParam['validatedData'];
        $request = $employeParam['request'];
        $user = $employeParam['user'];
        $company = $employeParam['company'];
        $order = $employeParam['order'];
        $helper = $employeParam['helper'];
        $ins_active = 0;
        if (strlen($validatedData['employee_state']) != 2) {
            $employeeState = $this->getState($validatedData['employee_state']);
        } else {
            $employeeState = $validatedData['employee_state'];
        }
        if (strlen($validatedData['company_state']) != 2) {
            $compState = $this->getState($validatedData['company_state']);
        } else {
            $compState = $validatedData['company_state'];
        }
        if (isset($request->ins_amount)) {
            $ins_active = 1;
        }
        $customMsg = "";
        if ($request->emp_custom_msg) {
            $customMsg = $request->emp_custom_msg;
        }

        // $settings = Systemsettings::latest('id')->first();
        // if ($settings) {
        //     $orderAmount = $settings->order_amount;
        // } else {
        //     $orderAmount = env('ORDER_AMT');
        // }
        $orderAmount = $helper->getDeviceAmount($validatedData['type_of_equipment']);
        $employeeData = [
            "emp_first_name" => $validatedData['employee_first_name'],
            "emp_last_name" => $validatedData['employee_last_name'],
            "emp_email" => $validatedData['employee_email'],
            "emp_phone" => $validatedData['employee_phone'],
            "emp_add_1" => $validatedData['employee_add_1'],
            "emp_add_2" => ($request->employee_add_2) ? $request->employee_add_2 : '',
            "emp_city" => $validatedData['employee_city'],
            "emp_state" => $employeeState,
            "emp_pcode" => $validatedData['employee_zip'],
            "return_service" => $validatedData['order_type'],
            "type_of_equip" => $validatedData['type_of_equipment'],
            "company_id" => $company->id,
            "user_id" => $user->id,
            "order_id" => $order->id,
            "receipient_name" => $validatedData['company_name'],
            "receipient_person" => $validatedData['comp_receip_name'],
            "receipient_email" => $validatedData['company_email'],
            "receipient_phone" => $validatedData['company_phone'],
            "receipient_add_1" => $validatedData['company_add_1'],
            "receipient_add_2" => ($request->company_add_2) ? $request->company_add_2 : '',
            "receipient_city" => $validatedData['company_city'],
            "receipient_state" => $compState,
            "receipient_zip" => $validatedData['company_zip'],
            "send_flag" => 0,
            "rec_flag" => 0,
            "source" => "API-WP",
            "insurance_active" => $ins_active,
            "insurance_amount" => ($request->ins_amount) ? $request->ins_amount : null,
            "custom_msg" => $customMsg,
            "order_amt" => $orderAmount
        ];
        if (isset($request->return_add_srv) && $request->return_add_srv != null) {
            $employeeData['return_additional_srv'] = $request->return_add_srv;
            if ($request->return_add_srv == 2) {
                $newEmpData = json_decode($request->new_emp_data, true);

                $newEmployeeState = $newEmpData['newemp_state'];
                if (strlen($newEmployeeState) != 2) {
                    $newEmployeeState = $this->getState($newEmployeeState);
                    $newEmpData['newemp_state'] = $newEmployeeState;
                }
                $employeeData['new_emp_data'] = json_encode($newEmpData);
            }
        }

        return $employeeData;
    }


    public function getUserData($validatedData)
    {
        $userData = [
            "name" => explode("@", $validatedData['email'])[0],
            "email" => $validatedData['email'],
            "password" => $validatedData['password'],
            "company_id" => 1,
            "role" => 1,
            "user_pkg" => $validatedData['user_pkg'],
            "username" => explode("@", $validatedData['email'])[0],
            "phone" => $validatedData['phone'],
            "secret_code" => Str::random(100)
        ];
        return $userData;
    }


    public function getCompanyData($validatedData, $user)
    {
        if (strlen($validatedData['company_state'] != 2)) {
            $compState = $this->getState($validatedData['company_state']);
        } else {
            $compState = $validatedData['company_state'];
        }
        $companyData = [
            "user_id" => $user->id,
            "company_name" => $validatedData['company_name'],
            "company_email" => $validatedData['company_email'],
            "company_phone" => $validatedData['company_phone'],
            "company_add_1" => $validatedData['company_add_1'],
            "company_city" => $validatedData['company_city'],
            "company_state" => $compState,
            "company_zip" => $validatedData['company_zip'],
            "receipient_name" => $validatedData['comp_receip_name'],
        ];
        return $companyData;
    }

    public function getState($s)
    {
        return $s;
        // $states = array(
        //     'Alabama' => 'AL',
        //     'Alaska' => 'AK',
        //     'Arizona' => 'AZ',
        //     'Arkansas' => 'AR',
        //     'California' => 'CA',
        //     'Colorado' => 'CO',
        //     'Connecticut' => 'CT',
        //     'Delaware' => 'DE',
        //     'District Of Columbia' => 'DC',
        //     'Florida' => 'FL',
        //     'Georgia' => 'GA',
        //     'Hawaii' => 'HI',
        //     'Idaho' => 'ID',
        //     'Illinois' => 'IL',
        //     'Indiana' => 'IN',
        //     'Iowa' => 'IA',
        //     'Kansas' => 'KS',
        //     'Kentucky' => 'KY',
        //     'Louisiana' => 'LA',
        //     'Maine' => 'ME',
        //     'Maryland' => 'MD',
        //     'Massachusetts' => 'MA',
        //     'Michigan' => 'MI',
        //     'Minnesota' => 'MN',
        //     'Mississippi' => 'MS',
        //     'Missouri' => 'MO',
        //     'Montana' => 'MT',
        //     'Nebraska' => 'NE',
        //     'Nevada' => 'NV',
        //     'New Hampshire' => 'NH',
        //     'New Jersey' => 'NJ',
        //     'New Mexico' => 'NM',
        //     'New York' => 'NY',
        //     'North Carolina' => 'NC',
        //     'North Dakota' => 'ND',
        //     'Ohio' => 'OH',
        //     'Oklahoma' => 'OK',
        //     'Oregon' => 'OR',
        //     'Pennsylvania' => 'PA',
        //     'Rhode Island' => 'RI',
        //     'South Carolina' => 'SC',
        //     'South Dakota' => 'SD',
        //     'Tennessee' => 'TN',
        //     'Texas' => 'TX',
        //     'Utah' => 'UT',
        //     'Vermont' => 'VT',
        //     'Virginia' => 'VA',
        //     'Washington' => 'WA',
        //     'West Virginia' => 'WV',
        //     'Wisconsin' => 'WI',
        //     'Wyoming' => 'WY',
        //     'Armed Forces (AA)' => 'AA',
        //     'Armed Forces (AE)' => 'AE',
        //     'Armed Forces (AP)' => 'AP'
        // );

        // return $states[$s];
    }


    public function updaterecord(Request $request)
    {

        $user = User::where("email", $request->email)->first();
        if ($user) {
            $status=$request->status==1?"active":"inactive";
            $companyDomain=explode(".",$request->company_domain)[0];

            $user->update([
                "name" => $request->name,
                "status" => $status
            ]);

            $company = Companies::where("user_id", $user->id)->first();
            if ($company) {
                $company->update([
                    "company_name" => $request->company_name,
                    "company_domain" => $companyDomain,
                    "company_phone" => $request->phone,
                    "company_add_1" => $request->company_add1,
                    "company_city" => $request->company_city,
                    "company_state" => $request->company_state,
                    "company_zip" => $request->company_zip,
                ]);
                if($status=="active")
                {
                        $data = User::join('companies', 'users.company_id', '=', 'companies.id')
                        ->select('users.*', 'companies.company_name as company_name', 'companies.company_domain as company_domain')
                        ->where("users.company_id", $company->id)->first();
                        $emailData = [
                        "emailTemplate" => 'activateAccount',
                        "subject" => 'White Label Registration is Active now!',
                        "to" => $data->email,
                        "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                        "cc" => "",
                        "fromEmail" => env('MAIL_USERNAME'),
                        "fromName" => 'Return Device',
                        "title" => 'White Label Registration is Active now!',
                        "template" => "activateAccount",
                        "mailData" => $data,
                        "mailTemplate" => 'mails.send_to_user'
                    ];
                    $this->mailService->sendMail($emailData);
                }

                return response()->json(["message" => "Data updated successfully", "status" => "success"], 200);
            }

        } else {
            session()->flash('success', 'Data cannot updated!');
            return response()->json(["message3" => "Data cannot update", "status" => "fail"], 400);
        }


        session()->flash('success', 'Data cannot updated!');
        return response()->json(["message3" => "Data cannot update", "status" => "fail"], 400);

    }

}
