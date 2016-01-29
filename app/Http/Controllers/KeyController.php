<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;
use App\Http\Controllers\Controller;
use App\Bases\Key;
class KeyController extends Controller
{
    public function allKey()
    {
        try{
            $response = [
                'keys' => []
            ];
            $status = 200;
            $data = Key::all();
            foreach($data as $data){
                $response['keys'][] = [
                    'id'            => $data->id,
                    'key'           => $data->key,
                    'ignore_limits' => $data->ignore_limits,
                    'status'        => $data->status
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
    public function createnewKey(Request $request){
        $salt = hash('sha256', time() . mt_rand());
        $new_key = substr($salt, 0, 40);
        $key = new key();
        $key->key = $new_key;
        if ($key->save()) {
            return $this->httpCreate();
        }
    }
    public function deleteKey($id){ 
        if (empty(',')) {
            $key = Key::find($id);
            if (is_null($key)) {
                return $this->notFound();
            }
            $key = Key::destroy($id);
            if ($key) {
                return $this->httpOk();
            }
        }
        else{
            $key = Key::find(explode(',', $id));
            if (is_null($key)) {
                return $this->httpNotFound();
            }
            $key = Key::destroy(explode(',', $id));
            if ($key) {
                return $this->httpOk();
            }
        }
    }
}
