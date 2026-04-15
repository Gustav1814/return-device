<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Systemsettings;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Libraries\Services\Helper;

class CouponController extends Controller
{
    protected $helper;
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }
    public function settings(Request $request)
    {
        $settings = Systemsettings::get();
        return view('pages.Admin.settings', ['settings' => $settings]);
    }


    public function updateOrderAmount(Request $request)
    {
        $data['order_amount'] = $request->Laptop_ord_amt;
        Systemsettings::where('equipment_type', 'Laptop')
            ->update($data);

        $data['order_amount'] = $request->Monitor_ord_amt;
        Systemsettings::where('equipment_type', 'Monitor')
            ->update($data);

        return response()->json([
            "status" => "success",
            "message" => "Order amount has updated!"
        ]);
    }

    public function couponList(Request $request)
    {
        $perPage = env("PER_PAGE_DATA");
        $data = Coupon::select('*')->orderBy('id', 'DESC')->paginate($perPage);
        return view('pages.dashboard.couponList', ['data' => $data]);
    }
    public function commission(Request $request)
    {
        $perPage = env("PER_PAGE_DATA");
          $data = DB::table('users')
            ->leftJoin('companies', 'users.id', '=', 'companies.user_id')
            ->where('companies.parent_company', 0)
            ->where('company_id', '<>', env('RR_COMPANY_ID'))
            ->paginate($perPage);
        return view('pages.dashboard.commission', ['data' => $data,'helper'=>$this->helper]);
    }

    public function couponAdd(Request $request)
    {

        // $perPage   =    env("PER_PAGE");
        // $data      =    Coupon::select('*')->orderBy('id','DESC')->paginate($perPage);
        if ($request->method() == 'POST') {

            // echo "---".$request->freeCpn;
            // $v = isset($request->freeCpn)?1:0;
            // echo $v;
            // exit();

            $coupon_name = Str::upper($request->coupon_name);
            $check_coupon_exits = DB::table('coupon')->where('coupon', $coupon_name)->first();
            if (is_null($check_coupon_exits)) {
                try {
                    $request->validate([
                        'coupon_name' => 'required',
                        'coupon_type' => 'required',
                        'coupon_apply_for' => 'required',
                        'amt_perc' => 'required',
                    ]);
                    $couponData = [
                        'coupon' => $coupon_name,
                        'type' => $request->coupon_type,
                        'coupon_apply_for' => $request->coupon_apply_for,
                        'amt_or_perc' => $request->amt_perc,
                        'status' => 1,
                        'freeall' => isset($request->freeCpn) ? 1 : 0
                    ];
                    Coupon::create($couponData);
                    return redirect()->route('admin.coupon.add')->with('successMsg', 'Coupon has added!');
                } catch (\Exception $exception) {
                    return redirect()->route('admin.coupon.add')->with('fail', __($exception->getMessage()));
                    // return response()->json(
                    //     [
                    //         'message' => __($exception->getMessage()),
                    //         // 'message'     =>  'Employee data cannot update at this time',
                    //         'status' => 'fail'
                    //     ]
                    // );
                }
            } else {
                return redirect()->route('admin.coupon.add')->with('fail', $coupon_name . ' Coupon Aready Exits!');
            }
        }



        return view('pages.dashboard.couponAdd');

    }




    public function couponEdit(Request $request)
    {
        $perPage = env("PER_PAGE");
        $coupon = Coupon::select('*')->where('id', $request->id)->first();

        if ($request->method() == 'POST') {
            $coupon_name = Str::upper($request->coupon_name);
            try {
                $request->validate([
                    'coupon_name' => 'required',
                    'coupon_type' => 'required',
                    'coupon_apply_for' => 'required',
                    'amt_perc' => 'required',
                    'status' => 'required',
                ]);
                $status = ($request->status == '1') ? 1 : 0;
                $couponData = [
                    'coupon' => $coupon_name,
                    'type' => $request->coupon_type,
                    'coupon_apply_for' => $request->coupon_apply_for,
                    'amt_or_perc' => $request->amt_perc,
                    'status' => $status,
                    'freeall' => isset($request->freeCpn) ? 1 : 0
                ];
                // Coupon::create($couponData);

                Coupon::where('id', $request->id)
                    ->update($couponData);


                return redirect()->route('admin.coupon.edit', $request->id)->with('successMsg', 'Coupon has updated!');
            } catch (\Exception $exception) {
                return redirect()->route('admin.coupon.edit', $request->id)->with('fail', __($exception->getMessage()));
                // return response()->json(
                //     [
                //         'message' => __($exception->getMessage()),
                //         // 'message'     =>  'Employee data cannot update at this time',
                //         'status' => 'fail'
                //     ]
                // );
            }
        }



        return view('pages.dashboard.couponEdit', ['coupon' => $coupon]);

    }

    public function couponDelete(Request $request)
    {
        $id = $request->id;
        $coupon = Coupon::find($id);
        $coupon->delete();
    }

}

