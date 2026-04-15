<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SaasController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CustomCorsMiddleware;
use App\Http\Middleware\LoadSettingsMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [UserController::class, 'register'])
    ->middleware(CustomCorsMiddleware::class);

Route::post('/update-record', [UserController::class, 'updaterecord'])
    ->middleware(CustomCorsMiddleware::class);

Route::group(['prefix' => 'v1'], function () {
    Route::group(['middleware' => 'validate.request'], function () {
        Route::get('device_returns', [DeviceController::class, 'deviceReturns']);
        Route::post('create-order', [DeviceController::class, 'createOrder'])->middleware('validate.html');
        Route::get('validate/user', [DeviceController::class, 'checkValidRequest']);
        Route::get('company-details', [DeviceController::class, 'getCompanyDetails']);
        Route::get('orders', [DeviceController::class, 'getAllOrders']);
        Route::get('get-device-prices', [DeviceController::class, 'getDevicePrices']);
    });
});

/*
|--------------------------------------------------------------------------
| SaaS dashboard JSON API (React SPA + same-session clients)
|--------------------------------------------------------------------------
| Prefix: /api/saas/* — uses web session + company tenant (LoadSettingsMiddleware).
*/
Route::middleware([LoadSettingsMiddleware::class, 'auth'])->prefix('saas')->group(function () {
    Route::get('/me', [SaasController::class, 'me']);
    Route::get('/dashboard', [SaasController::class, 'dashboard']);
    Route::get('/dashboard/charts', [SaasController::class, 'dashboardCharts']);
    Route::get('/dashboard/export', [SaasController::class, 'dashboardExport']);
    Route::get('/commissions', [SaasController::class, 'commissions']);
    Route::get('/commissions/partners', [SaasController::class, 'commissionPartners']);
    Route::get('/prices', [SaasController::class, 'prices']);
    Route::put('/prices', [SaasController::class, 'updatePrices']);
    Route::get('/settings', [SaasController::class, 'settings']);
    Route::put('/settings', [SaasController::class, 'updateSettings']);
    Route::post('/settings/logo', [SaasController::class, 'uploadLogo']);
    Route::get('/orders/export', [SaasController::class, 'ordersExport']);
    Route::get('/orders', [SaasController::class, 'orders']);
    Route::post('/orders', [SaasController::class, 'createOrder']);
    Route::get('/orders/{itemId}', [SaasController::class, 'orderDetail'])->whereNumber('itemId');
    Route::get('/users', [SaasController::class, 'users']);
    Route::get('/companies', [SaasController::class, 'companies']);
    Route::post('/companies/{id}/user-status', [SaasController::class, 'companyUserStatus'])->whereNumber('id');
    Route::patch('/companies/{id}/domain', [SaasController::class, 'companyPatchDomain'])->whereNumber('id');
    Route::get('/companies/{id}', [SaasController::class, 'companyShow'])->whereNumber('id');
    Route::put('/companies/{id}', [SaasController::class, 'companyUpdate'])->whereNumber('id');
    Route::get('/coupons', [SaasController::class, 'coupons']);
    Route::post('/coupons', [SaasController::class, 'couponStore']);
    Route::get('/coupons/{id}', [SaasController::class, 'couponShow'])->whereNumber('id');
    Route::put('/coupons/{id}', [SaasController::class, 'couponUpdate'])->whereNumber('id');
});
