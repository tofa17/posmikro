<?php

namespace App\Http\Middleware;
use App\Bases\Key;
use Closure;

class CekMiddleware
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
        if (!empty($request->input('x-auth'))) {
            $access_token = $request->input('x-auth');
        }
        else{
            $access_token = $request->header('x-auth');  
        }
        $cek_token = Key::where('key', $access_token)->first();
        if (!$access_token) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "An access token is required to request this resource.",
                    'required'      => "x-auth",
                    'code'      => 402
                ];
            return response()->json($response, 402);
        }
        if (is_null($cek_token)) {
            $response['error'][] = [
                    'status'    => false,
                    'message'   => "This token x-auth unauthorized",
                    'code'      => 401
                ];
            return response()->json($response, 401);
        }
        return $next($request);
    }
    /*public function handle($request, Closure $next, $permission = null)
    {
        if (!app('Illuminate\Contracts\Auth\Guard')->guest()) {

            if ($request->user()->can($permission)) {
                
                return $next($request);
            }
        }

        return $request->ajax ? response('Unauthorized.', 401) : redirect('/login');
    }*/
}
