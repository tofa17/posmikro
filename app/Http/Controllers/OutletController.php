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
                'alamat'        => 'required|max:50',
                'franchises_id' => 'integer'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $alamat          = $request->input('alamat');
        $franchises_id   = $request->input('franchises_id');
        $count = DB::table($this->keyID($request).'_outlets')->count();
        if (is_null($count)) {
            $count = 1;
        }
        $count = $count + 1;
        if ($franchises_id) {
            //cek franchisee
            $cek = DB::table($this->keyID($request).'_franchisees')
                        ->where('id', $franchises_id)
                        ->get();
            if (empty($cek)) {
                return $this->httpUnprocessableEntity('Id franchises not valid');
            }
            //insert data outlet with franchise
            $insert = DB::table($this->keyID($request).'_outlets')->insert(
                    [
                        'franchises_id'  => $franchises_id,
                        'alamat'         => $alamat,
                        'namaoutlet'     => 'OT'.$count
                    ]);
        }
        else{
            $insert = DB::table($this->keyID($request).'_outlets')->insert(
                    [
                        'alamat'        => $alamat,
                        'namaoutlet'    => 'OT'.$count
                    ]);
        }
        if (!$insert) {
            return $this->httpServerError();
        }
        //abil id outlet yang baru dibuat
        $outlet_id = DB::table($this->keyID($request).'_outlets')
                        ->orderBy('id', 'desc')
                        ->value('id');
        //ambil data produk
        $data = DB::table($this->keyID($request).'_produks')
                ->get();
        //input data produk
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $input = DB::table($this->keyID($request).'_metaproduks')->insert
                            ([
                                'outlet_id' => $outlet_id,
                                'produk_id' => $value->id,
                                'harga'     => $value->harga,
                                'diskon'    => $value->diskon
                            ]);
            }
            if ($input) {
                return $this->httpCreate();
            }
        }
        return $this->httpCreate();
    }

}
