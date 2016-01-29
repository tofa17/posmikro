<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Bases\Jenis;
class JenisController extends Controller
{
    function __construct(){
        $this->db = new Jenis();
    }
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    //menampilkan semua data jenis
    public function allJenis(){
        $response = array();
        $data = $this->db->get();
        if (!empty($data)) {
            foreach($data as $data){
                $response[] = [
                    'id'    => $data->id,
                    'jenis' => $data->jenisusaha
                ];
            }
            return $this->httpOk($response);
        }
        else{
            return $this->httpNotFound();
        }
    }
    //spesifik data jenis dari id jenis
    public function getJenis($id)
    {
        $data   =   Jenis::find($id);
        $id     =   (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest('tipe id tidak valid');
        }
        if (is_null($data)) {
            return $this->httpNotFound();
        }

        $response = array();
        $response = [
            'id'    => $data->id,
            'jenis' => $data->jenisusaha
        ];
        return $this->httpOk($response);
    }
    //menyimpan data jenis usaha
    public function saveJenis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenisusaha'     => 'required|unique:jenises'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $jenis = $request->input('jenisusaha');
        $this->db->jenisusaha = $jenis;
        if ($this->db->save()) {
            return $this->httpCreate();
        }
        return $this->httpServerError();
    }
    //mengedit data jenis usaha berdasarkan id jenis usaha
    public function updateJenis($id, Request $request){
        $id = (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest('tipe id tidak valid');
        }
        $cek = Jenis::find($id);
        if (is_null($cek)) {
            return $this->httpNotFound();
        }
        $validator = Validator::make($request->all(), [
            'jenisusaha'     => 'required|unique:jenises'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $jenis = $request->input('jenisusaha');
        $data = Jenis::where('id', $id)
                       ->update(['jenisusaha' => $jenis]);
        if ($data) {
            return $this->httpOk('data berhasil diupdate');
        }
        return $this->httpServerError();        
    }
    //menghapus data jenis usaha berdasarkan id
    public function deleteJenis($id){
        $id = (int) $id;
        if ($id <= 0) {
            return $this->httpBadRequest('tipe id tidak valid');
        }
        $cek = Jenis::find($id);
        if (is_null($cek)) {
            return $this->httpNotFound();
        }
        $data = Jenis::destroy($id);
        if ($data) {
            return $this->httpOk('data berhasil dihapus');
        }
        return $this->httpServerError();
    }    
}
