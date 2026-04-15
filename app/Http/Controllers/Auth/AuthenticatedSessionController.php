<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Single sign-in UI: React SaaS (Blade auth.login is no longer shown here).
     */
    public function create(Request $request): RedirectResponse
    {
        $to = url('/saas/login');
        $qs = array_filter(
            $request->only(['email', 'd', 'token', 'next']),
            static fn ($v) => $v !== null && $v !== '',
        );
        if ($qs !== []) {
            $to .= '?' . http_build_query($qs);
        }

        return redirect()->to($to);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->to(url('/saas/login'));
    }
}
