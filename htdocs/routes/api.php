<?php

Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::get('events', 'EventController@index');
Route::get('event/current', 'EventController@currentEvent');
Route::get('events/my', 'EventController@myEvents');
Route::get('event/{id}', 'EventController@show');
Route::put('event/{id}', 'EventController@update');
Route::post('events', 'EventController@store');
Route::delete('event/{id}', 'EventController@destroy');

Route::get('event/{id}/guests', 'EventController@guests');

Route::get('events/past', 'EventController@pastEvents');



Route::post('guests', 'GuestController@store');
Route::delete('guest/{idEvent}', 'GuestController@destroy');
