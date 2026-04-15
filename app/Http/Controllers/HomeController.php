<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\Services\Helper;
use App\Libraries\Services\Paypal;
use App\Libraries\Services\MailService;

use App\Models\User;
use App\Models\Companies;
use App\Models\Compemployees;
use App\Models\Orders;
use App\Models\Coupon;
use App\Models\Transactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use App\Models\Companysettings;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Systemsettings;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $helper;
    protected $mailService;

    public function __construct(MailService $mailService, Helper $helper)
    {
        $this->mailService = $mailService;
        $this->helper = $helper;
    }

    public function index(Request $request)
    {
        if ($_SERVER['SERVER_NAME'] != env('MAIN_DOMAIN') || Auth::check()) {
            return view('home.index');
        } else {
            return response()->json(
                [
                    'message' => "It is not accessible!",
                ],
                200
            );
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createOrder(Request $request)
    {
        $settings = app('companySettings');
        $priceSettings = Systemsettings::where('company_id', env('RR_COMPANY_ID'))->get();
        if (Systemsettings::where('company_id', $settings->company_id)->exists()) {
            $priceSettings= Systemsettings::where('company_id', $settings->company_id)->get();
        }
        return view('home.createOrder',compact('priceSettings'));
    }

    public function getOrderAmount(Request $request, Helper $helper)
    {
        // $settings = Systemsettings::latest('id')->first();
        // if ($settings) {
        //     $amount = $settings->order_amount;
        // } else {
        //     $amount = env("ORDER_AMT");
        // }
        $amount = $helper->getDeviceAmount($request->type_of_equipment);

        return response()->json(
            [
                'amount' => $amount,
                'message' => "Order amount",
                'status' => 1,
            ],
            200
        );
    }



    public function registercreateorder(Request $request, Paypal $paypal, Helper $helper)
    {
        $origin = $request->header('Origin');
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST']; // or $_SERVER['SERVER_NAME']
        $allowedOrigin = $protocol . '://' . $domain;
        if (trim($origin) !== trim($allowedOrigin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cross-origin request is not allowed'
            ], 403); // 403 Forbidden status
        }
        $settings = app('companySettings');
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
        }
        else if ($settings->id == env('COMPANY_SETTING_ID')) {
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
        }
        else {
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

$ccNo = $request->billing_cc_no??"4000000000000077";
             $cardExpireMonth="01";
              $expiryYear="22";
        if ($request->fcpn != 1 && $settings->id != env('COMPANY_SETTING_ID')) {

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
            if ($settings->id != env('COMPANY_SETTING_ID'))
            {
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
            }

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



        $amount = $helper->getDeviceAmount($validatedData['type_of_equipment']);
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
                    $param['helper'] = $helper;
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
            'EXPDATE' => $cardExpireMonth.$expiryYear, // MMYY format
            // 'CVV2'          => $request->billing_cc_no,
            'CVV2' => $request->billing_cc_cvv??"123",
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
        $response = $paypal->payment($params);
        \Log::info('Payment Response:',['response' => $response]);
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
                            // $token = $user->createToken("auth_token")->accessToken;
                            $token = '';
                            $newUser = 1;
                        } else {
                            // $token = $user->createToken("auth_token")->accessToken;
                            $token = '';
                        }

                        // $loginCr['email'] = $validatedData['email'];
                        // $loginCr['password'] = $validatedData['password'];
                        // Auth::attempt($loginCr);
                        // CREATE USER - END


                        // CREATE COMPANY - START
                        if ($user->company_id == 1) {
                            $settings = app('companySettings');
                            $companyData = $this->getCompanyData($validatedData, $user);
                            $company = Companies::create($companyData);
                            User::where('email', $request->email)->update([
                                'company_id' => $company->id,
                                'parent_comp_id' => $settings->company_id
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
                            'helper' => $helper
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


                        // $order_amount = $paymentAmount - $insAmount;
// $data = array('user_email'=>$user->email, 'order_id' => $order->id,'created_date' => date("Y-m-d"),
// 'insurance_amount' => $insAmount,'order_amount' => $order_amount , 'total' => $paymentAmount,'custom_content'=>'' );
// Mail::send('mails/mailCreateOrder', $data, function($message) {
//     $message->to('haroon.rightsolution@gmail.com', 'Tutorials Point');
//     $message->bcc('haroon.rightsolution@gmail.com', 'Tutorials Point');
//     $message->from('haroon.rightsolution@gmail.com','Test 2');
//     $message->subject('Order Confirmed - Remote Retrieval');


                        //    $message->from('my@email.com', 'My Company')
//                 ->to($toEmail, $toName)
//                 ->bcc('mybcc@email.com','My bcc Name')
//                 ->subject('New order');


                        // });

                        if ($newUser == 1) {

                            $emailData = [
                                "emailTemplate" => 'newSignupCredentials',
                                "subject" => 'Welcome to ReturnDevice.com - Sign up Information',
                                "to" => $request->email,
                                "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                                "cc" => "",
                                "fromEmail" => env('MAIL_USERNAME'),
                                "fromName" => 'Return Device',
                                "title" => 'Welcome to ReturnDevice.com - Sign up Information',
                                "template" => "newSignupCredentials",
                                "mailData" => $suborder,
                                "pwd" => $request->password,
                                "package" => 'basic',
                                "mailTemplate" => 'mails.send_to_user'
                            ];
                            // $this->mailService->sendMail($emailData);


                            $emailData = [
                                "emailTemplate" => 'newSignupFreeCoupon',
                                "subject" => 'First Laptop Retrieval Order Free on us.',
                                "to" => $request->email,
                                "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                                "cc" => "",
                                "fromEmail" => env('MAIL_USERNAME'),
                                "fromName" => 'Return Device',
                                "title" => 'First Laptop Retrieval Order Free on us.',
                                "template" => "newSignupFreeCoupon",
                                "mailData" => $suborder,
                                "company" => $company,
                                "pwd" => $request->password,
                                "package" => 'basic',
                                "mailTemplate" => 'mails.send_to_user'
                            ];
                            // $this->mailService->sendMail($emailData);
                        }
                        // MAIL CREATE ORDER

                        $emailData = [
                            "emailTemplate" => 'createOrderMail',
                            "subject" => "Order Confirmation and Details - #$order->id",
                            "to" => $request->email,
                            "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                            "cc" => "",
                            "fromEmail" => env('MAIL_USERNAME'),
                            "fromName" => 'No Reply from ReturnDevice',
                            "title" => 'Order Confirmation and Details',
                            "template" => "createOrderMail",
                            "mailData" => $suborder,
                            "insurance" => $insAmount ?? '',
                            "dd" => $dd_amt ?? '',
                            "paymentAmount" => $paymentAmount,
                            "package" => 'basic',
                            "mailTemplate" => 'mails.send_to_user'
                        ];
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
                if($settings->id == env('COMPANY_SETTING_ID') && $validatedData['order_type']=="Return To Company")
                {
                     $user = User::where('email', $validatedData['email'])->first();
                      if (is_null($user)) {
                        $userData = $this->getUserData($validatedData);
                        $user = User::create($userData);
                      }

                     $orderData = ["company_id" => $settings->company_id, "status" => "pending"];
                        $order = Orders::create($orderData);
                        $orderData = [
                        'order_id' => $order->id,
                        'company_id' => $settings->company_id,
                        'user_id' => $user->id,
                        'trans_response' => json_encode($response),
                        'status' => 'fail',
                        'amount' => $request->billing_amount
                    ];
                    Transactions::create($orderData);
                     $employeParam = [
                            'validatedData' => $validatedData,
                            'request' => $request,
                            'user' => $user,
                            'company' => $settings->company,
                            'order' => $order,
                            'helper' => $helper
                        ];
                        $employeeData = $this->getSuborderData($employeParam);
                        $suborder = Compemployees::create($employeeData);
                         Session::put('thanksToken', Str::random(20));
                         $sendEmails = compemployees::where('id', $suborder->id)->first();
                $sendEmails->companyData=$settings->company;
                $sendEmails->logo=$settings->logo;
                $emailTemplate = "compPaymentFailedOrder";
                $emailTemplateSubject = "New Order Created – Pending Payment - Order #".$sendEmails->order_id."-".$sendEmails->id;
                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" => $sendEmails->receipient_email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
                    "mailTemplate" => "mails.email_on_status_update",
                    "mailData" => $sendEmails,
                    "company" => $company,
                ];

                $this->mailService->sendMail($emailData);

                        return response()->json(
                            [
                                'orderId' => $order->id,
                                'accessToken' => '',
                                'user' => $user,
                                'message' => 'Order and User have created',
                                'status' => "success"
                            ]
                        );
                }
                $d = ['status' => "fail", 'message' => $parsedResponse['RESPMSG']];
                return response()->json($d);
            }
        } else {
            // ELSE OF "if ($response !== false) "
            $d = ['status' => "fail", 'message' => 'Error occurred while processing payment'];
            return response()->json($d);
        }

    }

    public function profilelogin(Request $request)
    {

        $userChk = User::where('id', $request->uid)
            ->where('secret_code', $request->secret_code)
            //->where('user_pkg', 'enterprise')
            ->first();

        if (is_null($userChk)) {
            return redirect()->away(env("WP_REMOTE_RET"));
        } else {
            Auth::loginUsingId($request->uid, true);
            $user = auth()->user();
            if ($request->r != "") {
                return redirect()->route($request->r);
            }
            if ($user) {
                // SET SESSION FOR THANKYOU PAGE
                Session::get('thanksToken', 'default');
                return redirect()->route('thank.you');
            } else {
                return redirect()->away(env("WP_REMOTE_RET"));
            }
        }
    }


    public function getDiscount(Request $request)
    {
        $validatedData = $request->validate([
            'coupon' => ['required'],
            'amount' => ['required']
        ]);

        $param['coupon'] = $validatedData['coupon'];
        $param['amount'] = $validatedData['amount'];
        $param['email'] = $request->email;
        if (isset($request->return_add_srv)) {
            $param['dd_srv'] = $request->return_add_srv;
        }
        $param['company_name'] = $request->company_name;
        $param['insurance'] = $request->insurance;
        $getDiscount = $this->get_discount_forAPI($param);
        return $getDiscount;
    }

    /**
     * MODULE: CREATE USER,ORDER AFTER TAKING SUCCESSFUL PAYMENT
     * DESCRIPTION: MAKE USER DATA FOR CREATING USER
     */
    public function getUserData($validatedData)
    {
        $userData = [
            "name" => explode("@", $validatedData['email'])[0],
            "email" => $validatedData['email'],
            "password" => $validatedData['password'],
            "company_id" => 1,
            "role" => 'USER',
            "user_pkg" => $validatedData['user_pkg'],
            "username" => explode("@", $validatedData['email'])[0],
            "phone" => $this->twillioPhoneFormat($validatedData['phone']),
            "secret_code" => Str::random(100)
        ];
        return $userData;
    }
    public function twillioPhoneFormat($phone)
    {
        $pattern = '~[()-]~';
        $phone = preg_replace($pattern, '', $phone);
        $phone = str_replace('+1', '', $phone);
        $phone = str_replace(" ", '', $phone);
        return env('TWILLIO_PREFIX') . $phone;
    }

    /**
     * MODULE: CREATE USER,ORDER AFTER TAKING SUCCESSFUL PAYMENT
     * DESCRIPTION: MAKE COMPANY DATA FOR CREATING COMPANY
     */
    public function getCompanyData($validatedData, $user)
    {
        $settings = app('companySettings');
        if (strlen($validatedData['company_state'] != 2)) {
            $compState = $this->getState($validatedData['company_state']);
        } else {
            $compState = $validatedData['company_state'];
        }
        $companyData = [
            "user_id" => $user->id,
            "company_name" => $validatedData['company_name'],
            "company_email" => $validatedData['company_email'],
            "company_phone" => $this->twillioPhoneFormat($validatedData['company_phone']),
            "company_add_1" => $validatedData['company_add_1'],
            "company_city" => $validatedData['company_city'],
            "company_state" => $compState,
            "company_zip" => $validatedData['company_zip'],
            "receipient_name" => $validatedData['comp_receip_name'],
            "parent_company" => $settings->company_id
        ];
        return $companyData;
    }



    public function getState($s)
    {
        return $s;

    }

    /**
     * MODULE: CREATE USER,ORDER AFTER TAKING SUCCESSFUL PAYMENT
     * DESCRIPTION: MAKE SUB-ORDER DATA FOR CREATING SUB-ORDER
     */
    public function getSuborderData($employeParam)
    {
        $companyID = '';
        $settings = app('companySettings');
        $companyID = $settings->company_id;
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
        $currentCompany = $helper->getCurrentCompany();
        // $companyID = '';
        // if ($currentCompany) {
        //     $companyID = $currentCompany->id;
        // }
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
            "order_amt" => $orderAmount,
            "parent_comp_id" => $companyID
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



    /**
     *
     * MODULE: CREATE FREE ORDER
     * DESC: CREATE FREE ORDER BY 100% DISCOUNT COUPON
     */
    public function createFreeOrder($param)
    {

        // Process response
        // if ($response !== false)
        // {

        $rs = 1;
        // parse_str($response, $parsedResponse);

        // TRANSACTION FAIL OR SUCCESS, MUST CREATE ORDER
        //if ($rs == 1)


        // TRANSACTION FAIL OR SUCCESS, MUST CREATE ORDER
        //if ($rs == 1)

        $user = User::where('email', $param['email'])->first();
        //if (is_null($user))
        // USER ALREADY REGISTER OR NOT, MUST GO IN THIS BLOCK
        if ($rs == 1) {

            // CREATE USER - START
            if (is_null($user)) {
                $userData = $this->getUserData($param['validatedData']);
                $user = User::create($userData);
                // $token = $user->createToken("auth_token")->accessToken;
                $token = '';
            } else {
                // $token = $user->createToken("auth_token")->accessToken;
                $token = '';
            }
            // CREATE USER - END


            // CREATE COMPANY - START
            if ($user->company_id == 1) {
                $settings = app('companySettings');
                $companyData = $this->getCompanyData($param['validatedData'], $user);
                $company = Companies::create($companyData);
                User::where('email', $param['email'])->update([
                    'company_id' => $company->id,
                    'parent_comp_id' => $settings->company_id
                ]);
            } else {
                $company = Companies::where("id", $user->company_id)->first();
            }
            // CREATE COMPANY - END

            // CREATE ORDER - START
            $orderStatus = 'completed';
            $transactionStatus = 'success';
            $orderData = ["company_id" => $company->id, "status" => $orderStatus];
            $order = Orders::create($orderData);
            // CREATE ORDER - END


            // CREATE SUB ORDER - START
            $employeParam = [
                'validatedData' => $param['validatedData'],
                'request' => $param['request'],
                'user' => $user,
                'company' => $company,
                'order' => $order,
                'helper' => $param['helper']
            ];
            $employeeData = $this->getSuborderData($employeParam);
            $suborder = Compemployees::create($employeeData);
            // CREATE SUB ORDER - END

            // CREATE TRANSACTION RECORD - START
            $orderData = [
                'order_id' => $order->id,
                'company_id' => $company->id,
                'user_id' => $user->id,
                'trans_response' => json_encode(['response' => 'Free Order', 'discount' => $param['discount'], 'coupon' => $param['coupon']]),
                'status' => 'success',
                'amount' => 0,
            ];
            Transactions::create($orderData);
            // CREATE TRANSACTION RECORD - END

            Session::put('thanksToken', Str::random(20));
            return [
                'orderId' => $order->id,
                'accessToken' => $token,
                'user' => $user,
                'message' => 'Order and User have created',
                'status' => "success"
            ];
            // return response()->json(
            //     [
            //         'accessToken' => $token,
            //         'user' => $user,
            //         'message' => 'Order and User have created',
            //         'status' => "success"
            //     ]
            // );
        } else {  // ELSE OF "if (is_null($user)) "
            //return response()->json(
            return [
                'token' => null,
                'user' => null,
                'message' => "User already exist!",
                'status' => "fail"
            ];
            //);
        }



        // }
    }




    public function get_discount_forAPI($param)
    {
        $coupon = $param['coupon'];
        $orderAmount = $param['amount'];
        $user_email = $param['email'];
        $dd_amt = 0;

        if ($param['company_name'] == "") {
            $d = ['status' => "fail", 'message' => 'Must fill company name'];
            return response()->json($d);
        }


        $ins_amount = isset($param['insurance']) ? $param['insurance'] : 0;
        $ins_amountH = $ins_amount;
        // TAKE FULL AMOUNT OF INSURANCE THEN APPLY CALCULATION
        if ($ins_amount != 0) {
            $ins_amount = round(($ins_amount * env("INSURANCE_RATE")) / 100, 2);
            $ins_amountH = $ins_amount;
        } else {
            $ins_amount = 0;
        }


        if (isset($param['dd_srv'])) {
            if ($param['dd_srv'] == 1) {
                $dd_amt = env('DD_COMPANY');
            } else if ($param['dd_srv'] == 2) {
                $dd_amt = env('DD_NEW_EMP');
            }
        }


        //Check If User Already Applied Coupon Free Order
        $user = User::where('email', $user_email)->first();
        if ($user) {
            if (Transactions::where("user_id", $user->id)->exists()) {
                $transactions = Transactions::where("user_id", $user->id)->get();
                foreach ($transactions as $transaction) {
                    $transResponse = json_decode($transaction->trans_response, true);
                    if (isset($transResponse['coupon'])) {
                        $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
                        if ($check_free_coupon_exit == 1) {
                            $d = ['status' => "fail", 'message' => 'As a new user, you can create only one order using a 100% discount coupon.'];
                            return response()->json($d);
                        }
                    }
                }
            }
        }

        // VALIDATE COUPON ON BASIS OF COMPANY - START
        if ($param['company_name'] != "Remote Retrieval") {
            $company = Companies::where('company_name', $param['company_name'])->get();
            foreach ($company as $uid) {
                $cpnParams['userId'] = $uid['user_id'];
                $cpnParams['coupon'] = $coupon;
                if (Transactions::where("user_id", $uid['user_id'])->exists()) {
                    $transactions = Transactions::where("user_id", $uid['user_id'])->get();
                    foreach ($transactions as $transaction) {
                        $transResponse = json_decode($transaction->trans_response, true);
                        if (isset($transResponse['coupon'])) {
                            $check_free_coupon_exit = Coupon::where("coupon", $transResponse['coupon'])->where("freeall", 1)->count();
                            // if ($check_free_coupon_exit == 1 && $cpnParams['coupon']->freeall == 1) {
                            if ($check_free_coupon_exit == 1) {
                                $d = ['status' => "fail", 'message' => 'The 100% free coupon can be used only once!'];
                                // return $d;
                                return response()->json($d);
                            }
                        }
                    }
                }
            }
        }

        // VALIDATE COUPON ON BASIS OF COMPANY - END



        $coupon = Coupon::where("coupon", $coupon)->where("status", 1)->first();
        if (is_null($coupon)) {
            $d = ['status' => "fail", 'message' => 'Invalid Coupon'];
            return response()->json($d);
        }

        // IF COUPON IS 100% FREE
        // if ($coupon->freeall == 1) {
        //     $ins_amount = 0;
        // }


        if ($coupon->type == "amount") {   // AMOUNT DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $coupon->amt_or_perc has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
                // echo "total".$totalAmt;
            }

        } else {
            // PERCENTAGE DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc / 100;
                $discount = round($totalAmt * $discount, 2);
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                $discount = $orderAmount * ($coupon->amt_or_perc / 100);
                $totalAmt = $orderAmount;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            }
        }

        // IF COUPON IS 100% FREE
        $message2 = '';
        if ($coupon->freeall == 1 && $ins_amountH == 0 && $dd_amt == 0) {
            $message2 = ' You can create a free order by clicking on the "Order Now" button.';
            $message = "The discount of $discount has been applied. " . $message2;
        } else if ($coupon->freeall == 1 && $ins_amountH != 0) {
            $message2 = ' An insurance fee of ' . $ins_amountH . ' will still be charged, even when a 100% discount coupon is used.';
            $message = "A discount of $discount has been applied. " . $message2;
        } else if ($coupon->freeall == 1 && $dd_amt != 0) {
            $message2 = ' Data destruction fee ' . $dd_amt . ' will still be charged, even when a 100% discount coupon is used.';
            $message = "A discount of $discount has been applied. " . $message2;
        } else {
            $message = "A discount of $discount has been applied. ";
        }

        // else {
        //     $message2 = ' An insurance fee of ' . $ins_amountH . ' will still be charged, even when a 100% discount coupon is used.';
        //     $message = "A discount of $discount has been applied. " . $message2;
        // }

        $d = [
            'status' => "success",
            'message' => $message,
            'coupon' => $coupon,
            'discount' => $discount,
            'totalAmt' => $totalAmt,
            'insurance' => $ins_amount,
            'result' => json_encode($param)
        ];
        if ($dd_amt) {
            $d['DD_Amount'] = $dd_amt;
        }
        return response()->json($d);


    }





    public function get_discount_forAPIPayment($param)
    {
        $dd_amt = 0;
        $coupon = $param['coupon'];
        $orderAmount = $param['amount'];
        $ins_amount = isset($param['insurance']) ? $param['insurance'] : 0;
        $ins_amountH = $ins_amount;
        if (isset($param['dd_amt'])) {
            $dd_amt = $param['dd_amt'];
        }


        // if ($ins_amount != 0) {
        //     $orderAmount = $orderAmount - $ins_amount;
        // }
        // if ($dd_amt != 0) {
        //     $orderAmount = $orderAmount - $dd_amt;
        // }

        if ($dd_amt != 0 || $ins_amount != 0) {
            $totalDeduct = $ins_amount + $dd_amt;
            $orderAmount = $orderAmount - $totalDeduct;
        }

        $coupon = Coupon::where("coupon", $coupon)->where("status", 1)->first();
        // IF COUPON IS 100% FREE
        if ($coupon->freeall == 1) {
            // $ins_amount = 0; // disable bcs ins is not complete free with order amt
        }

        // APPLY DISCOUNT BY COUPON
        // echo $orderAmount;
        // exit();
        $dis = $this->getDiscountByCoupon($coupon, $orderAmount, $ins_amount, $dd_amt);

        // IF COUPON IS 100% FREE
        $message2 = '';
        if ($coupon->freeall == 1 && $dd_amt == 0 && $ins_amount == 0) {
            $discount = $dis['discount'] + $ins_amountH;
            $message2 = ' You can create a free order by clicking on the "Order Now" button.';
        }
        $message = "The discount of " . $dis['discount'] . " has been applied. " . $message2;

        $d = [
            'status' => "success",
            'message' => $message,
            'coupon' => $coupon,
            'discount' => $dis['discount'],
            'totalAmt' => $dis['totalAmt'],
            'insurance' => $dis['ins_amount'],
            'dd_amt' => $dis['dd_amt'],
        ];
        return $d;


    }



    public function getDiscountByCoupon($coupon, $orderAmount, $ins_amount, $dd_amt)
    {
        if ($coupon->type == "amount") {   // AMOUNT DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $coupon->amt_or_perc has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
                // echo "total".$totalAmt;
            }

        } else {
            // PERCENTAGE DISCOUNT
            if ($coupon->coupon_apply_for == "total") {
                // DISCOUNT APPLY ON TOTAL AMOUNT
                $totalAmt = $orderAmount;
                $discount = $coupon->amt_or_perc / 100;
                $discount = round($totalAmt * $discount, 2);
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            } else {
                // DISCOUNT APPLY ON PER ORDER AMOUNT
                $discount = $orderAmount * ($coupon->amt_or_perc / 100);
                $totalAmt = $orderAmount;
                $totalAmt = $totalAmt - $discount;
                $totalAmt = $totalAmt + $ins_amount + $dd_amt;
                // $message  = "The discount of $discount has been applied!";
            }
        }

        $arr = ['totalAmt' => $totalAmt, 'discount' => $discount, 'ins_amount' => $ins_amount, 'dd_amt' => $dd_amt];
        return $arr;
    }



    public function thankYouUser()
    {
        if (Session::has('thanksToken')) {
            Session::forget('thanksToken');
            return view('pages.dashboard.thankYou', []);
        } else {
            return redirect()->route('home.index');
        }

    }

    public function dashboard(Request $request)
    {
        $settings = app('companySettings');
        if ($settings->company_id == env("RR_COMPANY_ID")) {
            $cntOrderInprogress = Compemployees::
                where('compemployees.soft_del', '=', 0)
                ->where(function ($query) {
                    $query
                        ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
                })
                ->orWhere(function ($subQuery) use ($settings) {
                    $subQuery->where('compemployees.dest_flag', '=', 1)
                        ->where('compemployees.soft_del', '=', 0)
                        ->where('compemployees.dest_label_status', '!=', 'DELIVERED');
                })
                ->count();
            $cntOrderCompleted = Compemployees::
                where('compemployees.soft_del', '=', 0)
                ->where(function ($query) {
                    $query
                        ->where('compemployees.receive_label_status', '=', 'DELIVERED');
                })
                ->orWhere(function ($subQuery) use ($settings) {
                    $subQuery->where('compemployees.dest_flag', '=', 1)
                        ->where('compemployees.soft_del', '=', 0)
                        ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->where('compemployees.dest_label_status', '=', 'DELIVERED');
                })
                ->count();
                $totalAmt=Transactions::join('compemployees','compemployees.order_id','=','transactions.order_id')->where('compemployees.soft_del', 0)->where('transactions.status','success')->sum('amount');
                $orderAmount=Compemployees::where('soft_del', '=', 0)->where('send_flag',1)->where('rec_flag',1)->select('company_id', 'type_of_equip', 'order_amt')->get()->toArray();
                $deviceAmount=0;
                $commissionAmount=0;
                foreach ($orderAmount as $value) {
                    $priceSettings = Systemsettings::where('company_id', env('RR_COMPANY_ID'))->where('equipment_type',$value['type_of_equip'])->first();
                    $deviceAmount+=$priceSettings->order_amount;
                    $priceSettingsCompany = Systemsettings::where('company_id', $value['company_id'])->where('equipment_type',$value['type_of_equip'])->first();
                    $amount=$priceSettingsCompany->order_amount-$priceSettings->order_amount;
                    $commissionAmount+=$amount;
                }
        } else {
            $cntOrderInprogress = Compemployees::
                where('parent_comp_id', $settings->company_id)
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($query) {
                    $query
                        ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
                })
                ->orWhere(function ($subQuery) use ($settings) {
                    $subQuery->where('compemployees.dest_flag', '=', 1)
                        ->where('compemployees.soft_del', '=', 0)
                        ->where('compemployees.dest_label_status', '!=', 'DELIVERED')
                        ->where('compemployees.parent_comp_id', '=', $settings->company_id)
                    ;
                })
                ->count();
            $cntOrderCompleted = Compemployees::
                where('parent_comp_id', $settings->company_id)
                ->where('compemployees.soft_del', '=', 0)
                ->where(function ($query) {
                    $query
                        ->where('compemployees.receive_label_status', '=', 'DELIVERED');
                })
                ->orWhere(function ($subQuery) use ($settings) {
                    $subQuery->where('compemployees.dest_flag', '=', 1)
                        ->where('compemployees.soft_del', '=', 0)
                        ->where('compemployees.receive_label_status', '=', 'DELIVERED')
                        ->where('compemployees.dest_label_status', '=', 'DELIVERED')
                        ->where('compemployees.parent_comp_id', '=', $settings->company_id)
                    ;
                })
                ->count();
                $totalAmt=Transactions::join('compemployees','compemployees.order_id','=','transactions.order_id')->where('compemployees.soft_del', 0)->where('transactions.status','success')->where('transactions.company_id',$settings->company_id)->sum('amount');
                $orderAmount=Compemployees::where('soft_del', '=', 0)->where('send_flag',1)->where('rec_flag',1)->where('company_id',$settings->company_id)->select('type_of_equip')->get()->toArray();
                $deviceAmount=0;
                $commissionAmount=0;
                foreach ($orderAmount as $value) {
                    $priceSettings = Systemsettings::where('company_id', env('RR_COMPANY_ID'))->where('equipment_type',$value['type_of_equip'])->first();
                    $deviceAmount+=$priceSettings->order_amount;
                    $priceSettingsCompany = Systemsettings::where('company_id', $settings->company_id)->where('equipment_type',$value['type_of_equip'])->first();
                    $amount=$priceSettingsCompany->order_amount-$priceSettings->order_amount;
                    $commissionAmount+=$amount;
                }
        }

        return view('dashboard', [
            'inProCnt' => $cntOrderInprogress,
            'compCnt' => $cntOrderCompleted,
            'totalAmount'=>$totalAmt,
            'deviceAmount'=>$deviceAmount,
            'commissionAmount'=>$commissionAmount
        ]);
    }

    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');

        // Check if the log file exists
        if (File::exists($logFile)) {
            $lines = explode("\n", File::get($logFile));
            $logs = implode("\n", array_slice($lines, -1000)); // Show the last 100 lines

            // Filter only lines with INFO
            $infoLogs = collect(explode("\n", $logs))
                ->filter(function ($line) {
                    // return str_contains($line, 'INFO'); // Filter lines containing 'INFO'
                    return $line; // Filter lines containing 'INFO'
                })
                ->implode("\n"); // Combine filtered lines back into a single string
        } else {
            $infoLogs = "No logs available.";
        }
        return view('pages.dashboard.logs', ['logs' => $infoLogs]);
    }
    public function createOrderMailScript(Request $request,$id)
    {
        $settings = app('companySettings');
           $sendEmails = compemployees::where('id', $id)->first();
                $sendEmails->companyData=$settings->company;
                $sendEmails->logo=$settings->logo;
                $emailTemplate = "compPaymentFailedOrder";
                $emailTemplateSubject = "New Order Created – Pending Payment - Order #".$sendEmails->order_id."-".$sendEmails->id;
                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" =>env('MAIL_BCC_USERNAME3'),
                    "bcc" => [env('MAIL_BCC_USERNAME')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
                    "mailTemplate" => "mails.email_on_status_update",
                    "mailData" => $sendEmails,
                ];

                $this->mailService->sendMail($emailData);
    }
    public function labelOrderMailScript(Request $request,$id,Helper $helper)
    {
        $sendEmails = compemployees::where('id', $id)->first();
                $company = null;
                if ($sendEmails->return_service == "Sell This Equipment") {
                    $company = Companies::where("id", $sendEmails->company_id)->first();
                }


                $compSettingsEmail = Companysettings::where("company_id", $sendEmails->parent_comp_id)->first();
                $sendEmails->companyData=$compSettingsEmail->company;
                $sendEmails->logo=$compSettingsEmail->logo;
                //if($sendEmails->rec_flag == 1) { $recRes = json_decode($response, true); }
                $recRes = json_decode($sendEmails->send_labelresponse, true);
                $trackingNo = ($recRes) ? $recRes['tracking_number'] : '';
                $trackingUrl = ($recRes) ? $recRes['tracking_url_provider'] : '';
                $emailTemplate = "empEmailAfterLabelcreate";
                $emailTemplateSubject = "Your $sendEmails->type_of_equip Return Box is on the Way by " . $sendEmails->receipient_name;
                $filePath = storage_path('app/public/orderMsg/' . $id . '.pdf');

                // echo $compSettingsEmail->logo;
                $logo = asset("storage/logoImage/$compSettingsEmail->logo");
                // echo '<img src="data:image/png;base64,$logo " alt="" width="200px">';
                // exit();

                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" => env('MAIL_BCC_USERNAME3'),
                    "bcc" => [env('MAIL_BCC_USERNAME')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
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
                $sendEmails = compemployees::where('id', $id)->first();
                $sendEmails->companyData=$compSettingsEmail->company;
                $sendEmails->logo=$compSettingsEmail->logo;
                $emailTemplate = "compEmailAfterLabelcreate";

                $emailTemplateSubject = $sendEmails->type_of_equip . " Retrieval Box for Order #$sendEmails->order_id-$sendEmails->id - Shipped to $sendEmails->emp_first_name";
                $emailData = [
                    "template" => $emailTemplate,
                    "subject" => $emailTemplateSubject,
                    "to" => env('MAIL_BCC_USERNAME3'),
                    "bcc" => [env('MAIL_BCC_USERNAME')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'No Reply from ReturnDevice',
                    "title" => $emailTemplateSubject,
                    "mailTemplate" => "mails.email_on_status_update",
                    "mailData" => $sendEmails,
                    "company" => $company,
                    "trackingNo" => $trackingNo,
                    "trackingUrl" => $trackingUrl,
                    "logo" => $compSettingsEmail->logo
                ];

                $this->mailService->sendMail($emailData);
    }
      /**
     * MODULE: API
     * DESCRIPTION: API KEY SCREEN
     */
    public function api()
    {
        $user = auth()->user();
        return view('pages.dashboard.api', ['user' => $user]);
    }


      /**
     * MODULE: API
     * DESCRIPTION: GENERATE KEY FOR API
     */
    public function generateApiKey(Request $request)
    {
        try {
            $apiKey = Str::random(150);
            $user = User::where('id', Auth::user()->id)->update(['api_key' => $apiKey]);

            return response()->json(
                [
                    'message' => 'API key has generated!',
                    'status' => 'success',
                    'apiKey' => $apiKey,
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                    'status' => 'fail',
                    'apiKey' => '',
                ]
            );
        }

    }
     /**
     * MODULE: API Integration Instructions
     * DESCRIPTION: GENERATE KEY FOR API
     */
     public function apiIntegration()
    {
        return view('pages.dashboard.api_integration');
    }
}
