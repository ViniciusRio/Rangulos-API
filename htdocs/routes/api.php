<?php

Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::get('events', 'EventController@index');
Route::get('event/{id}', 'EventController@show');
Route::put('event/{id}', 'EventController@update');
Route::post('events', 'EventController@store');
Route::delete('event/{id}', 'EventController@destroy');

Route::get('event/{id}/guests', 'EventController@guests');

Route::get('events/{id}/past', 'EventController@pastEvents');

