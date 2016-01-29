<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
class SadminController extends Controller
{
    function __construct()
    {
        //$this->middleware('AuthLoginMiddleware', ['only' => ['foo', 'bar']]); //selected method
        $this->middleware('AuthLoginMiddleware', ['except' => ['login']]) ;
    }
    public function allAdmin()
    {
        try{
            $response=[];
            $data = User::all();
            foreach($data as $data){
                $response[] = [
                    'email'    => $data->email,
                    'token'    => $data->remember_token
                ];
            }
        }
        catch (Exception $e){
            $response = null;
        }
        finally{
            return $this->httpOk($response);
        }
    }
    public function getAdmin($id)
    {
        $data   =   User::find($id);
        $id     =   (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest("Request not available");
        }
        if (is_null($data)) {
            return $this->httpNotFound();
        }
        $response[] = [
            'email'    => $data->email,
            'token'    => $data->remember_token
        ];

        return $this->httpOk($response);
    }
    public function saveAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users|min:5',
            'password'  => 'required|confirmed|min:5'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $password   = bcrypt($request->input('password'));
        $user       = new User();
        $user->email            = $request->input('email');
        $user->password         = $password;
        if ($user->save()) {
            return $this->httpCreate();
        }
    }

    public function updateEmail(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->httpNotFound();
        }

        $user = User::where('email', $request->email)
                      ->where('id', '!=', $id)->first();

        if (!is_null($user)) {
            $message = "email has already been taken";
            return $this->httpUnprocessableEntity($message);
        }

        $input  = $request->all();
        $user   = User::findOrFail($id);

        if ($user->update($input)) {
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }

    public function deleteAdmin($id){ 
        if (empty(',')) {
            $user = User::find($id);
            if (is_null($user)) {
                return $this->notFound();
            }
            $user = User::destroy($id);
            if ($user) {
                return $this->httpOk();
            }
        }
        else{
            $user = User::find(explode(',', $id));
            if (is_null($user)) {
                return $this->httpNotFound();
            }
            $user = User::destroy(explode(',', $id));
            if ($user) {
                return $this->httpOk();
            }
        }
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
            $new_key = substr($salt, 0, 40);
            User::where('email', $email)
                    ->update(['remember_token' => $new_key]);
            $data = User::where('email', $email)->get();
            if ($data) {
                foreach ($data as $data) {
                    $response[] = [
                        'id'       => $data->id,
                        'email'    => $data->email,
                        'token'    => $data->remember_token
                    ];
                }
                return $this->httpOk($response);
            }
        }
        return $this->httpNotFound();
    }
}
