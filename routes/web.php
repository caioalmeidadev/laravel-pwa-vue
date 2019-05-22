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

Auth::routes();

Route::middleware(['auth'])
        ->group(function(){
            Route::get('users','UsersController@index');
            Route::get('messages/{id}','ChatController@show');
            Route::post('messages','ChatController@store');

        });

Route::get('/home', 'HomeController@index')->name('home');
