<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Validator;
use DB;
class KasirController extends Controller
{
    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function addKasir(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $password   = bcrypt($request->input('password'));
        $email      = $request->input('email');
        $user = new User();
        $user->email    = $email;
        $user->password = $password;
        $user->key_id   = $this->keyID($request);
        $user->level    = 'kasir';
        if ($user->save()) {
            $id = User::where('email', $email)->value('id');
            $data = DB::table($this->keyID($request).'_kasirs')->insert(
                    [
                        'id'  => $id
                    ]);
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }
}
