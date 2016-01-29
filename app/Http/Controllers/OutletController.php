<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use DB;
class OutletController extends Controller
{
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function allOutlet(Request $request){
        $data = DB::table($this->keyID($request).'_outlets')->get();
        if ($data) {
            return $this->httpOk($data);
        }
        return $this->httpNotFound();
    }
    public function getOutlet(Request $request, $id){
        $id = (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest('Tipe integer');
        }
        $data = DB::table($this->keyID($request).'_outlets')
                    ->where('id', $id)
                    ->get();
        if ($data) {
            return $this->httpOk($data);
        }
        return $this->httpNotFound();
    }
    public function addOutlet(Request $request){
        $validator = Validator::make($request->all(),
            [
                // 'email'     => 'required|email|unique:users',
                // 'password'  => 'required|min:6|max:10|alpha_num',
                'alamat'       => 'required|max:50|alpha_num',
                'franchise_id' => 'integer'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $alamat = $request->input('alamat');
        $franchise_id = $request->input('franchise_id');
        //print_r($kasir_id); exit();
        // print_r($this->keyID($request)); exit();

        if ($franchise_id) {
            $data = DB::table($this->keyID($request).'_outlets')->insert(
                    [
                        'franchise_id'  => $franchise_id,
                        'alamat'        => $alamat
                    ]);
        }
        $data = DB::table($this->keyID($request).'_outlets')->insert(
                    [
                        'alamat'        => $alamat
                    ]);
        if (!$data) {
            return $this->httpServerError();
        }
        $outlet_id = DB::table($this->keyID($request).'_outlets')->value('id')->desc();
        print_r($outlet_id); exit();
        $data = DB::table($this->keyID($request).'_produks')
                ->select('id')
                ->get();
        foreach ($data as $key => $value) {
            DB::table($this->keyID($request).'_metaproduks')->insert
            ([
                'produk_id' => $data
            ]);
        }

        return $this->httpCreate();
    }

}
