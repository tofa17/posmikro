<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use DB;

class TransaksiController extends Controller
{
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function addTransaksi(Request $request){
        $validator = Validator::make($request->all(),
            [
                'produk_id'         => 'required|integer',
                'kode_transaksi'    => 'required',
                'tot_item'          => 'required|integer',
                'tot_harga'         => 'required|integer',
                'tgl_order'         => 'required'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $id_outlet = DB::table($this->keyID($request).'_kasirs')
                        ->where('id', $this->userID($request))
                        ->value('outlet_id');
        $data = DB::table($this->keyID($request).'_transaksis')
                    ->insert
                    ([
                        'outlet_id'         => $id_outlet,
                        'produk_id'         => $request->input('produk_id'),
                        'kode_transaksi'    => $request->input('kode_transaksi'),
                        'tot_item'          => $request->input('tot_item'),
                        'tot_harga'         => $request->input('tot_harga'),
                        'tgl_order'         => $request->input('tgl_order')
                    ]);
        if ($data) {
            return $this->httpCreate();
        }
    }
    public function todayRekap(Request $request){
        date_default_timezone_set("Asia/Jakarta"); 
        $date = date('Y-m-d');
        $validator = Validator::make($request->all(),
            [
                'outlet_id' => 'required|integer'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
/*        $id_outlet = DB::table($this->keyID($request).'_kasirs')
                        ->where('id', $this->userID($request))
                        ->value('outlet_id');*/
//        $id_outlet = $this->outletID($request);
        $id_outlet = $request->input('outlet_id');
        $transaksis = $this->keyID($request).'_transaksis';
        $produks = $this->keyID($request).'_produks';
        $data = DB::table($transaksis)
                    ->join($produks, $this->keyID($request).'_transaksis.produk_id', '=', $this->keyID($request).'_produks.id')
                    ->select('namaproduk as produk', 'harga', 'diskon', 
                        DB::raw('SUM(tot_item) as tot_item'),
                        DB::raw('SUM(tot_harga) as tot_harga'))
                    ->where('outlet_id', $id_outlet)
                    ->where('tgl_order', $date)
                    ->groupBy($produks = $this->keyID($request).'_transaksis.produk_id')
                    ->get();

        if ($data) {
            $total_harga = 0;
            foreach ($data as $data) {
                $diskon = $data->diskon;
                $harga = $data ->harga;
                $hargaDiskon = $harga;
                if($diskon > 0){
                    $hargaDiskon = $harga - (($diskon*$harga)/100);
                }
                $response[] = [
                        'produk'    => $data->produk,
                        'harga'     => $hargaDiskon,
                        'diskon'    => $data->diskon,
                        'tot_item'  => $data->tot_item,
                        'tot_harga' => $data->tot_harga
                    ];
                $total_harga = $total_harga + $data->tot_harga;
            }
            $response = 
                    [
                        'status'    => true,
                        'total'     => $total_harga,
                        'data'      => $response
                    ];
            return response()->json($response, 200);
        }
        return $this->httpNotfound();
    }
    public function reportRekap(Request $request){
        $validator = Validator::make($request->all(),
            [
                'outlet_id'  => 'required|integer',
                'start_date' => 'required|date',
                'end_date'   => 'required|date'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $id_outlet  = $request->input('outlet_id');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $transaksis = $this->keyID($request).'_transaksis';
        $produks = $this->keyID($request).'_produks';
        $data = DB::table($transaksis)
                    ->join($produks, $this->keyID($request).'_transaksis.produk_id', '=', 
                        $this->keyID($request).'_produks.id')
                    ->select('namaproduk as produk', 'harga', 'diskon', 
                        DB::raw('SUM(tot_item) as tot_item'),
                        DB::raw('SUM(tot_harga) as tot_harga'))
                    ->whereBetween('tgl_order', [$start_date, $end_date])
                    ->where('outlet_id', $id_outlet)
                    ->groupBy($produks = $this->keyID($request).'_transaksis.produk_id')
                    ->get();

        if ($data) {
            $total_harga = 0;
            foreach ($data as $data) {
                $diskon = $data->diskon;
                $harga = $data ->harga;
                $hargaDiskon = $harga;
                if($diskon > 0){
                    $hargaDiskon = $harga - (($diskon*$harga)/100);
                }
                $response[] = [
                        'produk'    => $data->produk,
                        'harga'     => $hargaDiskon,
                        'diskon'    => $data->diskon,
                        'tot_item'  => $data->tot_item,
                        'tot_harga' => $data->tot_harga
                    ];
                $total_harga = $total_harga + $data->tot_harga;
            }
            $response = 
                    [
                        'status'    => true,
                        'total'     => $total_harga,
                        'data'      => $response
                    ];
            return response()->json($response, 200);
        }
        return $this->httpNotfound();
    }
    
}
