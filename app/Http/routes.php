<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|$_SERVER['REQUEST_METHOD']=='POST'
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/key', ['uses' => 'KeyController@allKey']);
Route::post('/key', ['uses' => 'KeyController@createnewKey']);
Route::delete('/key/{id}', ['uses' => 'KeyController@deleteKey']);
// Route::get('auth/login', 'LogController@index');
// Route::post('auth/login', 'LogController@login');
// Route::get('auth/logout', 'LogController@logout');
// Route::post('auth/register', 'LogController@register');


Route::group(['prefix' => 'api', 'middleware' => 'CekMiddleware'], function()
{
    Route::group(['prefix' => 'auth'], function()
    {
        Route::group(['prefix' => 'register'], function()
        {
            Route::get('', ['uses' => 'RegisterController@index']);
            Route::post('', ['uses' => 'RegisterController@registerFranchisor']);
            Route::put('', ['uses' => 'RegisterController@index']);
            Route::patch('', ['uses' => 'RegisterController@index']);
            Route::delete('{id}', ['uses' => 'RegisterController@deleteFranchisor']);
        });
        Route::group(['prefix' => 'login'], function()
        {
            Route::get('', ['uses' => 'LogController@index']);
            Route::post('', ['uses' => 'LogController@login']);
        });
        Route::group(['prefix' => 'logout'], function()
        {
            Route::get('', ['uses' => 'LogController@index']);
            Route::post('', ['uses' => 'LogController@logout']);
        });
    });
//jenis usaha
    Route::group(['prefix' => 'konfig'], function()
    {
        Route::group(['prefix' => 'jenisusaha'], function()
        {
            Route::get('', ['uses' => 'JenisController@allJenis']);
            Route::get('{id}', ['uses' => 'JenisController@getJenis']);
            Route::post('', ['uses' => 'JenisController@saveJenis']);
            Route::put('{id}', ['uses' => 'JenisController@updateJenis']);
            Route::delete('{id}', ['uses' => 'JenisController@deleteJenis']);
        });
    });
//produk
    Route::group(['prefix' => 'produk'], function()
    {
        Route::get('', ['uses' => 'ProdukController@allProduk']);
        Route::get('{id}', ['uses' => 'ProdukController@getProduk']);
        Route::post('', ['uses' => 'ProdukController@saveProduk']);
        Route::put('{id}', ['uses' => 'ProdukController@updateProduk']);
        Route::delete('{id}', ['uses' => 'ProdukController@deleteProduk']);
    });
//franchise
/*    Route::group(['prefix' => 'franchise'], function()
    {
        Route::get('', ['uses' => 'FranchiseController@index']);
        Route::get('{id}', ['uses' => 'FranchiseController@index']);
        Route::post('', ['uses' => 'FranchiseController@saveFranchise']);
        Route::put('{id}', ['uses' => 'FranchiseController@index']);
        Route::delete('{id}', ['uses' => 'FranchiseController@index']);
    });*/
//akses admin with auth middleware
    Route::group(['prefix' => 'control-admin'], function()
    {
        Route::post('/login', ['uses' => 'SadminController@login']);
        Route::get('data', ['uses' => 'SadminController@allAdmin']);
        Route::get('data/{id}', ['uses' => 'SadminController@getAdmin']);
        Route::post('data', ['uses' => 'SadminController@saveAdmin']);
        Route::put('data/{id}', ['uses' => 'SadminController@updateEmail']);
        Route::delete('data/{id}', ['uses' => 'SadminController@deleteAdmin']);
//create new auth key
        Route::get('/key', ['uses' => 'KeyController@allKey']);
        Route::post('/key', ['uses' => 'KeyController@createnewKey']);
        Route::delete('/key/{id}', ['uses' => 'KeyController@deleteKey']);
//migrate table
        Route::get('/migrate', ['uses' => 'KonfigurasiController@Konfigurasi']);
    });
//akses franchisor with auth middleware
    Route::group(['prefix' => 'franchisor', 'middleware' => 'FranchisorMiddleware'], function()
    {
        //franchise
        Route::group(['prefix' => 'franchise'], function()
        {
            Route::get('', ['uses' => 'FranchiseController@index']);
            Route::get('{id}', ['uses' => 'FranchiseController@index']);
            Route::post('', ['uses' => 'FranchiseController@saveFranchise']);
            Route::put('{id}', ['uses' => 'FranchiseController@index']);
            Route::delete('{id}', ['uses' => 'FranchiseController@index']);
        });
        //jeniausaha
        Route::group(['prefix' => 'jenisusaha'], function()
        {
            Route::get('', ['uses' => 'JenisController@allJenis']);
            Route::get('{id}', ['uses' => 'JenisController@getJenis']);
            Route::post('', ['uses' => 'JenisController@saveJenis']);
            Route::put('{id}', ['uses' => 'JenisController@updateJenis']);
            Route::delete('{id}', ['uses' => 'JenisController@deleteJenis']);
        });
        //outlet
        Route::group(['prefix' => 'outlet'], function()
        {
            Route::post('', ['uses' => 'OutletController@addOutlet']);
            Route::get('', ['uses' => 'OutletController@allOutlet']);
            Route::get('{id}', ['uses' => 'OutletController@getOutlet']);
            Route::put('', ['uses' => 'OutletController@index']);
            Route::delete('', ['uses' => 'OutletController@index']);
        });
        //kasir
        Route::group(['prefix' => 'kasir'], function()
        {
            Route::post('', ['uses' => 'KasirController@addKasir']);
            Route::get('', ['uses' => 'KasirController@index']);
        });
        //transaksi

    });
    ////////sesi kasir
    Route::group(['prefix' => 'kasir', 'middleware' => 'AuthKeyMiddleware'], function()
    {
        Route::group(['prefix' => 'transaksi'], function()
        {
            Route::post('', ['uses' => 'TransaksiController@addTransaksi']);
            Route::get('', ['uses' => 'TransaksiController@index']);
            Route::put('', ['uses' => 'TransaksiController@index']);
            Route::delete('', ['uses' => 'TransaksiController@index']);
        });
        Route::group(['prefix' => 'rekap'], function()
        {
            Route::post('/today', ['uses' => 'TransaksiController@todayRekap']);
            Route::get('/today', ['uses' => 'TransaksiController@index']);
            Route::put('/today', ['uses' => 'TransaksiController@index']);
            Route::delete('/today', ['uses' => 'TransaksiController@index']);
            Route::post('/report', ['uses' => 'TransaksiController@reportRekap']);
            Route::get('/report', ['uses' => 'TransaksiController@index']);
            Route::put('/report', ['uses' => 'TransaksiController@index']);
            Route::delete('/report', ['uses' => 'TransaksiController@index']);
            Route::get('', ['uses' => 'TransaksiController@todayRekap']);
            Route::put('', ['uses' => 'TransaksiController@index']);
            Route::delete('', ['uses' => 'TransaksiController@index']);
        });
    });
//get image
    Route::group(['prefix' => 'image'], function()
    {
        Route::get('{image}', ['uses' => 'BarangController@getImage']);
    });
});