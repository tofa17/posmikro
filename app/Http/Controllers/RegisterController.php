<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use App\User;
use App\Bases\Token;
use App\Bases\Franchisor;
use Schema;
use Illuminate\Database\Schema\Blueprint;
use Mail;
//use App\Http\Controllers\Snowfire\Beautymail\Beautymail;
//use Snowfire\Beautymail\Beautymail as Beautymail;

class RegisterController extends Controller
{
    public function index(){
        return $this->httpBadRequest('Unknown method');
    }
    public function registerFranchisor(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6'
        ]);
        if ($validator->fails()) {
            $message = $validator->errors();
            return $this->httpUnprocessableEntity($message);
        }
        $salt = hash('sha256', $request->input('email').$request->input('password').time() . mt_rand());
        $new_key = substr($salt, 0, 10);
        $password   = md5($request->input('password'));
        $email      = $request->input('email');
        $salt = hash('sha256', $email.$password.time() . mt_rand());
        $token = substr($salt, 0, 40);
        if ($this->konfigurasi($new_key)) {
            $user = new User();
            $user->email            = $email;
            $user->password         = $password;
            $user->key_id           = $new_key;
            $user->level            = 'franchisor';
            if ($user->save()) {
                $user_id = User::where('email', $email)->first();
                $franchisor = new Franchisor();
                $franchisor->id = $user_id->id;
                $franchisor->save();
                $tokens = new Token();
                $tokens->user_id = $user_id->id;
                $tokens->token   = $token;
                $tokens->save();
                /*    $data = array(
                        'name' => 'Pos Mikro',
                    );
                    Mail::send('emails.test', $data, function ($message) {
                        $message
                        ->to('tofariyadi17@gmail.com', 'Tofa Riyadi')
                        ->subject('Registration');
                    });*/
                $response = [
                    'email'    => $email,
                    'token'    => $token,
                    'level'    => $user_id->level
                    //'key_id'   => $data->key_id
                ];
                return $this->httpCreate($response);
            }
        }
        return $this->httpServerError();
    }
    public function deleteFranchisor($id){
        $this->_key = $id;
        if ($this->delete()) {
            return 'sukses';
        }
    }
    function delete(){
        $this->data();
        $this->_dropTransaksis();
        $this->_dropKasirs();
        $this->_dropMetaproduks();
        $this->_dropProduks();
        $this->_dropOutlets();
        $this->_dropFranchisees();
        $this->_dropBukakasirs();
        $this->_dropPetticashs();
        return true;
    }
    function data(){
        //$user = new User();
        User::where('key_id', $this->_key)->delete();
        //DB::table('users')->where('key_id', '=', $this->_key)->delete();
    }
    ///
    public function konfigurasi($key){
        $this->_key = $key;
        //print_r($this->_franchisees()); exit();
        if (!$this->_franchisees()) {
            return false;
        }
        if (!$this->_outlets()) {
            $this->_dropFranchisees();
            return false;
        }
        if (!$this->_kasirs()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            return false;
        }
        if (!$this->_produks()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            $this->_dropKasirs();
            return false;
        }
        if (!$this->_metaproduks()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            $this->_dropKasirs();
            $this->_dropProduks();
            return false;
        }
        if (!$this->_transaksis()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            $this->_dropKasirs();
            $this->_dropProduks();
            $this->_dropMetaproduks();
            return false;
        }
        if (!$this->_bukakasirs()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            $this->_dropKasirs();
            $this->_dropProduks();
            $this->_dropMetaproduks();
            $this->_dropTransaksis();
            return false;
        }
        if (!$this->_petticashs()) {
            $this->_dropFranchisees();
            $this->_dropOutlets();
            $this->_dropKasirs();
            $this->_dropProduks();
            $this->_dropMetaproduks();
            $this->_transaksis();
            $this->_dropBukakasirs();
            return false;
        }
        return true;
    }
    ///generate table
    private function _franchisees(){
        Schema::create($this->_key.'_franchisees', function (Blueprint $table){
            $table->increments('id')->unsigned()->nullable()->unique();
            //$table->integer('user_id')->unsigned()->nullable()->unique();
            $table->integer('franchise_id')->unsigned()->nullable();
            $table->unique(array('id', 'franchise_id'));
            $table->string('nama', 30);
            $table->string('telepon', 20);
            $table->string('alamat', 50);
            $table->timestamps();
            $table->foreign('id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('franchise_id')
                  ->references('id')
                  ->on('franchises')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
        return true;
    }
    private function _kasirs(){
        Schema::create($this->_key.'_kasirs', function (Blueprint $table){
            $table->increments('id')->unsigned()->nullable()->unique();
            $table->integer('outlet_id')->unsigned()->nullable()->unique();
            $table->unique(array('id', 'outlet_id'));
            //$table->integer('user_id')
            $table->string('nama', 30);
            $table->string('telepon', 20);
            $table->string('alamat', 50);
            $table->timestamps();
            $table->foreign('id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('outlet_id')
                  ->references('id')
                  ->on($this->_key.'_outlets')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
/*            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');*/
        });
        return true;
    }

    private function _outlets(){
        Schema::create($this->_key.'_outlets', function (Blueprint $table){
            $table->increments('id');
            $table->integer('franchises_id')->unsigned()->nullable();
            //$table->integer('kasir_id')->unsigned()->nullable()->unique();
            $table->string('namaoutlet', 10)->unique();
            $table->string('alamat', 50);
            $table->double('longitude');
            $table->double('latitude');
            $table->timestamps();
            $table->foreign('franchises_id')
                  ->references('id')
                  ->on($this->_key.'_franchisees')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
/*            $table->foreign('kasir_id')
                  ->references('id')
                  ->on($this->_key.'_kasirs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');*/
        });
        return true;
    }

    private function _produks(){
        Schema::create($this->_key.'_produks', function (Blueprint $table){
            $table->increments('id');
            $table->string('namaproduk', 30);
            $table->integer('harga');
            $table->integer('diskon');
            $table->string('image');
            $table->string('image_url');
            $table->timestamps();
        });
        return true;
    }

    private function _metaproduks(){
        Schema::create($this->_key.'_metaproduks', function (Blueprint $table){
            $table->increments('id');
            $table->integer('outlet_id')->unsigned()->nullable();
            $table->integer('produk_id')->unsigned()->nullable();
            $table->unique(array('outlet_id', 'produk_id'));
            $table->integer('harga');
            $table->integer('diskon');
            $table->timestamps();
            $table->foreign('outlet_id')
                  ->references('id')
                  ->on($this->_key.'_outlets')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('produk_id')
                  ->references('id')
                  ->on($this->_key.'_produks')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
        return true;
    }
    private function _transaksis(){
        Schema::create($this->_key.'_transaksis', function (Blueprint $table){
            $table->increments('id');
            $table->integer('outlet_id')->unsigned()->nullable();
            $table->integer('produk_id')->unsigned()->nullable();
            $table->string('kode_transaksi');
            $table->integer('tot_item');
            $table->integer('tot_harga');
            $table->date('tgl_order');
            $table->timestamps();
        });
        Schema::table($this->_key.'_transaksis', function($table) {
            $table->foreign('produk_id')
                  ->references('id')
                  ->on($this->_key.'_metaproduks')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('outlet_id')
                  ->references('id')
                  ->on($this->_key.'_outlets')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
        return true;
    }
    private function _bukakasirs(){
        Schema::create($this->_key.'_bukakasirs', function (Blueprint $table){
            $table->increments('id');
            $table->integer('saldo');
            $table->timestamps();
        });
        return true;
    }
    private function _petticashs(){
        Schema::create($this->_key.'_petticashs', function (Blueprint $table){
            $table->increments('id');
            $table->integer('jumlah');
            $table->string('keperluan');
            $table->timestamps();
        });
        return true;
    }

/*    private function _orders(){
        Schema::create($this->_key.'_orders', function (Blueprint $table){
            $table->increments('id');
            $table->integer('metaproduk_id')->unsigned()->nullable();
            $table->char('qty', 3);
            $table->dateTime('tgl_order');
            $table->timestamps();
        });
        Schema::table($this->_key.'_orders', function($table) {
            $table->foreign('metaproduk_id')
                  ->references('id')
                  ->on($this->_key.'_metaproduks')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
        return true;
    }*/
/*    private function _transaksi(){
        Schema::create($this->_key.'_transaksi', function (Blueprint $table){
            $table->increments('id');
            $table->integer('outlet_id')->unsigned()->nullable();
            $table->char('qty', 3);
            $table->dateTime('tgl_order');
            $table->timestamps();
        });
        Schema::table($this->_key.'_orders', function($table) {
            $table->foreign('metaproduk_id')
                  ->references('id')
                  ->on($this->_key.'_metaproduks')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
        return true;
    }*/
    //drop tabel
    private function _dropFranchisees()
    {
        Schema::drop($this->_key.'_franchisees');
    }
    private function _dropKasirs()
    {
        Schema::drop($this->_key.'_kasirs');
    }
    private function _dropOutlets()
    {
        Schema::drop($this->_key.'_outlets');
    }
    private function _dropProduks()
    {
        Schema::drop($this->_key.'_produks');
    }
    private function _dropMetaproduks()
    {
        Schema::drop($this->_key.'_metaproduks');
    }
    private function _dropTransaksis()
    {
        Schema::drop($this->_key.'_transaksis');
    }
    private function _dropBukakasirs()
    {
        Schema::drop($this->_key.'_bukakasirs');
    }
    private function _dropPetticashs()
    {
        Schema::drop($this->_key.'_petticashs');
    }
}
