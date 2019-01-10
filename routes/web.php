<?php


Route::get('/', 'PagesController@index');

Route::get('download/{id}', 'PagesController@getFile');