<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\CouponController;
use App\Http\Middleware\LoadSettingsMiddleware;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserauthController;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// CRON - START
// DETECT SHIPPO LABELS
Route::get('/tracking-label/order', [OrderController::class, 'trackingLabelOrder'])->name('track.label');
// GENERATE EMAILS ON BASIS OF FLAGS
Route::get('/email-on-status-change', [OrderController::class, 'emailonStatusChange'])->name('email.statuschange');
// CRON - END


Route::middleware([LoadSettingsMiddleware::class, 'web'])->group(function () {
    //EMAIL FOR FAILED ORDER - TESTING PURPOSE
    Route::get('/email-fail-order/{id}', [HomeController::class, 'createOrderMailScript']);
     //LABEL EMAIL FOR ORDER - TESTING PURPOSE
    Route::get('/email-label-order/{id}', [HomeController::class, 'labelOrderMailScript']);
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    // Authenticated SaaS JSON API lives under routes/api.php → /api/saas/*

    // React SPA: named entry for Blade links (must be registered before the catch-all)
    Route::view('/saas/dashboard', 'saas')->name('saas.dashboard');

    // React SPA catch-all
    Route::get('/saas/{any?}', function () {
        return view('saas');
    })->where('any', '.*')->name('saas');
    // Public device-return wizard (React, dashboard styling). Legacy Blade was `home.createOrder`.
    Route::get('/order', function () {
        return redirect('/saas/order', 302);
    })->name('create.singleorder.notauth');

    Route::get('/order/config', function () {
        $settings = app('companySettings');
        $envId = env('COMPANY_SETTING_ID');

        return response()->json([
            'insurance_rate_percent' => (float) env('INSURANCE_RATE', 0),
            'dd_company' => (float) env('DD_COMPANY', 0),
            'dd_new_emp' => (float) env('DD_NEW_EMP', 0),
            'company_settings_id' => (int) $settings->id,
            'env_company_setting_id' => $envId !== null && $envId !== '' ? (int) $envId : null,
            'is_rr_default_company_settings' => (string) $settings->id === (string) $envId,
            'tenant_company_id' => (int) $settings->company_id,
            'remote_recipient' => [
                'company_name' => env('REMOTE_COMPANY_NAME'),
                'company_email' => env('REMOTE_COMPANY_EMAIL'),
                'company_phone' => env('REMOTE_COMPANY_PHONE'),
                'company_add_1' => env('REMOTE_COMPANY_ADD1'),
                'company_add_2' => env('REMOTE_COMPANY_ADD2'),
                'company_city' => env('REMOTE_COMPANY_CITY'),
                'company_state' => env('REMOTE_COMPANY_STATE'),
                'company_zip' => env('REMOTE_COMPANY_ZIP'),
                'comp_receip_name' => env('REMOTE_REC_NAME'),
            ],
        ]);
    })->name('order.config');
    Route::post('/sub-user-profile', [OrderController::class, 'submituserProfile'])->name('submit.user.profile');

    Route::post('/getcompanydetails', [OrderController::class, 'getCompanyDetails'])->name('getcompany_details');
    Route::post('/createorder', [HomeController::class, 'registercreateorder'])
        ->name('register.createorder');
    Route::get('/get-order-amount', [HomeController::class, 'getOrderAmount'])->name('order.amount');
    Route::get('/profilelogin', [HomeController::class, 'profilelogin'])->name('profileforlogin');
    Route::get('/get-discount-bycoupon', [HomeController::class, 'getDiscount'])->name('apply.coupon.api');
    Route::get('/thank-you', [HomeController::class, 'thankYouUser'])->name('thank.you');


    Route::get('wl-login', [AuthenticatedSessionController::class, 'create'])
        ->name('login')->middleware('guest');
    Route::post('wl-login', [AuthenticatedSessionController::class, 'store'])
        ->name('wl.login.store')->middleware('guest');

    Route::get('/lostpassword', [UserauthController::class, 'lostPassword'])->name('lost.password');
    Route::get('/updatepassword', [UserauthController::class, 'updatePassword'])->name('update.password');
    Route::post('/validate-email/forgot', [UserauthController::class, 'validateEmailForgotPassword'])->name('validate.email.forgotpd');
    Route::post('/update-password/forgot', [UserauthController::class, 'updateForgotPassword'])->name('sub.update.forgotpd');
    // REQUIRED LOGIN - START


    Route::middleware(['auth'])->group(function () {
        Route::get('/user-profile', [OrderController::class, 'userProfile'])->name('user.profile');
        Route::post('/sub-user-profile', [OrderController::class, 'submituserProfile'])->name('submit.user.profile');
        Route::get('/in-progress-orders', [OrderController::class, 'inProgressOrders'])->name('orders.list');
        Route::get('/in-progress-orders-search', [OrderController::class, 'filterInProgressOrders'])->name('orders.filter');
        Route::get('/completed-orders', [OrderController::class, 'completedOrders'])->name('completed.orders.list');
        Route::get('/completed-orders-search', [OrderController::class, 'filterCompletedOrders'])->name('completed.orders.filter');
        Route::get('/sub-order/edit/{sid}', [OrderController::class, 'subOrderEdit'])->name('suborder.edit');
        Route::post('/sub-order/edit/{sid}', [OrderController::class, 'subOrderEditPost'])->name('suborder.edit');
        Route::get('/order-detail/{oid}', [OrderController::class, 'orderDetail'])->name('order.detail');
        Route::get('/create-bulk-order', function () {
            return redirect('/saas/orders/bulk', 302);
        })->name('create.bulk.order');
        Route::post('/create-bycsv/order/', [OrderController::class, 'submitOrderbyCSV'])->name('submit.order.bycsv');
        Route::get('/users/list', [OrderController::class, 'usersList'])->name('users.list');
        Route::get('/users/list/search', [OrderController::class, 'usersSearch'])->name('users.search');
        Route::get('/theme-settings', [OrderController::class, 'companySettings'])->name('company.settings');
        Route::post('/company-settings-sub', [OrderController::class, 'companySettingsSubmit'])->name('company.settings.sub');
        Route::get('/price-settings', [OrderController::class, 'priceSettings'])->name('price.settings');
        Route::post('/price-settings-sub', [OrderController::class, 'priceSettingsSubmit'])->name('price.settings.sub');
        Route::get('/create-single-order', [OrderController::class, 'createSingleOrder'])->name('create.singleorder');
        Route::get('/dashboard', function () {
            return redirect(RouteServiceProvider::HOME, 302);
        })->name('dashboard');
        Route::get('/api/key',[HomeController::class,'api'])->name('api.key');
        Route::get('/api-integration',[HomeController::class,'apiIntegration'])->name('api.integration');
        Route::post('/api/generate-key',[HomeController::class,'generateApiKey'])->name('generate.api.key');
        Route::get('/sub-order/delete/{oid}', [OrderController::class, 'subOrderDelete'])->name('suborder.delete');
        Route::get('/orderpayment', [OrderController::class, 'orderPay'])->name('order.pay');
        Route::post('/payment-sub', [OrderController::class, 'paySub'])->name('pay.submit');
        Route::get('/createlabel', [LabelController::class, 'findRates'])->name('orderslabel.create');
        Route::get('/purchaselabel', [LabelController::class, 'purchaseLabel'])->name('orderslabel.purchase');
        Route::post('/apply-coupon', [OrderController::class, 'applyCoupon'])->name('apply.coupon');
        Route::get('/companies', [OrderController::class, 'companyList'])->name('company.list');
        Route::get('/edit/company/{id}', [OrderController::class, 'companyEdit'])->name('company.edit');
        Route::post('/editsub/company/{id}', [OrderController::class, 'companyEditSub'])->name('company.edit.sub');
        Route::get('/company-detail/{cid}', [OrderController::class, 'companyDetails'])->name('company.detail');
        Route::get('/update-company-status/{cid}', [OrderController::class, 'updateCompanyStatus'])->name('update.company.status');
        Route::get('/update-company-domain/{cid}', [OrderController::class, 'updateCompanyDomain'])->name('update.company.domain');
        // ->middleware(Authenticate::class);


        //COUPON
        Route::get('/coupon/list', [CouponController::class, 'couponList'])->name('admin.coupon.list');
        Route::get('/coupon/add', [CouponController::class, 'couponAdd'])->name('admin.coupon.add');
        Route::post('/coupon/create', [CouponController::class, 'couponAdd'])->name('admin.coupon.create');
        Route::get('/coupon/edit/{id}', [CouponController::class, 'couponEdit'])->name('admin.coupon.edit');
        Route::post('/coupon/editsub/{id}', [CouponController::class, 'couponEdit'])->name('admin.coupon.editsub');
        Route::get('/coupon/delete/{id}', [CouponController::class, 'couponDelete'])->name('admin.coupon.delete');
        Route::get('/commission', [CouponController::class, 'commission'])->name('admin.commission');


        // download csv file - start
        Route::get('/download-filecsv', function () {
            $filePath = '/sampleCSV/Sample2_CSV_RR_01.csv';
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            }
            return response()->json(['error' => 'File not found'], 404);
        });
        // download csv file - end
        // LOGS FOR SUPERADMIN
        Route::get('/logs', [HomeController::class, 'logs'])->name('logs');

    });

    Route::get('/api-collection/RD-Enterprise-API-Collection.postman_collection.json', function () {
    $path = storage_path('app/public/api-collection/RD-Enterprise-API-Collection.postman_collection.json');

    if (!file_exists($path)) {
        abort(404, 'File not found');
    }

    return response()->download($path, 'RD-Enterprise-API-Collection.postman_collection.json', [
        'Content-Type' => 'application/json',
    ]);
})->name('api.collection.download');

    // REQUIRED LOGIN - END





});

 

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
