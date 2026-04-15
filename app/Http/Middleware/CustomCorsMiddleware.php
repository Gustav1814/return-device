<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CustomCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $allowedIps = ['203.0.113.10', '127.0.0.1', '54.202.232.27', '54.185.37.154']; // Replace with the website's API IP address
        // if (!in_array($request->ip(), $allowedIps)) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        //$allowedOrigin = '127.0.0.1:8001'; // LOCAL
        // $allowedOrigin = '54.202.232.27'; // STAGING
        //$allowedOrigin = '54.185.37.154'; // LIVE
        //$headers = $request->headers->all(); // Dump all headers

        //return $next($request);

        $header = $request->header('Authorization');
        $token = '';
        if (Str::of($header)->startsWith('Bearer')) {
            $token = Str::of($header)->substr(7);
            $token = $token->value;
        }
        // echo "token:" . $token;
        // echo "reg_token" . env('WL_REG_TOKEN');
        if ($token != env('WL_REG_TOKEN')) {
            return response()->json(['message' => 'Unauthorized token'], 403);
        } else {
            return $next($request);
        }

    }
}
