<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libraries\Services\Helper;
use App\Libraries\Services\MailService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Companies;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\HomeController;
class UserauthController extends Controller
{
    protected $mailService;
    protected $helper;
    protected $homeController;

    public function __construct(
        MailService $mailService,
        Helper $helper,
        HomeController $homeController
    ) {
        $this->mailService = $mailService;
        $this->helper = $helper;
        $this->homeController = $homeController;
    }

    public function login(Request $request)
    {
        $param['request'] = $request;
        $param['webpageName'] = "User Login - Return Device";
        $param['webpageDatePublished'] = "2024-01-31T10:04:01+00:00";
        $param['webpageDateModified'] = "2024-01-31T10:04:01+00:00";
        $param['breadcrumbListListTwoName'] = "User Login";
        $param['webSiteDescription'] = "To ensure your IT devices like laptops are returned safely, proper packing is crucial. We offer tips to handle the packing process and make them ready for shipment.";
        $param['title'] = "User Login - Return Device";
        $param['ogTitle'] = "User Login - Return Device";
        $res = $this->homeController->tagsFunction($param);

        $pageData = $res['pageData'];
        $metaData = $res['metaData'];
        if (Auth::check()) {
            return redirect()->route('home.index'); // NORMAL USER
        }
        return view('pages.Frontend.login', compact('pageData', 'metaData'));
    }

    public function loginSubmit(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $validatedData['email'])->first();
        if (!is_null($user)) {
            if (Auth::attempt($validatedData)) {
                if ($user->role == 3 || $user->role == 4) {
                    return redirect()->route('orders.list'); // NORMAL USER
                } else {
                    return redirect()->route('dashboard'); // ADMIN
                }

            } else {
                session()->flash('errorMsg', 'Invalid login credentials!');
                return redirect()->route('user.login');
            }
        } else {
            session()->flash('errorMsg', 'Email is not registered!');
            return redirect()->route('user.login');
        }
    }



    public function register(Request $request)
    {
        // $param['request'] = $request;
        // $param['webpageName'] = "User Register - Remote Retrieval";
        // $param['webpageDatePublished'] = "2024-01-31T10:04:01+00:00";
        // $param['webpageDateModified'] = "2024-01-31T10:04:01+00:00";
        // $param['breadcrumbListListTwoName'] = "User Register";
        // $param['webSiteDescription'] = "Remote Device Returns Made Easy";
        // $param['title'] = "User Register - Remote Retrieval";
        // $param['ogTitle'] = "User Register - Remote Retrieval";
        // $res = $this->homeController->tagsFunction($param);

        // $pageData = $res['pageData'];
        // $metaData = $res['metaData'];
        if (Auth::check()) {
            return redirect()->route('home.index'); // NORMAL USER
        }
        return view('pages.Frontend.register', compact('pageData', 'metaData'));
    }

    public function registerSubmit(Request $request)
    {
        $param = ['request' => $request];
        $r = $this->helper->validateFrmRequest($param);
        if ($r['status'] != "success") {
            return response()->json($r, 403); // 403 Forbidden status
        }

        if ($request->user_pkg == "basic") {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'company_name' => 'required',
                'email' => ['required', 'email'],
                'password' => 'required|string|min:8', //['min:8', 'confirmed'],
                'phone_number' => 'required',
                'g-recaptcha-response' => 'required|recaptchav3:register,0.9'
            ]);

            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $company_name = $request->company_name;
            $phone_number = $request->phone_number;
            $email = $request->email;
            $password = $request->password;

        } else {
            $request->validate([
                'ent_first_name' => 'required',
                'ent_last_name' => 'required',
                'ent_company_name' => 'required',
                'ent_email' => ['required', 'email'],
                'ent_password' => 'required|string|min:8', //['min:8', 'confirmed'],
                'ent_phone_number' => 'required',
            ], [
                'ent_first_name.required' => 'The User First Name field is required.',
                'ent_last_name.required' => 'The User Last Name field is required.',
                'ent_company_name.required' => 'The Company Name field is required.',
                'ent_email.required' => 'The User Email field is required.',
                'ent_password.required' => 'The User Password field is required.',
                'ent_phone_number.required' => 'The User Phone field is required.',
            ]);

            $first_name = $request->ent_first_name;
            $last_name = $request->ent_last_name;
            $company_name = $request->ent_company_name;
            $phone_number = $request->ent_phone_number;
            $email = $request->ent_email;
            $password = $request->ent_password;
        }

        $user = User::where('email', $email)->first();
        if (is_null($user)) {
            if ($request->user_pkg == "basic") {
                $userPkg = "basic";
            } else {
                $userPkg = "enterprise";
            }
            $data = [
                "name" => $first_name . ' ' . $last_name,
                "email" => $email,
                "password" => $password,
                "company_id" => 1,
                "role" => 1,
                "user_pkg" => $userPkg,
                "username" => explode("@", $email)[0],
                "phone" => $phone_number,
                "secret_code" => Str::random(100)
            ];
            $user = User::create($data);
            $token = $user->createToken("auth_token")->accessToken;

            if ($company_name) {
                $company_name;
            } else {
                $company_name = 'company';
            }
            $companyData = [
                "user_id" => $user->id,
                "company_name" => $company_name,
                "company_email" => $email
            ];
            $company = Companies::create($companyData);
            User::where('email', $email)
                ->update(['company_id' => $company->id]);

            $data = ['email' => $email, 'password' => $password];
            if (Auth::attempt($data)) {

                $emailData = [
                    "emailTemplate" => 'newSignupCredentials',
                    "subject" => 'Welcome to ReturnDevice.com - Sign up Information',
                    "to" => $email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'Return Device',
                    "title" => 'Welcome to ReturnDevice.com - Sign up Information',
                    "template" => "newSignupCredentials",
                    "mailData" => '',
                    "pwd" => $password,
                    "package" => $userPkg,
                    "mailTemplate" => 'mails.send_to_user'
                ];
                //$this->mailService->sendMail($emailData);


                $emailData = [
                    "emailTemplate" => 'newSignupFreeCoupon',
                    "subject" => 'First Laptop Retrieval Order Free on us.',
                    "to" => $email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'Return Device',
                    "title" => 'First Laptop Retrieval Order Free on us.',
                    "template" => "newSignupFreeCoupon",
                    "mailData" => '',
                    "company" => $company,
                    "pwd" => $password,
                    "package" => $userPkg,
                    "mailTemplate" => 'mails.send_to_user'
                ];
                // $this->mailService->sendMail($emailData);



                return redirect()->route('dashboard'); // NORMAL USER
            }


        } else {
            session()->flash('errorMsg', 'Email already registered!');
            return redirect()->route('user.register');
        }

    }

    public function lostPassword(Request $request)
    {

        if (Auth::check()) {
            return redirect()->route('home.index'); // NORMAL USER
        }

        return view('home.lostPassword');
    }

    /**
     * MODULE: FORGOT PASSWORD
     * DESCRIPTION: VALIDATE EMAIL FOR FORGOT PASSWORD
     */
    public function validateEmailForgotPassword(Request $request)
    {
        try {
            $validatedData = $request->validate(['email' => ['required', 'email']]);
            $subDomain = explode(env('CURR_DOMAIN'), $_SERVER['SERVER_NAME'])[0] ?? null;
            if ($subDomain != env('MAIN_DOMAIN')) {
                $company = Companies::where('company_domain', $subDomain)->first();
                $user = User::where('email', $validatedData['email'])
                    ->where('company_id', $company->id)
                    ->first();
            } else {
                $user = User::where('email', $validatedData['email'])
                    ->first();
            }

            if (!is_null($user)) {
                $token = $user->secret_code;

                $d = date("Y-m-d H:i:s", strtotime('+2 hours'));
                $d1 = base64_encode($d);
                $forgotPdURL = route('update.password') . "?email=$user->email&d=$d1&token=$token";
                $emailData = [
                    "emailTemplate" => 'forgotPDCredentials',
                    "subject" => 'Welcome to ReturnDevice.com - Forgot Your Password.',
                    "to" => $user->email,
                    "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
                    "cc" => "",
                    "fromEmail" => env('MAIL_USERNAME'),
                    "fromName" => 'Return Device',
                    "title" => 'Welcome to ReturnDevice.com - Forgot Your Password.',
                    "template" => "forgotPDCredentials",
                    "mailData" => '',
                    "mailTemplate" => 'mails.send_to_user',
                    'token' => $token,
                    'email' => $user->email,
                    'message' => 'Email exist!',
                    'status' => 1,
                    'd' => $d1,
                    'url' => $forgotPdURL
                ];
                // $this->mailService->sendMail($emailData);
                session()->flash('successMsg', 'We have sent a link to set new password to your email address. Password link will expire in 2 hours!');
                return redirect()->route('lost.password');
            } else {
                session()->flash('errorMsg', 'Email not exist!');
                return redirect()->route('lost.password');
            }
        } catch (\Exception $exception) {
            session()->flash('errorMsg', 'Email not exist!');
            return redirect()->route('lost.password');
        }

    }

    /**
     * MODULE: FORGOT PASSWORD
     * DESCRIPTION: UPDATE PASSWORD
     */
    public function updateForgotPassword(Request $request)
    {
        $validatedData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['min:8', 'confirmed']
        ]);

        $d = base64_decode($request->d);
        if (date('Y-m-d H:i:s') >= $d) {

            session()->flash('errorMsg', 'Link has expired!');
            return redirect()->route('update.password', ['email' => $request->email, 'd' => $request->d, 'token' => $request->sc]);
        }
        $user = User::where('email', $validatedData['email'])
            ->where('secret_code', $request->sc)
            ->first();
        if (!is_null($user)) {
            User::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            session()->flash('successMsg', 'Password has updated successfully!');
            return redirect()->route('login', ['email' => $request->email, 'd' => $request->d, 'token' => $request->sc]);
        } else {
            session()->flash('errorMsg', 'Password cannot update!');
            return redirect()->route('update.password', ['email' => $request->email, 'd' => $request->d, 'token' => $request->sc]);
        }
    }

    public function updatePassword(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home.index'); // NORMAL USER
        }
        $valid = 0;
        $user = User::where('email', $request->email)
            ->where('secret_code', $request->token)
            ->first();
        if (!is_null($user)) {
            $valid = 1;
        }
        return view('home.updatePassword', ['valid' => $valid]);
    }



}
