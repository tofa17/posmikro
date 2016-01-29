<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class FranchisorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//login authentication
        if (!empty($request->input('x-auth-login'))) {
            $x_login = $request->input('x-auth-login');
        }
        else{
            $x_login = $request->header('x-auth-login');  
        }
        $cek_token = DB::table('users')
                        ->join('tokens', 'users.id', '=', 'tokens.user_id')
                        ->where('token', $x_login)
                        ->where('level', '=', 'franchisor')
                        ->orWhere('level', '=', 'admin')->first();
        if (!$x_login) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "An access token is required to request this resource.",
                    'required'      => "x-auth-login",
                    'code'      => 402
                ];
            return response()->json($response, 402);
        }
        if (is_null($cek_token)) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "This auth token franchisor unauthorized",
                    'code'      => 401
                ];
            return response()->json($response, 401);
        }
        return $next($request);
    }
}
