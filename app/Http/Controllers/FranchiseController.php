<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Bases\Franchise;
use App\Bases\Franchisor;
use App\Bases\Jenis;
use Intervention\Image\ImageManager;
use Storage;
use Image;

class FranchiseController extends Controller
{
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function allFranchise(Request $request){
        $franchises = Franchise::where('franchisor_id', $this->userID($request))->get();
        if (empty($franchises)) {
            return $this->httpNotFound();
        }
        return $this->httpOk($franchises);
    }
    public function saveFranchise(Request $request)
    {
/*        $validator = Validator::make($request->all(), [
            'jenis'         => 'required|integer',
            'namausaha'     => 'required|unique:franchises',
            'telepon'       => 'required',
            'alamat'        => 'required|max:50'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $jenis      = $request->input('jenis');
        $namausaha  = $request->input('namausaha');
        $telepon    = $request->input('telepon');
        $alamat     = $request->input('alamat');
        $image      = $request->input('logo');
*/

        $validator = Validator::make($request->all(), [
            'logo' => 'image|mimes:jpg,jpeg,png|max:2000'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $jenis      = $request->header('jenis');
        $namausaha  = $request->header('namausaha');
        $telepon    = $request->header('telepon');
        $alamat     = $request->header('alamat');
        $image      = $request->file('logo');
        $cek = Jenis::where('id', $jenis)->first();
        if (empty($cek)) {
            return $this->httpUnprocessableEntity('Id jenis tidak valid');
        }
        $db = new Franchise();
        $db->jenis          = $jenis;
        $db->namausaha      = $namausaha;
        $db->franchisor_id  = $this->userID($request);
        $franchisor = new Franchisor();
        $franchisor->where('id', $this->userID($request))
                   ->update(['alamat' => $alamat, 'telepon' => $telepon]);
        if ($image) {
            //$image ='data:image/jpeg;base64,' .base64_encode($image);
            /*header("Content-type: image/jpeg");
            $image = Image::make($image);
            $image = $image->response('jpg', 70);*/
            $salt = hash('sha256', time() . mt_rand());
            $link = substr($salt, 0, 40).".png";
            $resizedImage   =   $this->resize($image, 500, 500, $link);
            if(!$resizedImage)
            {
                return response()->json(['status' => false, 'msg' => 'gagal'], 204);
            }
            $db->logo      = $link;
            $db->logo_url  = url('api/logo/'.$link);
        }
        $db->logo      = "avatar.png";
        $db->logo_url  = url('api/logo/avatar.png');
        if ($db->save()) {
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }
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
            return $img->save(storage_path('app/logo/'.$imgurl));
        }
        catch(Exception $e)
        {
            return response()->json(['status' => false, 'msg' => 'gagal menyimpan'], 204);
        }

    }
}
