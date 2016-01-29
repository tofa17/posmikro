<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Response;
class TestMiddleware implements Middleware
{
 /**
  * Handle an incoming request.
  *
  * @param \Illuminate\Http\Request $request
  * @param \Closure $next
  * @return mixed
  */
    public function handle($request, Closure $next)
    {
    return $next($request)->header('Access-Control-Allow-Origin' , '*')
          ->header('Access-Control-Allow-Methods', 'GET')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
    }
}