<?php

namespace App\Http\Controllers;
use Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class KonfigurasiController extends Controller
{
    public function konfigurasi(){
        $key = $_GET['key'];
        $this->_key = $key;
        $this->_franchisee();
        $this->_kasir();
        $this->_outlet();
        $this->_barang();
        $this->_metabarang();
        $this->_orders();
    }
/*
    private function _key(){
        $key = '67890';
        return $key;
    }
*/
    private function _franchisee(){
        Schema::create($this->_key.'_franchisees', function (Blueprint $table){
            $table->increments('id_franchisee');
            $table->string('f_key');
            $table->integer('id_franchise')->unsigned()->nullable();
            $table->unique(array('f_key', 'id_franchise'));
            $table->string('nama_franchisee', 30);
            $table->string('alamat_franchisee', 30);
            $table->timestamps();
        });
        Schema::table($this->_key.'_franchisees', function($table) {
            $table->foreign('f_key')
                  ->references('email')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('id_franchise')
                  ->references('id_franchise')
                  ->on('franchises')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    private function _kasir(){
        Schema::create($this->_key.'_kasirs', function (Blueprint $table){
            $table->increments('id_kasir');
            $table->string('k_key')->unique();
            $table->string('nama_kasir', 30);
            $table->string('alamat_kasir', 30);
            $table->timestamps();
        });
        Schema::table($this->_key.'_kasirs', function($table) {
            $table->foreign('k_key')
                  ->references('email')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    private function _outlet(){
        Schema::create($this->_key.'_outlets', function (Blueprint $table){
            $table->increments('id_outlet');
            $table->string('id_f');
            $table->string('id_k')->unique();
            $table->string('alamat_outlet', 50);
            $table->double('longitude');
            $table->double('latitude');
            $table->string('maps');
            $table->timestamps();
        });
        Schema::table($this->_key.'_outlets', function($table) {
            $table->foreign('id_f')
                  ->references('f_key')
                  ->on($this->_key.'_franchisees')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('id_k')
                  ->references('k_key')
                  ->on($this->_key.'_kasirs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    private function _barang(){
        Schema::create($this->_key.'_barangs', function (Blueprint $table){
            $table->increments('id_barang');
            $table->string('namabarang', 30);
            $table->string('image');
            $table->string('image_url');
            $table->timestamps();
        });
    }

    private function _metabarang(){
        Schema::create($this->_key.'_metabarangs', function (Blueprint $table){
            $table->increments('id_metabarang');
            $table->integer('id_outlet')->unsigned()->nullable();
            $table->integer('id_barang')->unsigned()->nullable();
            $table->unique(array('id_outlet', 'id_barang'));
            $table->float('harga');
            $table->integer('diskon');
            $table->timestamps();
        });
        Schema::table($this->_key.'_metabarangs', function($table) {
            $table->foreign('id_outlet')
                  ->references('id_outlet')
                  ->on($this->_key.'_outlets')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('id_barang')
                  ->references('id_barang')
                  ->on($this->_key.'_barangs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    private function _orders(){
        Schema::create($this->_key.'_orders', function (Blueprint $table){
            $table->increments('id_orders');
            $table->integer('id_metabarang')->unsigned()->nullable();
            $table->char('qty', 3);
            $table->dateTime('tgl_order');
            $table->timestamps();
        });
        Schema::table($this->_key.'_orders', function($table) {
            $table->foreign('id_metabarang')
                  ->references('id_metabarang')
                  ->on($this->_key.'_metabarangs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }
}