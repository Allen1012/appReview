<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');

});

Route::get('/test', 'TestController@index');


Route::group(['prefix' => 'home', 'namespace' => 'Home'], function()
{
    Route::get('/', 'IndexController@index');
    Route::get('/phpinfo',  function(){ phpinfo();exit;});
    Route::get('/home',  function(){ echo "23432423";exit;});
    Route::get('/home',  function(){ echo "23432423";exit;});
    Route::get('/phpinfo',  function(){ phpinfo();exit;});
    Route::get('/cache', function () {
        echo \Illuminate\Support\Facades\Cache::get('key');
    });

});
