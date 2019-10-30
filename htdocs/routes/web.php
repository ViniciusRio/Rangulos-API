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
Route::get('events', 'EventController@index');
Route::get('event/{id}', 'EventController@show');
Route::put('event/{id}', 'EventController@update');
Route::post('events', 'EventController@store');
Route::delete('event/{id}', 'EventController@destroy');