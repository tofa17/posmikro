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
    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }
//ambil semua data barang
    public function allProduk(Request $request)
    {
        try{
            $response = array();
            $status = 200;
            $data = DB::table($this->keyID($request).'_produks')->get();
            foreach($data as $data){
                $response[] = [
                    'id'        => $data->id_barang,
                    'item_name' => $data->namabarang,
                    'img'       => $data->image,
                    'img_url'   => $data->image_url,
                    'harga'     => $data->harga,
                    'deskripsi' => $data->deskripsi
                ];
            }
        }
        catch (Exception $e){
            $status = 404;
        }
        finally{
            return $this->httpOk($response);
        }
    }
//ambil data barang berdasarkan id
    public function getBarang($id)
    {
        $data   =   Barang::where('id_barang', $id)->first();
        $id     =   (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest();
        }
        if (is_null($data)) {
            return $this->httpNotFound();
        }

        $response = array();
        $response = [
            'id'        => $data->id_barang,
            'item_name' => $data->namabarang,
            'img'       => $data->image,
            'img_url'   => $data->image_url,
            'harga'     => $data->harga
        ];
        return $this->httpOk($response);
    }
//ambil data image berdasarkan url image
    public function getImage($image){
        $entry  =   Barang::where('image', '=', $image)->firstOrFail();
        //$file   =   Storage::disk('images')->get($entry->foto);
        $file = Storage::disk('images')->get($entry->image);
            return (new Response($file, 200))
                  ->header('Content-Type', $entry->image);
    }
//input data barang
    public function saveBarang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'namabarang'    => 'required|unique:barangs|min:3',
            'image'         => 'image|mimes:jpg,jpeg,png|max:2000',
            'harga'         => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['status' => false,'msg' => 'tidak dapat diproses', 'error' => $errors], 402);
        }
        $image  = $request->file('image');
        $konten = new Barang();
        $konten->namabarang = $request->input('namabarang');
        $konten->harga = $request->input('harga');
        if ($image) {
            $salt = hash('sha256', $image->getClientOriginalName().time() . mt_rand());
            $link = substr($salt, 0, 40).".png";
            $resizedImage   =   $this->resize($image, 500, 500, $link);
            if(!$resizedImage)
            {
                return response()->json(['status' => false, 'msg' => 'gagal'], 204);
            }
            $konten->image   = $link;
            $konten->image_url    = url('api/image/'.$link);
            /*$data =$konten->save();
            if ($data) {
                return response()->json(['status' => true, 'msg' => 'data berhasil disimpan'], 201);
            }*/
        }
        if ($konten->save()) {
            return response()->json(['status' => true, 'msg' => 'data berhasil disimpan'], 201);
        }
        

    }
//hapus barang with image
    public function deleteBarang($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['status' => false, 'msg' => 'BAD REQUEST'], 400);
        }
        $cek = Barang::find($id);
        if (is_null($cek)) {
            return response()->json(['status' => false, 'msg' => 'data tidak ditemukan'], 404);
        }
        $data = Barang::destroy($id);
        Storage::disk('images')->delete($cek->foto);
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