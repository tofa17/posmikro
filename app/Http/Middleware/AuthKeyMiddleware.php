<?php

namespace App\Http\Middleware;
use App\Bases\Token;
use Closure;

class AuthKeyMiddleware
{
    public function handle($request, Closure $next)
    {
//login authentication
        if (!empty($request->input('x-auth-login'))) {
            $x_login = $request->input('x-auth-login');
        }
        else{
            $x_login = $request->header('x-auth-login');  
        }
        $cek_token = Token::where('token', $x_login)->first();
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
                    'message'   => "This token login unauthorized",
                    'code'      => 401
                ];
            return response()->json($response, 401);
        }
//table key authentication
/*        if (!empty($request->input('x-auth-key'))) {
            $x_key = $request->input('x-auth-key');
        }
        else{
            $x_key = $request->header('x-auth-key');  
        }
        $cek_token = User::where('key_id', $x_key)->first();
        if (!$x_key) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "An access token is required to request this resource.",
                    'required'  => "x-auth-key",
                    'code'      => 402
                ];
            return response()->json($response, 402);
        }
        if (is_null($cek_token)) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "This token app key unauthorized",
                    'code'      => 401
                ];
            return response()->json($response, 401);
        }*/
//cek this table key and token user login
/*        $combine = User::where('remember_token', $x_login)->first();
        if (!$combine) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "Cannot access this app.",
                    'required'  => "x-auth-key and x-auth-login not math",
                    'code'      => 422
                ];
            return response()->json($response, 422);
        }*/
        return $next($request);
    }
}
