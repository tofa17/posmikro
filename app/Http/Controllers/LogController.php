<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;
use Auth;
use App\User;
use App\Bases\Token;
use App\Http\Controllers\Controller;
class LogController extends Controller
{
    function __construct()
    {
        $this->middleware('AuthLoginMiddleware', ['only' => ['logout']]);
    }
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->httpPaymentRequired($errors);
        }
        $email      = $request->input('email');
        $password   = $request->input('password');
        $data = array(
            'email'     => $email,
            'password'  => $password
        );
        if ($data = Auth::attempt($data)) {
            $salt = hash('sha256', $email.$password.time() . mt_rand());
            $token = substr($salt, 0, 40);
            
            $user_id = User::where('email', $email)->first();
            $tokens = new Token();
            $tokens->user_id = $user_id->id;
            $tokens->token   = $token;
            $tokens->save();
            if ($user_id) {
                $response = [
                    'email'    => $email,
                    'token'    => $token,
                    'level'    => $user_id->level
                    //'key_id'   => $data->key_id
                ];
                return $this->httpOk($response);
            }
        }
        return $this->httpNotFound();
    }
    public function logout(Request $request){
        if (!empty($request->input('x-auth-login'))) {
            $remember_token = $request->input('x-auth-login');
            }
        else{
            $remember_token = $request->header('x-auth-login');  
        }
        User::where('remember_token', $remember_token)
                    ->update(['remember_token' => null]);
        return $this->httpOk();
    }
}
