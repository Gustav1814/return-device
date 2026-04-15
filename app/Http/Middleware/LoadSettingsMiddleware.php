<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use App\Models\Companysettings;
use App\Models\Companies;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class LoadSettingsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // CHECK SESSION TIMEOUT - START
        $expiresAt = Session::get('expires_at');
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            Session::forget('companySettings');
            Session::forget('expires_at');
        }
        // CHECK SESSION TIMEOUT - END

        // Tenant SaaS dashboard (/saas): branding and companysettings are scoped by the logged-in
        // user's company_id (the organization they belong to after signup / purchase). This is not
        // a separate "companies admin" product—each customer user sees their own tenant's settings.
        $companyID = Auth::check()
            ? (int) (Auth::user()->company_id ?? 0)
            : (int) (Companies::query()->orderBy('id')->value('id') ?? 0);

        // Reuse session cache only when it matches this request's tenant (avoids wrong branding
        // after logout or when switching users in the same browser).
        if ($companyID > 0 && Session::has('companySettings')) {
            $cached = Session::get('companySettings');
            if ($cached instanceof Companysettings && (int) $cached->company_id === $companyID) {
                Session::put('expires_at', now()->addHour());
                View::share('companySettings', $cached);
                app()->singleton('companySettings', fn() => $cached);

                return $next($request);
            }
        }

        if ($companyID <= 0) {
            // Fresh install: no companies yet — allow SaaS shell, login, and JSON API to hit auth (401) instead of tenant error.
            if (
                $request->is('saas', 'saas/*', 'wl-login')
                || $request->is('api/saas', 'api/saas/*')
            ) {
                return $next($request);
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Company not configured.',
                    'status' => 'Fail',
                    'code' => 401,
                    'custom_code' => 401,
                ], 401);
            }
            abort(401, 'Company not configured.');
        }

        // GET COMPANY SETTINGS FOR CURRENT COMPANY & BIND IT WITH SERVICE AND VIEW - START
        $settings = Companysettings::query()->firstOrCreate(
            ['company_id' => $companyID],
            [
                'btn_bg_color' => '#6366f1',
                'btn_font_color' => '#ffffff',
                'theme_bg_color' => '#f8fafc',
                'theme_font_color' => '#1e293b',
                'settings_data' => [],
            ]
        );
        Session::put('companySettings', $settings);
        Session::put('expires_at', now()->addHour());
        View::share('companySettings', $settings);
        app()->singleton('companySettings', fn() => $settings);
        // GET COMPANY SETTINGS FOR CURRENT COMPANY & BIND IT WITH SERVICE AND VIEW - END


        return $next($request);
    }
}
