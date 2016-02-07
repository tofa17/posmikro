<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Bases\Token;
use DB;
abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

/*    protected $remember_token;
    public function auth(){
        if (!empty($request->input('x-auth-login'))) {
            $remember_token = $request->input('x-auth-login');
            }
        else{
            $remember_token = $request->header('x-auth-login');  
        }        
    }*/
    protected function userID($request){
        if ($request->header('x-auth-login')!=null) {
            $next = $request->header('x-auth-login');
        }
        else{
            $next = $request->input('x-auth-login');
        }
        $token = Token::where('token', $next)->value('user_id');
        return $token;
    }
    protected function keyID($request){
        if ($request->header('x-auth-login')!=null) {
            $next = $request->header('x-auth-login');
        }
        else{
            $next = $request->input('x-auth-login');
        }
        $token = DB::table('users')
            ->join('tokens', 'users.id', '=', 'tokens.user_id')
            ->where('token', $next)
            ->value('key_id');
        return $token;
    }
    protected function outletID($request){
        $id_outlet = DB::table($this->keyID($request).'_kasirs')
                        ->where('id', $this->userID($request))
                        ->value('outlet_id');
        return $id_outlet;
    }
    protected function authToken($request){
        if ($request->header('x-auth-key')!=null) {
            $next = $request->header('x-auth-key');
        }
        else{
            $next = $request->input('x-auth-key');
        }
        return $next;
    }
    protected function httpCreate($msg = null){
        $response = 
                [
                    'status'    => true,
                    'code'      => 201,
                    'message'   => 'Data successfully saved',
                    'data'      => $msg
                ];
        return response()->json($response, 201);
    }
    protected function httpOk($msg=null){
    	$response = 
                [
                    'status'    => true,
                    'code'      => 200,
                    'data'		=> $msg,
                ];
        return response()->json($response, 200);
    }
    protected function httpBadRequest($msg=null){
    	$response = 
                [
                    'status'    => false,
                    'code'      => 400,
                    'message'   => 'Bad request',
                    'error'     => $msg
                ];
    	return response()->json($response, 400);
    }
    protected function httpNotFound(){
    	$response = 
                [
                    'status'    => false,
                    'code'      => 404,
                    'message'   => 'Data not found'
                ];
        return response()->json($response, 404);
    }
    protected function httpPaymentRequired($msg=null){
    	$response = 
                [
                    'status'    => false,
                    'code'      => 402,
                    'message'   => 'Field required',
                    'error'     => $msg
                ];
        return response()->json($response, 402);
    }
    protected function httpUnprocessableEntity($msg=null){
            $response = 
                [
                    'status'    => false,
                    'code'      => 422,
                    'message'   => 'Unprocessable entity',
                    'error'     => $msg
                ];
            return response()->json($response, 422);
    }
    protected function httpServerError(){
        $response = 
                [
                    'status'    => false,
                    'code'      => 500,
                    'message'   => 'Internal server error'
                ];
        return response()->json($response, 500);
    }
    protected function httpNotAllowed(){
        $response = 
                [
                    'status'    => false,
                    'code'      => 405,
                    'message'   => 'Method not allowed'
                ];
        return response()->json($response, 405);
    }
    protected function httpNotContent(){
        $response = 
                [
                    'status'    => false,
                    'code'      => 204,
                    'message'   => 'No content saved'
                ];
        return response()->json($response, 204);
    }
}
