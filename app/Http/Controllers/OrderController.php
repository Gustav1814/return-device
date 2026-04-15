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
use Carbon\Carbon;
use App\Libraries\Services\Paypal;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\Coupon;
use Illuminate\Support\Facades\Crypt;
use App\Models\Labeltracking;
use DateTime;
use App\Libraries\Services\Shipping;
use App\Models\Emailonstatus;
class OrderController extends Controller
{
    protected $helper;
    protected $mailService;

    public function __construct(MailService $mailService, Helper $helper)
    {
        $this->mailService = $mailService;
        $this->helper = $helper;
    }
    /**
     * Display a listing of the resource.
     */


    public function inProgressOrders()
    {
        $perPage = env("PER_PAGE_DATA");
        $usersData = "";
        $settings = app('companySettings');

        $query = DB::table('compemployees')
            ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
            ->leftJoin('companies as order_parent_company', 'compemployees.parent_comp_id', '=', 'order_parent_company.id')
            ->select(
                'compemployees.id as item_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.return_service as return_srv',
                'compemployees.order_id as order_id',
                'compemployees.send_flag as box_label',
                'compemployees.rec_flag as device_label',
                'compemployees.dest_flag as dest_label',
                // 'compemployees.name as employee_name',
                'companies.company_name as company_name',
                'companies.created_at as created_at',
                'compemployees.created_at as order_createdAt',
                'compemployees.receipient_name as company',
                // 'orders.order_number',
                'users.id as userId',
                'orders.status as payStatus',
                'companies.company_name as order_company_name', // Order's company name
                'order_parent_company.company_name as order_parent_company_name' // Order's parent company name
            )
            ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
        if ($settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }
        $query->where('compemployees.soft_del', '!=', 1);
        $data = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);


        // $this->helper->getAdminSettings();
        return view('pages.dashboard.orderlist', ["data" => $data]);
    }

    public function filterInProgressOrders(Request $request)
    {
        $perPage = env("PER_PAGE_DATA");
        $settings = app('companySettings');
        $customSearch = "";
        // Base query with necessary joins
        $query = DB::table('compemployees')
            ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
            ->leftJoin('companies as order_parent_company', 'compemployees.parent_comp_id', '=', 'order_parent_company.id')

            ->select(
                'compemployees.id as item_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.return_service as return_srv',
                'compemployees.order_id as order_id',
                'companies.company_name as company_name',
                'companies.created_at as created_at',
                'users.id as userId',
                'orders.status as payStatus',
                'compemployees.send_flag as box_label',
                'compemployees.rec_flag as device_label',
                'compemployees.created_at as order_createdAt',
                'order_parent_company.company_name as order_parent_company_name',
                'compemployees.receipient_name as company'
            )
            ->where('compemployees.receive_label_status', '!=', 'DELIVERED');

        // Restrict to the company if applicable
        if ($settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }

        $query->where('compemployees.soft_del', '!=', 1);

        // Apply filters based on the request
        if ($request->filled('search_by')) {
            $searchBy = $request->input('search_by');
            if ($searchBy === 'Device Type' && $request->filled('device_type')) {
                $query->where('compemployees.type_of_equip', $request->input('device_type'));
            } elseif ($searchBy === 'Return Service' && $request->filled('return_service')) {
                $query->where('compemployees.return_service', $request->input('return_service'));
            } elseif ($searchBy === 'Date' && $request->filled('date')) {
                $query->whereDate('companies.created_at', $request->input('date'));
            } elseif ($searchBy === 'Custom Search' && $request->filled('search')) {
                $customSearch = $request->input('search');
                $query->where(function ($q) use ($customSearch) {
                    $q->where('compemployees.id', 'like', "%$customSearch%")
                        ->orWhere('compemployees.type_of_equip', 'like', "%$customSearch%")
                        ->orWhere('compemployees.return_service', 'like', "%$customSearch%")
                        ->orWhere('companies.company_name', 'like', "%$customSearch%");
                });
            }
        }

        // Order and paginate the results
        $data = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);

        // Add filter parameters to pagination links
        $data->appends($request->except('page'));

        // Return view with the filtered data
        return view('pages.dashboard.orderlist', [
            "data" => $data,
            'search_details' => [
                'search_by' => $searchBy,
                'device_type' => $request->input('device_type'),
                'return_service' => $request->input('return_service'),
                'date' => $request->input('date'),
                'search' => $customSearch
            ]
        ]);
    }

    public function completedOrders()
    {
        $perPage = env("PER_PAGE_DATA");
        $usersData = "";
        $settings = app('companySettings');

        $query = DB::table('compemployees')
            ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
            ->select(
                'compemployees.id as item_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.return_service as return_srv',
                'compemployees.send_flag as box_label',
                'compemployees.rec_flag as device_label',
                'compemployees.dest_flag as dest_label',
                'compemployees.order_id as order_id',
                // 'compemployees.name as employee_name',
                'companies.company_name as company_name',
                'companies.created_at as created_at',
                // 'orders.order_number',
                'users.id as userId'
            )
            ->where('compemployees.receive_label_status', 'DELIVERED');

        if ($settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }
        $query->where('compemployees.soft_del', '!=', 1);
        $data = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);



        // $this->helper->getAdminSettings();
        return view('pages.dashboard.completeOrderlist', ["data" => $data]);
    }

    public function filterCompletedOrders(Request $request)
    {
        $perPage = env("PER_PAGE_DATA");
        $settings = app('companySettings');
        $customSearch = "";
        // Base query with necessary joins
        $query = DB::table('compemployees')
            ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
            ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
            ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
            ->select(
                'compemployees.id as item_id',
                'compemployees.type_of_equip as equip_type',
                'compemployees.return_service as return_srv',
                'compemployees.order_id as order_id',
                'companies.company_name as company_name',
                'companies.created_at as created_at',
                'users.id as userId'
            )
            ->where('compemployees.receive_label_status', 'DELIVERED');

        // Restrict to the company if applicable
        if ($settings->company_id != env('RR_COMPANY_ID')) {
            $query->where('compemployees.parent_comp_id', $settings->company_id);
        }

        $query->where('compemployees.soft_del', '!=', 1);

        // Apply filters based on the request
        if ($request->filled('search_by')) {
            $searchBy = $request->input('search_by');
            if ($searchBy === 'Device Type' && $request->filled('device_type')) {
                $query->where('compemployees.type_of_equip', $request->input('device_type'));
            } elseif ($searchBy === 'Return Service' && $request->filled('return_service')) {
                $query->where('compemployees.return_service', $request->input('return_service'));
            } elseif ($searchBy === 'Date' && $request->filled('date')) {
                $query->whereDate('companies.created_at', $request->input('date'));
            } elseif ($searchBy === 'Custom Search' && $request->filled('search')) {
                $customSearch = $request->input('search');
                $query->where(function ($q) use ($customSearch) {
                    $q->where('compemployees.id', 'like', "%$customSearch%")
                        ->orWhere('compemployees.type_of_equip', 'like', "%$customSearch%")
                        ->orWhere('compemployees.return_service', 'like', "%$customSearch%")
                        ->orWhere('companies.company_name', 'like', "%$customSearch%");
                });
            }
        }

        // Order and paginate the results
        $data = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);

        // Add filter parameters to pagination links
        $data->appends($request->except('page'));


        // Return view with the filtered data
        return view('pages.dashboard.completeOrderlist', [
            "data" => $data,
            'search_details' => [
                'search_by' => $searchBy,
                'device_type' => $request->input('device_type'),
                'return_service' => $request->input('return_service'),
                'date' => $request->input('date'),
                'search' => $customSearch
            ]
        ]);
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


    public function usersList()
    {
        $perPage = env("PER_PAGE_DATA");
        $usersData = "";
        $settings = app('companySettings');
        $query = User::join('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.*', 'companies.company_name as company_name');
        if ($settings->company_id != 1) {
            $query->where('users.parent_comp_id', $settings->company_id);
        }
        $usersData = $query->paginate($perPage);

        return view('pages.dashboard.userlist', ["data" => $usersData]);
    }

    public function usersSearch(Request $request)
    {
        $perPage = env("PER_PAGE_DATA", 15);
        $settings = app('companySettings');
        $searchTerm = $request->input('search'); // Capture the search term

        $query = User::join('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.*', 'companies.company_name as company_name');

        if ($settings->company_id != 1) {
            $query->where('users.parent_comp_id', $settings->company_id);
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('users.id', $searchTerm)
                    ->orWhere('users.name', 'like', "%$searchTerm%")
                    ->orWhere('users.email', 'like', "%$searchTerm%")
                    ->orWhere('companies.company_name', 'like', "%$searchTerm%")
                    ->orWhere('users.phone', 'like', "%$searchTerm%")
                    ->orWhere('users.created_at', 'like', "%$searchTerm%");
            });
        }

        $usersData = $query->paginate($perPage);
        $usersData->appends($request->except('page'));
        // Pass the search term to the view
        return view('pages.dashboard.userlist', [
            'data' => $usersData,
            'searchTerm' => $searchTerm,
        ]);
    }


    public function createBulkOrder()
    {
        return view('pages.dashboard.bulkOrder', []);
    }

    public function createSingleOrder()
    {
        return view('pages.dashboard.singleOrder', []);
    }


    public function getCompanyDetails(Request $request)
    {
        try {
            $company = Companies::where('id', $request->cid)->first();
            return response()->json(
                [
                    'details' => $company,
                    'message' => 'Company data',
                    'status' => 'success',
                    'srv_type' => $request->return_srv
                ]
            );
        } catch (\Exception $exception) {

            return response()->json(
                [
                    //'message'     =>  __($exception->getMessage()),
                    'details' => '',
                    'message' => 'Company data not found',
                    'status' => 'fail'
                ]
            );
        }
    }

    public function userProfile()
    {
        try {
            $data = User::join('companies', 'companies.id', '=', 'users.company_id')
                ->where('users.email', Auth::user()->email)
                ->first(['companies.*', 'companies.company_name', 'users.*']);
            $setting = Settings::where('company_id', Auth::user()->company_id)->first();

            // return view('pages.profile', ['data' => $data, 'setting' => $setting]);
            return view('pages.dashboard.userProfile', ['data' => $data, 'setting' => $setting]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => __($e->getMessage()),
                    // 'message' => 'Company data cannot update at this time',
                    'status' => 'fail'
                ]
            );
        }

    }

    public function submituserProfile(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email',
        // ]);

        $rules = [
            'usr_name' => 'required',
            'usr_phone' => 'required',
            'comp_name' => 'required',
            'comp_rec_name' => 'required',
            'comp_email' => 'required',
            'comp_phone' => 'required',
            'comp_add_1' => 'required',
            'comp_city' => 'required',
            'comp_state' => 'required',
            'comp_zip' => 'required',

        ];

        // $validatedData = $request->validate([
        //     'usr_name' => ['required'],
        //     'usr_phone' => ['required']
        // ]);

        $messages = [
            'usr_name.required' => 'Please enter profile name.',
            'usr_phone.required' => 'Please enter phone number.',
            'comp_name.required' => 'Please enter company name.',
            'comp_rec_name.required' => 'Please enter company receipient name.',
            'comp_email.required' => 'Please enter company email.',
            'comp_phone.required' => 'Please enter company phone.',
            'comp_add_1.required' => 'Please enter company address 1.',
            'comp_city.required' => 'Please enter company city.',
            'comp_state.required' => 'Please enter company state.',
            'comp_zip.required' => 'Please enter company zip.',
        ];

        $validatedData = $request->validate($rules, $messages);


        // one - start
        try {
            // if ($request->usr_name == "") {
            //     return response()->json(['message' => 'Must fill name field', 'status' => 'fail']);
            // }
            // if ($request->usr_phone == "") {
            //     return response()->json(['message' => 'Must fill phone field', 'status' => 'fail']);
            // }

            User::where('email', Auth::user()->email)->update([
                'name' => $request->usr_name,
                'phone' => $request->usr_phone
            ]);

            Companies::where('id', Auth::user()->company_id)
                ->update([
                    'company_name' => $request->comp_name,
                    'receipient_name' => $request->comp_rec_name,
                    'company_add_1' => $request->comp_add_1,
                    'company_add_2' => $request->comp_add_2,
                    'company_phone' => $request->comp_phone,
                    'company_city' => $request->comp_city,
                    'company_state' => $request->comp_state,
                    'company_zip' => $request->comp_zip,
                    'company_email' => $request->comp_email,
                ]);

            session()->flash('successMsg', 'Data has been updated!');
            return redirect()->route('user.profile');
            // return response()->json(
            //     [
            //         'message' => 'Data has updated!',
            //         'status' => 'success'
            //     ]
            // );
        } catch (\Exception $exception) {

            return response()->json(
                [
                    // 'message'     =>  __($exception->getMessage()),
                    'message' => 'User data cannot update at this time',
                    'status' => 'fail'
                ]
            );
        }

        // one - end

        // two - start
        // try {
        //     if (
        //         $request->comp_name == "" || $request->comp_rec_name == ""
        //         || $request->comp_add_1 == "" || $request->comp_city == ""
        //         || $request->comp_state == "" || $request->comp_email == ""
        //     ) {
        //         return response()->json(['message' => 'Must fill all fields', 'status' => 'fail']);
        //     }
        //     Companies::where('id', Auth::user()->company_id)
        //         ->update([
        //             'company_name' => $request->comp_name,
        //             'receipient_name' => $request->comp_rec_name,
        //             'company_add_1' => $request->comp_add_1,
        //             'company_add_2' => $request->comp_add_2,
        //             'company_phone' => $request->comp_phone,
        //             'company_city' => $request->comp_city,
        //             'company_state' => $request->comp_state,
        //             'company_zip' => $request->comp_zip,
        //             'company_email' => $request->comp_email,
        //         ]);
        //     return response()->json(
        //         [
        //             'message' => 'Company data has updated!',
        //             'status' => 'success'
        //         ]
        //     );
        // } catch (\Exception $exception) {

        //     return response()->json(
        //         [
        //             //'message'     =>  __($exception->getMessage()),
        //             'message' => 'Company data cannot update at this time',
        //             'status' => 'fail'
        //         ]
        //     );
        // }

        // two - end

        // three - start

        // $data = array();
        // try {
        //     if ($request->set_usr_phone == "" && isset($request->sms_flag)) {
        //         return response()->json(['message' => 'Must fill text field', 'status' => 'fail']);
        //     }
        //     if ($request->user_email == "" && isset($request->email_flag)) {
        //         return response()->json(['message' => 'Must fill text field', 'status' => 'fail']);
        //     }

        //     if ($request->set_usr_phone != "") {
        //         $data['sms_val'] = $request->set_usr_phone;
        //     }
        //     if ($request->user_email != "") {
        //         $data['email_val'] = $request->user_email;
        //     }
        //     $settings = Settings::where('company_id', Auth::user()->company_id)->first();
        //     $sms_flag = 0;
        //     $email_flag = 0;
        //     if ($request->sms_flag == 'yes') {
        //         $sms_flag = 1;
        //     }
        //     if ($request->email_flag == 'yes') {
        //         $email_flag = 1;
        //     }
        //     if (is_null($settings)) {
        //         $data = ["company_id" => Auth::user()->company_id];
        //         $data['sms_flag'] = 0;
        //         $data['email_flag'] = 0;
        //         if (isset($request->sms_flag)) {
        //             $data['sms_flag'] = $sms_flag;
        //         }
        //         if (isset($request->email_flag)) {
        //             $data['email_flag'] = $email_flag;
        //         }
        //         if (isset($request->user_email)) {
        //             $data['email_val'] = $request->user_email;
        //         }
        //         if (isset($request->set_usr_phone)) {
        //             $data['sms_val'] = $request->set_usr_phone;
        //         }
        //         Settings::create($data);
        //     } else {
        //         if (isset($request->sms_flag)) {
        //             $data['sms_flag'] = $sms_flag;
        //         }
        //         if (isset($request->email_flag)) {
        //             $data['email_flag'] = $email_flag;
        //         }
        //         if (isset($request->user_email)) {
        //             $data['email_val'] = $request->user_email;
        //         }
        //         if (isset($request->set_usr_phone)) {
        //             $data['sms_val'] = $request->set_usr_phone;
        //         }

        //         Settings::where('company_id', Auth::user()->company_id)->update($data);
        //     }
        //     return response()->json(
        //         [
        //             'message' => 'Data has updated!',
        //             'status' => 'success'
        //         ]
        //     );
        // } catch (\Exception $exception) {

        //     return response()->json(
        //         [
        //             'message' => __($exception->getMessage()),
        //             // 'message' => 'Data cannot update at this time',
        //             'status' => 'fail'
        //         ]
        //     );
        // }

        // three - end

    }

    public function companySettings()
    {
        return view('pages.dashboard.companySettings', []);
    }

    public function companySettingsSubmit(Request $request)
    {
        $settings = app('companySettings');

        // ✅ Validation for both logo & favicon
        $rules = [
            'theme_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:max_width=300,max_height=100',
            'theme_fav'    => 'nullable|image|mimes:jpeg,png,jpg,ico|max:2048|dimensions:max_width=100,max_height=100',
        ];
        $request->validate($rules);

        $company = Companies::where('id', $settings->company_id)->first();

        $settingData = [
            'theme'           => $request->theme_color,
            'btn_bg_color'    => $request->button_background_color,
            'btn_font_color'  => $request->button_text_color,
            'theme_font_color'=> $request->text_color
        ];

        // ✅ Handle Theme Logo
        if ($request->hasFile('theme_logo')) {
            $file = $request->file('theme_logo');
            $filename = $company->company_domain . '.' . $file->getClientOriginalExtension();

            if (
                isset($settings->logo)
                && file_exists(public_path("storage/logoImage/{$settings->logo}"))
                && $settings->logo != $filename
            ) {
                // unlink(public_path("storage/logoImage/{$settings->logo}"));
            }

            try {
                $filePath = $file->storeAs('logoImage', $filename, 'public');
                $settingData['logo'] = $filename;
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => __($exception->getMessage()),
                    'status'  => 'fail'
                ]);
            }
        }

        // ✅ Handle Favicon
        if ($request->hasFile('theme_fav')) {
            $file = $request->file('theme_fav');
            $faviconName = $company->company_domain . '_favicon.' . $file->getClientOriginalExtension();

            if (
                isset($settings->favicon)
                && file_exists(public_path("storage/favicon/{$settings->favicon}"))
                && $settings->favicon != $faviconName
            ) {
                // unlink(public_path("storage/favicon/{$settings->favicon}"));
            }

            try {
                $filePath = $file->storeAs('favicon', $faviconName, 'public');
                $settingData['favicon'] = $faviconName;
            } catch (\Exception $exception) {
                return response()->json([
                    'message' => __($exception->getMessage()),
                    'status'  => 'fail'
                ]);
            }
        }

        // ✅ Update DB
        Companysettings::where('company_id', $settings->company_id)
            ->update($settingData);

        // ✅ Clear Session Cache
        Session::forget('companySettings');
        Session::forget('expires_at');

        return redirect()->route('company.settings')->with('success', 'Settings updated successfully!');
    }



    public function submitOrderbyCSV(Request $request)
    {
        // Validate the request to ensure a file is uploaded
        $request->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        // Handle the file upload
        if ($request->hasFile('csvFile')) {
            $file = $request->file('csvFile');
            $path = $file->getRealPath();

            // Read the CSV file using League\Csv package
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0); // Set the CSV header offset

            // Get the records from the CSV
            $records = $csv->getRecords();

            $requiredColumns = [
                'employee_first_name',
                'employee_last_name',
                'employee_email',
                'employee_phone',
                'employee_address1',
                'employee_city',
                'employee_state',
                'employee_postalcode',
                'company_name',
                'company_receipient_name',
                'company_email',
                'company_phone',
                'company_address1',
                'company_city',
                'company_state',
                'company_postalcode',
                'type_of_equipment',
                'return_service'
            ];

            // Process each record
            $param = array();
            $param['requiredColumns'] = $requiredColumns;
            $chk = 1;
            $r = 0;
            $orderId = null;
            foreach ($records as $record) {
                if ($chk == 1) {
                    $param['record'] = $record;
                    $validateCSV = $this->validateCSVHeader($param);
                    if ($validateCSV) {
                        return $validateCSV;
                    }
                    $chk = 2;
                }

                $createOrder = $this->orderCreatebyCSV($record, $orderId);
                if (isset($createOrder) && $createOrder->original['status'] == 'success') {
                    $r = $r + 1;
                    $orderId = $createOrder->original['order_id'];
                }
            }

            return response()->json(['message' => ' order has created!'], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function validateCSVHeader($param)
    {
        $requiredColumns = $param['requiredColumns'];
        $record = $param['record'];
        $missingColumns = array_diff($requiredColumns, array_keys($record));

        if (!empty($missingColumns)) {
            return response()->json([
                'message' => implode(', ', $missingColumns)
            ], 422);
        }
    }



    public function orderCreatebyCSV($record, $orderId = null)
    {
        try {
            // $order = Orders::where('company_id', Auth::user()->company_id)
            //     ->where('status', 'pending')->first();
            if (is_null($orderId)) {
                $orderData = ["company_id" => Auth::user()->company_id, "status" => 'pending'];
                $order = Orders::create($orderData);
                $orderId = $order->id;
            }
            // else{
            //     $orderId;
            // }
            // $orderData = ["company_id" => Auth::user()->company_id, "status" => 'pending'];
            // $order     = Orders::create($orderData);
            $settings = app('companySettings');
            $employeeData = [
                "emp_first_name" => $record['employee_first_name'],
                "emp_last_name" => $record['employee_last_name'],
                "emp_email" => $record['employee_email'],
                "emp_phone" => $record['employee_phone'],
                "emp_add_1" => $record['employee_address1'],
                "emp_add_2" => isset($record['employee_address2']) ? $record['employee_address2'] : '',
                "emp_city" => $record['employee_city'],
                "emp_state" => $record['employee_state'],
                "emp_pcode" => $record['employee_postalcode'],
                "return_service" => $record['return_service'],
                "type_of_equip" => $record['type_of_equipment'],
                "company_id" => Auth::user()->company_id,
                "parent_comp_id" => $settings->company_id,
                "user_id" => Auth::user()->id,
                "order_id" => $orderId,
                "receipient_name" => $record['company_name'],
                "receipient_person" => $record['company_receipient_name'],
                "receipient_email" => $record['company_email'],
                "receipient_phone" => $record['company_phone'],
                "receipient_add_1" => $record['company_address1'],
                "receipient_add_2" => isset($record['company_address2']) ? $record['company_address2'] : '',
                "receipient_city" => $record['company_city'],
                "receipient_state" => $record['company_state'],
                "receipient_zip" => $record['company_postalcode'],
                "send_flag" => 0,
                "rec_flag" => 0,
                "source" => "CSV",
                "custom_msg" => isset($record['custom_message']) ? $record['custom_message'] : ''
            ];

            if ($record['insurance_amount'] != null && $record['insurance_amount'] != 0) {
                $employeeData['insurance_active'] = 1;
                $employeeData['insurance_amount'] = $record['insurance_amount'];
            }
            $settings = Systemsettings::where('equipment_type', ucfirst($record['type_of_equipment']))
                ->first();
            if ($settings) {
                $orderAmount = $settings->order_amount;
            } else {
                $orderAmount = env('ORDER_AMT');
            }
            //$employeeData['custom_msg'] = '';
            $employeeData['order_amt'] = $orderAmount;
            $employee = Compemployees::create($employeeData);


            return response()->json(
                [
                    'message' => 'Employee data has updated!',
                    'status' => 'success',
                    'order_id' => $order->id
                ]
            );
        } catch (\Exception $exception) {

            return response()->json(
                [
                    'message' => __($exception->getMessage()),
                    // 'message'     =>  'Employee data cannot update at this time',
                    'status' => 'fail'
                ]
            );
        }
    }



    public function subOrderEdit(Request $request)
    {
        $adminCompanyId = (int) env('RR_COMPANY_ID', 1);
        $authCompanyId = (int) (Auth::user()->company_id ?? 0);
        if ($authCompanyId !== $adminCompanyId) {
            return redirect()->route('dashboard')->with('error', 'Only admin can edit orders.');
        }

        $settings = app('companySettings');
        $newEmpData = '';
        $query = Compemployees::where('id', $request->sid);
        if ((int) $settings->company_id !== $adminCompanyId) {
            $query->where('parent_comp_id', $settings->company_id);
        }
        $data = $query->first();


        if (is_null($data)) {
            return redirect()->route('dashboard')->with('error', 'No order exist!');
        }

        if ($data->order_id) {
            // $ord = Orders::where("id", $data->order_id)->where("status", "pending")->first();
            $ord = Orders::where("id", $data->order_id)->first();
            if (is_null($ord)) {
                return redirect()->route('dashboard')->with('error', 'No order exist!');
            }
        }

        if ($data->return_additional_srv != null) {
            if ($data->return_additional_srv == 2) {
                $newEmpData = json_decode($data->new_emp_data, true);
            }
        }
        return view('pages.dashboard.edit_order', ['data' => $data, 'newEmpData' => $newEmpData]);
    }

    public function subOrderEditPost(Request $request)
    {
        try {
            $adminCompanyId = (int) env('RR_COMPANY_ID', 1);
            $authCompanyId = (int) (Auth::user()->company_id ?? 0);
            if ($authCompanyId !== $adminCompanyId) {
                session()->flash('errorMsg', 'Only admin can edit orders.');
                return response()->json(
                    [
                        'message' => 'Only admin can edit orders.',
                        'status' => 'fail'
                    ],
                    403
                );
            }

            $subOrder=Compemployees::where('id', $request->sid)->first();
            if (is_null($subOrder)) {
                session()->flash('errorMsg', 'Order Not Found!');
                return response()->json(
                    [
                        'message' => 'Order Not Found!',
                        'status' => 'fail'
                    ]
                );
            }
            if ($request->return_srv == 1) {
                $returnSrv = "Return To Company";
            }
            if ($request->return_srv == 2) {
                $returnSrv = "Sell This Equipment";
            }

            $settings = Systemsettings::where('equipment_type', ucfirst($request->equipment_type))
                ->where('company_id', $subOrder->parent_comp_id)->first();
            if ($settings) {
                $orderAmount = $settings->order_amount;
            } else {
                $orderAmount = env('ORDER_AMT');
            }


            $employeeData = [
                "emp_first_name" => $request->emp_firstname,
                "emp_last_name" => $request->emp_lastname,
                "emp_email" => $request->emp_email,
                "emp_phone" => $request->emp_phone,
                "emp_add_1" => $request->emp_add1,
                "emp_add_2" => $request->emp_add2,
                "emp_city" => $request->emp_city,
                "emp_state" => $request->emp_state,
                "emp_pcode" => $request->emp_pcode,
                "return_service" => $returnSrv,
                "type_of_equip" => $request->equipment_type,
                "receipient_name" => $request->comp_name,
                "receipient_person" => $request->comp_rec_person,
                "receipient_email" => $request->comp_email,
                "receipient_phone" => $request->comp_phone,
                "receipient_add_1" => $request->comp_add_1,
                "receipient_add_2" => $request->comp_add_2,
                "receipient_city" => $request->comp_city,
                "receipient_state" => $request->comp_state,
                "receipient_zip" => $request->comp_zip,
                "insurance_active" => $request->ins_tick,
                "insurance_amount" => $request->ins_amount,
                "custom_msg" => $request->custom_msg,
                "order_amt" => $orderAmount
            ];

            if (isset($request->data_destruction)) {
                $employeeData['return_additional_srv'] = $request->data_destruction;
                if ($request->data_destruction == 2) {
                    $newEmpData = array();
                    $newEmpData['newemp_first_name'] = $request->new_emp_firstname;
                    $newEmpData['newemp_last_name'] = $request->new_emp_lastname;
                    $newEmpData['newemp_state'] = $request->new_emp_state;
                    $newEmpData['newemp_phone'] = $request->new_emp_phone;
                    $newEmpData['newemp_email'] = $request->new_emp_email;
                    $newEmpData['newemp_add_2'] = $request->new_emp_add2;
                    $newEmpData['newemp_add_1'] = $request->new_emp_add1;
                    $newEmpData['newemp_city'] = $request->new_emp_city;
                    $newEmpData['newemp_zip'] = $request->new_emp_pcode;
                    $newEmpData['newemp_msg'] = $request->new_custom_msg;
                    $employeeData['new_emp_data'] = json_encode($newEmpData);
                }
            } else {
                $employeeData['return_additional_srv'] = null;
            }

            $company = Compemployees::where('id', $request->sid)
                ->update($employeeData);

            if (is_null($company)) {
                session()->flash('errorMsg', 'Employee data not update!');
                return response()->json(
                    [
                        'message' => 'Employee data not update',
                        'status' => 'fail'
                    ]
                );
            }
            // $employee = Compemployees::create($employeeData);

            // {{ route('suborder.edit',$data->id) }}
            // return redirect()->route('suborder.edit',['sid'=>$request->sid])->with('success', 'Data has updated!');
            session()->flash('successMsg', 'Employee data has updated!');
            return response()->json(
                [
                    'message' => 'Employee data has updated!',
                    'status' => 'success',
                    'srv_type' => $request->return_srv
                ]
            );
        } catch (\Exception $exception) {

            session()->flash('errorMsg', 'Employee data cannot update at this time');
            return response()->json(
                [
                    // 'message' => __($exception->getMessage()),
                    'message' => 'Employee data cannot update at this time',
                    'status' => 'fail'
                ]
            );
        }
    }


    public function orderDetail(Request $request)
    {
        $subOrderId = $request->oid;

        $dataExist = Orders::with('compemployees')
            ->join('compemployees', 'orders.id', '=', 'compemployees.order_id')
            ->where('compemployees.id', '=', $subOrderId)
            ->where('compemployees.soft_del', 0)
            ->select('compemployees.*', 'orders.status as order_status')
            ->exists();

        if (!$dataExist) {
            return redirect()->route('dashboard')->with('error', 'No record found');
        }

        $employees = Orders::with('compemployees')
            ->join('compemployees', 'orders.id', '=', 'compemployees.order_id')
            ->where('compemployees.id', '=', $subOrderId)
            ->where('compemployees.soft_del', 0)
            ->select('compemployees.*', 'orders.status as order_status')
            ->first();

        $sendRes = null;
        $recRes = null;
        $destRes = null;
        $newEmpData = null;
        $dd_amt = 0;
        $dd_amtDet = '';
        $sendTrackNo = '';
        $sendTrackURL = '';
        $recTrackNo = '';
        $recTrackURL = '';
        $destTrackNo = '';
        $destTrackURL = '';
        $trackDetails = '';
        $sendRes = '';
        $recRes = '';
        $ins_amountDet = '';
        $PayDetCpn = '';

        // WORK FOR PAYMENT DETAILS - START
        $ins_amount = $this->helper->getInsuranceAmountSingleorder($employees);
        if ($ins_amount != 0) {
            $ins_amountDet = "Insurance Amount: <strong>$$ins_amount</strong>";
        }
        if (
            Transactions::where("order_id", $employees->order_id)
                ->where("status", "success")
                ->exists()
        ) {
            $amount = $employees->order_amt;
            $transaction = Transactions::where("order_id", $employees->order_id)
                ->where("status", "success")
                ->first();
            $transResponse = json_decode($transaction->trans_response, true);
            if (isset($transResponse['coupon'])) {
                if (isset($transResponse['discount'])) {
                    $discount = $transResponse['discount'];
                } else {
                    $discount = $amount - $transaction->amount;
                }
                $PayDetCpn = "Coupon Discount Amount: <strong>$" . $discount . "</strong>";
            }
        }

        if ($employees->return_additional_srv != null) {
            if ($employees->return_additional_srv == 1) {
                $dd_amt += env('DD_COMPANY');
            } else if ($employees->return_additional_srv == 2) {
                $dd_amt += env('DD_NEW_EMP');
            }
        }
        // WORK FOR PAYMENT DETAILS - END

        // DATA DESTUCTION FLAG - START
        $ddSrv = '';
        if ($employees->return_additional_srv != null) {
            $dataDestSrv = $employees->return_additional_srv;
            if ($dataDestSrv == 1) {
                $ddSrv = 'Service: <strong>Data destruction and return to company</strong>';
            } else if ($dataDestSrv == 2) {
                $ddSrv = 'Service: <strong>Data destruction and deliver to new employee</strong>';
            }
        }
        // DATA DESTRUCTION FLAG - END


        // $ins_amount = $this->getInsuranceAmount($employees); // GET INSURANCE AMOUNT

        // LABEL TRACK - START


        if ($employees->send_flag == 1) {
            $sendRes = json_decode($employees->send_labelresponse, true);

            $sendTrackNo = ($sendRes['tracking_number']) ? $sendRes['tracking_number'] : '';
            $sendTrackURL = ($sendRes['tracking_url_provider']) ? $sendRes['tracking_url_provider'] : '';
        }


        if ($employees->rec_flag == 1) {
            $recRes = json_decode($employees->receive_labelresponse, true);

            $recTrackNo = ($recRes['tracking_number']) ? $recRes['tracking_number'] : '';
            $recTrackURL = ($recRes['tracking_url_provider']) ? $recRes['tracking_url_provider'] : '';
        }

        if ($employees->dest_flag == 1) {
            $destRes = json_decode($employees->dest_labelresponse, true);

            $destTrackNo = ($destRes['tracking_number']) ? $destRes['tracking_number'] : '';
            $destTrackURL = ($destRes['tracking_url_provider']) ? $destRes['tracking_url_provider'] : '';
        }


        if ($recTrackNo) {
            $trackDetails = $employees->type_of_equip . " Box Tracking:<a target='_blank' href='" . $sendTrackURL . "'>" . $sendTrackNo . "</a><br/>
      " . $employees->type_of_equip . " Return Tracking: <a  target='_blank' href=" . $recTrackURL . ">" . $recTrackNo . "</a>";
            if ($destTrackNo) {
                $trackDetails .= "<br/>After Destruction: <a  target='_blank' href=" . $destTrackURL . ">" . $destTrackNo . "</a>";
            }
        }
        // LABEL TRACK - END


        return view('pages.dashboard.order_detail', [
            'data' => $employees,
            'trackDetails' => $trackDetails,
            'insAmt' => $ins_amountDet,
            'PayDetCoupon' => $PayDetCpn,
            'ddSrv' => $ddSrv,
            'dd_amtDet' => $dd_amtDet,
            'sendRes' => $sendRes,
            'recRes' => $recRes,
            'destRes' => $destRes,
            // 'newEmpData' => $newEmpData
        ]);
    }

    public function subOrderDelete(Request $request)
    {
        $subOrder = Compemployees::where('parent_comp_id', Auth::user()->company_id)->first();
        if (is_null($subOrder)) {
            session()->flash('error', 'You have no permission to delete order!');
            // return redirect()->route('orders.list')->with('error', 'You have no permission to delete order!');
            return response()->json(
                [
                    // 'message' => __($exception->getMessage()),
                    'message' => 'You have no permission to delete order!',
                    'status' => 'fail'
                ]
            );
        }

        Compemployees::where('id', $request->oid)->update(['soft_del' => 1]);
        session()->flash('success', 'Order has deleted!');
        Log::info("Sub-order ($request->oid) has deleted by user (" . Auth::user()->id . ")");
        // return redirect()->route('orders.list')->with('success', 'Order has deleted!');
        return response()->json(
            [
                // 'message' => __($exception->getMessage()),
                'message' => 'Order has deleted!',
                'status' => 'success'
            ]
        );

    }



    public function priceSettings()
    {
        $settings = app('companySettings');
        $defSettings = Systemsettings::where('company_id', env('RR_COMPANY_ID'))->get();
        if (Systemsettings::where('company_id', $settings->company_id)->exists()) {
            $compSettings = Systemsettings::where('company_id', $settings->company_id)->get();
        } else {
            $now = Carbon::now();
            $set = [
                [
                    "company_id" => $settings->company_id,
                    "order_amount" => $defSettings[0]->order_amount,
                    "equipment_type" => "Laptop",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "company_id" => $settings->company_id,
                    "order_amount" => $defSettings[1]->order_amount,
                    "equipment_type" => "Monitor",
                    "created_at" => $now,
                    "updated_at" => $now,
                ]
            ];
            Systemsettings::insert($set);
            $compSettings = Systemsettings::where('company_id', $settings->company_id)->get();
        }

        return view('pages.dashboard.priceSettings', compact('defSettings', 'compSettings'));
    }

    public function priceSettingsSubmit(Request $request)
    {
        $settings = app('companySettings');
        $laptop = ['order_amount' => $request->device_Laptop];
        $monitor = ['order_amount' => $request->device_Monitor];
        $defaultLaptopPrice = Systemsettings::select('order_amount')->where('company_id', env("RR_COMPANY_ID"))
            ->where("equipment_type", "Laptop")->first();
        $defaultMonitorPrice = Systemsettings::select('order_amount')->where('company_id', env("RR_COMPANY_ID"))
            ->where("equipment_type", "Monitor")->first();
        if ($request->device_Laptop < $defaultLaptopPrice->order_amount && $settings->company_id != env("RR_COMPANY_ID")) {
            session()->flash('error', "The laptop price cannot be less than the default value of $$defaultLaptopPrice->order_amount");
            return redirect()->route('price.settings');
        }
        if ($request->device_Monitor < $defaultMonitorPrice->order_amount && $settings->company_id != env("RR_COMPANY_ID")) {
            echo $request->device_Monitor . '-' . $defaultLaptopPrice->order_amount;
            session()->flash('error', "The monitor price cannot be less than the default value of $$defaultMonitorPrice->order_amount");
            return redirect()->route('price.settings');
        }

        try {

            Systemsettings::where('company_id', $settings->company_id)
                ->where("equipment_type", "Laptop")
                ->update($laptop);
            Systemsettings::where('company_id', $settings->company_id)
                ->where("equipment_type", operator: "Monitor")
                ->update($monitor);
            session()->flash('success', 'Price has been updated!');
            return redirect()->route('price.settings');
        } catch (\Exception $exception) {

            session()->flash('error', __("Price cannot update at this time!"));
            return redirect()->route('price.settings');

            // return response()->json(
            //     [
            //         'message' => __($exception->getMessage()),
            //         // 'message'     =>  'Employee data cannot update at this time',
            //         'status' => 'fail'
            //     ]
            // );
        }
    }




    public function orderPay(Request $request, Helper $helper)
    {
        $data = [];

        if ($request->oid) {
            $perPage = 15;
            $data = Orders::join('compemployees', 'compemployees.order_id', '=', 'orders.id')
                ->where('compemployees.parent_comp_id', Auth::user()->company_id)
                ->where('orders.status', 'pending')
                ->where('orders.id', $request->oid)
                ->where('compemployees.soft_del', 0)
                // ->get(['orders.*', 'compemployees.*']);
                ->paginate($perPage, ['orders.*', 'compemployees.*'])
                ->appends(['oid' => $request->oid]);
        }
        if (count($data) == 0) {
            return redirect()->route('dashboard')->with('error', 'Invalid Order ID!');
        }

        // ORDER LIST - START
        // $perPage = env("PER_PAGE_DATA");
        // $usersData = "";
        // $settings = app('companySettings');

        // $query = DB::table('compemployees')
        //     ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
        //     ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
        //     ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
        //     ->select(
        //         'compemployees.id as item_id',
        //         'compemployees.type_of_equip as equip_type',
        //         'compemployees.return_service as return_srv',
        //         'compemployees.order_id as order_id',
        //         // 'compemployees.name as employee_name',
        //         'companies.company_name as company_name',
        //         'companies.created_at as created_at',
        //         // 'orders.order_number',
        //         'users.id as userId',
        //         'orders.status as payStatus',
        //     )
        //     ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
        // if ($settings->company_id != env('RR_COMPANY_ID')) {
        //     $query->where('compemployees.parent_comp_id', $settings->company_id);
        // }
        // $query->where('compemployees.soft_del', '!=', 1);
        // $orderList = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);
        // ORDER LIST - END



        $ins_amount = $this->helper->getInsuranceAmount($data); // GET INSURANCE AMOUNT

        $settings = Systemsettings::latest('id')->first();
        if ($settings) {
            $orderAmount = $settings->order_amount;
        } else {
            $orderAmount = env('ORDER_AMT');
        }

        // ORDER AMT - START
        if ($data[0]->order_amt) {
            $ord_amt = $data[0]->order_amt;
        } else {
            $ord_amt = $orderAmount;
        }

        $ord_amt = 0;
        $dd_amt = 0;
        foreach ($data as $dt) {
            // $amt = $helper->getDeviceAmount($dt->type_of_equip);
            // $ord_amt += $amt;
            $ord_amt += $dt->order_amt;
            // DD AMT
            if ($dt->return_additional_srv != null) {
                if ($dt->return_additional_srv == 1) {
                    $dd_amt += env('DD_COMPANY');
                } else if ($dt->return_additional_srv == 2) {
                    $dd_amt += env('DD_NEW_EMP');
                }
            }

        }
        // ORDER AMT - END
        return view('pages.dashboard.orders_pay', [
            'employeesCount' => count($data),
            'totalAmt' => ($ord_amt) + $ins_amount + $dd_amt,
            'employees' => '',
            'ins_amount' => $ins_amount,
            'DD_amt' => $dd_amt,
            // 'orderList' => $orderList,
            'data' => $data
        ]);
    }




    public function paySub(Request $request, Paypal $paypal, helper $helper)
    {

        $data = Orders::join('compemployees', 'compemployees.order_id', '=', 'orders.id')
            ->where('compemployees.parent_comp_id', Auth::user()->company_id)
            ->where('orders.status', 'pending')
            ->where('orders.id', $request->oid)
            ->where('compemployees.soft_del', 0)
            ->get(['orders.*', 'compemployees.*']);

        if (count($data) == 0) {
            $d = ['status' => "fail", 'message' => 'Invalid order'];
            return response()->json($d);
        }

        // $settings = Systemsettings::latest('id')->first();
        // if ($settings) {
        //     $amount = $settings->order_amount;
        // } else {
        //     $amount = env("ORDER_AMT");
        // }

        // ORDER AMT - START

        // if ($data[0]->order_amt) {
        //     $amount = $data[0]->order_amt * count($data);
        // } else {
        //     $amount = $amount * count($data);
        // }
        $amount = 0;
        $dd_amt = 0;
        foreach ($data as $dt) {
            $amount += $dt['order_amt'];

            if ($dt->return_additional_srv != null) {
                if ($dt->return_additional_srv == 1) {
                    $dd_amt += env('DD_COMPANY');
                } else if ($dt->return_additional_srv == 2) {
                    $dd_amt += env('DD_NEW_EMP');
                }
            }

        }
        // ORDER AMT - END
        if ($amount == 0) {
            $d = ['status' => "fail", 'message' => 'Invalid amount'];
            return response()->json($d);
        }

        $ins_amount = $this->helper->getInsuranceAmount($data); // GET INSURANCE AMOUNT
        $amount = $amount + $ins_amount + $dd_amt;              // ADD INSURANCE AMOUNT IN TOTAL & DD amt

        // COUPON - START
        if ($request->cpn) {
            $param['coupon'] = $request->cpn;
            $param['orderId'] = $request->oid;

            // COUPON FOR FREE ORDER - START
            if ($request->fcpn == 1) {
                $param['discount'] = $amount;
                $param['ins_amount'] = $ins_amount;
                $param['orderCnt'] = count($data);


                $coupon = Coupon::where("coupon", $request->cpn)->where("status", 1)->first();
                if (is_null($coupon)) {
                    $d = ['status' => "fail", 'message' => 'Invalid Coupon'];
                    return response()->json($d);
                }
                if ($coupon->freeall == 1 && $ins_amount == 0) {
                    $r = $this->createFreeOrder($param);
                    return response()->json($r);
                }
            }
            // COUPON FOR FREE ORDER - END

            $discountObj = $helper->get_discount($param); // GET DISCOUNT
            $ins_amount = $discountObj->original['insurance'];
            $amount = $discountObj->original['totalAmt'];
            $discount = $discountObj->original['discount'];
            $amountTotal = round($amount + $discount, 2);
        }
        // COUPON - END

        $cardNumber = $request->cc_no;
        $cardCvv = $request->cvv;
        $cardholderName = $request->cardholder_name;
        if (strlen($request->cc_month) == 1) {
            $cardExpireMonth = '0' . $request->cc_month;
        } else {
            $cardExpireMonth = $request->cc_month;
        }
        $cardExpireYear = $request->cc_year;
        $billingCity = $request->comp_city;
        $billingState = $request->comp_state;
        $billingZip = $request->comp_zip;

        if (
            $cardNumber == "" || $cardCvv == "" || $cardExpireMonth == "" || $cardExpireYear == ""
        ) {
            $d = ['status' => "fail", 'message' => 'Must fill all fields!'];
            return response()->json($d);
        }
        // Build request parameters


        $params = [
            'USER' => env('PAYFLOW_USER'),
            'VENDOR' => env('PAYFLOW_VENDOR'),
            'PARTNER' => env('PAYFLOW_PARTNER'),
            'PWD' => env('PAYFLOW_PD'),
            'TRXTYPE' => 'S', // Sale transaction
            'TENDER' => 'C', // Card payment
            'AMT' => $amount,
            'CURRENCY' => env('PAYFLOW_CURRENCY'),
            'ACCT' => $cardNumber,
            'EXPDATE' => $cardExpireMonth . $cardExpireYear, // MMYY format
            'CVV2' => $cardCvv,
            'CITY' => $billingCity,
            'STATE' => $billingState,
            'ZIP' => $billingZip,
            'COMMENT1' => "From RR Enterprise order",
            'apiEndpoint' => env('PAYFLOW_API_URL'),
        ];

        if ($request->cpn) {
            $params['COMMENT2'] = "$discount Discount from total $amountTotal";
        }

        // Send request to PayPal Payflow Pro API
        $response = $paypal->payment($params);
        $orders =
            // Orders::where('company_id', Auth::user()->company_id)
            Orders::where('id', $request->oid)
                ->where('status', 'pending')->first();
        $oid = $orders->id;

        // Process response
        if ($response !== false) {
            parse_str($response, $parsedResponse);
            if (isset($parsedResponse['RESULT']) && $parsedResponse['RESULT'] == 0) {
                $orders->update(['status' => "completed"]);

                if ($ins_amount != 0 && !empty($request->cpn)) {
                    $trans_response = [
                        'response' => $response,
                        'discount' => $discount,
                        'coupon' => $request->cpn,
                        'ins_amount' => $ins_amount,
                    ];
                } else if ($ins_amount == 0 && !empty($request->cpn)) {
                    $trans_response = [
                        'response' => 'Free Order',
                        'discount' => $discount,
                        'coupon' => $request->cpn,
                    ];
                } else {
                    $trans_response = $response;
                }

                $orderData = [
                    'order_id' => $oid,
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::user()->id,
                    'trans_response' => json_encode($trans_response),
                    'status' => 'success',
                    'amount' => $amount,
                ];
                Transactions::create($orderData);
                $d = ['status' => "success", 'message' => 'Payment process has completed successfully!'];
                // Order Create Email
                $data = [
                    "template" => "orderCreate",
                    "title" => "Order Confirmation - Return Device.",
                    "email" => Auth::user()->email,
                    "order_id" => $request->oid,
                    "create_date" => date('d-m-Y'),
                    "orders_count" => count($data),
                    "insurance_amount" => $ins_amount,
                    "total_amount" => $amount,

                ];
                $orderSub = "Order Confirmation and Details:  #$request->oid";
                // Mail::send('mails.mail', $data, function($message) {
                //     $message->to(Auth::user()->email)->subject($orderSub);
                //     $message->bcc([env('MAIL_BCC_USERNAME'),env('MAIL_BCC_USERNAME2')]);
                //     $message->from(env('MAIL_USERNAME'),'Remote Retrieval');
                // });

                // Mail::send('mails.mail', $data, function ($message) use ($orderSub) {
                //     $message->to(Auth::user()->email)->subject($orderSub);
                //     $message->bcc([env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2')]);
                //     $message->from(env('MAIL_USERNAME'), 'Remote Retrieval');
                // });
                Session::put('thanksToken', Str::random(20));
                return response()->json($d);

            } else {
                $orderData = [
                    'order_id' => $oid,
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::user()->id,
                    'trans_response' => json_encode($response),
                    'status' => 'fail',
                    'amount' => $amount
                ];
                Transactions::create($orderData);

                $d = ['status' => "fail", 'message' => $parsedResponse['RESPMSG']];
                return response()->json($d);
            }
        } else {
            $d = ['status' => "fail", 'message' => 'Error occurred while processing payment'];
            return response()->json($d);
        }
    }


    public function applyCoupon(Request $request, Helper $helper)
    {
        $param['coupon'] = $request->coupon;
        $param['orderId'] = $request->oid;
        $discount = $helper->get_discount($param); // GET DISCOUNT
        return $discount;
    }

    /**
     *
     * MODULE: CREATE FREE ORDER
     * DESC: CREATE FREE ORDER BY 100% DISCOUNT COUPON
     */
    public function createFreeOrder($param)
    {
        $orderId = $param['orderId'];
        $orders = Orders::where('company_id', Auth::user()->company_id)
            ->where('id', $orderId)
            ->where('status', 'pending')->first();
        $oid = $orders->id;


        $orders->update(['status' => "completed"]);
        $orderData = [
            'order_id' => $oid,
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::user()->id,
            'trans_response' => json_encode(['response' => 'Free Order', 'discount' => $param['discount'], 'coupon' => $param['coupon']]),
            'status' => 'success',
            'amount' => 0,
        ];
        Transactions::create($orderData);
        $d = ['status' => "success", 'message' => 'Free order has created'];
        // Order Create Email
        $data = [
            "template" => "orderCreate",
            "title" => "Free Order Confirmation - Return Device.",
            "email" => Auth::user()->email,
            "order_id" => $oid,
            "create_date" => date('d-m-Y'),
            "orders_count" => $param['orderCnt'],
            "insurance_amount" => $param['ins_amount'],
            "discount" => $param['discount'],
            "total_amount" => 0,

        ];
        // Mail::send('mails.mail', $data, function($message) {
        //     $message->to(Auth::user()->email)->subject('Free Order Confirmation - Remote Retrieval.');
        //     $message->bcc([env('MAIL_BCC_USERNAME'),env('MAIL_BCC_USERNAME2')]);
        //     $message->from(env('MAIL_USERNAME'),'Remote Retrieval');
        // });
        $orderSub = "Order Confirmation:  #$oid";
        // Mail::send('mails.mail', $data, function ($message) use ($orderSub) {
        //     $message->to(Auth::user()->email)->subject($orderSub);
        //     $message->bcc([env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')]);
        //     $message->from(env('MAIL_USERNAME'), 'Remote Retrieval');
        // });
        return $d;
    }

    public function companyList(Request $request)
    {
        $perPage = env("PER_PAGE_DATA");
        $usersData = "";
        $settings = app('companySettings');

        $data = DB::table('users')
            ->leftJoin('companies', 'users.id', '=', 'companies.user_id')
            ->where('companies.parent_company', 0)
            ->where('company_id', '<>', env('RR_COMPANY_ID'))
            ->paginate($perPage);
        // ->get();
        // $query = DB::table('compemployees')
        //     ->leftJoin('companies', 'compemployees.company_id', '=', 'companies.id')
        //     ->leftJoin('orders', 'compemployees.order_id', '=', 'orders.id')
        //     ->leftJoin('users', 'compemployees.user_id', '=', 'users.id')
        //     ->select(
        //         'compemployees.id as item_id',
        //         'compemployees.type_of_equip as equip_type',
        //         'compemployees.return_service as return_srv',
        //         'compemployees.order_id as order_id',
        //         // 'compemployees.name as employee_name',
        //         'companies.company_name as company_name',
        //         'companies.created_at as created_at',
        //         // 'orders.order_number',
        //         'users.id as userId',
        //         'orders.status as payStatus',
        //     )
        //     ->where('compemployees.receive_label_status', '!=', 'DELIVERED');
        // if ($settings->company_id != env('RR_COMPANY_ID')) {
        //     $query->where('compemployees.parent_comp_id', $settings->company_id);
        // }
        // $query->where('compemployees.soft_del', '!=', 1);
        // $data = $query->orderBy('compemployees.id', 'desc')->paginate($perPage);


        // $this->helper->getAdminSettings();
        return view('pages.dashboard.companies', ["data" => $data]);
    }

    public function companyDetails(Request $request)
    {

        $settings = app('companySettings');
        if ($settings->company_id != env('RR_COMPANY_ID')) {
            return redirect()->route('dashboard');
        }

        $data = DB::table('users')
            ->leftJoin('companies', 'users.id', '=', 'companies.user_id')
            ->where('companies.parent_company', 0)
            ->where('companies.id', $request->cid)
            ->first();
        if (is_null($data)) {
            return redirect()->route('dashboard')->with('error', 'No order exist!');
        }
        return view('pages.dashboard.company_detail', [
            'data' => $data
        ]);
    }

    public function updateCompanyStatus(Request $request)
    {
        try {
            $data = DB::table('users')
                ->where('users.company_id', $request->cid)
                ->update(['status' => $request->status]);
            if ($request->status == "active") {
                //$user = User::where("company_id", $request->cid)->first();
                $data = User::join('companies', 'users.company_id', '=', 'companies.id')
                    ->select('users.*', 'companies.company_name as company_name', 'companies.company_domain as company_domain')
                    ->where("users.company_id", $request->cid)->first();
                // echo $user->name;
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
            $d = ['status' => "success", 'message' => 'Status has updated!'];
            session()->flash('success', 'Status has been updated!');
        } catch (\Exception $exception) {
            $d = ['status' => "fail", 'message' => 'Status cannot update!'];
            session()->flash('error', 'Status cannot update!');
        }


        return response()->json($d);
    }



    public function updateCompanyDomain(Request $request)
    {
        try {
            Companies::where("id", $request->cid)->update(
                ["company_domain" => $request->company_domain]
            );


            $d = ['status' => "success", 'message' => 'Domain has updated!'];
            session()->flash('success', 'Domain has been updated!');
        } catch (\Exception $exception) {
            $d = ['status' => "fail", 'message' => 'Domain cannot update!'];
            session()->flash('error', 'Domain cannot update!');
        }


        return response()->json($d);
    }

    /**
     * MODULE: TRACKING INFO BY CRON
     * DESCRIPTION: GET TRACK INFO BY CRON, UPDATE RECORD
     */
    public function trackingLabelOrder(Request $request, Helper $helper, Shipping $shipping): void
    {
        $to = date('Y-m-d 00:00:00');
        $from = date("Y-m-d 11:59:00", strtotime("-3 months"));
        $subOrder = Compemployees::whereBetween('created_at', [$from, $to])
            ->where('send_flag', 1)->where('rec_flag', 1)->get();
        $param = array();
        $param['helper'] = $helper;
        $param['shipping'] = $shipping;
        $param['lblCarrier'] = env('LABEL_CARRIER');


        foreach ($subOrder as $ord) {
            if ($ord->send_label_status != 'DELIVERED') {
                $sendlblResponse = json_decode($ord->send_labelresponse, true);

                // carrier work - start
                if (strpos($sendlblResponse['tracking_url_provider'], 'usps') !== false) {
                    $param['lblCarrier'] = 'usps';
                }
                if (strpos($sendlblResponse['tracking_url_provider'], 'ups') !== false) {
                    $param['lblCarrier'] = 'ups';
                }
                // carrier work - end

                $param['objectId'] = $sendlblResponse['tracking_number'];
                $trackInfo = $helper->get_tracking_info($param); // GET TRACKING INFO
                $boxTracking = $this->getLastTracking("box", $ord->id);
                if (!isset($boxTracking) || $trackInfo->status != $boxTracking->status) {
                    $this->boxTracking($trackInfo, $ord);
                }

            } // END 'DELIVERED' CONDITION

            if ($ord->receive_labelresponse != 'DELIVERED') {
                $receive_labelresponse = json_decode($ord->receive_labelresponse, true);

                // carrier work - start
                if (strpos($receive_labelresponse['tracking_url_provider'], 'usps') !== false) {
                    $param['lblCarrier'] = 'usps';
                }
                if (strpos($receive_labelresponse['tracking_url_provider'], 'ups') !== false) {
                    $param['lblCarrier'] = 'ups';
                }
                // carrier work - end


                $param['objectId'] = $receive_labelresponse['tracking_number'];
                $trackInfo = $helper->get_tracking_info($param); // GET TRACKING INFO

                $deviceTracking = $this->getLastTracking("device", $ord->id);
                //if (!isset($deviceTracking) || $trackInfo->status != $deviceTracking->status) {
                if (
                    !isset($deviceTracking) ||
                    isset($trackInfo) && is_object($trackInfo) &&
                    $trackInfo->status != $deviceTracking->status
                ) {
                    $this->deviceTracking($trackInfo, $ord);
                }

            } // END 'DELIVERED' CONDITION

        } // END FOREACH

    }



    /**
     * MODULE: TRACKING INFO BY CRON
     * DESCRIPTION: ADD TRACKING FOR DEVICE (Labeltracking), UPDATE DEVICE STATUS(Compemployees)
     */
    public function deviceTracking($trackInfo, $ord)
    {
        if (is_null($trackInfo) || !isset($trackInfo->status_date)) {
            $date = date("Y-m-d H:i:s");
        } else {
            $date = new DateTime($trackInfo->status_date);
            $date = $date->format('Y-m-d H:i:s');
        }
        $arr = array();
        if ($trackInfo) {
            $trackStatus = $trackInfo->status;
        } else {
            $trackStatus = '';
        }
        if ($trackInfo) {
            $trackObjId = $trackInfo->object_id;
        } else {
            $trackObjId = '';
        }
        $arr['status'] = $trackStatus;
        $arr['trackingID'] = $trackObjId;
        $arr['response_date'] = $date;
        $prevTrackData = [
            "suborder_id" => $ord->id,
            "tracking_id" => $trackObjId,
            "flag" => 'device',
            "response_date" => $date,
            "status" => $trackStatus,
            "response" => json_encode($arr),

        ];
        Labeltracking::create($prevTrackData);
        Compemployees::where('id', $ord->id)
            ->update(['receive_label_status' => $trackStatus]);
    }


    /**
     * MODULE: TRACKING INFO BY CRON
     * DESCRIPTION: GET LAST TRACKING INFO OF BOX/DEVICE
     */
    public function getLastTracking($t, $ordId)
    {
        return Labeltracking::where("suborder_id", $ordId)->where("flag", $t)->orderBy('id', 'DESC')->limit(1)->first();
    }


    /**
     * MODULE: TRACKING INFO BY CRON
     * DESCRIPTION: ADD TRACKING FOR BOX (Labeltracking), UPDATE BOX STATUS(Compemployees)
     */
    public function boxTracking($trackInfo, $ord)
    {

        if (is_null($trackInfo) || !isset($trackInfo->status_date)) {
            $date = date("Y-m-d H:i:s");
        } else {
            $date = new DateTime($trackInfo->status_date);
            $date = $date->format('Y-m-d H:i:s');
        }

        $arr = array();
        if ($trackInfo) {
            $trackStatus = $trackInfo->status;
        } else {
            $trackStatus = '';
        }
        if ($trackInfo) {
            $trackObjId = $trackInfo->object_id;
        } else {
            $trackObjId = '';
        }
        $arr['status'] = $trackStatus;
        $arr['trackingID'] = $trackObjId;
        $arr['response_date'] = $date;
        $prevTrackData = [
            "suborder_id" => $ord->id,
            "tracking_id" => $trackObjId,
            "flag" => 'box',
            "response_date" => $date,
            "status" => $trackStatus,
            "response" => json_encode($arr),

        ];
        Labeltracking::create($prevTrackData);
        Compemployees::where('id', $ord->id)
            ->update(['send_label_status' => $trackStatus]);
    }


    /**
     *
     * MODULE: EMAIL ON STATUS CHANGE
     * DESC: GENERATE EMAIL ON STATUS CHANGE
     */
    public function emailonStatusChange()
    {
        $to = date('Y-m-d 00:00:00');
        $from = date("Y-m-d 11:59:00", strtotime("-3 months"));
        $subOrder = Compemployees::whereBetween('created_at', [$from, $to])
            ->where('send_flag', 1)
            ->where('rec_flag', 1)
            ->get();

        $emailStatus = ["box_del_emp" => 0, "box_del_emp_dt" => date("Y-m'd")];

        foreach ($subOrder as $order) {
            if (!Emailonstatus::where("suborder_id", $order['id'])->exists()) {
                $emailStatus['suborder_id'] = $order['id'];
                Emailonstatus::create($emailStatus);
            }

            // if ($order['return_additional_srv'] != null) {
            //     // IF ORDER HAS DATA DESTRUCTION SERVICE THEN THIS BLOCK WILL CALL

            //     // When box is delivered to employee
            //     if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "PRE_TRANSIT") {
            //         $status = "box_del_to_emp";
            //         $this->sendEmailOnStatusUpdatesDD($order, $status);
            //     }

            //     // When box with laptop is shipped by employee
            //     if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "TRANSIT") {
            //         $status = "box_shipped_by_emp";
            //         $this->sendEmailOnStatusUpdatesDD($order, $status);
            //     }

            //     // When box with laptop is delivered to the company
            //     if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "DELIVERED") {
            //         $status = "device_shipped_to_comp";
            //         $this->sendEmailOnStatusUpdatesDD($order, $status);
            //     }

            //     // When box with laptop is delivered to the company
            //     if (
            //         $order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "DELIVERED"
            //         && $order['dest_label_status'] == "DELIVERED"
            //     ) {
            //         $status = "device_return_to_comp_after_DD";
            //         $this->sendEmailOnStatusUpdatesDD($order, $status);
            //     }


            // } else {


            // When box is delivered to employee
            if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "PRE_TRANSIT") {
                $status = "box_del_to_emp";
                $this->sendEmailOnStatusUpdates($order, $status);
            }

            // When box with laptop is shipped by employee
            if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "TRANSIT") {
                $status = "box_shipped_by_emp";
                $this->sendEmailOnStatusUpdates($order, $status);
            }

            // When box with laptop is delivered to the company
            if ($order['send_label_status'] == "DELIVERED" && $order['receive_label_status'] == "DELIVERED") {
                $status = "device_shipped_to_comp";
                $this->sendEmailOnStatusUpdates($order, $status);
            }



            // }





        } // END FOREACH


    }



    /**
     * When box is delivered to employee
     * SEND EMAIL TO EMPLOYEE AS REMINDER AFTER PASSING 3 AND 7 DAYS
     */

    public function empReminderEmailBoxDeliveredToEmp($order, $Emailonstatus)
    {
        if ($Emailonstatus->device_del_start == 0) {
            $date1 = new DateTime($Emailonstatus->box_del_emp_dt);
            $date2 = new DateTime(date("Y-m-d"));
            $interval = $date1->diff($date2);

            $param = [];
            $recRes = null;
            if ($order['rec_flag'] == 1) {
                $recRes = json_decode($order['receive_labelresponse'], true);
            }
            $param['emailTo'] = $order['emp_email'];
            $param['emailCC'] = $order['receipient_email'];
            $param['emailBcc'] = '';
            $param['trackingNo'] = ($recRes['tracking_number']) ? $recRes['tracking_number'] : '';
            $param['trackingUrl'] = ($recRes['tracking_url_provider']) ? $recRes['tracking_url_provider'] : '';
            $param['order'] = $order;

            if ($interval->days == 3 || $interval->days == 7) {
                // EMAIL FOR EMPLOYEE
                $param['emailTo'] = $order['emp_email'];
                $param['emailTemplate'] = "empEmailboxDeliveredToEmployeeAsReminder";
                $param['emailTemplateSubject'] = "Reminder: Laptop Return for " . $order['receipient_name'];
                $this->sendEmail($param);

                // EMAIL FOR COMPANY
                $param['emailTo'] = $order['receipient_email'];
                $param['emailTemplate'] = "compEmailboxDeliveredToEmployeeAsReminder";
                $param['emailTemplateSubject'] = "Reminder sent to " . $order['emp_first_name'];
                $this->sendEmail($param);

                // SEND SMS TO EMPLOYEE - START
                $smsdata = [
                    'to' => $order['emp_phone'],
                    'message' => "Friendly reminder for " . $order['type_of_equip'] . " return for " . $order['receipient_name'],
                    'company_id' => $order['company_id'],
                    'user_id' => $order['user_id'],
                    'order_id' => $order['order_id']
                ];
                $this->helper->sendSms($smsdata);
                // SEND SMS TO EMPLOYEE - END

                // SEND SMS TO COMPANY - START
                $smsdata = [
                    'to' => $order['receipient_phone'],
                    'message' => "We have sent a friendly reminder to " . $order['emp_first_name'] . " for " . $order['type_of_equip'] . " retrieval",
                    'company_id' => $order['company_id'],
                    'user_id' => $order['user_id'],
                    'order_id' => $order['order_id']
                ];
                $this->helper->sendSms($smsdata);
                // SEND SMS TO COMPANY - END
            }

        }


    }

    public function sendEmailOnStatusUpdates($order, $status)
    {
        $Emailonstatus = Emailonstatus::where("suborder_id", $order['id'])->first();
        // case 1
        if ($status == "box_del_to_emp") {
            if ($Emailonstatus->box_del_emp == 0) {
                Emailonstatus::where("suborder_id", $order['id'])->update([
                    "box_del_emp" => 1,
                    "box_del_emp_dt" => date("Y-m'd")
                ]);
                $this->empEmailBoxDeliveredToEmp($order);
                $this->companyEmailBoxDeliveredToEmp($order);
            } else {
                // AFTER 3 OR 7 DAYS REMINDER
                $this->empReminderEmailBoxDeliveredToEmp($order, $Emailonstatus);
            }
        }

        // case 2
        if ($status == "box_shipped_by_emp") {
            $Emailonstatus = Emailonstatus::where("suborder_id", $order['id'])->first();
            if ($Emailonstatus->device_del_start == 0) {
                Emailonstatus::where("suborder_id", $order['id'])->update([
                    "device_del_start" => 1,
                    "device_del_start_dt" => date("Y-m'd")
                ]);
                $this->empEmailBoxShippedToCompany($order);
                $this->companyEmailBoxShippedToCompany($order);
            }
        }

        // case 3
        if ($status == "device_shipped_to_comp") {
            $Emailonstatus = Emailonstatus::where("suborder_id", $order['id'])->first();
            if ($Emailonstatus->device_del_comp == 0) {
                Emailonstatus::where("suborder_id", $order['id'])->update([
                    "device_del_comp" => 1,
                    "device_del_comp_dt" => date("Y-m'd")
                ]);
                $this->empEmailDeviceDeliveredToCompany($order);
                $this->companyEmailDeviceDeliveredToCompany($order);
            }
        }

    }


    /**
     * When box is delivered to employee
     * SEND EMAIL TO EMPLOYEE
     */

    public function empEmailBoxDeliveredToEmp($order)
    {
        $param = [];
        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['emp_email'];
        $param['emailCC'] = $order['receipient_email'];
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes['tracking_number']) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes['tracking_url_provider']) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "boxDeliveredToEmployee";
        $param['emailTemplateSubject'] = $order['type_of_equip'] . " Retrieval Box Delivered - Action Required";
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['emp_phone'],
            'message' => $order['type_of_equip'] . " retrieval box by " . $order['receipient_name'] . " is delivered to your address.",
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        $this->helper->sendSms($smsdata);
    }


    /**
     * When box is delivered to employee
     * SEND EMAIL TO COMPANY
     */
    public function companyEmailBoxDeliveredToEmp($order)
    {

        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['receipient_email'];
        $param['emailCC'] = '';
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes['tracking_number']) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes['tracking_url_provider']) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "boxDeliveredToEmployee_companyemail";
        $param['emailTemplateSubject'] = $order['type_of_equip'] . " Retrieval Box for Order #" . $order['order_id'] . " - Delivered to " . $order['emp_first_name'];
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['receipient_phone'],
            'message' => $order['type_of_equip'] . " retrieval box to " . $order['emp_first_name'] . " is delivered.",
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        // $this->sendSms($smsdata);
        $this->helper->sendSms($smsdata);
    }


    /**
     * When box with laptop is shipped by employee
     * SEND EMAIL TO EMPLOYEE
     */
    public function empEmailBoxShippedToCompany($order)
    {

        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['emp_email'];
        $param['emailCC'] = '';
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes['tracking_number']) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes['tracking_url_provider']) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "deviceDeliveryStartToCompany_employeeemail";
        $param['emailTemplateSubject'] = "Thank you for sending the " . $order['type_of_equip'] . " for " . $order['receipient_name'];
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['emp_phone'],
            'message' => "Thank you for sending the " . $order['type_of_equip'] . " for " . $order['receipient_name'],
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        $this->helper->sendSms($smsdata);
    }

    /**
     * When box with laptop is shipped by employee
     * SEND EMAIL TO COMPANY
     */
    public function companyEmailBoxShippedToCompany($order)
    {

        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['receipient_email'];
        $param['emailCC'] = '';
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "deviceDeliveryStartToCompany_companyemail";
        $param['emailTemplateSubject'] = "Laptop Retrieval is on the Way for Order #" . $order['order_id'];
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['receipient_phone'],
            'message' => $order['type_of_equip'] . " Retrieval is on the way for order #" . $order['order_id'] . " by " . $order['emp_first_name'],
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        $this->helper->sendSms($smsdata);
    }

    /**
     * When box with laptop is delivered to the company
     * SEND EMAIL TO EMPLOYEE
     */
    public function empEmailDeviceDeliveredToCompany($order)
    {
        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['emp_email'];
        $param['emailCC'] = '';
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "deviceDeliveredToCompany_employeeemail";
        $param['emailTemplateSubject'] = $order['type_of_equip'] . " Delivered to " . $order['receipient_name'];
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['emp_phone'],
            'message' => $order['type_of_equip'] . " delivered to " . $order['receipient_name'],
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        $this->helper->sendSms($smsdata);
    }

    /**
     * When box with laptop is delivered to the company
     * SEND EMAIL TO COMPANY
     */
    public function companyEmailDeviceDeliveredToCompany($order)
    {

        $recRes = null;
        if ($order['rec_flag'] == 1) {
            $recRes = json_decode($order['receive_labelresponse'], true);
        }
        $param['emailTo'] = $order['receipient_email'];
        $param['emailCC'] = '';
        $param['emailBcc'] = '';
        $param['trackingNo'] = ($recRes) ? $recRes['tracking_number'] : '';
        $param['trackingUrl'] = ($recRes) ? $recRes['tracking_url_provider'] : '';
        $param['emailTemplate'] = "deviceDeliveredToCompany_companyemail";
        $param['emailTemplateSubject'] = "Laptop Delivered Order #" . $order['order_id'] . " - Order Completed";
        $param['order'] = $order;
        $this->sendEmail($param);
        $smsdata = [
            'to' => $order['receipient_phone'],
            'message' => $order['type_of_equip'] . " delivered for #" . $order['order_id'] . " by " . $order['emp_first_name'],
            'company_id' => $order['company_id'],
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id']
        ];
        $this->helper->sendSms($smsdata);
    }
    /**
     * CENTRALIZED FUNCTION TO SEND EMAIL OF STATUS CHANGE
     *
     */
    public function sendEmail($param)
    {
        $company = null;
        if ($param['order']['return_service'] == "Sell This Equipment") {
            $company = Companies::where("id", $param['order']['company_id'])->first();
        }
        $compSettingsEmail = Companysettings::where("company_id", $param['order']['company_id'])->first();
        $param['order']['logo']=$compSettingsEmail->logo??"";

        $emailData = [
            "template" => $param['emailTemplate'],
            "subject" => $param['emailTemplateSubject'],
            "to" => $param['emailTo'],
            "bcc" => [env('MAIL_BCC_USERNAME'), env('MAIL_BCC_USERNAME2'), env('MAIL_BCC_USERNAME3')],
            "cc" => "",
            "fromEmail" => env('MAIL_USERNAME'),
            "fromName" => 'Return Device',
            "title" => $param['emailTemplateSubject'],
            "mailTemplate" => "mails.email_on_status_update",
            "mailData" => $param['order'],
            "trackingNo" => $param['trackingNo'],
            "trackingUrl" => $param['trackingUrl'],
            "order" => $param['order'],
            "company" => $company,
        ];
        $this->mailService->sendMail($emailData);
    }


    public function companyEdit(Request $request)
    {
        $company = Companies::where('id', $request->id)->first();
        $user = User::where("id", $company->user_id)->first();
        return view('pages.dashboard.companyEdit', ['c' => $company, 'u' => $user]);
    }
    public function companyEditSub(Request $request)
    {
        $validatedData = $request->validate([
            'company_name' => ['required'],
            'domain' => ['required'],
            'company_domain' => ['required'],
            'receipient_name' => ['required'],
            'company_email' => ['required'],
            'company_add_1' => ['required'],
            'company_phone' => ['required'],
            'company_city' => ['required'],
            'company_state' => ['required'],
            'company_zip' => ['required'],
        ]);

        try {
            $company = Companies::where('id', $request->id)->update([
                'company_name' => $request->company_name,
                'domain' => $request->domain,
                'company_domain' => $request->company_domain,
                'receipient_name' => $request->receipient_name,
                'company_email' => $request->company_email,
                'company_add_1' => $request->company_add_1,
                'company_add_2' => $request->company_add_2,
                'company_phone' => $request->company_phone,
                'company_city' => $request->company_city,
                'company_state' => $request->company_state,
                'company_zip' => $request->company_zip
            ]);
            User::where("company_id", $request->id)->update([
                'status' => $request->status
            ]);

            session()->flash('success', 'Data updated successfully!');
            return redirect()->route('company.edit', ['id' => $request->id]);
        } catch (\Exception $exception) {
            // $d = ['status' => "fail", 'message' => 'Status cannot update!'];
            session()->flash('error', 'Data cannot update!');
            return redirect()->route('company.edit', ['id' => $request->id]);
        }

    }

}
