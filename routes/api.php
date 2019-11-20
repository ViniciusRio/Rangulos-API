<?php

Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');
Route::get('logout', 'AuthController@logout');
Route::get('user', 'AuthController@show');

Route::get('events', 'EventController@index');
Route::get('events/current', 'EventController@currentEvents');
Route::get('events/my', 'EventController@myEvents');
Route::get('event/{id}', 'EventController@show');
Route::put('event/{id}', 'EventController@update');
Route::post('events', 'EventController@store');
Route::delete('event/{id}', 'EventController@destroy');

Route::get('event/{id}/guests', 'EventController@guests');

Route::get('events/past', 'EventController@pastEvents');

Route::post('guest/{id}', 'GuestController@store');
Route::delete('guest/{idEvent}', 'GuestController@destroy');
Route::post('guest/{idEvent}/rate', 'GuestController@rate');
Route::post('event/pay', 'EventController@payEvent');
Route::post('event/{id}/restore', 'EventController@restore');
Route::post('event/{id}/upload', 'EventController@upload');
Route::get('event/{id}/image', 'EventController@getImage');






