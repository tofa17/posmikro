<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Barang;
use Validator;
use Storage;
use DB;
use Intervention\Image\ImageManager;
class ProdukController extends Controller
{
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
//ambil semua data barang
    public function allProduk(Request $request)
    {
            $data = DB::table($this->keyID($request).'_produks')->get();
            if (empty($data)) {
                return $this->httpNotFound();
            }
            foreach($data as $data){
                $response[] = [
                    'id'            => $data->id,
                    'namaproduk'    => $data->namaproduk,
                    'harga'         => $data->harga,
                    'diskon'        => $data->diskon,
                    'image'         => $data->image,
                    'imgage_url'    => $data->image_url,
                ];
            }
            return $this->httpOk($response);
    }
//ambil data barang berdasarkan id
    public function getProduk(Request $request, $id)
    {
        $data   =   DB::table($this->keyID($request).'_produks')
                        ->where('id', $id)->first();
        $id     =   (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest();
        }
        if (is_null($data)) {
            return $this->httpNotFound();
        }
        $response[] = [
            'id'         => $data->id,
            'namaproduk' => $data->namaproduk,
            'image'      => $data->image,
            'image_url'  => $data->image_url,
            'harga'      => $data->harga,
            'diskon'     => $data->diskon
        ];
        return $this->httpOk($response);
    }
//ambil data image berdasarkan url image
    public function getImage(Request $request, $image){
        $entry  =   DB::table($this->keyID($request).'_produks')->where('image', '=', $image)->first();
        //$file   =   Storage::disk('images')->get($entry->foto);
        $file = Storage::disk('images')->get($entry->image);
            return (new Response($file, 200))
                  ->header('Content-Type', $entry->image);
    }
//input data barang
    public function saveProduk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namaproduk'    => 'required|unique:'.$this->keyID($request).'_produks|min:3',
            'image'         => 'image|mimes:jpg,jpeg,png|max:2000',
            'harga'         => 'required|numeric',
            'diskon'        => 'integer|max:100'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $namaproduk = $request->input('namaproduk');
        $image      = $request->file('image');
        $harga      = $request->input('harga');
        $diskon     = $request->input('diskon');
        if ($image) {
            $salt = hash('sha256', $image->getClientOriginalName().time() . mt_rand());
            $link = substr($salt, 0, 40).".png";
            $resizedImage   =   $this->resize($image, 500, 500, $link);
            if(!$resizedImage)
            {
                return response()->json(['status' => false, 'msg' => 'gagal'], 204);
            }
            $image      = $link;
            $image_url  = url('api/image/'.$link);
/*            $data = DB::table($this->keyID($request).'_produks')->insert(
                    [
                        'namaproduk'    => $namaproduk,
                        'harga'         => $harga,
                        'diskon'        => $diskon,
                        'image'         => $image,
                        'image_url'     => $image_url
                    ]);
            return $this->httpServerError();*/
        }
        else{
            $image      = "avatar.png";
            $image_url  = url('api/logo/avatar.png');
        }
        $data = DB::table($this->keyID($request).'_produks')->insert(
                    [
                        'namaproduk'    => $namaproduk,
                        'harga'         => $harga,
                        'diskon'        => $diskon,
                        'image'         => $image,
                        'image_url'     => $image_url
                    ]);
        if ($data) {
            //insert to meta produk
            $outlet = DB::table($this->keyID($request).'_outlets')
                        ->get();
            if (!empty($outlet)) {
                $produk = DB::table($this->keyID($request).'_produks')
                                ->orderBy('id', 'desc')
                                ->value('id');
                foreach ($outlet as $key => $value) {
                    $input = DB::table($this->keyID($request).'_metaproduks')->insert
                                ([
                                    'outlet_id' => $value->id,
                                    'produk_id' => $produk,
                                    'harga'     => $harga,
                                    'diskon'    => $diskon
                                ]);
                }
            }
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }
//hapus barang with image
    public function deleteBarang(Request $request, $id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['status' => false, 'msg' => 'BAD REQUEST'], 400);
        }
        $cek = DB::table($this->keyID($request).'_produks')
                    ->where('id', $id)
                    ->first();
        if (empty($cek)) {
            return response()->json(['status' => false, 'msg' => 'data tidak ditemukan'], 404);
        }
        $data = DB::table($this->keyID($request))
                    ->where('id', $id)
                    ->delete();
        Storage::disk('images')->delete($cek->image);
        return response()->json(['status' => true, 'msg' => 'succes'], 200);
    }
//resize image
    private function resize($image, $width, $height, $imgurl)
    {
        try 
        {
            $imagePath      =   $image->getRealPath();
            $imageManager   =   new ImageManager();
            $img            =   $imageManager->make($imagePath);
            /*$img->resize(intval($width), null, function($constraint) {
                 $constraint->aspectRatio();
            });*/
            $img->resize(intval($width), intval($height));
            return $img->save(storage_path('app/image/'.$imgurl));
        }
        catch(Exception $e)
        {
            return response()->json(['status' => false, 'msg' => 'gagal menyimpan'], 204);
        }

    }
}