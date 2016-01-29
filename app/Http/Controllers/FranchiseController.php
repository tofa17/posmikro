<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Bases\Franchise;
use App\Bases\Franchisor;
use Intervention\Image\ImageManager;
use Storage;

class FranchiseController extends Controller
{
/*    function __construct(){
        $this->middleware('AuthKeyMiddleware');
    }*/
	public function index(){
        return $this->httpBadRequest('Unknown method');
	}
	public function saveFranchise(Request $request)
    {
        $validator = Validator::make($request->all(), [
        	'jenis'			=> 'required',
            'logo'     		=> 'image|mimes:jpg,jpeg,png|max:2000',
            'namausaha'    	=> 'required|unique:franchises',
            'telepon'		=> 'required',
            'alamat'		=> 'required|max:50'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }

        //print_r($this->userID($request)); exit();
        //$input = $request->all();
        //print_r($this->keyID($request)); exit();
        $jenis 		= $request->input('jenis');
        $namausaha 	= $request->input('namausaha');
        $telepon	= $request->input('telepon');
        $alamat		= $request->input('alamat');
        $image      = $request->file('logo');
        $db = new Franchise();
        $db->jenis 			= $jenis;
        $db->namausaha 		= $namausaha;
        $db->franchisor_id 	= $this->userID($request);
        $franchisor = new Franchisor();
        $franchisor->where('id', $this->userID($request))
                   ->update(['alamat' => $alamat, 'telepon' => $telepon]);
        if ($image) {
            $salt = hash('sha256', $image->getClientOriginalName().time() . mt_rand());
            $link = substr($salt, 0, 40).".png";
            $resizedImage   =   $this->resize($image, 500, 500, $link);
            if(!$resizedImage)
            {
                return response()->json(['status' => false, 'msg' => 'gagal'], 204);
            }
            $db->logo   = $link;
            //$konten->image_url    = url('api/image/'.$link);
        }
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
