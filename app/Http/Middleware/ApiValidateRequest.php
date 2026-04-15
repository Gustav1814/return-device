<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ApiValidateRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
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

        if (is_null($token)) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                "code" => 401,
                "custom_code" => 401
            ], 401);
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
        $subDomain = explode(env('CURR_DOMAIN'), $_SERVER['SERVER_NAME'])[0] ?? null;
        $q = User::join('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.*', 'companies.company_name as company_name')
            ->where('users.status', "active")
            ->where('users.api_key', $token);

        if ($_SERVER['SERVER_NAME'] != env('ALLOW_DOMAIN')) {
            $q->where('companies.company_domain', $subDomain);
        }
        $company = $q->first();
        if (!$company) {
            return response()->json([
                'message' => 'Invalid Key!',
                'status' => 'Fail',
                "code" => 401,
                "custom_code" => 401
            ], 401);
        }
        return $next($request);
    }
}
