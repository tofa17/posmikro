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
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function addKasir(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|max:10',
            'outlet_id' => 'required|integer|unique:'.$this->keyID($request).'_kasirs'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $email      = $request->input('email');
        $password   = md5($request->input('password'));
        $outlet_id  = $request->input('outlet_id');
        //cek id outlet
        if ($outlet_id) {
            $cek = DB::table($this->keyID($request).'_outlets')
                        ->where('id', $outlet_id)
                        ->first();
            if (empty($cek)) {
                return $this->httpUnprocessableEntity('Id outlet not valid');
            }
        }
        $user = new User();
        $user->email    = $email;
        $user->password = $password;
        $user->key_id   = $this->keyID($request);
        $user->level    = 'kasir';
        if ($user->save()) {
            $id = User::where('email', $email)->value('id');
            $data = DB::table($this->keyID($request).'_kasirs')->insert(
                    [
                        'id'        => $id,
                        'outlet_id' => $outlet_id
                    ]);
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }
}
